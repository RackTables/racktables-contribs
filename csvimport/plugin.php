<?php

function plugin_csvimport_info()
{
	return array
	(
		'name' => 'csvimport',
		'longname' => 'CSV Import tool',
		'version' => '2.0',
		'home_url' => 'https://github.com/RackTables/racktables-contribs'
	);
}

function plugin_csvimport_init()
{
	global $page, $tab;

	// Build Navigation
	$page['import']['title'] = 'Import CSV data';
	$page['import']['parent'] = 'config';
	$tab['import']['default'] = 'Import';
	registerTabHandler ('import', 'default', 'import_csv_data');
	registerOpHandler ('import', 'default', 'importData', 'importData');

	// Work in progress
	//$tab['import']['delete'] = 'Delete';
	//registerTabHandler ('import', 'delete', 'delete_csv_data');
	//registerOpHandler ('import', 'delete', 'importData', 'deleteData');
}

function plugin_csvimport_install()
{
	return TRUE;
}

function plugin_csvimport_uninstall()
{
	return TRUE;
}

function plugin_csvimport_upgrade ()
{
	return TRUE;
}

// tabhandler
function import_csv_data ()
{
	// Used for uploading a csv file, or manually pasting csv data
	startPortlet ('Import Racktables data');
	printOpFormIntro ('importData', array (), TRUE);
	echo "<table border=0 cellspacing=0 cellpadding='5' align='center'>";
	echo "<tr><td class=tdleft><label>File: <input type='file' size='10' name='file' tabindex=100></label></td><td class=tdcenter>";
	printImageHREF ('CREATE', 'Import file', TRUE, 102);
	echo '</td></tr>';
	echo '<tr><td>Manual input field</td></tr>';
	echo '<tr><td valign=top colspan=2><textarea tabindex=101 name=csv_text rows=10 cols=80></textarea></td>';
	echo '<td rowspan=2>';
	echo '</td></tr>';
	echo "</table></form><br>";
	finishPortlet();
}

// tabhandler
function delete_csv_data ()
{
	// Used for uploading a csv file, or manually pasting csv data
	startPortlet ('Delete Racktables data');
	printOpFormIntro ('importData', array (), TRUE);
	echo "<table border=0 cellspacing=0 cellpadding='5' align='center'>";
	echo "<tr><td class=tdleft><label>File: <input type='file' size='10' name='file' tabindex=100></label></td><td class=tdcenter>";
	printImageHREF ('CREATE', 'Import file', TRUE, 102);
	echo '</td></tr>';
	echo '<tr><td>Manual input field</td></tr>';
	echo '<tr><td valign=top colspan=2><textarea tabindex=101 name=csv_text rows=10 cols=80></textarea></td>';
	echo '<td rowspan=2>';
	echo '</td></tr>';
	echo "</table></form><br>";
	finishPortlet();
}

function deleteData()
{
	setFuncMessages (__FUNCTION__, array ('OK' => 0, 'ERR1' => 207));
	assertStringArg ('csv_text', TRUE);
	$row = 1;

	// if the manual input is empty, load the selected file
	if (strlen(($_REQUEST['csv_text'])) == 0)
	{
		if ($_FILES['file']['error'])
			return showFuncMessage (__FUNCTION__, 'ERR1', array ($_FILES['file']['error']));

		// manage files from different OSes
		ini_set("auto_detect_line_endings", TRUE);

		if (($handle = fopen($_FILES['file']['tmp_name'], "r")) !== FALSE)
		{
			showNotice ("Deleting from ".$_FILES['file']['name']);
			while (($csvdata = fgetcsv($handle, 1000, ";")) !== FALSE)
			{
				$result = usePreparedSelectBlade ('SELECT id FROM Object WHERE name = ?', array ($csvdata[0]));
				$object = $result->fetch (PDO::FETCH_ASSOC);
				if (!$object)
					showError ("Line ".$row.": Object ".$csvdata[0]. " not found");
				else
				{
					commitDeleteObject ($object['id']);
					showSuccess ("Line $row: Object ".$csvdata[0]. " deleted");
				}
				$row++;
			}
			fclose($handle);
		}
	}
	else
	{
		$data = explode("\n",$_REQUEST['csv_text']);
		showNotice ("Deleting from manual input field");
		foreach ($data as $dataitem)
		{
			$csvdata = str_getcsv($dataitem,";");
			addServerObject($csvdata,$row);
			$row++;
		}
	}

	return showFuncMessage (__FUNCTION__, 'OK', array (htmlspecialchars ("Deleting finished.")));
}

