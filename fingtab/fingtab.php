<?php
/**
 * Fing network scan plugin
 * version 0.2
 *
 * Written by Florian Pfaff
 *
 * Inspired by livePTR and rc_pingreply
 *
 * The purpose of this plugin is to scan networks which cannot be reached directly.
 * It uses SSH to connect to a host which has overlook fing installed and uses it to scan the defined network
 *
 * Requirements:
 *   - SSH client on racktables server
 *   - Overlook Fing http://www.overlooksoft.com/download installed on a host in the network you want to scan
 *   - the host with overlook fing has to be reachable via SSH
 *   - key SSH authentication needs to be configured
 *   - $FING_ssh_binary and $FING_settings need to be configured in the secret.php
 *
 */

// register fing tab
$tab['ipv4net']['fing'] = 'Fing Scan';
$tabhandler['ipv4net']['fing'] = 'FingTab';
$ophandler['ipv4net']['fing']['importFingData'] = 'importFingData';



/**
 * Execute a command return exit code
 * provide STDOUT and STDERR
 */
function my_shell_exec($cmd, &$stdout=null, &$stderr=null) {
    $proc = proc_open($cmd, array(
        1 => array('pipe','w'),
        2 => array('pipe','w'),
    ),$pipes);
    $stdout = stream_get_contents($pipes[1]);
    fclose($pipes[1]);
    $stderr = stream_get_contents($pipes[2]);
    fclose($pipes[2]);
    return proc_close($proc);
}


/**
 * Parse a csv returned by fing and return an array with the parsed information
 */
function parse_fing_csv($csv){
    $known_ips = array();
    foreach (preg_split("/((\r?\n)|(\r\n?))/", $csv) as $line) {
        $fields= explode(";", $line);

        if ($fields[0]) {
            $tmp_array = array(
                "ip" => $fields[0],
                "state" => $fields[2],
                "timestamp" => $fields[3],
                "hostname" => $fields[4],
                "mac" => $fields[5],
                "vendor" => array_key_exists(6,$fields) ? $fields[6] : ""
            );
            $known_ips[$fields[0]] = $tmp_array;
        }
    }
    return $known_ips;
}


/**
 * get hostname, state and mac/vendor information for a given ip
 */
function get_fing_info($ip,$known_ips){
    $hostname = "";
    $state = "down";
    $mac_vendor ="";

    if (array_key_exists($ip, $known_ips))
    {
        $hostname = $known_ips[$ip]["hostname"];
        $state = strtoupper($known_ips[$ip]["state"]);
        $mac_vendor = $known_ips[$ip]["mac"];
        if ($known_ips[$ip]["vendor"])
            $mac_vendor = $mac_vendor." (".$known_ips[$ip]["vendor"].")";
    }
    $result = array($hostname, $state, $mac_vendor);
    return $result;
}


/**
 * Return the amount of hosts which are up
 */
function get_fing_up_count($known_ips)
{
    $count = 0;
    foreach ($known_ips as $cip)
    {
        if (strtoupper($cip["state"]) == "UP")
            $count++;
    }
    return $count;
}

/**
 * render error message
 */
function render_fing_error($title,$msg)
{
    echo "<div class='msg_error'>";
    echo "<h2>${title}</h2>";
    echo $msg;
    echo "</div>";
}

/**
 * Class FingException
 */
class FingException extends Exception {}


/**
 * get fing settings array for a specific subnet
 */
function get_fing_settings($ip,$mask)
{
    global $FING_settings;
    if (!$FING_settings)
        throw new FingException("Fing configuration not found. Please make sure \$FING_settings is configured in the secret.php");

    $net = $ip."/".$mask;
    if (!array_key_exists($net,$FING_settings))
        throw new FingException("No matching Fing configuration found for network ${net}");

    return $FING_settings[$net];
}


/**
 * scan a subnet using fing
 */
