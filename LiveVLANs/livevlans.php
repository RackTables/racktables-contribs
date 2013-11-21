<?php

/*
The "Live VLANs" RackTables feature was introduced in release 0.14.6 and
removed in release 0.20.2. The "802.1Q" RackTables feature, which was
introduced in release 0.18.0, fits 99% of VLAN management cases and
should be used instead.
*/

$tab['object']['livevlans'] = 'Live VLANs';
$tabhandler['object']['livevlans'] = 'renderVLANMembership';
$trigger['object']['livevlans'] = 'trigger_livevlans';
$ophandler['object']['livevlans']['setPortVLAN'] = 'setPortVLAN';
$delayauth['object-livevlans-setPortVLAN'] = TRUE;

// This trigger filters out everything except switches with known-good
// software.
function trigger_livevlans ()
{
	return checkTypeAndAttribute
	(
		getBypassValue(),
		8, // network switch
		4, // SW type
		// Cisco IOS 12.0
		// Cisco IOS 12.1
		// Cisco IOS 12.2
		array (244, 251, 252)
	);
}

// This function launches specified gateway with specified
// command-line arguments and feeds it with the commands stored
// in the second arg as array.
// The answers are stored in another array, which is returned
// by this function. In the case when a gateway cannot be found,
// finishes prematurely or exits with non-zero return code,
// a single-item array is returned with the only "ERR" record,
// which explains the reason.
function queryGateway ($gwname, $questions)
{
	global $racktables_gwdir;
	$execpath = "${racktables_gwdir}/{$gwname}/main";
	$dspec = array
	(
		0 => array ("pipe", "r"),
		1 => array ("pipe", "w"),
		2 => array ("file", "/dev/null", "a")
	);
	$pipes = array();
	$gateway = proc_open ($execpath, $dspec, $pipes);
	if (!is_resource ($gateway))
		return array ('ERR proc_open() failed in ' . __FUNCTION__);

// Dialogue starts. Send all questions.
	foreach ($questions as $q)
		fwrite ($pipes[0], "$q\n");
	fclose ($pipes[0]);

// Fetch replies.
	$answers = array ();
	while (!feof($pipes[1]))
	{
		$a = fgets ($pipes[1]);
		if (!strlen ($a))
			continue;
		// Somehow I got a space appended at the end. Kick it.
		$answers[] = trim ($a);
	}
	fclose($pipes[1]);

	$retval = proc_close ($gateway);
	if ($retval != 0)
		throw new RTGatewayError ("gateway failed with code ${retval}");
	if (!count ($answers))
		throw new RTGatewayError ('no response from gateway');
	if (count ($answers) != count ($questions))
		throw new RTGatewayError ('protocol violation');
	foreach ($answers as $a)
		if (strpos ($a, 'OK!') !== 0)
			throw new RTGatewayError ("subcommand failed with status: ${a}");
	return $answers;
}

// This functions returns an array for VLAN list, and an array for port list (both
// form another array themselves) and another one with MAC address list.
// The ports in the latter array are marked with either VLAN ID or 'trunk'.
// We don't sort the port list, as the gateway is believed to have done this already
// (or at least the underlying switch software ought to). This is important, as the
// port info is transferred to/from form not by names, but by numbers.
function getSwitchVLANs ($object_id = 0)
{
	global $remote_username;
	$objectInfo = spotEntity ('object', $object_id);
	$endpoints = findAllEndpoints ($object_id, $objectInfo['name']);
	if (count ($endpoints) == 0)
		throw new RTGatewayError ('no management address set');
	if (count ($endpoints) > 1)
		throw new RTGatewayError ('cannot pick management address');
	$hwtype = $swtype = 'unknown';
	foreach (getAttrValues ($object_id) as $record)
	{
		if ($record['name'] == 'SW type' && strlen ($record['o_value']))
			$swtype = str_replace (' ', '+', execGMarker ($record['o_value']));
		if ($record['name'] == 'HW type' && strlen ($record['o_value']))
			$hwtype = str_replace (' ', '+', execGMarker ($record['o_value']));
	}
	$endpoint = str_replace (' ', '+', $endpoints[0]);
	$commands = array
	(
		"connect ${endpoint} ${hwtype} ${swtype} ${remote_username}",
		'listvlans',
		'listports',
		'listmacs'
	);
	$data = queryGateway ('switchvlans', $commands);
	if (strpos ($data[0], 'OK!') !== 0)
		throw new RTGatewayError ("gateway failed with status: ${data[0]}.");
	// Now we have VLAN list in $data[1] and port list in $data[2]. Let's sort this out.
	$tmp = array_unique (explode (';', substr ($data[1], strlen ('OK!'))));
	if (count ($tmp) == 0)
		throw new RTGatewayError ('gateway returned no records');
	$vlanlist = array();
	foreach ($tmp as $record)
	{
		list ($vlanid, $vlandescr) = explode ('=', $record);
		$vlanlist[$vlanid] = $vlandescr;
	}
	$portlist = array();
	foreach (explode (';', substr ($data[2], strlen ('OK!'))) as $pair)
	{
		list ($portname, $pair2) = explode ('=', $pair);
		list ($status, $vlanid) = explode (',', $pair2);
		$portlist[] = array ('portname' => $portname, 'status' => $status, 'vlanid' => $vlanid);
	}
	if (count ($portlist) == 0)
		throw new RTGatewayError ('gateway returned no records');
	$maclist = array();
	foreach (explode (';', substr ($data[3], strlen ('OK!'))) as $pair)
		if (preg_match ('/^([^=]+)=(.+)/', $pair, $m))
		{
			$macaddr = $m[1];
			list ($vlanid, $ifname) = explode ('@', $m[2]);
			$maclist[$ifname][$vlanid][] = $macaddr;
		}
	return array ($vlanlist, $portlist, $maclist);
}

