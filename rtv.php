<?php 

/* This script is "RackTables visualised", it was contributed
 * by Colin Coe to racktables-users mailing list in 2009.
 */

// Our purpose is to produce a graphic
header ("Content-type: image/png"); 

function GetMySQLData() {
	global $devices, $links, $sites, $port_count, $ports, $racks;

	$sql_query = "
select distinct RO.id as ROid1, RO2.id as ROid2, RO.name as ROname1, RO2.name as ROname2, P.name as pname1, P2.name as pname2, L.porta, RR.name as site, R.name as rackname, R2.name as rackname2, P.type as linktype, P.label as label1, P2.label as label2
from RackObject RO, RackObject RO2, Port P, Link L, Link L2, Port P2, RackRow RR, Rack R, Rack R2, RackSpace RS, RackSpace RS2
where RO.name      != RO2.name                         and
      RR.id         = R.row_id                         and
      (R.id         = RS.rack_id                       and
      RS.object_id  = RO.id                            and
      RO.id         = P.object_id)                     and
      (R2.id        = RS2.rack_id                      and
      RS2.object_id = RO2.id                           and
      RO2.id        = P2.object_id)                    and
      P.id          = L.porta                          and
      RO2.id        = P2.object_id                     and
     (P2.id         = L2.porta or P2.id    = L2.portb) and
     (L2.porta      = L.porta and L2.portb = L.portb)
order by L.porta;";

	include "/var/www/racktables/inc/secret.php";
	$dblink = mysql_connect("localhost", $db_username, $db_password) or die(mysql_error()); 
	mysql_select_db("racktables", $dblink) or die(mysql_error()); 
	$result = mysql_query($sql_query, $dblink) or die(mysql_error()); 
	while($row = mysql_fetch_assoc($result)) { 
		$devices[$row['ROname1']] = $row['rackname'];
		$devices[$row['ROname2']] = $row['rackname2'];

		$links[$row['porta']] = array(
				"Site"    => $row['site'],
				"Device1" => $row['ROname1'],
				"Port1"   => $row['pname1'],
				"Label1"  => $row['label1'],
				"Rack1"   => $row['rackname'],
				"Device2" => $row['ROname2'],
				"Port2"   => $row['pname2'],
				"Label2"  => $row['label2'],
				"Rack2"   => $row['rackname2'],
				"Type"    => $row['linktype']);

		$sites[$row['ROname1']]   = $row['site'];
		$sites[$row['ROname2']]   = $row['site'];
		$racks[$row['rackname']]  = $row['site'];
		$racks[$row['rackname2']] = $row['site'];

		$presult = mysql_query("SELECT name FROM Port where object_id = ".$row['ROid1'], $dblink);
		$port_count[$row['ROname1']] = mysql_num_rows($presult);
		$i = 0;
		while($prow = mysql_fetch_assoc($presult)) { 
			$ports[$row['ROname1']][$i] = $prow['name'];
			$i++;
		}
		unset($i);

		$i = 0;
		$presult = mysql_query("SELECT * FROM Port where object_id = ".$row['ROid2'], $dblink);
		$port_count[$row['ROname2']] = mysql_num_rows($presult);
		while($prow = mysql_fetch_assoc($presult)) { 
			$ports[$row['ROname2']][$i] = $prow['name'];
			$i++;
		}
		unset($i);
	}
//	asort($ports);
}

function SwapLink(&$link) {
	$items = array('Device', 'Port', 'Label', 'Rack');
	foreach ($items as $item) {
		$tmp = $link[sprintf("%s1", $item)];
		$link[sprintf("%s1", $item)] = $link[sprintf("%s2", $item)];
		$link[sprintf("%s2", $item)] = $tmp;
		unset($tmp);
	}
	unset($items);
}

function CountRacksInSite($site) {
	global $racks;
	
	$rack_count   = 0;
	foreach($racks as $rackname => $sitename) {
		if ($site == $sitename) {
			$rack_count++;
		}
	}

	return $rack_count;
}

function CountDevicesInRack($site) {
	// How many devices in the most populated rack?
	global $devices, $racks;

	$rack = array();
	$max = 0;
	
	foreach($devices as $device_name => $installed_rack) {
		if ($racks[$installed_rack] == $site) {
			if (isset($rack[$installed_rack])) {
				$rack[$installed_rack]++;
			} 
			else {
				$rack[$installed_rack]=1;
			}
			if ($rack[$installed_rack] > $max) {
				$max = $rack[$installed_rack];
			}
		}
	}

	return $max;
}

