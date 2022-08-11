<?php

global $RACK_W;
global $BORDER_W;
global $ITEM_W;
global $BORDER_H;
global $RU_H;
global $IMGZOOM;
global $IMGCGZOOM;
global $SCREW_W;
global $LF;

global $racktables_plugins_dir;

$RACK_W = 392;
$BORDER_W = 42;
$ITEM_W = $RACK_W - (2 * $BORDER_W);
$BORDER_H = 33;
$RU_H = 29;
$IMGZOOM = 45;
$IMGCGZOOM = 80;
$SCREW_W = 10;
$LF =  "\n";

function plugin_graphRacks_info ()
{
	return array
	(
		'name' => 'graphRacks',
		'longname' => 'Graph Racks',
		'version' => '1.0',
		'home_url' => 'by Alberto Avai'
	);
}

function plugin_graphRacks_init ()
{
	global $tab;
	registerTabHandler ('row', 'graphics', 'renderGraphicRow');
	$tab['row']['graphics'] = 'Graphics';
	registerTabHandler ('rack', 'graphics', 'renderGraphicOneRack');
	$tab['rack']['graphics'] = 'Graphics';
}

function plugin_graphRacks_install ()
{
	return TRUE;
}

function plugin_graphRacks_uninstall ()
{
	return TRUE;
}

function plugin_graphRacks_upgrade ()
{
	return TRUE;
}

function renderGraphicRow($row_id)
{
	global $racktables_plugins_dir;

	$rowInfo = getRowInfo ($row_id);
	startPortlet('');
	$h = 620; // e.g. 900 - 280 (or 1080 - 280)
	$w = 1560; // e.g. 1600 - 40 (or 1920 - 40)
	$row = normalizeStringForFilename($rowInfo['name']);
	if (isset($_REQUEST['w']) && isset($_REQUEST['h']))
	{
		$w = $_REQUEST['w'];
		$h = $_REQUEST['h'];
	}
	else
	{
		echo "\n<script type='text/javascript'>
<!--
window.getWinH = function(){
  if (window.innerHeight != undefined) {
    return window.innerHeight-165; // Chrome, FF, Opera
  } else {
    var B = top.document.body;
    var D = top.document.documentElement;
    return Math.max(D.clientHeight, B.clientHeight)-150; // IE
  }
}
location='".$_SERVER['REQUEST_URI']."&w='+(screen.width-40)+'&h='+window.getWinH();
//-->
</script>\n";
	}

	echo "<div name='grcontents'>";
	$frame = file_get_contents($racktables_plugins_dir . '/rackimg/' . $row . '.htm');
	if ($frame !== FALSE)
		echo $frame;
	echo "</div>";

	finishPortlet();
}

function renderGraphicOneRack($rack_id)
{
	global $racktables_plugins_dir;

	$rackData = spotEntity ('rack', $rack_id);
	startPortlet('');
	$h = 620; // e.g. 900 - 280 (or 1080 - 280)
	$w = 1560; // e.g. 1600 - 40 (or 1920 - 40)
	$rack = normalizeStringForFilename($rackData['name']);
	if (isset($_REQUEST['w']) && isset($_REQUEST['h']))
	{
		$w = $_REQUEST['w'];
		$h = $_REQUEST['h'];
	}
	else
	{
		echo "\n<script type='text/javascript'>
<!--
window.getWinH = function(){
  if (window.innerHeight != undefined) {
    return window.innerHeight-165; // Chrome, FF, Opera
  } else {
    var B = top.document.body;
    var D = top.document.documentElement;
    return Math.max(D.clientHeight, B.clientHeight)-150; // IE
  }
}
location='".$_SERVER['REQUEST_URI']."&w='+(screen.width-40)+'&h='+window.getWinH()+'&rack=${rack}&rackid=${rack_id}';
//-->
</script>\n";
	}

	echo "<div name='grcontents'>";
	$frame = file_get_contents($racktables_plugins_dir . "/rackimg/racksel.htm");
	if ($frame !== FALSE)
		echo $frame;
	echo "</div>";

	finishPortlet();
}