function importData()
{
	setFuncMessages (__FUNCTION__, array ('OK' => 0, 'ERR1' => 207));
	assertStringArg ('csv_text', TRUE);
	$row_number = 1;
	// if the manual input is empty, load the selected file
	if (strlen(($_REQUEST['csv_text'])) == 0)
	{
		ini_set("auto_detect_line_endings", TRUE);

		if (($handle = fopen($_FILES['file']['tmp_name'], "r")) !== FALSE)
		{
			showNotice ("Importing ".$_FILES['file']['name']);
			while (($csvdata = fgetcsv($handle, 1000, ";")) !== FALSE)
			{
				$csvdata[0] = trim($csvdata[0]);
				if ($csvdata[0] == "OBJECT") 			addObject($csvdata,$row_number);
				if ($csvdata[0] == "RACK")				addRackImport($csvdata,$row_number);
				if ($csvdata[0] == "RACKASSIGNMENT") 	addRackAssignment($csvdata,$row_number);
				if ($csvdata[0] == "VLAN") 				addVLAN($csvdata,$row_number);
				if ($csvdata[0] == "CABLELINK") 		addCableLink($csvdata,$row_number);
				if ($csvdata[0] == "IP") 				addIP($csvdata,$row_number);
				if ($csvdata[0] == "OBJECTIP") 			addObjectIP($csvdata,$row_number);
				if ($csvdata[0] == "OBJECTATTRIBUTE") 	setObjectAttributes($csvdata,$row_number);
				if ($csvdata[0] == "CONTAINERLINK")		addContainerLink($csvdata,$row_number);
				if ($csvdata[0] == "OBJECTTAG")			addTag($csvdata,$row_number);
				if ($csvdata[0] == "TAG")			addTag($csvdata,$row_number);
				if ($csvdata[0] == "UPDATEIP")			updateIP($csvdata,$row_number);
				$row_number++;
			}
			fclose($handle);
		}
		else
		{
			return showFuncMessage (__FUNCTION__, 'ERR1', array ($_FILES['file']['error']));
		}
	}
	else
	{
		$data = explode("\n",$_REQUEST['csv_text']);
		showNotice ("Importing from manual input field");
		foreach ($data as $dataitem)
		{
			$csvdata = str_getcsv($dataitem,";");
			$csvdata[0] = trim($csvdata[0]);
			if ($csvdata[0] == "OBJECT") 			addObject($csvdata,$row_number);
			if ($csvdata[0] == "RACK")				addRackImport($csvdata,$row_number);
			if ($csvdata[0] == "RACKASSIGNMENT") 	addRackAssignment($csvdata,$row_number);
			if ($csvdata[0] == "VLAN") 				addVLAN($csvdata,$row_number);
			if ($csvdata[0] == "CABLELINK") 		addCableLink($csvdata,$row_number);
			if ($csvdata[0] == "IP") 				addIP($csvdata,$row_number);
			if ($csvdata[0] == "OBJECTIP") 			addObjectIP($csvdata,$row_number);
			if ($csvdata[0] == "OBJECTATTRIBUTE") 	setObjectAttributes($csvdata,$row_number);
			if ($csvdata[0] == "CONTAINERLINK")		addContainerLink($csvdata,$row_number);
			if ($csvdata[0] == "OBJECTTAG")			addTag($csvdata,$row_number);
			if ($csvdata[0] == "TAG")			addTag($csvdata,$row_number);
			if ($csvdata[0] == "UPDATEIP")			updateIP($csvdata,$row_number);
			$row_number++;
		}
	}
	return showFuncMessage (__FUNCTION__, 'OK', array (htmlspecialchars ("Import finished.")));
}