function setSwitchVLANs ($object_id = 0, $setcmd)
{
	global $remote_username;
	$objectInfo = spotEntity ('object', $object_id);
	$endpoints = findAllEndpoints ($object_id, $objectInfo['name']);
	if (count ($endpoints) == 0)
		throw new RTGatewayError ('no management address set');
	if (count ($endpoints) > 1)
		throw new RTGatewayError ('cannot pick management address');
	$hwtype = $swtype = 'unknown';
	foreach (getAttrValues ($object_id) as $record)
	{
		if ($record['name'] == 'SW type' && strlen ($record['o_value']))
			$swtype = strtr (execGMarker ($record['o_value']), ' ', '+');
		if ($record['name'] == 'HW type' && strlen ($record['o_value']))
			$hwtype = strtr (execGMarker ($record['o_value']), ' ', '+');
	}
	$endpoint = str_replace (' ', '+', $endpoints[0]);
	$data = queryGateway
	(
		'switchvlans',
		array ("connect ${endpoint} ${hwtype} ${swtype} ${remote_username}", $setcmd)
	);
	// Finally we can parse the response into message array.
	foreach (explode (';', substr ($data[1], strlen ('OK!'))) as $text)
	{
		$message = 'gw: ' . substr ($text, 2);
		if (strpos ($text, 'I!') === 0)
			showSuccess ($message); // generic gateway success
		elseif (strpos ($text, 'W!') === 0)
			showWarning ($message); // generic gateway warning
		elseif (strpos ($text, 'E!') === 0)
			showError ($message); // generic gateway error
		else // All improperly formatted messages must be treated as error conditions.
			showError ('unexpected line from gw: ' . $text);
	}
}