function graphRacks ($parent_id_filter = 0, $dbg = 0)
{
	global $racktables_plugins_dir;
	global $RACK_W, $BORDER_W, $ITEM_W, $BORDER_H, $RU_H, $IMGZOOM;
	global $LF;

	set_time_limit(120); // 2 minutes (4 x default 30 sec) // try to avoid: PHP Fatal error:  Maximum execution time of 30 seconds exceeded

	if ($dbg > 0)
	{
		echo "Start ".__FUNCTION__." (".date('Y-m-d H:i:s').")$LF$LF";
		echo "parent_id_filter = $parent_id_filter$LF$LF";
	}

	$top = imagecreatefrompng($racktables_plugins_dir . "/rackimg/frame/top.png");
	$left = imagecreatefrompng($racktables_plugins_dir . "/rackimg/frame/left.png");
	$right = imagecreatefrompng($racktables_plugins_dir . "/rackimg/frame/right.png");
	$bottom = imagecreatefrompng($racktables_plugins_dir . "/rackimg/frame/bottom.png");
	$empty = imagecreatefrompng($racktables_plugins_dir . "/rackimg/frame/empty.png");
	$rear = imagecreatefrompng($racktables_plugins_dir . "/rackimg/frame/rear.png");
	$unknown = imagecreatefrompng($racktables_plugins_dir . "/rackimg/frame/unknown.png");
  $htmlmap = "";

	array_map('unlink', glob($racktables_plugins_dir . "/rackimg/*.png"));
	array_map('unlink', glob($racktables_plugins_dir . "/rackimg/*.htm"));

	$racks_javascript_contents = "
var ax=0;
var ay=0;

function mouseIEO(){ // IE, Opera
  ay = event.clientY;
  ax = event.clientX;
}

function mouseNS(e){ // FireFox
  ay = e.pageY;
  ax = e.pageX;
}

function showOverInfo(info) {
  if ((document.getElementById&&!document.all)){ // FireFox
    document.onmousemove=mouseNS;
  } else { // IE, Opera
    document.onmousemove=mouseIEO;
  }
  if (info.length) {
    var delta=20;
    var urlparams=location.search;
    if (urlparams.indexOf('rackspace') !== -1) { // match
      delta=320;
    }
    var l = \"<p class='overinfo' style='position:absolute; top:\" + (ay-200) + \"px; left:\" + (ax-delta) + \"px;'>\" + info;
    if (document.getElementById) { // n6
      document.getElementById('hiddenlayer').innerHTML = l;
      document.getElementById('hiddenlayer').style.visibility = 'visible';
    }
    else if (document.layers) { // n4
      document.hiddenlayer.innerHTML = l;
      document.hiddenlayer.visibility = 'visible';
    }
    else if (document.all) { // ie
      document.all.hiddenlayer.innerHTML = l;
      document.all.hiddenlayer.style.visibility = 'visible';
    }
  } else {
    if (document.getElementById) { // n6
      document.getElementById('hiddenlayer').innerHTML = '';
      document.getElementById('hiddenlayer').style.visibility = 'hidden';
    }
    else if (document.layers) { // n4
      document.hiddenlayer.innerHTML = '';
      document.hiddenlayer.visibility = 'hidden';
    }
    else if (document.all) { // ie
      document.all.hiddenlayer.innerHTML = '';
      document.all.hiddenlayer.style.visibility = 'hidden';
    }
  }
}

function show_server_name(server) {
  var detail = '';
  if ((server != null) && (server.length)) {
    detail = server + '<br>';
  }
  showOverInfo(detail); // pass '' to hide
}
";
	$racks_empty_file_contents = "<p>\n&nbsp;&nbsp;&nbsp;(No content available)\n</p>\n";
	$racks_row_file_contents_init = "<style>
TABLE.grzoom { zoom: ${IMGZOOM}%; /* Webkit browsers */ zoom: 0.${IMGZOOM}; /* Other non-webkit browsers */ -moz-transform-origin: 0 0; -moz-transform: scale(0.${IMGZOOM}, 0.${IMGZOOM}); /* Moz-browsers */ }
#hiddenlayer { visibility:hidden; z-index:2; }
P.overinfo { border:2px solid black; padding:2px; background-color:#F0F0FF; font-size:14px; width:300px; text-align: center; }
A.nodec { color: black; text-decoration: none; }
</style>
<style type='text/css' media='print'>
  div.hiddenlayer { visibility:none }
  div.mainheader { display:none }
  div.menubar { display:none }
  div.tabbar { display:none }
  div.msgbar { display:none }
  div.graphmenu { display:none }
  td.pcleft { display:none }
  div.portlet { border-style:hidden; border-margin:0px }
</style>
<script src=\"../plugins/rackimg/scripts.js\" type=\"text/javascript\"></script>
<div id=\"hiddenlayer\" style=\"position:absolute; margin-left:0px\"></div>
<table cellspacing=0 cellpadding=10 border=0 rules=none style=\"font-size: 24px;\" class='grzoom'>
<tr>
<script type='text/javascript'>
<!--
var tmp = '?' + (new Date()).getTime();
";
	$racks_sel_file_contents_init = "<style>
TABLE.grzoom { zoom: ${IMGZOOM}%; /* Webkit browsers */ zoom: 0.${IMGZOOM}; /* Other non-webkit browsers */ -moz-transform-origin: 0 0; -moz-transform: scale(0.${IMGZOOM}, 0.${IMGZOOM}); /* Moz-browsers */ }
#hiddenlayer { visibility:hidden; z-index:2; }
P.overinfo { border:2px solid black; padding:2px; background-color:#F0F0FF; font-size:14px; width:300px; text-align: center; }
A.nodec { color: black; text-decoration: none; }
</style>
<style type='text/css' media='print'>
  div.hiddenlayer { visibility:none }
  div.mainheader { display:none }
  div.menubar { display:none }
  div.tabbar { display:none }
  div.msgbar { display:none }
  div.graphmenu { display:none }
  td.pcleft { display:none }
  div.portlet { border-style:hidden; border-margin:0px }
</style>
<script src=\"../plugins/rackimg/scripts.js\" type=\"text/javascript\"></script>
<script type='text/javascript'>
<!--
var tmp = '?' + (new Date()).getTime();
var rack = 'error';
var rackid = '';
var newquerystring = '?';
params = location.search;
params = params.slice(1); // delete initial '?'
var paramsArray = params.split('&');
for (var i=0; i < paramsArray.length; i++) {
  var valuesArray = paramsArray[i].split('=');
  if (valuesArray[0] == 'rack') {
    rack = valuesArray[1];
  } else if (valuesArray[0] == 'rackid') {
    rackid = valuesArray[1];
  } else {
    if (newquerystring.length > 1) {
      newquerystring += '&';
    }
    newquerystring += valuesArray[0] + '=' + valuesArray[1];
  }
}
//-->
</script>
<div id=\"hiddenlayer\" style=\"position:absolute; margin-left:0px\"></div>
<table cellspacing=0 cellpadding=10 border=0 rules=none style=\"font-size: 24px;\" width='100%' class='grzoom'>
<tr>
<script type='text/javascript'>
<!--
document.write(\"<td valign='bottom' align='center'><img src='../plugins/rackimg/\"+rack+\".png\"+tmp+\"' usemap='#map\"+rackid+\"' border='0'></td>\");
";
	$racks_row_file_contents_end = "//-->\n</script>\n</tr>\n</table>\n";

	// fill the declared values rackdecl array
	$rackdecl = array();
	foreach (scanRealmByText ('rack') as $rack)
	{
		$rackdecl[$rack['name']] = array
		(
			'value' => $rack['name'],
			'rack_id' => $rack['id'],
			'location' => $rack['location_name'],
			'location_id' => $rack['location_id'],
			'row' => $rack['row_name'],
			'height' => $rack['height'],
			'ruinv' => considerConfiguredConstraint ($rack, 'REVERSED_RACKS_LISTSRC'),
		);
	}

	$rowName = ""; // define variable outside the loop
	$prevRowName = "";
	$prevLocation = "";
	$htmlallmaps = "";
	foreach ($rackdecl as $rack_id => $rackd)
	{
		$locationTree = getLocationInfo($rackd['location_id'], $parent_id_filter);
		$locationTreeRef = normalizeStringForFilename($locationTree);
		if ($locationTree == "")
			continue;
		if ($locationTree != $prevLocation)
		{
			$file = extract_db_image_and_html($rackd['location_id'], $dbg);
			$prevLocation = $locationTree;
		}
		$rowName = $rackd['row'];
		if ($prevRowName != $rowName)
		{
			if ($prevRowName != "")
			{
				if ($dbg > 0)
					echo "SAVING row_file ($prevRowName)$LF$LF";
				$racks_row_file_contents .= $htmlmap.$racks_row_file_contents_end;
				if (file_put_contents($racktables_plugins_dir . "/rackimg/" . normalizeStringForFilename($prevRowName) . ".htm", $racks_row_file_contents) == FALSE)
				{
					echo "***** ERROR: failed to write file " . $racktables_plugins_dir . "/rackimg/" . normalizeStringForFilename($prevRowName) . ".htm$LF$LF";
				}
			}
			$racks_row_file_contents = $racks_row_file_contents_init;
			$htmlallmaps .= $htmlmap;
			$htmlmap = "";
			$prevRowName = $rowName;
		}
		$rackName = $rackd['value'];
		if ($dbg > 0)
		{
			echo "Rack: ${rackd['value']} - id ${rackd['rack_id']}$LF";
			echo "Location: ${rackd['location']} - id ${rackd['location_id']}$LF";
			echo "Location tree: $locationTree$LF";
			echo "Row: $rowName$LF$LF";
			echo "Processing rack id = ${rackd['rack_id']}$LF";
		}
		$totalH = $BORDER_H+($rackd['height']*$RU_H)+$BORDER_H;
		$img = imagecreatetruecolor($RACK_W, $totalH);
		if ($img !== false)
		{
			imageinterlace($img, 1);
			$blackbgd = imagecolorallocate ($img, 0, 0, 0);
			renderRack_frame($img, $top, $left, $right, $bottom, $empty, $rackd['height'], $rackd['ruinv'], $rackName, $dbg);
			$htmlmap .= renderRack_2image ($img, $rackd['rack_id'], $unknown, $rear, $dbg);
			$imgName = normalizeStringForFilename($rackName) . ".png";
			if ($dbg > 0)
				echo "Filename: $imgName$LF$LF";
			imagepng($img, $racktables_plugins_dir . "/rackimg/" . $imgName, 9);
			loadImageInDB($rackd['rack_id'], 'image/png', $imgName);
			imagedestroy($img);
			$racks_row_file_contents .= "document.write(\"<td valign='bottom' align='center'><a href='/racktables/index.php?page=rack&rack_id=${rackd['rack_id']}' target='_top' class='nodec'>$rackName</a><br><img src='../plugins/rackimg/${imgName}\"+tmp+\"' usemap='#map".$rackd['rack_id']."' border='0'></td>\");\n";
		}
        }
	if ($dbg > 1)
		echo "END OF LOOP$LF";
	if ($dbg > 0)
		echo "RowName = $rowName, Prev = $prevRowName$LF";
	if ($prevRowName != "")
	{
		if ($dbg > 0)
			echo "SAVING row_file ($prevRowName)$LF$LF";
		$racks_row_file_contents .= $htmlmap.$racks_row_file_contents_end;
		if (file_put_contents($racktables_plugins_dir . "/rackimg/" . normalizeStringForFilename($prevRowName) . ".htm", $racks_row_file_contents) == FALSE)
		{
			echo "***** ERROR: failed to write file " . $racktables_plugins_dir . "/rackimg/" . normalizeStringForFilename($prevRowName) . ".htm$LF$LF";
		}
		$htmlallmaps .= $htmlmap;
	}
	if ($dbg > 0)
		echo "SAVING row_file (racksel.htm)$LF$LF";
	$racks_sel_file_contents = $racks_sel_file_contents_init.$htmlallmaps.$racks_row_file_contents_end;
	if (file_put_contents($racktables_plugins_dir . "/rackimg/racksel.htm", $racks_sel_file_contents) == FALSE)
	{
		echo "***** ERROR: failed to write file " . $racktables_plugins_dir . "/rackimg/racksel.htm$LF$LF";
	}
	if (file_put_contents($racktables_plugins_dir . "/rackimg/empty.htm", $racks_empty_file_contents) == FALSE)
	{
		echo "***** ERROR: failed to write file " . $racktables_plugins_dir . "/rackimg/empty.htm$LF$LF";
	}
	if (file_put_contents($racktables_plugins_dir . "/rackimg/scripts.js", $racks_javascript_contents) == FALSE)
	{
		echo"***** ERROR: failed to write file " . $racktables_plugins_dir . "/rackimg/scripts.js$LF$LF";
	}

	imagedestroy($top);
	imagedestroy($left);
	imagedestroy($right);
	imagedestroy($bottom);
	imagedestroy($empty);
	imagedestroy($rear);
	imagedestroy($unknown);

	if ($dbg > 0)
	{
		echo "The end! (".date('Y-m-d H:i:s').")$LF$LF";
		return;
	}
}

function renderRack_frame ($img, $top, $left, $right, $bottom, $empty, $numRU = 42, $invRU = false, $rackName = "", $dbg = 0)
{
	global $RACK_W, $BORDER_W, $ITEM_W, $BORDER_H, $RU_H;
	imagecopy($img, $top, 0, 0, 0, 0, $RACK_W, $BORDER_H);
	imagecopy($img, $bottom, 0, $BORDER_H+($RU_H*$numRU), 0, 0, $RACK_W, $BORDER_H);
	$lightgray = imagecolorallocate($img, 128, 128, 128);
	for ($i = 0; $i < $numRU; $i++)
	{
		imagecopy($img, $left, 0, $BORDER_H+($RU_H*$i), 0, 0, $BORDER_W, $RU_H);
		imagecopy($img, $empty, $BORDER_W, $BORDER_H+($RU_H*$i), 0, 0, $ITEM_W, $RU_H);
		imagecopy($img, $right, $BORDER_W+$ITEM_W, $BORDER_H+($RU_H*$i), 0, 0, $BORDER_W, $RU_H);
		if ($invRU)
			$ru = $i + 1;
		else
			$ru = $numRU - $i;
		imagestring($img, 1, 30+($ru<10?5:0), $BORDER_H+($RU_H*$i)+10, $ru, $lightgray); 
		imagestring($img, 1, $RACK_W-$BORDER_W+3, $BORDER_H+($RU_H*$i)+10, $ru, $lightgray); 
	}
	imagestring($img, 5, $RACK_W/2-(9*strlen($rackName)/2), 1, $rackName, $lightgray);
}

function renderRack_2image ($img, $rack_id, $unknown, $rear, $dbg)
{
	global $RACK_W, $BORDER_W, $ITEM_W, $BORDER_H, $RU_H, $SCREW_W;
	global $LF;

	if ($dbg > 1)
		echo "Called for rack_id: $rack_id$LF";
	$htmlmap = "document.write('<map name=\"map".$rack_id."\">');\n";
	$htmlmapback = "";
	$rackData = spotEntity ('rack', $rack_id);
	amplifyCell ($rackData);
	markAllSpans ($rackData);
	// render back
	$locidx = 2;
	for ($i = $rackData['height']; $i > 0; $i--)
	{
		if ($dbg > 1)
			echo "RU (back): $i$LF";
		if (isset ($rackData[$i][$locidx]['skipped']) && isset ($rackData[$i][$locidx-1]['skipped'])) // usually when previous locidx's rowspan>1
			continue;
		if (isset ($rackData[$i][$locidx]['skipped']) && ($rackData[$i][$locidx-1]['state'] == 'F')) // usually when previous row's rowspan>1
			continue;
		$rowspan = 1;
		if (isset ($rackData[$i][$locidx]['rowspan']))
			$rowspan = $rackData[$i][$locidx]['rowspan'];
		else if (isset ($rackData[$i][$locidx-1]['rowspan']))
			$rowspan = $rackData[$i][$locidx-1]['rowspan'];
		if ($dbg > 1)
			echo "rowspan=$rowspan$LF\nstate: ".$rackData[$i][$locidx]['state']."$LF";
		if ($rackData[$i][$locidx]['state'] == 'T')
		{
			imagecopyresized($img, $rear, $BORDER_W+$SCREW_W, $BORDER_H+($RU_H*($rackData['height']-$i)), 0, 0, $ITEM_W-2*$SCREW_W, $RU_H*$rowspan, imagesx($rear), imagesy($rear));
			$objectData = spotEntity ('object', $rackData[$i][$locidx]['object_id']);
			$x0 = $BORDER_W;
			$y0 = $BORDER_H+($RU_H*($rackData['height']-$i));
			$x1 = $BORDER_W+$ITEM_W-1;
			$y1 = $BORDER_H+($RU_H*($rackData['height']-$i))+($RU_H*$rowspan)-1;
			$htmlmapback .= "document.write('  <area shape=\"rect\" coords=\"${x0},${y0},${x1},${y1}\" href=\"/racktables/index.php?page=object&object_id=".$rackData[$i][$locidx]['object_id']."\" target=\"_top\" border=\"0\" onmouseover=\"show_server_name(\'${objectData['name']}\');\" onmouseout=\"show_server_name(\'\');\">');\n";
		}
	}
	// render front
	$locidx = 0;
	for ($i = $rackData['height']; $i > 0; $i--)
	{
		if ($dbg > 1)
			echo "RU: $i$LF";
		if (isset ($rackData[$i][$locidx]['skipped'])) // usually when previous row's rowspan>1
			continue;
		$rowspan = 1;
		if (isset ($rackData[$i][$locidx]['rowspan']))
			$rowspan = $rackData[$i][$locidx]['rowspan'];
		if ($dbg > 1)
			echo "rowspan=$rowspan$LF\nstate: ".$rackData[$i][$locidx]['state']."$LF";
		if ($rackData[$i][$locidx]['state'] == 'T')
		{
			$imgOk = 0;
			$files = getFilesOfEntityWithTag('object', $rackData[$i][$locidx]['object_id'], 'Front image'); // first: create tag and assign it to all 'front' images
			if (count($files) > 0)
			{
				if ($dbg > 1)
					echo "Numero files: ".count($files)."$LF\nFirst file: ".array_values($files)[0]['name']."$LF\n";
				$file = getFile(array_values($files)[0]['id']);
				if ($file !== NULL)
				{
					$objectData = spotEntity ('object', $rackData[$i][$locidx]['object_id']);
					$image = imagecreatefromstring ($file['contents']);
					imagecopyresized($img, $image, $BORDER_W, $BORDER_H+($RU_H*($rackData['height']-$i)), 0, 0, $ITEM_W, $RU_H*$rowspan, imagesx($image), imagesy($image));
					imagedestroy($image);
					$x0 = $BORDER_W;
					$y0 = $BORDER_H+($RU_H*($rackData['height']-$i));
					$x1 = $BORDER_W+$ITEM_W-1;
					$y1 = $BORDER_H+($RU_H*($rackData['height']-$i))+($RU_H*$rowspan)-1;
					$htmlmap .= "document.write('  <area shape=\"rect\" coords=\"${x0},${y0},${x1},${y1}\" href=\"/racktables/index.php?page=object&object_id=".$rackData[$i][$locidx]['object_id']."\" target=\"_top\" border=\"0\" onmouseover=\"show_server_name(\'${objectData['name']}\');\" onmouseout=\"show_server_name(\'\');\">');\n";
					$imgOk = 1;
				}
			}
			if ($imgOk == 0)
				imagecopyresized($img, $unknown, $BORDER_W+$SCREW_W, $BORDER_H+($RU_H*($rackData['height']-$i)), 0, 0, $ITEM_W-2*$SCREW_W, $RU_H*$rowspan, imagesx($unknown), imagesy($unknown));
		}
	}
	$htmlmap .= $htmlmapback;
	$htmlmap .= "document.write('</map>');\n";
	return $htmlmap;
}

function extract_db_image_and_html ($locationId = 0, $dbg = 0)
{
	global $racktables_plugins_dir;
	global $LF;
	global $IMGCGZOOM;

	$location = spotEntity ('location', $locationId);
	$filename = normalizeStringForFilename($location['name']);
	if ($dbg > 0)
	{
		echo "location for image: ${location['name']}$LF";
		echo "filename: $filename$LF$LF";
	}
	$files = getFilesOfEntity('location', $locationId);
	if (count($files) > 0)
		$file = getFile(array_values($files)[0]['id']);
	else
		$file = NULL;
	if ($file !== NULL)
	{
		file_put_contents($racktables_plugins_dir . "/rackimg/" . $filename . ".png", $file['contents']);
		file_put_contents($racktables_plugins_dir . "/rackimg/" . $filename . ".htm", "<style> 
img.map, map area { outline: none; }
</style>
<script type='text/javascript'>
<!--
document.write(\"<img src='${filename}.png\"+tmp+\"'>\");
//-->
</script>
");
		return $filename;
	}
	return "";
}

function normalizeStringForFilename ($str = '')
{
	$str = strip_tags($str); 
	$str = preg_replace('/[\r\n\t ]+/', ' ', $str);
	$str = preg_replace('/[\"\*\/\:\<\>\?\'\|]+/', ' ', $str);
	$str = strtolower($str);
	$str = html_entity_decode( $str, ENT_QUOTES, "utf-8" );
	$str = htmlentities($str, ENT_QUOTES, "utf-8");
	$str = preg_replace("/(&)([a-z])([a-z]+;)/i", '$2', $str);
	$str = str_replace(' ', '-', $str);
	$str = rawurlencode($str);
	$str = str_replace('%', '-', $str);
	return $str;
}

function getFilesOfEntityWithTag ($entity_type = NULL, $entity_id = 0, $tag_name = NULL)
{
	$result = usePreparedSelectBlade
	(
		'SELECT FileLink.file_id, FileLink.id AS link_id, name, type, size, ctime, mtime, atime, comment ' .
		'FROM FileLink LEFT JOIN File ON FileLink.file_id = File.id LEFT JOIN TagStorage ON TagStorage.entity_id = File.id LEFT JOIN TagTree ON TagTree.id = TagStorage.tag_id ' .
		'WHERE FileLink.entity_type = ? AND FileLink.entity_id = ? AND TagTree.tag = ? AND TagStorage.entity_realm = "file" ORDER BY name',
		array ($entity_type, $entity_id, $tag_name)
	);
	$ret = array();
	while ($row = $result->fetch (PDO::FETCH_ASSOC))
		$ret[$row['file_id']] = array (
			'id' => $row['file_id'],
			'link_id' => $row['link_id'],
			'name' => $row['name'],
			'type' => $row['type'],
			'size' => $row['size'],
			'ctime' => $row['ctime'],
			'mtime' => $row['mtime'],
			'atime' => $row['atime'],
			'comment' => $row['comment'],
		);
	return $ret;
}

function loadImageInDB ($rackId, $imgType, $imgName)
{
	global $racktables_plugins_dir;
	global $LF;

	if (strtolower(substr($imgName, 0, 4)) === "rack")
		$dbImgName = $imgName;
	else
		$dbImgName = 'rack-'.$imgName;
	if (get_cfg_var('file_uploads') == 1) // Make sure the file can be uploaded
	{
		$files = getFilesOfEntity('rack', $rackId);
		$fileId = -1;
		foreach ($files as $id => $file)
		{
			if ($file['name'] == $dbImgName)
			{
				$fileId = $id;
				break;
			}
		}
		if (FALSE === $fp = fopen($racktables_plugins_dir."/rackimg/".$imgName, 'rb'))
		{
			echo "Failed to access the image file ($imgName)$LF";
			return;
		}
		if ($fileId != -1)
		{
			// replace image that already exists and is linked to rack object
			echo "REPLACING Img '$dbImgName'$LF$LF";
			commitReplaceFile ($fileId, $fp);
		}
		else
		{
			// file is not linked, but may exist !
			$fileId = findFileByName($dbImgName);
			if ($fileId == NULL)
			{
				// create new image (and link it to rack object)
				echo "ADDING Img '$dbImgName'$LF$LF";
				commitAddFile ($dbImgName, $imgType, $fp, 'Created by graphRacks');
				$fileId = lastInsertID();
			}
			else
			{
				// replace existing image (and link it to rack object)
				echo "REPLACING Unlinked Img '$dbImgName'$LF$LF";
				commitReplaceFile ($fileId, $fp);
			}
			// (re)create link to rack
			usePreparedInsertBlade ('FileLink', array('file_id' => $fileId, 'entity_type' => 'rack', 'entity_id' => $rackId));
		}
		fclose($fp);
	}
}

function getLocationInfo ($location_id, $filter = 0)
{
        $match = 0;
        $locationIdx = 0;
        $locationTree = '';
        while ($location_id)
        {
                if ($location_id == $filter)
                        $match = 1;
                if ($locationIdx == 20)
                {
                        showWarning ("Warning: There is likely a circular reference in the location tree.  Investigate location ${location_id}.");
                        break;
                }
                $parentLocation = spotEntity ('location', $location_id);
                $locationTree = " / ${parentLocation['name']}" . $locationTree;
                $location_id = $parentLocation['parent_id'];
                $locationIdx++;
        }
        if (($filter != 0) && ($match == 0))
                return "";
        return substr ($locationTree, 3);
}