// This function adds a object to racktables and report appropriate results in the GUI
function addObject($csvdata,$row_number)
{
	$object_type = 		trim ($csvdata[1]);
	$object_name = 		trim ($csvdata[2]);
	$object_label = 	trim ($csvdata[3]);
	$object_assettag = 	trim ($csvdata[4]);
	$ifName = 			explode(',',$csvdata[5]);
	$ifType = 			explode(',',$csvdata[6]);

	// Check Objecttype
	if ($object_type == "SERVER") 		$object_type = 4;
	if ($object_type == "PATCHPANEL")  	$object_type = 9;
	if ($object_type == "SWITCH") 		$object_type = 8;
	if ($object_type == "VM")			$object_type = 1504;
	if (is_numeric($object_type))
	{
		$result = usePreparedSelectBlade ('SELECT dict_value FROM Dictionary WHERE dict_key = ?', array ($object_type));
		$db_object_type = $result->fetch (PDO::FETCH_ASSOC);
		if ($db_object_type)
			$object_type_name = $db_object_type['dict_value'];
		else
		{
			showError("line $row_number: Object type ".$object_type. " does not exist. Import FAILED.");
			return FALSE;
		}
	}
	else
	{
		showError("line $row_number: Object type ".$object_type. " does not exist. Import FAILED.");
		return FALSE;
	}

	if (strlen($object_name) > 0)
	{
		try
		{
			$object_id = commitAddObject ( $object_name, $object_label, $object_type, $object_assettag, array());
		}
		catch (Exception $e)
		{
			showError("line $row_number: Import ". $object_type_name. " Object ".$object_name. " FAILED; object already exists");
			return FALSE;
		}
	}

	// When available, import the port information

	if ((count($ifName) > 0) & (count($ifType > 0)) & (count($ifName) == count($ifType)) )
	{
		// temporary disable autocreation of ports
		$tempAUTOPORTS_CONFIG =  getConfigVar ('AUTOPORTS_CONFIG');
		setConfigVar ('AUTOPORTS_CONFIG',"");

		for ($i=0 ; $i < count($ifName); $i++ )
		{
			if (strlen($ifName[$i]) > 0)
			{
				/*to do
				 Add Check for port compatibility, specified itType should be linked it iif_type 1 ('hardwired')
				 Else an foreign key error is thrown
				*/

				$prefix = "";
				$suffix = "";
				$pattern = "!(?<=[[])[^]]+(?=[]])!";
				preg_match($pattern,$ifName[$i],$match);

				if ((count($match) > 0) & (strpos($match[0],'-') !== FALSE))
				{
					$prefix = substr($ifName[$i],0, strpos($ifName[$i],'['));
					$suffix = substr($ifName[$i],strpos($ifName[$i],']')+1, strlen($ifName[$i])-1);
					$portlist = explode('-',$match[0]);
					if ((is_numeric($portlist[0])) & (is_numeric($portlist[1])) & ($portlist[0] < $portlist[1]))
					{
						for ($p = $portlist[0]; $p <= $portlist[1]; $p++)
							$new_port_id = commitAddPort ( $object_id, $prefix.$p.$suffix, trim ($ifType[$i]), "", "" );
					}
				}
				else
					$new_port_id = commitAddPort ( $object_id, trim ($ifName[$i]), trim ($ifType[$i]), "", "" );
			}
		}

		setConfigVar ('AUTOPORTS_CONFIG',$tempAUTOPORTS_CONFIG);
	}
	else
	{
		showNotice("No valid Port information found, skipping port import.");
	}
	showSuccess("line $row_number: Import ". $object_type_name. " Object ".$object_name. " successful; object_id=".$object_id);
}

