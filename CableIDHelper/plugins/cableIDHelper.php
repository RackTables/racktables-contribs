<?php
// Racktables Cable ID helper Plugin v.0.2
// Copy this file into the plugin directory

// 2014-08-20 - Mogilowski Sebastian <sebastian@mogilowski.net>

// http://www.mogilowski.net/projects/racktables


$tab['rack']['CableIDs'] = 'Cable IDs';
$tabhandler['rack']['CableIDs'] = 'CableIDTabHandler';

function CableIDTabHandler()
{
	echo '<div class=portlet><h2>Cable ID Helper</h2></div>';

	$rack = spotEntity ('rack', $_REQUEST['rack_id']);

	$result = usePreparedSelectBlade ('SELECT DISTINCT object_id FROM RackSpace WHERE rack_id = ? ', array ($rack['id']) );
	$objects = $result->fetchAll (PDO::FETCH_ASSOC);

	$cableIDs = array();
	foreach ( $objects as $object )
	{

		$pals = getObjectPortsAndLinks($object['object_id']);

		foreach ( $pals as $portLink )
		{

			if ( $portLink['cableid'] )
			{

				$new       = true;
				$dublicate = false;

				foreach ( $cableIDs as $key=>$cableID ) {

					if ( ( ( $portLink['object_id'] == $cableID['object1_id'] ) &&  ( $portLink['name'] == $cableID['object1_port'] ) ) ||
					     ( ( $portLink['object_id'] == $cableID['object2_id'] ) &&  ( $portLink['name'] == $cableID['object2_port'] ) ) )
					{

						$new = false; // Link already in List
					}

					// Check for duplicate cable ids
					if ( $new && ( $portLink['cableid'] == $cableID['cableID'] ) )
					{
						$dublicate                   = true;
						$cableIDs[$key]['dublicate'] = true;
					}

				}

				if ($new)
				{

					$cableID = array();

					$cableID['cableID']      = $portLink['cableid'];
					$cableID['object1_id']   = $portLink['object_id'];
					$cableID['object1_name'] = $portLink['object_name'];
					$cableID['object1_port'] = $portLink['name'];
					$cableID['object2_id']   = $portLink['remote_object_id'];
					$cableID['object2_name'] = $portLink['remote_object_name'];
					$cableID['object2_port'] = $portLink['remote_name'];
					$cableID['dublicate']    = $dublicate;

					array_push($cableIDs, $cableID);

				}

			}
		}

	}

	// Sort by cableIDs
	usort($cableIDs, function ($elem1, $elem2) {
		return strnatcasecmp($elem1['cableID'], $elem2['cableID']);
	});

	// Print table
	echo '<table class="cooltable" align="center" border="0" cellpadding="5" cellspacing="0">'.
			'<tbody>'.
			'  <tr>'.
			  '  <th>CableID</th>'.
			  '  <th>Object 1</th>'.
			  '  <th>Object 2</th>'.
			'  </tr>';

	$i = 0;
	foreach ( $cableIDs as $cableID )
	{
		if ( $i % 2 )
			$class = 'row_even tdleft';
		else
			$class = 'row_odd tdleft';

		if ( $cableID['dublicate'] )
			$class .= ' trerror';

		echo '<tr class="'.$class.'">'.
				'<td>'.$cableID['cableID'].'</td>'.
				'<td><a href="'.makeHref ( array( 'page' => 'object', 'object_id' => $cableID['object1_id']) ).'">'.$cableID['object1_name'].': '.$cableID['object1_port'].'</a></td>'.
				'<td><a href="'.makeHref ( array( 'page' => 'object', 'object_id' => $cableID['object2_id']) ).'">'.$cableID['object2_name'].': '.$cableID['object2_port'].'</a></td>'.
			  '</tr>';

		$i++;

	}

	echo '  </tbody>'.
		 '</table>';

}