function get_fing_scan($ip,$mask)
{
    global $FING_ssh_binary;

    if (!$FING_ssh_binary)
        throw new FingException("\$FING_ssh_binary is not defined in secret.php");

    $settings = get_fing_settings($ip,$mask);

    $fing_gw = $settings["gateway"];
    $user = $settings["user"];
    $sudo = $settings["gateway"] ? "sudo" : "";
    $id_file = $settings["id_file"];
    $cmd = "$FING_ssh_binary $user@$fing_gw -v -i $id_file -o 'PasswordAuthentication no' $sudo  fing -r 1 -n $ip/$mask -o table,csv,console --silent";

    $ret_val = my_shell_exec("$cmd", $std_out, $std_err);
    if ($ret_val>0)
        throw new FingException("Error executing fing:<br><pre>Command: $cmd\n\n exit code: $ret_val\n\n STDERR: $std_err\n\n STDOUT: $std_out\n</pre>");

    $known_ips = parse_fing_csv($std_out);

    return $known_ips;
}

/**
 *
 * address allocation setting (copied from interface.php)
 *
 */




/**
 *
 * Fing tab handler
 *
 */
function FingTab($id)
{
    $can_import = permitted (NULL, NULL, 'importFingData');


    //
    // allocation settings
    //
    // address allocation code, IPv4 networks view
    $aac_left = array
    (
        'regular' => '',
        'virtual' => '<span class="aac-left" title="Loopback">L:</span>',
        'shared' => '<span class="aac-left" title="Shared">S:</span>',
        'router' => '<span class="aac-left" title="Router">R:</span>',
        'point2point' => '<span class="aac-left" title="Point-to-point">P:</span>',
    );

    //
    // header
    //
    global $pageno, $tabno;

    $maxperpage = getConfigVar('IPV4_ADDRS_PER_PAGE');
    $range = spotEntity('ipv4net', $id);
    loadIPAddrList($range);
    echo "<center><h1>${range['ip']}/${range['mask']}</h1><h2>${range['name']}</h2></center>\n";


    //
    // execute fing
    //
    try {
        $known_ips = get_fing_scan($range['ip'], $range['mask']);
        $fing_cfg = get_fing_settings($range['ip'], $range['mask']);
        $fing_gw = $fing_cfg["gateway"];
    } catch (FingException $e) {
        render_fing_error("Could not get network scan via fing:",$e->getMessage());
        return FALSE;
    }

    echo "<table class=objview border=0 width='100%'><tr><td class=pcleft>";
    startPortlet ('overlook fing (via: '.$fing_gw.')');

    //
    // pagination
    //
    if (isset($_REQUEST['pg']))
        $page = $_REQUEST['pg'];
    else
        $page = 0;
    $startip = ip4_bin2int ($range['ip_bin']);
    $endip = ip4_bin2int (ip_last ($range));
    $numpages = 0;
    if ($endip - $startip > $maxperpage)
    {
        $numpages = ($endip - $startip) / $maxperpage;
        $startip = $startip + $page * $maxperpage;
        $endip = $startip + $maxperpage - 1;
    }
    echo "<center>";
    if ($numpages)
        echo '<h3>' . ip4_format (ip4_int2bin ($startip)) . ' ~ ' . ip4_format (ip4_int2bin ($endip)) . '</h3>';
    for ($i=0; $i<$numpages; $i++)
        if ($i == $page)
            echo "<b>$i</b> ";
        else
            echo "<a href='".makeHref(array('page'=>$pageno, 'tab'=>$tabno, 'id'=>$id, 'pg'=>$i))."'>$i</a> ";
    echo "</center>";

    if ($can_import)
    {
        printOpFormIntro ('importFingData', array ('addrcount' => ($endip - $startip + 1)));
        $box_counter = 1;
    }


    echo "<table class='widetable' border=0 cellspacing=0 cellpadding=5 align='center'>\n";
    echo "<tr><th class='tdleft'>address</th><th class='tdleft'>state</th><th class='tdleft'>current name</th><th class='tdleft'>DNS name</th><th class='tdleft'>MAC</th><th class='tdleft'>Allocation</th>";
    if ($can_import)
        echo '<th>import</th>';
    echo "</tr>\n";


    //
    // Loop through all IPs
    //
    $cnt_match = $cnt_missing = $cnt_mismatch = $cnt_total = 0;
    for ($ip = $startip; $ip <= $endip; $ip++)
    {
        $cnt_total++;
        $print_cbox = FALSE;
        $ip_bin = ip4_int2bin($ip);
        $addr = isset ($range['addrlist'][$ip_bin]) ? $range['addrlist'][$ip_bin] : array ('name' => '', 'reserved' => 'no');
        $straddr = ip4_format ($ip_bin);

        list($fing_hostname, $fing_state, $fing_mac_vendor) = get_fing_info($straddr, $known_ips);
        $ip_is_up = strtoupper($fing_state) == "UP" ? TRUE : FALSE;

        if ($can_import)
        {
            echo "<input type=hidden name=addr_${cnt_total} value=${straddr}>\n";
            echo "<input type=hidden name=descr_${cnt_total} value=${fing_hostname}>\n";
            echo "<input type=hidden name=rsvd_${cnt_total} value=${addr['reserved']}>\n";
        }

        $skip_dns_check = FALSE;
        echo "<tr";
        // Ignore network and broadcast addresses
        if (($ip == $startip && $addr['name'] == 'network') || ($ip == $endip && $addr['name'] == 'broadcast'))
        {
            echo " class='trbusy'";
            $skip_dns_check = TRUE;
        }
        elseif (!$ip_is_up)
            echo " class='trnull'";
        // set line color depending if we have the name already in the DB
        if (!$skip_dns_check) {
            if ($addr['name'] == $fing_hostname) {
                if (strlen($fing_hostname)) {
                    echo ' class=trok';
                    $cnt_match++;
                }
            } elseif (!strlen($addr['name']) or !strlen($fing_hostname)) {
                echo ' class=trwarning';
                $print_cbox = TRUE;
                $cnt_missing++;
            } else {
                echo ' class=trerror';
                $print_cbox = TRUE;
                $cnt_mismatch++;
            }
        }

        //IP
        echo "><td class='tdleft";
        if (isset ($range['addrlist'][$ip_bin]['class']) and strlen ($range['addrlist'][$ip_bin]['class']))
            echo ' ' . $range['addrlist'][$ip_bin]['class'];
        echo "'><a href='".makeHref(array('page'=>'ipaddress', 'ip'=>$straddr))."'>${straddr}</a></td>";

        //other columns
        if ($skip_dns_check)
            echo "<td class='tdleft'>&nbsp;</td>";
        else {
            if (!$ip_is_up)
                echo "<td class='tdleft'>" . $fing_state . "</td>";
            else
                echo "<td class='tdleft'><div class='strong'>" . $fing_state . "</div></td>";
        }
        echo "<td class=tdleft>${addr['name']}</td>";
        echo "<td class='tdleft'>".$fing_hostname."</td>";
        echo "<td class='tdleft'>".$fing_mac_vendor."</td>";


        //allocation
        echo "<td>";
        $delim = '';
        if ( $addr['reserved'] == 'yes')
        {
            echo "<strong>RESERVED</strong> ";
            $delim = '; ';
        }
        foreach ($addr['allocs'] as $ref)
        {
            echo $delim . $aac_left[$ref['type']];
            echo makeIPAllocLink ($ip_bin, $ref, TRUE);
            $delim = '; ';
        }
        if ($delim != '')
            $delim = '<br>';
        foreach ($addr['vslist'] as $vs_id)
        {
            $vs = spotEntity ('ipv4vs', $vs_id);
            echo $delim . mkA ("${vs['name']}:${vs['vport']}/${vs['proto']}", 'ipv4vs', $vs['id']) . '&rarr;';
            $delim = '<br>';
        }
        foreach ($addr['vsglist'] as $vs_id)
        {
            $vs = spotEntity ('ipvs', $vs_id);
            echo $delim . mkA ($vs['name'], 'ipvs', $vs['id']) . '&rarr;';
            $delim = '<br>';
        }
        foreach ($addr['rsplist'] as $rsp_id)
        {
            $rsp = spotEntity ('ipv4rspool', $rsp_id);
            echo "${delim}&rarr;" . mkA ($rsp['name'], 'ipv4rspool', $rsp['id']);
            $delim = '<br>';
        }
        echo "</td>";

        // import column
        if ($can_import)
        {
            echo '<td>';
            if ($print_cbox)
                echo "<input type=checkbox name=import_${cnt_total} id=atom_1_" . $box_counter++ . "_1>";
            else
                echo '&nbsp;';
            echo '</td>';
        }
        echo "</tr>";
    }

    if ($can_import && $box_counter > 1)
    {
        echo '<tr><td colspan=4 align=center><input type=submit value="Import selected records"></td><td colspan=2 align=right>';
        addJS ('js/racktables.js');
        echo --$box_counter ? "<a href='javascript:;' onclick=\"toggleColumnOfAtoms(1, 1, ${box_counter})\">(toggle selection)</a>" : '&nbsp;';
        echo '</td></tr>';
    }

    echo "</table>";
    if ($can_import)
        echo '</form>';
    finishPortlet();

    echo "</td><td class=pcright>";

    //
    // PING Statistics
    //
    startPortlet ('ping stats');
    $cnt_ping_up = get_fing_up_count($known_ips);
    echo "<table border=0 width='100%' cellspacing=0 cellpadding=2>";
    echo "<tr class=trok><th class=tdright>Replied to Ping</th><td class=tdleft>${cnt_ping_up}</td></tr>\n";
    echo "<tr class=trwarning><th class=tdright>No Response</th><td class=tdleft>".($cnt_total-$cnt_ping_up)."</td></tr>\n";
    echo "</table>\n";
    finishPortlet();

    //
    // DNS Statistics
    //
    startPortlet ('dns stats');
    echo "<table border=0 width='100%' cellspacing=0 cellpadding=2>";
    echo "<tr class=trok><th class=tdright>Exact matches:</th><td class=tdleft>${cnt_match}</td></tr>\n";
    echo "<tr class=trwarning><th class=tdright>Missing from DB/DNS:</th><td class=tdleft>${cnt_missing}</td></tr>\n";
    if ($cnt_mismatch)
        echo "<tr class=trerror><th class=tdright>Mismatches:</th><td class=tdleft>${cnt_mismatch}</td></tr>\n";
    echo "</table>\n";
    finishPortlet();
}