function addRackImport($csvdata,$row_number)
{
	$location = 		trim($csvdata[1]);
	$location_child = 	trim($csvdata[2]);
	$rackrow = 			trim($csvdata[3]);
	$rack = 			trim($csvdata[4]);
	if (!isset($csvdata[5]))
		$rack_height = 46;
	else
		$rack_height = $csvdata[5];

	// Handle Location entry
	if (strlen($location ) > 0)
	{
		$result = usePreparedSelectBlade ('SELECT id, objtype_id FROM Object WHERE name = ?', array ($location));
		$db_location = $result->fetch (PDO::FETCH_ASSOC);
		// Object already exists
		if ($db_location)
		{
			$location_id = $db_location['id'];
			// Object already exists but is not a Location (objecttype 1562) cannot continue
			if ($db_location['objtype_id'] != 1562)
			{
				showError("Line $row_number: Location " . $location . " already exists as another Objecttype, Import FAILED.");
				return FALSE;
			}
		}
		// Object does not exist, create new location
		else
		{
			$location_id = commitAddObject ($location, "", 1562, "", array());
			showSuccess ("Line $row_number: Location ".$location. " imported; object_id=".$location_id);
		}
	}

	//Handle Child location entry
	if (strlen($location_child) > 0)
	{
		$location_child_id = 0;
		$result = usePreparedSelectBlade
		(
			'SELECT o.id, o.objtype_id, o.name, e.parent_entity_id ' .
			'FROM Object o LEFT JOIN EntityLink e ON e.child_entity_id = o.id WHERE name = ?',
			array ($location_child)
		);
		$db_location_child = $result->fetch (PDO::FETCH_ASSOC);

		if ($db_location_child) {   // Object already exists
			$location_child_id = $db_location_child['id'];

			if ($db_location_child['objtype_id'] != 1562) { // Object already exists but is not a Location (objecttype 1562) cannot continue
				showError("Line $row_number: Location Child " . $location_child . " already exists as another Objecttype, Import FAILED.");
				return FALSE;
			}
			if ($db_location_child['parent_entity_id'] != $location_id) { // The child Location id doesnt not match with the parent location ID
				showError("Line $row_number: Location Child " . $location_child . " mismatch with parent location_id, Import FAILED.");
				return FALSE;
			}
		}
		else { // Location child does not exist, create new object and link to parent location
			$location_child_id = commitAddObject ($location_child, "", 1562, "", array());
			commitLinkEntities ('location', $location_id , 'location', $location_child_id );
			showSuccess ("Line $row_number: Child Location ".$location_child. " imported; object_id=".$location_child_id);
		}
	}

	//Handle Row entry
	if (strlen($rackrow) > 0)
	{
		$result = usePreparedSelectBlade
		(
			'SELECT o.id, o.objtype_id, o.name, e.parent_entity_id ' .
			'FROM Object o LEFT JOIN EntityLink e ON e.child_entity_id = o.id WHERE name = ?',
			array ($rackrow)
		);
		$db_rackrow = $result->fetch (PDO::FETCH_ASSOC);
		// Object already exists
		if ($db_rackrow)
		{
			$rackrow_id = $db_rackrow['id'];
			// Object already exists but is not a Row (objecttype 1561) cannot continue
			if ($db_rackrow['objtype_id'] != 1561)
			{
				showError("Line $row_number: Row " . $rackrow. $db_rackrow['objtype_id'] . " already exists as another Objecttype, Import FAILED.");
				return FALSE;
			}
			// The Row doesnt not match with the parent or child location ID
			if (($db_rackrow['parent_entity_id'] != $location_id) & ($db_rackrow['parent_entity_id'] != $location_child_id))
			{
				showError("Line $row_number: Row " . $rackrow . " mismatch with parent location_id, Import FAILED.". $db_rackrow['parent_entity_id']. " , " . $location_id . " , " . $location_child_id);
				return FALSE;
			}
		}
		// Row does not exist, create new object and link to parent location
		else
		{
			$rackrow_id = commitAddObject ($rackrow, "", 1561, "", array());
			if ( $location_child_id == 0)
				commitLinkEntities ('location', $location_id , 'row', $rackrow_id );
			else
				commitLinkEntities ('location', $location_child_id  , 'row', $rackrow_id );
			showSuccess ("Line $row_number: Row ".$rackrow. " imported; object_id=".$rackrow_id);
		}
	}

	//Handle Rack entry
	if (strlen($rack) > 0)
	{
		$result = usePreparedSelectBlade ('SELECT id, objtype_id FROM Object WHERE name = ?', array ($rack));
		$db_rack = $result->fetch (PDO::FETCH_ASSOC);

		// Rack Object already exists
		if ($db_rack)
		{
			$rack_id = $db_rack['id'];
			// Object already exists but is not a Location (objecttype 1562) cannot continue
			if ($db_rack['objtype_id'] != 1560)
			{
				showError("Line $row_number: Rack " . $rack . " already exists as another Objecttype, Import FAILED.");
				return FALSE;
			}
		}
		//  Rack Object does not exist, create new rack
		else
		{
			$rack_id = commitAddObject ($rack, "", 1560, "", array());	// Object type 1560 = rack
			commitLinkEntities ('row', $rackrow_id  , 'rack', $rack_id );
			commitUpdateAttrValue ($rack_id, 27, $rack_height);		// attribute type 27 = height

			// The new rack(s) should be placed on the bottom of the list, sort-wise
			$rowInfo = getRowInfo($rackrow_id);
			$sort_order = $rowInfo['count']+1;
			commitUpdateAttrValue ($rack_id, 29, $sort_order);

			showSuccess ("Line $row_number: Rack ".$rack. " imported; object_id=".$rack_id);
		}
	}
}

