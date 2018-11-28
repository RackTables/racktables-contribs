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
//         0.6.1: Wrap every X racks
//         0.6.2: Enable tag coloring and base on new renderRacks.
//                Split Name, Rack & Zero-U into separate rows to improve alignment.

$tab['row']['full_row_view'] = 'Full Row View';
$tabhandler['row']['full_row_view'] = 'FullRowView';
$ophandler['row']['full_row_view']['preparePrint'] ='preparePrint';

// Set variables
$frvVersion = "0.6.2";
$wrapRacks = 5;
$frv_topRow = "";
$frv_middleRow = "";
$frv_bottomRow = "";

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
    global $wrapRacks;
    global $frv_topRow;
    global $frv_middleRow;
    global $frv_bottomRow;
    $rowData = getRowInfo ($row_id);

    $cellfilter = getCellFilter();
    $rackList = filterCellList (listCells ('rack', $row_id), $cellfilter['expression']);
    // echo "<form method=post name=ImportObject action='?module=redirect&page=row&row_id=$row_id&tab=full_row_view&op=preparePrint'>";
    echo "<font size=1em color=gray>version ${frvVersion}&nbsp;</font>";
    // echo "<input type=submit name=got_very_fast_data value='Print view'>";
    // echo "</form>";
    echo '<table>';  // Start of the main table
    $count = 1;
    foreach ($rackList as $rack) 
    {
        if($count > $wrapRacks) {
            // Wrap every $wrapRacks racks
            echo '<tr>';	// Start of Top Row - Rack Names
            echo $frv_topRow;
            echo '</tr>';
            echo '<tr>';	// Start of Middle Row - Racks
            echo $frv_middleRow;
            echo '</tr>';
            echo '<tr>';	// Start of the Bottom Row - Zero U Spce
            echo $frv_bottomRow;
            echo '</tr>';
            $count = 1;  // reset rack count to 1
            $frv_topRow = "";
            $frv_middleRow = "";
            $frv_bottomRow = "";
        }
	// echo "<br>Schrank: ${rack['name']} ${rack['id']}";
	// $rackData = spotEntity ('rack', ${rack['id']});

        $frv_topRow .= '<td nowrap="nowrap" valign="bottom">' .
                    '<div class="phgrack" style="float: top; width: 240px">';
        $frv_middleRow .= '<td nowrap="nowrap" valign="bottom">' .
                    '<div class="phgrack" style="float: top; width: 240px">';
        $frv_bottomRow .= '<td nowrap="nowrap" valign="top">' .
                    '<div class="phgrack" style="float: top; width: 240px">';

        renderReducedRack("${rack['id']}");

        $frv_topRow .=  '</div></td>';
        $frv_middleRow .= '</div></td>';
        $frv_bottomRow .= '</div></td>';

        $count++;
    }
    // Final print of rows
    echo '<tr>';	// Start of Top Row - Rack Names
    echo $frv_topRow;
    echo '</tr>';
    echo '<tr>';	// Start of Middle Row - Racks
    echo $frv_middleRow;
    echo '</tr>';
    echo '<tr>';	// Start of the Bottom Row - Zero U Spce
    echo $frv_bottomRow;
    echo '</tr>';
    $count = 1;  // reset rack count to 1
    $frv_topRow = "";
    $frv_middleRow = "";
    $frv_bottomRow = "";
    echo '</tr></table>';
}


// This is from interface.php: renderRack
// This function renders rack as HTML table.
function renderReducedRack ($rack_id)
{
	global $frv_topRow;
	global $frv_middleRow;
	global $frv_bottomRow;

	$rackData = spotEntity ('rack', $rack_id);
	amplifyCell ($rackData);
	markAllSpans ($rackData);
	setEntityColors ($rackData);

	$frv_topRow .= '<center>' .
				'<table border=0>' .
				'<tr>' .
					'<td valign=middle>' .
						'<h2>' . mkA ($rackData['name'], 'rack', $rackData['id']) .
						'</h2>' .
					'</td>' .
				'</tr>' .
				'</table>' .
				"</center>\n";

	$frv_middleRow .= '<center>' .
						"<table class=rackphg border=0 cellspacing=0 cellpadding=1>\n" .
						'<tr>' .
							'<th width="10%">&nbsp;</th>' .
							'<th width="20%">Front</th>' .
							'<th width="50%">Interior</th>' .
							'<th width="20%">Back</th>' .
						"</tr>\n";

	$reverse = considerConfiguredConstraint ($rackData, 'REVERSED_RACKS_LISTSRC');
	for ($i = $rackData['height']; $i > 0; $i--)
	{
		$frv_middleRow .= "<th>" . inverseRackUnit ($rackData['height'], $i, $reverse) . "</th>";
		for ($locidx = 0; $locidx < 3; $locidx++)
		{
			if (isset ($rackData[$i][$locidx]['skipped']))
				continue;
			$state = $rackData[$i][$locidx]['state'];

			$class = "atom state_${state}";

			if (isset ($rackData[$i][$locidx]['hl']))
				$class .= $rackData[$i][$locidx]['hl'];

			if($state == 'T')
			{
				$objectData = spotEntity ('object', $rackData[$i][$locidx]['object_id']);
				setEntityColors ($objectData);
				$class .= getCellClass ($objectData, 'atom_plain');
			}

			$frv_middleRow .= "<td class='${class}'";

			if (isset ($rackData[$i][$locidx]['colspan']))
				$frv_middleRow .= ' colspan=' . $rackData[$i][$locidx]['colspan'];
			if (isset ($rackData[$i][$locidx]['rowspan']))
				$frv_middleRow .= ' rowspan=' . $rackData[$i][$locidx]['rowspan'];
			$frv_middleRow .= ">";
			switch ($state)
			{
				case 'T':
					$frv_middleRow .= getOutputOf('printObjectDetailsForRenderRack', $rackData[$i][$locidx]['object_id']);
					break;
				case 'A':
					$frv_middleRow .= '<div title="This rackspace does not exist">&nbsp;</div>';
					break;
				case 'F':
					$frv_middleRow .= '<div title="Free rackspace">&nbsp;</div>';
					break;
				case 'U':
					$frv_middleRow .= '<div title="Problematic rackspace, you CAN\'T mount here">&nbsp;</div>';
					break;
				default:
					$frv_middleRow .= '<div title="No data">&nbsp;</div>';
					break;
			}
			$frv_middleRow .= '</td>';
		}
		$frv_middleRow .= "</tr>\n";
	}
	$frv_middleRow .= "</table></center>\n";

	// Get a list of all of objects Zero-U mounted to this rack
	$zeroUObjects = getChildren ($rackData, 'object');
	uasort ($zeroUObjects, 'compare_name');
	if (count ($zeroUObjects) > 0)
	{
		$frv_bottomRow .= '<br>' .
							'<center>' .
								'<table width="75%" class=rack border=0 cellspacing=0 cellpadding=1>' . "\n" .
									'<tr>' .
										'<th>Zero-U:</th>' .
									"</tr>\n";

		foreach ($zeroUObjects as $zeroUObject)
		{
			$state = 'T';
			if ($zeroUObject['has_problems'] == 'yes')
				$state .= 'w';

			$class = "atom state_${state}";
			setEntityColors ($zeroUObject);
			$class .= getCellClass ($zeroUObject, 'atom_plain');

			$frv_bottomRow .= "<tr><td class='${class}'>";
			$frv_bottomRow .= getOutputOf('printObjectDetailsForRenderRack', $zeroUObject['id']);
			$frv_bottomRow .= "</td></tr>\n";
		}
		$frv_bottomRow .= "</table></center>\n";
	}
}