$msgcode['importFingData']['OK'] = 26;
$msgcode['importFingData']['ERR'] = 141;
function importFingData ()
{
    $net = spotEntity ('ipv4net', getBypassValue());
    assertUIntArg ('addrcount');
    $nbad = $ngood = 0;
    for ($i = 1; $i <= $_REQUEST['addrcount']; $i++)
    {
        $inputname = "import_${i}";
        if (! isCheckSet ($inputname))
            continue;
        $ip_bin = assertIPv4Arg ("addr_${i}");
        assertStringArg ("descr_${i}", TRUE);
        assertStringArg ("rsvd_${i}");
        // Non-existent addresses will not have this argument set in request.
        $rsvd = 'no';
        if ($_REQUEST["rsvd_${i}"] == 'yes')
            $rsvd = 'yes';
        try
        {
            if (! ip_in_range ($ip_bin, $net))
                throw new InvalidArgException ('ip_bin', $ip_bin);
            updateAddress ($ip_bin, $_REQUEST["descr_${i}"], $rsvd);
            $ngood++;
        }
        catch (RackTablesError $e)
        {
            $nbad++;
        }
    }
    if (!$nbad)
        showFuncMessage (__FUNCTION__, 'OK', array ($ngood));
    else
        showFuncMessage (__FUNCTION__, 'ERR', array ($nbad, $ngood));
}