$msgcode['setPortVLAN']['ERR'] = 164;
// This handler's context is pre-built, but not authorized. It is assumed, that the
// handler will take existing context and before each commit check authorization
// on the base chain plus necessary context added.
function setPortVLAN ()
{
	assertUIntArg ('portcount');
	try
	{
		$data = getSwitchVLANs ($_REQUEST['object_id']);
	}
	catch (RTGatewayError $re)
	{
		return showFuncMessage (__FUNCTION__, 'ERR', array ($re->getMessage()));
	}
	list ($vlanlist, $portlist) = $data;
	// Here we just build up 1 set command for the gateway with all of the ports
	// included. The gateway is expected to filter unnecessary changes silently
	// and to provide a list of responses with either error or success message
	// for each of the rest.
	$nports = $_REQUEST['portcount'];
	$prefix = 'set ';
	$setcmd = '';
	for ($i = 0; $i < $nports; $i++)
	{
		genericAssertion ('portname_' . $i, 'string');
		genericAssertion ('vlanid_' . $i, 'string');
		if ($_REQUEST['portname_' . $i] != $portlist[$i]['portname'])
			throw new InvalidRequestArgException ('portname_' . $i, $_REQUEST['portname_' . $i], 'expected to be ' . $portlist[$i]['portname']);
		if
		(
			$_REQUEST['vlanid_' . $i] == $portlist[$i]['vlanid'] ||
			$portlist[$i]['vlanid'] == 'TRUNK'
		)
			continue;
		$portname = $_REQUEST['portname_' . $i];
		$oldvlanid = $portlist[$i]['vlanid'];
		$newvlanid = $_REQUEST['vlanid_' . $i];
		if
		(
			!permitted (NULL, NULL, NULL, array (array ('tag' => '$fromvlan_' . $oldvlanid), array ('tag' => '$vlan_' . $oldvlanid))) or
			!permitted (NULL, NULL, NULL, array (array ('tag' => '$tovlan_' . $newvlanid), array ('tag' => '$vlan_' . $newvlanid)))
		)
		{
			showOneLiner (159, array ($portname, $oldvlanid, $newvlanid));
			continue;
		}
		$setcmd .= $prefix . $portname . '=' . $newvlanid;
		$prefix = ';';
	}
	// Feed the gateway and interpret its (non)response.
	if ($setcmd == '')
		showOneLiner (201);
	else
	{
		try
		{
			setSwitchVLANs ($_REQUEST['object_id'], $setcmd); // shows messages by itself
		}
		catch (RTGatewayError $e)
		{
			showFuncMessage (__FUNCTION__, 'ERR', array ($e->getMessage()));
		}
	}
}

