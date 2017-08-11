<?php
//
// Render a full row of racks view with server names
//
// Written by Philipp Grau <phgrau@zedat.fu-berlin.de>
// 
//
// The purpose of this plugin is to render a web page with all racks of a row
// with clickable hostnames and display the first tag.
// Currently five racks will be grouped in a row.
//
// History
// Version 0.1: Initial release (Tested on 0.20.4)
//         0.2: Removed call of markupObjectProblems(), function was removed in 0.20.5
//         0.3: Add patch for interface for rotated labels in layout style V (for bladecenters)
//         0.4: Display rack row from left to right, no wrapping
//         0.5: Rearrange files for simpler installation.
//         0.6: Do not call deprecated functions.

$tab['row']['full_row_view'] = 'Full Row View';
$tabhandler['row']['full_row_view'] = 'FullRowView';
$ophandler['row']['full_row_view']['preparePrint'] ='preparePrint';

// Set variables
$frvVersion = "0.6";

function preparePrint()
{
    return showSuccess ("Created <a href=\"rowprint.php\">Print preview</a>!");
}


// display the import page.
function FullRowView()
{
    // load a customized stylesheet
addCSS(<<<ENDOFCSS
.rackphg {
	font: bold 10px Verdana, sans-serif;
	border: 1px solid black;
	border-top: 0px solid black;
	border-right: 0px solid black;
	text-align: center;
}

.rackphg th {
	border-top: 1px solid black;
	border-right: 1px solid black;
}

.rackphg td {
	border-top: 1px solid black;
	border-right: 1px solid black;
}

.rackphg a {
	font: 10px Verdana, sans-serif;
	color: #000;
	text-decoration: none;
}

.rackphg a:hover {
	text-decoration: underline;
}
ENDOFCSS
, TRUE);
    if (isset($_REQUEST['row_id']))
      $row_id = $_REQUEST['row_id'];
    else
      $rack_id=1;

    global $frvVersion;
    $rowData = getRowInfo ($row_id);

    $cellfilter = getCellFilter();
    $rackList = filterCellList (listCells ('rack', $row_id), $cellfilter['expression']);
    // echo "<form method=post name=ImportObject action='?module=redirect&page=row&row_id=$row_id&tab=full_row_view&op=preparePrint'>";
    echo "<font size=1em color=gray>version ${frvVersion}&nbsp;</font>";
    // echo "<input type=submit name=got_very_fast_data value='Print view'>";
    // echo "</form>";
    echo '<table><tr><td nowrap="nowrap" valign="top">';
    $count = 1;
    foreach ($rackList as $rack) 
    {
	// echo "<br>Schrank: ${rack['name']} ${rack['id']}";
	// $rackData = spotEntity ('rack', ${rack['id']});
	echo '<div class="phgrack" style="float: top; width: 240px">';
	renderReducedRack("${rack['id']}");
	echo '</div>';
        echo '</td><td nowrap="nowrap" valign="top">';

    }
    echo '</td></tr></table>';
}

// This is form interface.php: renderRack
// This function renders rack as HTML table.
function renderReducedRack ($rack_id, $hl_obj_id = 0)
{
	$rackData = spotEntity ('rack', $rack_id);
	amplifyCell ($rackData);
	markAllSpans ($rackData);
	if ($hl_obj_id > 0)
		highlightObject ($rackData, $hl_obj_id);
	// markupObjectProblems ($rackData); // Function removed in 0.20.5
	echo "<center><table border=0><tr valign=middle>";
	echo '<td><h2>' . mkA ($rackData['name'], 'rack', $rackData['id']) . '</h2></td>';
	echo "</h2></td></tr></table>\n";
	echo "<table class=rackphg border=0 cellspacing=0 cellpadding=1>\n";
	echo "<tr><th width='10%'>&nbsp;</th><th width='20%'>Front</th>";
	echo "<th width='50%'>Interior</th><th width='20%'>Back</th></tr>\n";
	for ($i = $rackData['height']; $i > 0; $i--)
	{
		echo "<tr><td>" . inverseRackUnit ($i, $rackData) . "</td>";
		for ($locidx = 0; $locidx < 3; $locidx++)
		{
			if (isset ($rackData[$i][$locidx]['skipped']))
				continue;
			$state = $rackData[$i][$locidx]['state'];
			echo "<td class='atom state_${state}";
			if (isset ($rackData[$i][$locidx]['hl']))
				echo $rackData[$i][$locidx]['hl'];
			echo "'";
			if (isset ($rackData[$i][$locidx]['colspan']))
				echo ' colspan=' . $rackData[$i][$locidx]['colspan'];
			if (isset ($rackData[$i][$locidx]['rowspan']))
				echo ' rowspan=' . $rackData[$i][$locidx]['rowspan'];
			echo ">";

			switch ($state)
			{
				case 'T':
					printObjectDetailsForRenderRack($rackData[$i][$locidx]['object_id']);
			                // TODO set background color based on the tag
			                $o = spotEntity ('object',$rackData[$i][$locidx]['object_id']);
			                while ( list ($key,$val) = each( $o['etags'] )) {
					    echo "<div style='font: 8px Verdana,sans-serif; text-decoration:none; color=black'>";
					    echo $val['tag'];
					    echo "</div>";
					    break;
					}
					break;
				case 'A':
					echo '<div title="This rackspace does not exist">&nbsp;</div>';
					break;
				case 'F':
					echo '<div title="Free rackspace">&nbsp;</div>';
					break;
				case 'U':
					echo '<div title="Problematic rackspace, you CAN\'T mount here">&nbsp;</div>';
					break;
				default:
					echo '<div title="No data">&nbsp;</div>';
					break;
			}
			echo '</td>';
		}
		echo "</tr>\n";
	}
	echo "</table>\n";
	// Get a list of all of objects Zero-U mounted to this rack
	$zeroUObjects = getEntityRelatives('children', 'rack', $rack_id);
	if (count ($zeroUObjects) > 0)
	{
		echo "<br><table width='75%' class=rack border=0 cellspacing=0 cellpadding=1>\n";
		echo "<tr><th>Zero-U:</th></tr>\n";
		foreach ($zeroUObjects as $zeroUObject)
		{
			$state = ($zeroUObject['entity_id'] == $hl_obj_id) ? 'Th' : 'T';
			echo "<tr><td class='atom state_${state}'>";
			printObjectDetailsForRenderRack($zeroUObject['entity_id']);
			echo "</td></tr>\n";
		}
		echo "</table>\n";
	}
	echo "</center>\n";
}
