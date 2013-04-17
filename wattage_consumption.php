<?php

//-----------------------------------------------------------------------------------
// Wattage Consumption - written by curtisb
//
//  Updated for Racktables-0.20.4 by Mark Wilkinson
//          bonus of Rack Wattage portlet in Rack Page if portlet patch applied (bugs.racktables.org ID 691)
//
//
// Usage - Create attribute 'Wattage consumption' and map to Servers, Chassis, etc
//         Set power values for servers,etc.
//         View Report page (or Rack page)
//
$tab['reports']['watts_per_row'] = 'Enviromental Totals';
$tabhandler['reports']['watts_per_row'] = 'getWattsPerRow';
if ( function_exists('registerPortletHandler') )
	registerPortletHandler( 'rack', 'default', 'left', 'Wattage Consumption', 'renderPortletWattConsumption');

function getWattsPerRow ()
{
	// assertions
	// find the needed attributes

	global $nextorder;

	// Was this function called with a specific row_id?
	if (isset ($_REQUEST['row_id']))
	{
		assertStringArg ('row_id');
		$row_toshow = $_REQUEST['row_id'];
	}
	else
		$row_toshow = -1;

	//from renderRackspace(), interface.php:151
	$found_racks = array();
	$rows = array();
	$cellfilter = getCellFilter();
	$rackCount = 0;
	$order = 'odd';
	// get rackspace information
	foreach (getAllRows() as $row_id => $rowInfo)
	{
		$rackList = filterCellList (listCells ('rack', $row_id), $cellfilter['expression']);
		$found_racks = array_merge ($found_racks, $rackList);
		$rows[] = array
		(
			'location_id' => $rowInfo['location_id'],
			'location_name' => $rowInfo['location_name'],
			'row_id' => $row_id,
			'row_name' => $rowInfo['name'],
			'racks' => $rackList
		);
		$rackCount += count ($rackList);
	}

	// Main layout starts.
	echo "<table border=0 class=objectview cellspacing=0 cellpadding=0>";

	// Left portlet with list of rows.
	echo "<tr><td class=pcleft width='50%'>";
	startPortlet ('Rack Rows (' . count ($rows) . ')');
	echo "<table border=0 cellspacing=0 cellpadding=3 width='100%'>\n";
	foreach ($rows as $row)
	{
		$row_id = $row['row_id'];
		$row_name = $row['row_name'];
		$row_location = $row['location_name'];
		$rackList = $row['racks'];

		echo "<tr class=row_${order}><td width='20%'></td><td class=tdleft>";
		if (! count ($rackList))
			echo "${row_location} - ${row_name} (empty row)";
		else
			echo "<a href='" . makeHref (array ('page' => 'reports', 'tab' => 'watts_per_row', 'row_id' => $row_id)) . "'>${row_location} - ${row_name}</a>";
		echo "<td><tr>\n";
		$order = $nextorder[$order];
	}

	echo "</td></tr>\n";
	echo "</table><br>\n";
	finishPortlet();

	echo "</td><td class=pcright>";

	// Right Portlet: Draw the racks in the selected row
	if ($row_toshow > -1)
	{
		$rowInfo = getRowInfo ($row_toshow);
		$cellfilter = getCellFilter();
		$rackList = filterCellList (listCells ('rack', $row_toshow), $cellfilter['expression']);

		$rackwidth = getRackImageWidth() * getConfigVar ('ROW_SCALE');
		// Maximum number of racks per row is proportionally less, but at least 1.
		$maxPerRow = max (floor (getConfigVar ('RACKS_PER_ROW') / getConfigVar ('ROW_SCALE')), 1);
		$rackListIdx = 0;
		$rowTotalWattage = 0;
		$order = 'odd';
		startPortlet ('Racks within ' . $rowInfo['name'] . ' (' . count($rackList) . ')' );
		echo "<table border=0 cellspacing=5 align='center'><tr>";
		foreach ($rackList as $rack)
		{
			$rackTotalWattage = 0;
			// see renderRack(), interface.php:311
			$rackData = spotEntity ('rack', $rack['id']);
			amplifyCell ($rackData);
			$objectChildren = getEntityRelatives ('children', 'object', $objectData['id']);
			foreach ($rackData['mountedObjects'] as $object)
			{
				$objectData = spotEntity ('object', $object);
				amplifyCell ($objectData);
				foreach (getAttrValues ($objectData['id']) as $record)
					if ($record['name'] == 'Wattage consumption')
						$rackTotalWattage += $record['value'];
			}
			if ($rackListIdx % $maxPerRow == 0)
				echo $rackListIdx > 0 ? '</tr><tr>' : '<tr>';
			echo "<td align=center class=row_${order}><a href='" . makeHref (array( 'page' => 'rack', 'rack_id' => $rack['id'])) . "'>";

			echo "<img border=0 width=${rackwidth} height=" . (getRackImageHeight ($rack['height']) * getConfigVar ('ROW_SCALE'));
			echo " title='${rack['height']} units'";
			echo "src='?module=image&img=minirack&rack_id=${rack['id']}'>";
			echo "<br>${rack['name']} ($rackTotalWattage)</a></td>";
			$order = $nextorder[$order];
			$rackListIdx++;
			$rowTotalWattage += $rackTotalWattage;
		}

		echo "</tr><tr><td align=center colspan=";
		print (count ($rackList));
		echo "><br><b>The row total for attribute Wattage consuption is:  $rowTotalWattage</b></td>\n";

		echo "</tr></table>\n";
		finishPortlet();
	}
	echo "</td></tr></table>";
}

function renderPortletWattConsumption ($info)
{
	$rackTotalWattage = 0;
	$rackData = spotEntity ('rack', $info['id']);
	amplifyCell ($rackData);
	$objectChildren = getEntityRelatives ('children', 'object', $objectData['id']);
	foreach ($rackData['mountedObjects'] as $object)
	{
		$objectData = spotEntity ('object', $object);
		amplifyCell ($objectData);
		foreach (getAttrValues ($objectData['id']) as $record)
			if ($record['name'] == 'Wattage consumption')
				$rackTotalWattage += $record['value'];
	}
	startPortlet ('Wattage Consumption' );
	echo "<table border=0 cellspacing=5 align='center'><tr>";
	echo "<td>The total for attribute Wattage consuption is:  <b>$rackTotalWattage</b></td>\n";
	echo "</tr></table>\n";
	finishPortlet();
}

?>