// This function adds Rack assignment info for an object
function addRackAssignment($csvdata,$row_number)
{

	$object = 		trim ($csvdata[1]);
	$rack = 		trim ($csvdata[2]);
	$rackUnits = 	explode(',',$csvdata[3]);
	$fib = 			explode(',',$csvdata[4]);

	if (strlen($object ) > 0)
	{
		$query = 'SELECT id, objtype_id FROM Object WHERE name = ?';
		$result = usePreparedSelectBlade ($query, array ($object));
		$db_object = $result->fetch (PDO::FETCH_ASSOC);

		$result = usePreparedSelectBlade ($query, array ($rack));
		$db_rack = $result->fetch (PDO::FETCH_ASSOC);
		// Go ahead when Rack and object exists
		if (($db_object) & ($db_rack))
		{
			for ($i=0 ; $i < count($rackUnits); $i++ )
			{
				try
				{
					if($rackUnits[$i] == 0)
					{
						// Zero-U
						commitLinkEntities ('rack', $db_rack['id'], 'object', $db_object['id']);
					}
					else
					{
						if (strpos($fib[$i],'f') !== FALSE)
							usePreparedInsertBlade ('RackSpace', array ('rack_id' => $db_rack['id'], 'unit_no' => $rackUnits[$i], 'atom' => 'front', 'state' => 'T', 'object_id' => $db_object['id']));
						if (strpos($fib[$i],'i') !== FALSE)
							usePreparedInsertBlade ('RackSpace', array ('rack_id' => $db_rack['id'], 'unit_no' => $rackUnits[$i], 'atom' => 'interior', 'state' => 'T', 'object_id' => $db_object['id']));
						if (strpos($fib[$i],'b') !== FALSE)
							usePreparedInsertBlade ('RackSpace', array ('rack_id' => $db_rack['id'], 'unit_no' => $rackUnits[$i], 'atom' => 'rear', 'state' => 'T', 'object_id' => $db_object['id']));
					}

					usePreparedDeleteBlade ('RackThumbnail', array ('rack_id' => $db_rack['id']));  //Updates the thumbnail of the rack
				}
				catch(Exception $e)
				{
					showWarning("Line $row_number: \"$object\" \"$rack\" ".$fib[$i]." failure. $e");
					continue;
				}
			}
			showSuccess("line $row_number: Rack Assignment for  ".$object. " successful");
		}
		else
		{
			showError("Line $row_number: Object " . $object . " or Rack " . $rack. " does not exist. Import FAILED.");
			return FALSE;
		}
	}
}

function addCableLink($csvdata,$row_number)
{
	$object_a = 	trim ($csvdata[1]);
	$port_a = 		trim ($csvdata[2]);
	$object_b = 	trim ($csvdata[3]);
	$port_b = 		trim ($csvdata[4]);
	$cable_id = 	trim ($csvdata[5]);
	$query = 'SELECT Port.id, Object.name FROM Port, Object WHERE Port.object_id = Object.id AND Port.name = ? AND Object.name = ?';

	// Check if object_a and port_a exist, if not; stop and return false
	$result = usePreparedSelectBlade ($query, array ($port_a, $object_a));
	$db_result_a = $result->fetch (PDO::FETCH_ASSOC);
	if (!$db_result_a)
	{
		showError("line $row_number: Import CableLink ". $cable_id. " FAILED; The object-port combination ".$object_a." ".$port_a." does not exist.");
		return FALSE;
	}

	// Check if object_a and port_a exist, if not; stop and return false
	$result = usePreparedSelectBlade ($query, array ($port_b, $object_b));
	$db_result_b = $result->fetch (PDO::FETCH_ASSOC);
	if (!$db_result_b)
	{
		showError("line $row_number: Import CableLink ". $cable_id. " FAILED; The object-port combination ".$object_b." ".$port_b." does not exist.");
		return FALSE;
	}

	// Check if port types are compatible
	// Prevent SQL LOCK TABLES errors
	$port1 = getPortInfo($db_result_a['id']);
	$port2 = getPortInfo($db_result_b['id']);

	if (!arePortTypesCompatible($port1['oif_id'], $port2['oif_id']))
	{
		showError("line $row_number: Import CableLink $cable_id FAILED; The porttypes mismatch $object_a $port_a -> $object_b $port_b. ".$port1['oif_name']." != ".$port2['oif_name']);
		return FALSE;
	}

	// Create Link
	try
	{
		$linkresult = linkPorts ($db_result_a['id'], $db_result_b['id'], $cable_id);

		// port already linked
		if(!is_numeric($linkresult))
		{
			showError("line $row_number: Import CableLink ". $cable_id." FAILED. $object_a $port_a -> $object_b $port_b \"".$linkresult."\" Link exists?!");
			return FALSE;
		}
	}
	catch (Exception $e)
	{
		showError("line $row_number: Import CableLink ". $cable_id." FAILED. Possible porttype mismatch. Complete Exception data: ".$e);
		return FALSE;
	}
	showSuccess ("Line $row_number: Import CableLink ".$cable_id. " imported.");
}