// This function queries the gateway about current VLAN configuration and
// renders a form suitable for submit. Ah, and it does submit processing as well.
function renderVLANMembership ($object_id)
{
	try
	{
		$data = getSwitchVLANs ($object_id);
	}
	catch (RTGatewayError $re)
	{
		showWarning ('Device configuration unavailable:<br>' . $re->getMessage());
		return;
	}
	list ($vlanlist, $portlist, $maclist) = $data;
	$vlanpermissions = array();
	foreach ($portlist as $port)
	{
		if (array_key_exists ($port['vlanid'], $vlanpermissions))
			continue;
		$vlanpermissions[$port['vlanid']] = array();
		foreach (array_keys ($vlanlist) as $to)
			if
			(
				permitted (NULL, NULL, 'setPortVLAN', array (array ('tag' => '$fromvlan_' . $port['vlanid']), array ('tag' => '$vlan_' . $port['vlanid']))) and
				permitted (NULL, NULL, 'setPortVLAN', array (array ('tag' => '$tovlan_' . $to), array ('tag' => '$vlan_' . $to)))
			)
				$vlanpermissions[$port['vlanid']][] = $to;
	}

	if (isset ($_REQUEST['hl_port_id']))
	{
		assertUIntArg ('hl_port_id');
		$hl_port_id = intval ($_REQUEST['hl_port_id']);
		$object = spotEntity ('object', $object_id);
		amplifyCell ($object);
		foreach ($object['ports'] as $port)
			if (mb_strlen ($port['name']) && $port['id'] == $hl_port_id)
			{
				$hl_port_name = $port['name'];
				break;
			}
	}

	echo '<table border=0 width="100%"><tr><td colspan=3>';
	startPortlet ('Current status');
	echo "<table class=widetable cellspacing=3 cellpadding=5 align=center width='100%'><tr>";
	printOpFormIntro ('setPortVLAN');
	$portcount = count ($portlist);
	echo "<input type=hidden name=portcount value=" . $portcount . ">\n";
	$portno = 0;
	$ports_per_row = 12;
	foreach ($portlist as $port)
	{
		// Don't let wide forms break our fancy pages.
		if ($portno % $ports_per_row == 0)
		{
			if ($portno > 0)
				echo "</tr>\n";
			echo "<tr><th>" . ($portno + 1) . "-" . ($portno + $ports_per_row > $portcount ? $portcount : $portno + $ports_per_row) . "</th>";
		}
		$td_class = 'port_';
		if ($port['status'] == 'notconnect')
			$td_class .= 'notconnect';
		elseif ($port['status'] == 'disabled')
			$td_class .= 'disabled';
		elseif ($port['status'] != 'connected')
			$td_class .= 'unknown';
		elseif (!isset ($maclist[$port['portname']]))
			$td_class .= 'connected_none';
		else
		{
			$maccount = 0;
			foreach ($maclist[$port['portname']] as $vlanid => $addrs)
				$maccount += count ($addrs);
			if ($maccount == 1)
				$td_class .= 'connected_single';
			else
				$td_class .= 'connected_multi';
		}
		if (isset ($hl_port_name) and strcasecmp ($hl_port_name, $port['portname']) == 0)
			$td_class .= (strlen($td_class) ? ' ' : '') . 'border_highlight';
		echo "<td class='$td_class'>" . $port['portname'] . '<br>';
		echo "<input type=hidden name=portname_${portno} value=" . $port['portname'] . '>';
		if ($port['vlanid'] == 'trunk')
		{
			echo "<input type=hidden name=vlanid_${portno} value='trunk'>";
			echo "<select disabled multiple='multiple' size=1><option>TRUNK</option></select>";
		}
		elseif ($port['vlanid'] == 'routed')
		{
			echo "<input type=hidden name=vlanid_${portno} value='routed'>";
			echo "<select disabled multiple='multiple' size=1><option>ROUTED</option></select>";
		}
		elseif (!array_key_exists ($port['vlanid'], $vlanpermissions) or !count ($vlanpermissions[$port['vlanid']]))
		{
			echo "<input type=hidden name=vlanid_${portno} value=${port['vlanid']}>";
			echo "<select disabled name=vlanid_${portno}>";
			echo "<option value=${port['vlanid']} selected>${port['vlanid']}</option>";
			echo "</select>";
		}
		else
		{
			echo "<select name=vlanid_${portno}>";
			// A port may belong to a VLAN, which is absent from the VLAN table, this is normal.
			// We must be able to render its SELECT properly at least.
			$in_table = FALSE;
			foreach ($vlanpermissions[$port['vlanid']] as $v)
			{
				echo "<option value=${v}";
				if ($v == $port['vlanid'])
				{
					echo ' selected';
					$in_table = TRUE;
				}
				echo ">${v}</option>\n";
			}
			if (!$in_table)
				echo "<option value=${port['vlanid']} selected>${port['vlanid']}</option>\n";
			echo "</select>";
		}
		$portno++;
		echo "</td>";
	}
	echo "</tr><tr><td colspan=" . ($ports_per_row + 1) . "><input type=submit value='Save changes'></form></td></tr></table>";
	finishPortlet();

	echo '</td></tr><tr><td class=pcleft>';
	startPortlet ('VLAN table');
	echo '<table class=cooltable cellspacing=0 cellpadding=5 align=center width="100%">';
	echo "<tr><th>ID</th><th>Description</th></tr>";
	$order = 'even';
	global $nextorder;
	foreach ($vlanlist as $id => $descr)
	{
		echo "<tr class=row_${order}><td class=tdright>${id}</td><td class=tdleft>${descr}</td></tr>";
		$order = $nextorder[$order];
	}
	echo '</table>';
	finishPortlet();

	echo '</td><td class=pcright>';

	startPortlet ('Color legend');
	echo '<table>';
	echo "<tr><th>port state</th><th>color code</th></tr>";
	echo "<tr><td>not connected</td><td class=port_notconnect>SAMPLE</td></tr>";
	echo "<tr><td>disabled</td><td class=port_disabled>SAMPLE</td></tr>";
	echo "<tr><td>unknown</td><td class=port_unknown>SAMPLE</td></tr>";
	echo "<tr><td>connected with none MAC addresses active</td><td class=port_connected_none>SAMPLE</td></tr>";
	echo "<tr><td>connected with 1 MAC addresses active</td><td class=port_connected_single>SAMPLE</td></tr>";
	echo "<tr><td>connected with 1+ MAC addresses active</td><td class=port_connected_multi>SAMPLE</td></tr>";
	echo '</table>';
	finishPortlet();

	echo '</td><td class=pcright>';

	if (count ($maclist))
	{
		startPortlet ('MAC address table');
		echo '<table border=0 class=cooltable align=center cellspacing=0 cellpadding=5>';
		echo "<tr><th>Port</th><th>VLAN ID</th><th>MAC address</th></tr>\n";
		$order = 'even';
		foreach ($maclist as $portname => $portdata)
			foreach ($portdata as $vlanid => $addrgroup)
				foreach ($addrgroup as $addr)
				{
					echo "<tr class=row_${order}><td class=tdleft>$portname</td><td class=tdleft>$vlanid</td>";
					echo "<td class=tdleft>$addr</td></tr>\n";
					$order = $nextorder[$order];
				}
		echo '</table>';
		finishPortlet();
	}

	// End of main table.
	echo '</td></tr></table>';
}

?>
