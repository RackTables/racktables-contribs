<?php

//

// 2012-11-16  Blazej Gomoluch:
//		* changes to work in 0.20.1 (gateways.php: added function to breedfunc array)
//		* moved to registerTabHandler()
// 2012-02-27  Blazej Gomoluch:
//		* modified function ios12ReadInterfaces to skip text from banners etc and start to parse from line with show interfaces
// 2012-02-18  Blazej Gomoluch:
//		* added description column
// 2012-12-20  Blazej Gomoluch:
//		* switched from patching CSS to addCSS()
//		* switched to queryDevice() function
//		* indentation format
//

$tab['object']['liveinterfaces'] = 'Live interfaces';
$trigger['object']['liveinterfaces'] = 'trigger_liveports';
registerTabHandler('object','liveinterfaces','renderInterfacesStats');


//after this time (in minutes) we consider that the port is not used 
global $port_use_age;
$port_use_age = 151200; // 7 weeks

function time_convert($cisco_time)
{
	$time = -1;
	if( preg_match('/^uptime is ((\d+) years?, )?((\d+) weeks?, )?((\d+) days?, )?((\d+) hours?, )?(\d+) minutes?/',$cisco_time,$matches) )
	{
		//convert to minutes
		$time = $matches[2]*525600 + $matches[4] *10080 + $matches[6]*1440 + $matches[8]*60 + $matches[9];
	}
	elseif( preg_match('/(\d+)w(\d+)d/',$cisco_time,$matches) )
	{
		//convert to minutes
		$time = $matches[1]*10080 + $matches[2]*1440;
	}
	elseif( preg_match('/(\d+):(\d+):(d\+)/',$cisco_time,$matches) )
	{
		//convert to minutes
		$time = $matches[1]*60 + $matches[2] + 1;
	}

	return $time;
}


// renders one table: port link status 
function renderInterfacesStats($object_id)
{
	global $nextorder;
	global $port_use_age;

	addCSS("/* Live Interfaces */
		.text_green  { color: #00cc00; }
		.text_yellow { color: #ff9900; }
		.text_red    { color: #ff0000; }",TRUE);

	startPortlet('Interfaces statistics');

	//get data: show version
	try
	{
		$uptime = queryDevice ($object_id, 'getuptime');
	}
	catch (RackTablesError $e) {}

	echo "<table width='100%'><tr>";

	if (permitted (NULL, NULL, 'get_link_status'))
	{
		//get data: show interfaces
		try
		{
			$interfaces = queryDevice ($object_id, 'getinterfaces');
		}
		catch (RackTablesError $e) {}

		if (! empty ($interfaces))
		{
			$last_reboot = time_convert($uptime);
			echo "<td";
			echo $last_reboot < $port_use_age?" class='text_red'>":$last_reboot > 262080?" class='text_green'>":">";                        
			echo $uptime . "</tr>";

			echo "<table width='80%' class='widetable' cellspacing=0 cellpadding='5px' align='center'><tr><th>Port<th>Link status<th>Last input<th>Last clear<th>Input packets<th>Output packets<th>Description</tr>";
			$order = 'even';
			foreach ($interfaces as $pn => $int)
			{
				echo "<tr class='row_$order'>";
				$order = $nextorder[$order];
				echo '<td>' . $pn . '<td';
				echo $int['status']=="connected"?" class='text_green'>":">";
				echo $int['status'] . '<td';
				echo time_convert($int['last']) > $port_use_age?" class='text_yellow'>":">";
				echo $int['last'] . '<td';
				echo (time_convert($int['clear']) > 0) ? ((time_convert($int['clear']) < $port_use_age) ? " class='text_red'>":">"):">";
				echo $int['clear'];
				echo '<td>' . $int['in_pkts'];
				echo '<td>' . $int['out_pkts'];
				echo '<td>' . $int['desc'];
				echo '</tr>';
			}
			echo "</table></td>";
		}
	}

	echo "</td></tr></table>";
	finishPortlet();
}


function ios12ReadUptime ($input)
{
	$result = array();

	foreach (explode ("\n", $input) as $line)
	{
		//match just one line with uptime stats
		if ( preg_match ('/uptime is.*/', trim ($line), $match))
		{
			$result = trim($match[0]);
		}
	}
	return $result;
}

function ios12ReadInterfaces ($input)
{
	$result = array();
	$state = 'start';

	foreach (explode ("\n", $input) as $line)
	{

		switch ($state)
		{
			case 'start':
				if ( preg_match ('/^[a-zA-Z0-9-_]*#show interfaces$/', $line) )
				{
					$state = 'intSearch';
				}	
				break;
			case 'intSearch':
				//check if line doesn't start with whitespace
				if ( preg_match ('/^\w.*/', $line))
				{
					$field_list = preg_split('/\s+/', $line);
					$size=count($field_list);
					if ($size < 8)
						break;
					$portname = ios12ShortenIfName ($field_list[0]);
					if ( substr($field_list[$size - 1],0,1) == "(")
					{
						$result[$portname]['status']= substr($field_list[$size - 1],1,-1);
					}
					else
					{
						$result[$portname]['status']="down";
						if ( $field_list[2]=="administratively" ) $result[$portname]['status']="disabled";
						if ( $field_list[6]=="up" ) $result[$portname]['status']="connected";
					}
					$result[$portname]['name']= $field_list[0];
					$result[$portname]['desc']="";
					//next search: last input
					$state = 'descriptionSearch';
				}
				break;
			case 'descriptionSearch':
				//search for description (this line may not appear!)
				if ( preg_match ('/  Description: (.*)$/', $line, $matches))
				{
					$result[$portname]['desc']=htmlspecialchars($matches[1]);
					//next search: last input
					$state = 'lastInputSearch';
				}
				//no break - keep searching as description is optional!
			case 'lastInputSearch':
				//search for last input
				if ( preg_match ('/  Last input (.*?), output.*/i', $line, $matches))
				{
					$result[$portname]['last']=$matches[1];
					//next search: last clearing
					$state = 'lastClearSearch';
				}
				break;
			case 'lastClearSearch':
				//search for last clearing
				if ( preg_match ('/  Last clearing of \"show interface\" counters (.*)/i', $line, $matches))
				{
					$result[$portname]['clear']=$matches[1];
					//next search: next input packets
					$state = 'pktInputSearch';
				}
				break;
			case 'pktInputSearch':
				if ( preg_match ('/   ([0-9]*) packets input/i', $line, $matches))
				{
					$result[$portname]['in_pkts']=$matches[1];
					//next search: output packets
					$state = 'pktOutputSearch';
				}
				break;
			case 'pktOutputSearch':
				if ( preg_match ('/   ([0-9]*) packets output/i', $line, $matches))
				{
					$result[$portname]['out_pkts']=$matches[1];
					//next search: next interface
					$state = 'intSearch';
				}
				break;
		}
	}
	return $result;
}