function addVLAN($csvdata,$row_number)
{
	$vlan_domain = 		trim ($csvdata[1]);
	$vlan_name = 		trim ($csvdata[2]);
	$vlan_id = 			trim ($csvdata[3]);
	$vlan_propagation = trim ($csvdata[4]);
	if ($vlan_propagation != 'ondemand') $vlan_propagation = "compulsory";
	$ip_ranges = 		explode(',',$csvdata[5]);
	$query = 'SELECT id FROM VLANDomain WHERE description = ?';

	// Check if VLAN domain exists
	$result = usePreparedSelectBlade ($query, array ($vlan_domain));
	$db_result = $result->fetch (PDO::FETCH_ASSOC);

	// If VLAN domain does not exists, create domain
	if (!$db_result)
	{
		usePreparedInsertBlade ('VLANDomain', array ('description' => $vlan_domain));
		$result = usePreparedSelectBlade ($query, array ($vlan_domain));
		$db_result = $result->fetch (PDO::FETCH_ASSOC);
		showSuccess ("Line $row_number: VLAN Domain ".$vlan_domain. " imported; object_id=".$db_result['id']);
	}
	$domain_id = $db_result['id'];

	$catched = FALSE;
	// Create VLAN
	try
	{
		usePreparedInsertBlade ("VLANDescription", array('domain_id' => $domain_id , 'vlan_id' => $vlan_id, 'vlan_type' => $vlan_propagation, 'vlan_descr' => $vlan_name));
	}
	catch (Exception $e)
	{
		showError("line $row_number: Import ". $vlan_name. " vlan_id ".$vlan_id. " FAILED; VLAN already exists");
		$catched = TRUE;
	}

	if(!$catched)
		showSuccess ("Line $row_number: VLAN ".$vlan_name. " imported; vlan_id=".$vlan_id);

	// Try to attach VLANs to IP ranges
	foreach ($ip_ranges as $ip_range)
	{
		try
		{
			$net = spotNetworkByIP (ip_parse($ip_range));
		}
		catch (Exception $e)
		{
			showError("line $row_number: Unable to find/parse IP network address ". $ip_range);
		}
		if (isset($net['id']))
		{
			try
			{
				if (strpos($ip_range,".")) commitSupplementVLANIPv4 ($domain_id."-".$vlan_id, $net['id']);
				if (strpos($ip_range,":")) commitSupplementVLANIPv6 ($domain_id."-".$vlan_id, $net['id']);
				showSuccess ("Line $row_number: VLAN ".$vlan_name. " attached to IP range ".$ip_range);
			}
			catch (Exception $e)
			{
				showWarning ("Line $row_number: VLAN ".$vlan_name. " unable to attach to range $ip_range. $e");
			}
		}
		else
		{
			showError ("Line $row_number: VLAN ".$vlan_name. " unable to attach to range ".$ip_range);
		}
	}
}


function addIP($csvdata,$row_number)
{
	$prefix = 		trim ($csvdata[1]);
	$ip_name= 		trim ($csvdata[2]);
	$is_connected = trim ($csvdata[3]);
	$vlan_domain = 	trim ($csvdata[4]);
	$vlan_id =  	trim ($csvdata[5]);
	$vlan_ck = 		NULL;

	// Check if vlan domain - vlan combination exists
	if ((strlen($vlan_domain) > 0) & (strlen($vlan_id) > 0))
	{
		$result = usePreparedSelectBlade
		(
			'SELECT VLANDescription.domain_id, VLANDomain.description FROM VLANDescription, VLANDomain ' .
			'WHERE VLANDomain.id = VLANDescription.domain_id AND VLANDescription.vlan_id = ? AND VLANDomain.description = ?',
			array ($vlan_id, $vlan_domain)
		);
		$vlan_result = $result->fetch (PDO::FETCH_ASSOC);
		if (!$vlan_result)
		{
			showError("line $row_number: Import IP ". $cable_id. " FAILED; The VLAN domain - VLAN combination ".$vlan_domain." -  ".$vlan_id." does not exist.");
			return FALSE;
		}
		else
		{
			$vlan_ck = $vlan_result['domain_id']."-".$vlan_id;
		}
	}

	// Create IP range
	try
	{
		if (strpos($prefix,".")) createIPv4Prefix($prefix, $ip_name, $is_connected, array(), $vlan_ck);
		if (strpos($prefix,":")) createIPv6Prefix($prefix, $ip_name, $is_connected, array(), $vlan_ck);
	}
	catch (Exception $e)
	{
		showError("line $row_number: Import IP ". $prefix." FAILED. Complete Exception data: ".$e);
		return FALSE;
	}
	showSuccess ("Line $row_number: Import IP ".$prefix. " imported. ".$vlan_ck);
}