function CountLinks($site) {
	global $links;

	$tmp_array = array();
	$max = 0;

	foreach($links as $link) {
		if ($link['Site'] == $site) {
			if ($link['Rack1'] > $link['Rack2']) {
				// For simplicity, make sure all links go from left to right
				SwapLink($link);
			}
			for($i=1;$i<=2;$i++) {
				if (isset($tmp_array[$link['Rack'.$i]])) {
					$tmp_array[$link['Rack'.$i]]++;
				}
				else {
					$tmp_array[$link['Rack'.$i]] = 1;
				}
				if ($tmp_array[$link['Rack'.$i]] > $max) {
					$max = $tmp_array[$link['Rack'.$i]];
				}
			}
		}
	}

	return $max;
}


function Draw($site) {
	global $image, $racks, $devices, $links, $port_count, $ports;

	$rack_count   = CountRacksInSite($site);

	// Define the modifiers
	$modifier      = 3;
	$top_modifier  = 0;
	$dev_modifier  = array();
	$side_modifier = array();

	$rack_buffer  = (CountLinks($site) + 2) * $modifier ; // Space between racks
	$rack_margin  = $rack_buffer; // Space between the racks and edge of page
	$y_top_margin = 100 + (CountLinks($site) * 1.25);
	$y_bot_margin = 75;

	$page_size    = 7;
	$page_counter = 0;
	$x_offset     = 25;
	$y_offset     = 50;
	$height       = 100;
	$rack_width   = 300;
	$rack_height  = CountDevicesInRack($site) * $height;
	$width        = $rack_width;
	$x_buffer     = $width + intval($width * 0.5);
	$y_buffer     = 50;

	ksort($racks);
	ksort($devices);

	$x_max = ($rack_count * $rack_width) + ($rack_count* $rack_buffer) + ($rack_margin * 2);
	$y_max = $rack_height + $y_top_margin + intval(ImageFontHeight(2) * (CountLinks($site) * 1.25) + 0.5);

	$image = ImageCreate($x_max, $y_max) or die ("Cannot Create image");
	$background_color = imagecolorallocate($image, 255, 255, 255);

	$pink  = ImageColorAllocate($image, 255, 105, 180);
	$white = ImageColorAllocate($image, 255, 255, 255);
	$black = ImageColorAllocate($image, 0, 0, 0);
	$red   = ImageColorAllocate($image, 255, 0, 0);
	$green = ImageColorAllocate($image, 89, 200, 180);
	$blue  = ImageColorAllocate($image, 34,68,228);
	$grey  = ImageColorAllocate($image, 225, 225, 225);

	$title = "RackTables Visualised ($site)";
	$title_font = 5;
	$title_font_width = ImageFontWidth($title_font);
	ImageString($image, $title_font, ($x_max / 2) - (($title_font_width * strlen($title)) / 2), 
				$title_font_width * 2, $title, $black);

	// Draw racks
	$rc = 1;
	ksort($racks, SORT_STRING);

	foreach($racks as $rackname => $sitename) {
		if ($site != $sitename) {
			continue;
		}
		$y = $y_top_margin;
		$x = $rack_margin + (($rc - 1) * $rack_width) + ($rc * $rack_buffer);
		$x = (($rc - 1) * $rack_width) + ($rc * $rack_buffer);
		ImageRectangle($image, $x, $y, $x + $rack_width, $y + $rack_height, $black);
		$rack_name_width = ImageFontWidth($rackname);
		$rack_name_font  = 2;

		ImageString($image, $rack_name_font, $x + ($rack_width - ($rack_name_width * strlen($rackname))) / 2,
				$y - intval(ImageFontHeight($rack_name_font) * 1.25), $rackname, $black);

		// Draw devices
		foreach($devices as $device_name => $installed_rack) {
			if ($installed_rack != $rackname) {
				continue;
			}
			ImageRectangle($image, $x, $y, $x + $width, $y + $height, $black);
			ImageString($image, 2, $x + 5, $y + 3, strtoupper($device_name), $black);
			$unit = ($width / (($port_count[$device_name]) + 1));
			for($i=0;$i<$port_count[$device_name];$i++) {
				$cx = $x + ($unit * ($i + 1));

				$coord[$device_name][$ports[$device_name][$i]][0] = $cx;
				$coord[$device_name][$ports[$device_name][$i]][1] = intval($y + $height / 2);

				ImageFilledEllipse($image, $cx, $coord[$device_name][$ports[$device_name][$i]][1], 3, 3, $black);

				ImageStringUp($image, 1, $cx - intval(ImageFontWidth(1) / 2), $coord[$device_name][$ports[$device_name][$i]][1] - 5, $ports[$device_name][$i], $black);
			}
			$y = $y + $height;
		}
		$rc++;
	}
	unset($rc);

	$line_tracker = array();
	$index = 0;

	$label = "Device Inter-Connections";
	ImageString($image, 3, $rack_margin, $y_top_margin + $rack_height + 20, $label, $black);
	ImageLine($image, $rack_margin, $y_top_margin + $rack_height + 20 + ImageFontHeight(3) + 1, $rack_margin + (ImageFontWidth(3) * strlen($label)), $y_top_margin + $rack_height + 20 + ImageFontHeight(3) + 1, $black);
	unset($label);

	$y = $y_top_margin - intval(ImageFontHeight(2) * 1.25);

	$text_x_coord['data']  = $rack_margin;
	$text_x_coord['power'] = $rack_margin + 320;
	$text_x_coord['kvm']   = $rack_margin + 640;

	// Iterate through the links
	foreach($links as $link) {

		switch($link['Type']) {
			case 16: // Power
				$colour = $red;
				$type = 'power';
				break;
			case 19: // Fast Ethernet
				$colour = $blue;
				$type = 'data';
				break;
			case 24: // GB Ethernet
				$colour = $blue;
				$type = 'data';
				break;
			case 1077: // SFF
				$colour = $blue;
				$type = 'data';
				break;
			case 33: // KVM
				$colour = $green;
				$type = 'kvm';
				break;
			case 446: // KVM
				$colour = $green;
				$type = 'kvm';
				break;
		}

		// Filter out devices with un-linked ports
		if ((isset($coord[$link['Device1']][$link['Port1']][0]) and 
				isset($coord[$link['Device1']][$link['Port1']][1]) and 
				isset($coord[$link['Device2']][$link['Port2']][0]) and 
				isset($coord[$link['Device2']][$link['Port2']][1]))) {
			$d1x = $coord[$link['Device1']][$link['Port1']][0];
			$d1y = $coord[$link['Device1']][$link['Port1']][1];
			$d2x = $coord[$link['Device2']][$link['Port2']][0];
			$d2y = $coord[$link['Device2']][$link['Port2']][1];

//			$port_list = $port_list + intval(ImageFontHeight(2) * 1.25);
			if (isset($text_y_coord[$type])) {
				$text_y_coord[$type] += intval(ImageFontHeight(2) * 1.25);
			}
			else {			
				$text_y_coord[$type] = $y_top_margin + $rack_height + 20 + intval(ImageFontHeight(2) * 1.25);
			}

			// Print the link information and darw a simple table
			ImageString($image, 2, $text_x_coord[$type], $text_y_coord[$type], $link['Device1'].":".$link['Port1'], $colour);
			ImageString($image, 2, $text_x_coord[$type] + 150, $text_y_coord[$type], $link['Device2'].":".$link['Port2'], $colour);
			ImageLine($image, $text_x_coord[$type] - 10, $text_y_coord[$type], $text_x_coord[$type] + 300, $text_y_coord[$type], $grey);
			if ($text_y_coord[$type] != $y_top_margin + $rack_height + 20 + intval(ImageFontHeight(2) * 1.25)) {
				ImageLine($image, $text_x_coord[$type] - 10, $text_y_coord[$type] + intval(ImageFontHeight(2) * 1.25), $text_x_coord[$type] + 300, $text_y_coord[$type] + intval(ImageFontHeight(2) * 1.25), $grey);
			}	
			ImageLine($image, $text_x_coord[$type] - 10, $text_y_coord[$type], $text_x_coord[$type] - 10, $text_y_coord[$type] + intval(ImageFontHeight(2) * 1.25), $grey);
			ImageLine($image, $text_x_coord[$type] + 150 - 10, $text_y_coord[$type], $text_x_coord[$type] + 150 - 10, $text_y_coord[$type] + intval(ImageFontHeight(2) * 1.25), $grey);
			ImageLine($image, $text_x_coord[$type] + 300, $text_y_coord[$type], $text_x_coord[$type] + 300, $text_y_coord[$type] + intval(ImageFontHeight(2) * 1.25), $grey);

			if (isset($side_modifier[$link['Rack1']])) {
				$side_modifier[$link['Rack1']] += $modifier;
			}
			else {
				$side_modifier[$link['Rack1']] = $modifier;
			}

			if (isset($side_modifier[$link['Rack2']])) {
				$side_modifier[$link['Rack2']] += $modifier;
			}
			else {
				$side_modifier[$link['Rack2']] = $modifier;
			}

			if (isset($dev_modifier[$link['Device1']])) {
				$dev_modifier[$link['Device1']] += $modifier;
			}
			else {
				$dev_modifier[$link['Device1']] = $modifier;
			}

			if (isset($dev_modifier[$link['Device2']])) {
				$dev_modifier[$link['Device2']] += $modifier;
			}
			else {
				$dev_modifier[$link['Device2']] = $modifier;
			}

			$r1x = $d1x - $rack_margin;
			$i = 0;
			while ($r1x > 0) {
				$r1x = $r1x - $rack_width - $rack_buffer;
				$i++;
			}
			$r1x = $rack_margin + ($rack_width * $i) + ($rack_buffer * $i);
			$r1x = ($rack_width * $i) + ($rack_buffer * $i);

			$r2x = $d2x - $rack_margin;
			$j = 0;
			while ($r2x > 0) {
				$r2x = $r2x - $rack_width - $rack_buffer;
				$j++;
			}
			$r2x = $rack_margin + ($rack_width * ($j - 1)) + ($rack_buffer * $j);
			$r2x = ($rack_width * ($j - 1)) + ($rack_buffer * $j);

			ImageLine($image, $d1x, $d1y, $d1x, $d1y + $dev_modifier[$link['Device1']], $colour);
			ImageLine($image, $d1x, $d1y + $dev_modifier[$link['Device1']], $r1x + $side_modifier[$link['Rack1']], $d1y + $dev_modifier[$link['Device1']], $colour);
			if ($link['Rack1'] == $link['Rack2']) {
				ImageLine($image, $r1x + $side_modifier[$link['Rack1']], $d1y + $dev_modifier[$link['Device1']], $r1x + $side_modifier[$link['Rack1']], $d2y + $dev_modifier[$link['Device2']], $colour);
				ImageLine($image, $r1x + $side_modifier[$link['Rack1']], $d2y + $dev_modifier[$link['Device2']], $d2x, $d2y + $dev_modifier[$link['Device2']], $colour);
//				ImageLine($image, $d2x, $d2y + $dev_modifier[$link['Device2']], $d2x, $d2y, $colour);
			}
			else {
				$top_modifier += $modifier;
//				ImageLine($image, $d1x, $d1y, $d1x, $d1y + $dev_modifier[$link['Device1']], $colour);
//				ImageLine($image, $d1x, $d1y + $dev_modifier[$link['Device1']], $r1x + $side_modifier[$link['Rack1']], $d1y + $dev_modifier[$link['Device1']], $colour);
				ImageLine($image, $r1x + $side_modifier[$link['Rack1']], $d1y + $dev_modifier[$link['Device1']], $r1x + $side_modifier[$link['Rack1']], $y - $top_modifier, $colour);
				ImageLine($image, $r1x + $side_modifier[$link['Rack1']], $y - $top_modifier, $d2x - ($d2x - $r2x) + $rack_width + $side_modifier[$link['Rack2']], $y - $top_modifier, $colour);
				ImageLine($image, $d2x - ($d2x - $r2x) + $rack_width + $side_modifier[$link['Rack2']], $y - $top_modifier, $d2x - ($d2x - $r2x) + $rack_width + $side_modifier[$link['Rack2']], $d2y + $dev_modifier[$link['Device2']], $colour);
				ImageLine($image, $d2x - ($d2x - $r2x) + $rack_width + $side_modifier[$link['Rack2']], $d2y + $dev_modifier[$link['Device2']], $d2x, $d2y + $dev_modifier[$link['Device2']], $colour);
			}
			ImageLine($image, $d2x, $d2y + $dev_modifier[$link['Device2']], $d2x, $d2y, $colour);


			unset($i ,$j, $r1x, $r2x, $ry);
		}
	}

	ob_start();
	ImagePng($image);
	$contents = ob_get_contents();
	ob_end_clean();

	$new_site = str_replace(' ', '_', $site);

	$fh = fopen("/tmp/$new_site.png", "w");
	fwrite($fh, $contents);
	fclose($fh);
}

$devices    = array();
$links      = array();
$sites      = array();
$ports      = array();
$racks      = array();
$port_count = array();

GetMySQLData();

foreach($sites as $site) {
	$image = "";
	CountRacksInSite($site);
	CountLinks($site);
	Draw($site);
}
