<?php
//
// Network ping plugin.
// Version 0.1
//
// Written by Tommy Botten Jensen
//
// The purpose of this plugin is to map your IP ranges with the reality of your
// network using ICMP.
//
// History
// Version 0.1:  Initial release
//
// Requirements:
// You need 'fping' from your local repo or http://fping.sourceforge.net/
// Racktables must be hosted on a system that has ICMP PING access to your hosts
//
// Installation:
// 1)  Copy script to inc folder as ping.php
// 2)  Add include to inc/local.php: include("ping.php");
// 3)  Install fping if you did not read the "requirements"
// 4)  Adjust the $pingtimeout value below to match your network.


// Set variables
$Version = "0.1";
$username = $_SERVER['PHP_AUTH_USER'];
$nextorder['odd'] = 'even';
$nextorder['even'] = 'odd';

// Depot Tab for objects.
$tab['ipv4net']['ping'] = 'Ping overview';
$tabhandler['ipv4net']['ping'] = 'PingTab';
$ophandler['ipv4net']['ping']['importPingData'] = 'importPingData';


function importPingData() {
 // Stub connection for now :(
}


// Display the ping overview:
function PingTab($id) {
	$pingtimeout = "50";

        if (isset($_REQUEST['pg']))
                $page = $_REQUEST['pg'];
        else
                $page=0;
        global $pageno, $tabno;
        $maxperpage = getConfigVar ('IPV4_ADDRS_PER_PAGE');
        $range = spotEntity ('ipv4net', $id);
        loadIPv4AddrList ($range);
        echo "<center><h1>${range['ip']}/${range['mask']}</h1><h2>${range['name']}</h2></center>\n";

        echo "<table class=objview border=0 width='100%'><tr><td class=pcleft>";
        startPortlet ('icmp ping comparrison:');
        $startip = $range['ip_bin'] & $range['mask_bin'];
        $endip = $range['ip_bin'] | $range['mask_bin_inv'];
        $realstartip = $startip;
        $realendip = $endip;
        $numpages = 0;
        if ($endip - $startip > $maxperpage)
        {
                $numpages = ($endip - $startip) / $maxperpage;
                $startip = $startip + $page * $maxperpage;
                $endip = $startip + $maxperpage - 1;
        }
        echo "<center>";
        if ($numpages)
                echo '<h3>' . long2ip ($startip) . ' ~ ' . long2ip ($endip) . '</h3>';
        for ($i=0; $i<$numpages; $i++)
                if ($i == $page)
                        echo "<b>$i</b> ";
                else
                        echo "<a href='".makeHref(array('page'=>$pageno, 'tab'=>$tabno, 'id'=>$id, 'pg'=>$i))."'>$i</a> ";
        echo "</center>";

        echo "<table class='widetable' border=0 cellspacing=0 cellpadding=5 align='center'>\n";
        echo "<tr><th>address</th><th>name</th><th>response</th></tr>\n";
        $idx = 1;
        $box_counter = 1;
        $cnt_ok = $cnt_noreply = $cnt_mismatch = 0;
        for ($ip = $startip; $ip <= $endip; $ip++)
        {
                $addr = isset ($range['addrlist'][$ip]) ? $range['addrlist'][$ip] : array ('name' => '', 'reserved' => 'no');
                $straddr = long2ip ($ip);
		system("/usr/sbin/fping -q -c 1 -t $pingtimeout $straddr",$pingreply);

		// FIXME: This is a huge and ugly IF/ELSE block. Prettify anyone?
		if (!$pingreply) {
			if (($addr['name']) or (isset ($range['addrlist'][$ip]['class']) and strlen ($range['addrlist'][$ip]['class']))) {
				echo '<tr class=trok';
                        	$cnt_ok++;
			}
			else {
				echo '<tr class=trerror';
                        	$cnt_mismatch++;
			}
		}
		else {
			if (($addr['name']) or (isset ($range['addrlist'][$ip]['class']) and strlen ($range['addrlist'][$ip]['class']))) {
				echo '<tr class=trwarning';
                        	$cnt_noreply++;
			}
			else {
				echo '<tr';
			}
		}

                echo "><td class='tdleft";
                if (isset ($range['addrlist'][$ip]['class']) and strlen ($range['addrlist'][$ip]['class']))
                        echo ' ' . $range['addrlist'][$ip]['class'];
                echo "'><a href='".makeHref(array('page'=>'ipaddress', 'ip'=>$straddr))."'>${straddr}</a></td>";
                echo "<td class=tdleft>${addr['name']}</td><td class=tderror>";
                if (!$pingreply)
                        echo "Yes";
                else
                        echo "No";
                echo "</td></tr>\n";
                $idx++;
        }
        echo "</td></tr>";
        echo "</table>";
        echo "</form>";
        finishPortlet();

        echo "</td><td class=pcright>";

        startPortlet ('stats');
        echo "<table border=0 width='100%' cellspacing=0 cellpadding=2>";
        echo "<tr class=trok><th class=tdright>OKs:</th><td class=tdleft>${cnt_ok}</td></tr>\n";
        echo "<tr class=trwarning><th class=tdright>Did not reply:</th><td class=tdleft>${cnt_noreply}</td></tr>\n";
        if ($cnt_mismatch)
                echo "<tr class=trerror><th class=tdright>Unallocated answer:</th><td class=tdleft>${cnt_mismatch}</td></tr>\n";
        echo "</table>\n";
        finishPortlet();

        echo "</td></tr></table>\n";
}
?>