function addObjectIP($csvdata,$row_number)
{
	$objectName = 		trim ($csvdata[1]);
	$ifName = 			trim ($csvdata[2]);
	$ipAddress = 		trim ($csvdata[3]);
	if (!isset($csvdata[4]))
		$type = "router";
	else
		$type = trim (strtolower($csvdata[4]));

	//Check if object exists, and return object_id
	$result = usePreparedSelectBlade ('SELECT id FROM Object WHERE name = ?', array ($objectName));
	$db_object = $result->fetch (PDO::FETCH_ASSOC);

	//if object exists, create IP interface
	if ($db_object)
	{
		try
		{
			bindIPToObject (ip_parse($ipAddress), $db_object['id'], $ifName, $type);
		}
		catch (Exception $e)
		{
			showError("line $row_number: IP interface ". $ifName. " import FAILED" . "Reason: ". $e);
			return FALSE;
		}
		showSuccess ("Line $row_number: IP interface ".$ifName. " imported.");
	}
	else
	{
		showError("Line $row_number: IP interface, Object " .$objectName. " does not exist. Import FAILED.");
	}
}

// This function sets attributes for an object
function setObjectAttributes($csvdata,$row_number)
{
	$object = 	trim ($csvdata[1]);
	$attr_id = 	trim ($csvdata[2]);
	$attr_value = 	trim ($csvdata[3]);

	if (strlen($object ) > 0)
	{
		$result = usePreparedSelectBlade ('SELECT id, name, label, asset_no, has_problems, comment FROM Object WHERE name = ?', array ($object));
		$db_object = $result->fetch (PDO::FETCH_ASSOC);

		// Go ahead when object exists
		if ($db_object)
		{
			if ($attr_id == "NAME") $db_object['name'] = $attr_value;
			if ($attr_id == "LABEL") $db_object['label'] = $attr_value;
			if ($attr_id == "HASPROBLEMS") $db_object['has_problems'] = $attr_value;
			if ($attr_id == "ASSETTAG") $db_object['asset_no'] = $attr_value;
			if ($attr_id == "COMMENT") $db_object['comment'] = $attr_value;

			if (preg_match('/NAME|LABEL|HASPROBLEMS|ASSETTAG|COMMENT/',$attr_id))
			{
				commitUpdateObject ($db_object['id'],$db_object['name'],$db_object['label'],$db_object['has_problems'],$db_object['asset_no'],$db_object['comment']);
			}
			else
			{
				commitUpdateAttrValue ($db_object['id'], $attr_id, $attr_value);
			}
			showSuccess("line $row_number: attribute for  ".$object. ": ".$attr_id." ".$attr_value." updated");
		}
		else
		{
			showError("line $row_number: attribute for  ".$object. ": ".$attr_id." ".$attr_value." not updated. Import FAILED.");
		}
	}
}

function addContainerLink($csvdata,$row_number)
{
	$parentObjectName = trim ($csvdata[1]);
	$childObjectName = trim ($csvdata[2]);

	if ((strlen($parentObjectName) > 0) & (strlen($childObjectName) > 0))
	{
		$query = 'SELECT id FROM Object WHERE name = ?';
		// Check if parent object exists and return object_id
		$parentResult = usePreparedSelectBlade ($query, array ($parentObjectName));
		$parentDB_object = $parentResult->fetch (PDO::FETCH_ASSOC);

		// Check if child object exists and return object_id
		$childResult = usePreparedSelectBlade ($query, array ($childObjectName));
		$childDB_object = $childResult->fetch (PDO::FETCH_ASSOC);

		// if both objects exist, create an EntityLink between them
		if (($parentDB_object) & ($childDB_object))
		{
			$object_parent_id = $parentDB_object['id'];
			$object_child_id = $childDB_object['id'];
			commitLinkEntities ('object', $object_parent_id , 'object', $object_child_id );
			showSuccess ("Line $row_number: Added ".$childObjectName. " to parent container ".$parentObjectName.".");
		}
		else
		{
			showError("Line $row_number: Unable to add ".$childObjectName. " to parent container ".$parentObjectName.". One of the objects does not exist.");
		}
	}
}

function addTag($csvdata,$row_number)
{
	$cmd = trim($csvdata[0]);
	if($cmd == "OBJECTTAG")
	{
	 	$newdata[0] = "TAG";
		$newdata[1] = "object";
		$newdata[2] = $csvdata[1];
		$newdata[3] = $csvdata[2];

		$csvdata = $newdata;
	}

	$realm = trim($csvdata[1]);
	$Name = trim ($csvdata[2]);
	$tagNames = explode(',', $csvdata[3]);

	if ((!empty($realm)) && (strlen($Name) > 0) && (!empty($tagNames)))
	{
		switch($realm)	{
		case 'object':
				// Check if object exists and return object_id
				$result = usePreparedSelectBlade ("SELECT Object.id FROM Object WHERE Object.name='$Name';");
				$entity = $result->fetch (PDO::FETCH_ASSOC);
				break;
			case 'rack':
				// Check if rack exists and return object_id
				$result = usePreparedSelectBlade ("SELECT Rack.id FROM Rack WHERE Rack.name='$Name';");
				$entity = $result->fetch (PDO::FETCH_ASSOC);
				break;
			case 'ipv4net':
				$a = explode('/',$Name,2);
				$ipaddress = $a[0];
				$ip_bin = ip_parse($ipaddress);
				if(isset($a[1]))
					$masklen = strval($a[1]);
				else
					$masklen = 32;

				/* from database.php fetchIPv4AddressNetworkRow() */

				$query = 'SELECT IPv4Network.id FROM IPv4Network WHERE ' .
				"? & (4294967295 >> (32 - mask)) << (32 - mask) = ip " .
				"AND mask = ? " .
				'ORDER BY mask DESC LIMIT 1';
				$result = usePreparedSelectBlade ($query, array (ip4_bin2db ($ip_bin), $masklen));
				$entity = $result->fetch (PDO::FETCH_ASSOC);

				if($entity)
					$Name = "$ipaddress/".$entity['mask'];

				break;
			default:
				showError("Line $row_number: Realm $realm not implemented yet!.");
				return False;
				break;
			}
		}

		if(!$entity)
		{
			showError("Line $row_number: Unable to add tags to $realm ".$Name.". The $realm does not exist.");
			return False;
		}

		$entity_id = $entity['id'];

		foreach($tagNames as $tagName)
		{
			$tagName = trim($tagName);

			// Check if tag exists and return tag_id
			$tagResult = usePreparedSelectBlade ("SELECT TagTree.id FROM TagTree WHERE TagTree.tag='".$tagName."';");
			$db_Tag = $tagResult->fetch (PDO::FETCH_ASSOC);

			// if both the object and the tag exist, create an entry in the TagStorage table
			if (($db_Tag))
			{
				$tag_id = $db_Tag['id'];
				try
				{
					addTagForEntity ($realm, $entity_id, $tag_id );
				}
				catch(Exception $e)
				{
					showWarning ("Line $row_number: Added tag ".$tagName. " to object ".$Name.". Entry already exists. ".$e);
					continue;
				}

				showSuccess ("Line $row_number: Added tag ".$tagName. " to object ".$Name.".");
			}
			else
			{
				showError("Line $row_number: Unable to add tag ".$tagName. " to object ".$Name.". The tag does not exist.");
			}
		}
}

function updateIP($csvdata,$row_number)
{
	$ipaddress = trim ($csvdata[1]);
	$name =	trim ($csvdata[2]);
	$reserved = 	trim ($csvdata[3]);
	$comment = 	trim ($csvdata[4]);

	if(isset($csvdata[5]))
		$user = trim ($csvdata[5]);
	else
		$user = FALSE;

	$ip_bin = ip_parse($ipaddress);

	$netaddress = getIPAddressNetworkID($ip_bin);
	if(empty($netaddress))
	{
		showError("line $row_number: FAILED. update IP $ipaddress does not exist!");
		return FALSE;
	}

	$address = getIPAddress($ip_bin);
	if($address['reserved'] == 'yes')
	{
		showError("line $row_number: FAILED. update IP $ipaddress already reserved!");
		return FALSE;
	}

	try
	{
		if($user)
			addIPLogEntry_User($ip_bin, "Import Source Username", $user);

		updateAddress ($ip_bin, $name, $reserved, $comment);
	}
	catch (Exception $e)
	{
		showError("line $row_number: update IP $ipaddress FAILED" . "Reason: ". $e);
		return FALSE;
	}

	showSuccess ("Line $row_number: IP $ipaddress updated.");
}

function addIPLogEntry_User($ip_bin, $message, $username)
{

	switch (strlen ($ip_bin))
	{
		case 4:
			usePreparedExecuteBlade
			(
				"INSERT INTO IPv4Log (ip, date, user, message) VALUES (?, NOW(), ?, ?)",
				array (ip4_bin2db ($ip_bin), $username, $message)
			);
			break;
		case 16:
			usePreparedExecuteBlade
			(
				"INSERT INTO IPv6Log (ip, date, user, message) VALUES (?, NOW(), ?, ?)",
				array ($ip_bin, $username, $message)
			);
			break;
		default: throw new InvalidArgException ('ip_bin', $ip_bin, "Invalid binary IP");
	}
}
