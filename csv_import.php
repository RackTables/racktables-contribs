<?php

/*
 
Copyright 2014 Erik Ruiter

This is a Racktables plugin which enables loading batches of data into racktables.
It adds an CSV import page on the configuration menu screen.
From here you can choose to either import a CSV file, or paste some manual CSV lines in a textbox.

OBJECT; objecttype ; common name ; Visible label ; Asset tag; portname,portname,etc ; porttype,porttype,etc
OBJECT;PATCHPANEL;atestpanel;testpanel;testpanel;ge-0/0/[0-11].0,ge-0/1/[0-11];24,29

OBJECT;SERVER;head02.hathi.surfsara.nl;head02.hathi.surfsara.nl;head02.hathi.surfsara.nl;IPMI,eth0,eth1,eth2;24,24,24,24; 
SERVER;common name ; Visible label ; Asset tag; portname,portname,etc ; porttype,porttype,etc


PATCHPANEL; common name ; Visible label ; Asset tag; portnumber,portnumber,etc ; porttype,porttype,etc

atestpanel;testpanel;testpanel;ge-0/0/[0-11].0,ge-0/1/[0-11];24,29
SWITCH; ge-0/0/[0-47]
CUSTOMOBJECT;
RACK; 

OBJECT;SERVER;a_head02.hathi.surfsara.nl;head02.hathi.surfsara.nl;head02.hathi.surfsara.nl;IPMI,eth0,eth1,eth2;24,24,24,24; 
OBJECT;PATCHPANEL;a_testpanel;testpanel;testpanel;[1-12],[13-24];24,29
OBJECT;SWITCH;a_testswitch;testswitch;testswitch;ge-0/0/[0-11].0,ge-0/1/[0-11];24,29

*/

// Build Navigation
$page['import']['title'] = 'Import csv data';
$page['import']['parent'] = 'config';
$tab['import']['default'] = 'Import csv data';
$tab['import']['delete'] = 'Delete csv data';
$tabhandler['import']['default'] = 'import_csv_data';
$tabhandler['import']['delete'] = 'delete_csv_data';
$ophandler['import']['default']['importData'] = 'importData';
$ophandler['import']['delete']['importData'] = 'deleteData';

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

$msgcode['deleteData']['OK'] = 0;
$msgcode['deleteData']['ERR1'] = 207;
function deleteData()
{
	assertStringArg ('csv_text', TRUE);
	$row = 1;

	// if the manual input is empty, load the selected file
	if (strlen(($_REQUEST['csv_text'])) == 0) 
	{  
		if ($_FILES['file']['error'])
			return showFuncMessage (__FUNCTION__, 'ERR1', array ($_FILES['file']['error']));
		
		// manage files from different OSes
		ini_set("auto_detect_line_endings", true);
			
		if (($handle = fopen($_FILES['file']['tmp_name'], "r")) !== FALSE) 
		{
			showNotice ("Deleting from ".$_FILES['file']['name']);
			while (($csvdata = fgetcsv($handle, 1000, ";")) !== FALSE) 
			{
				$result = usePreparedSelectBlade ("SELECT  Object.id from Object where Object.name='".$csvdata[0]."';");
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

$msgcode['importData']['OK'] = 0;
$msgcode['importData']['ERR1'] = 207;
function importData()
{
	assertStringArg ('csv_text', TRUE);
	$row = 1;
	// if the manual input is empty, load the selected file
	if (strlen(($_REQUEST['csv_text'])) == 0) 
	{   
		ini_set("auto_detect_line_endings", true);
			
		if (($handle = fopen($_FILES['file']['tmp_name'], "r")) !== FALSE) 
		{
			showNotice ("Importing ".$_FILES['file']['name']);
			while (($csvdata = fgetcsv($handle, 1000, ";")) !== FALSE) 
			{
				if ($csvdata[0] == "OBJECT") 			addObject($csvdata,$row);
				if ($csvdata[0] == "RACK")				addRackImport($csvdata,$row);
				if ($csvdata[0] == "RACKASSIGNMENT") 	addRackAssignment($csvdata,$row);
				$row++;
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
			if ($csvdata[0] == "OBJECT") 			addObject($csvdata,$row);
			if ($csvdata[0] == "RACK")				addRackImport($csvdata,$row);
			if ($csvdata[0] == "RACKASSIGNMENT") 	addRackAssignment($csvdata,$row);
			$row++;
		}		
	}
	return showFuncMessage (__FUNCTION__, 'OK', array (htmlspecialchars ("Import finished.")));
}


// This function adds a object to racktables and report appropriate results in the GUI
// The following format is used:
// OBJECT; objecttype ; common name ; Visible label ; Asset tag; portname,portname,etc ; porttype,porttype,etc
// OBJECT;PATCHPANEL;atestpanel;testpanel;testpanel;ge-0/0/[0-11].0,ge-0/1/[0-11];24,29
// OBJECT;SWITCH;opti-r1.rtr.sara.nl;opti-r1.rtr.sara.nl;opti-r1.rtr.sara.nl;Et[1-24];1084
// OBJECT;SWITCH;border-sw1.rtr.sara.nl;border-sw1.rtr.sara.nl;border-sw1.rtr.sara.nl;xe-0/0/[0-3],et-0/1/0,xe-0/2/[0-3],et-0/3/0,xe-1/0/[0-7],xe-1/1/[0-7],xe-1/2/[0-7],xe-1/3/[0-7];1084,1668,1084,1668,1084,1084,1084,1084
// OBJECT;SWITCH;sw1-fw-ext-vc.rtr.sara.nl;sw1-fw-ext-vc.rtr.sara.nl;sw1-fw-ext-vc.rtr.sara.nl;xe-0/0/[0-31],xe-2/0/[0-31];1084,1084

function addObject($csvdata,$row) 
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
	if (is_numeric($object_type)) 
	{
		$result = usePreparedSelectBlade ("SELECT  Dictionary.dict_value from Dictionary where Dictionary.dict_key=".$object_type.";");
		$db_object_type = $result->fetch (PDO::FETCH_ASSOC);
		if ($db_object_type) 
			$object_type_name = $db_object_type['dict_value'];
		else
		{
			showError("line $row: Object type ".$object_type. " does not exist. Import FAILED.");
			return FALSE;
		}
	}
	else
	{
		showError("line $row: Object type ".$object_type. " does not exist. Import FAILED.");
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
			showError("line $row: Import ". $object_type_name. " Object ".$object_name. " FAILED; object already exists");
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
			$prefix = "";
			$suffix = "";
    		$pattern = "!(?<=[[])[^]]+(?=[]])!";
    		preg_match($pattern,$ifName[$i],$match);

			if ((count($match) > 0) & (strpos($match[0],'-') !== false))	
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

		setConfigVar ('AUTOPORTS_CONFIG',$tempAUTOPORTS_CONFIG);
	}
	else 
	{
		showNotice("No valid Port information found, skipping port import.");
	}
	showSuccess("line $row: Import ". $object_type_name. " Object ".$object_name. " successful; object_id=".$object_id);
}

// This function adds Rack assignment info for an object
function addRackAssignment($csvdata,$row) 
{
//Object;Rack;units;fib
	//p-head04.alley.sara.nl;V36;4,5,6;fib,fib,fi

	$object = 		trim ($csvdata[0]);
	$rack = 		trim ($csvdata[1]);
	$rackUnits = 	explode(',',$csvdata[2]);
	$fib = 			explode(',',$csvdata[3]);

	if (strlen($object ) > 0) 
	{

		$result = usePreparedSelectBlade ("SELECT  Object.id, Object.objtype_id from Object where Object.name='".$object."';");
		$db_object = $result->fetch (PDO::FETCH_ASSOC);
		
		$result = usePreparedSelectBlade ("SELECT  Object.id, Object.objtype_id from Object where Object.name='".$rack."';");
		$db_rack = $result->fetch (PDO::FETCH_ASSOC);
		// Go ahead when Rack and object exists
		if (($db_object) & ($db_rack)) 
		{
			for ($i=0 ; $i < count($rackUnits); $i++ ) 
			{
				if (strpos($fib[$i],'f') !== false)
					usePreparedInsertBlade ('RackSpace', array ('rack_id' => $db_rack['id'], 'unit_no' => $rackUnits[$i], 'atom' => 'front', 'state' => 'T', 'object_id' => $db_object['id']));
				if (strpos($fib[$i],'i') !== false)
					usePreparedInsertBlade ('RackSpace', array ('rack_id' => $db_rack['id'], 'unit_no' => $rackUnits[$i], 'atom' => 'interior', 'state' => 'T', 'object_id' => $db_object['id']));
				if (strpos($fib[$i],'b') !== false)
					usePreparedInsertBlade ('RackSpace', array ('rack_id' => $db_rack['id'], 'unit_no' => $rackUnits[$i], 'atom' => 'rear', 'state' => 'T', 'object_id' => $db_object['id']));
				
				usePreparedDeleteBlade ('RackThumbnail', array ('rack_id' => $db_rack['id']));  //Updates the thumbnail of the rack
			}
			showSuccess("line $row: Rack Assignment for  ".$object. " successful");
		}
		else
		{
			showError("Line $row: Object " . $object . " or Rack " . $rack. " does not exist. Import FAILED.");
			return FALSE;
		}
	}
}


function addRackImport($csvdata,$row) 
{

	$location = 		$csvdata[0];
	$location_child = 	$csvdata[1];
	$rackrow = 			$csvdata[2];
	$rack = 			$csvdata[3];
	if (!isset($csvdata[4])) 
		$rack_height = 46;
	else
		$rack_height = $csvdata[4];

	// Handle Location entry
	if (strlen($location ) > 0) 
	{
		$result = usePreparedSelectBlade ("SELECT  Object.id, Object.objtype_id from Object where Object.name='".$location."';");
		$db_location = $result->fetch (PDO::FETCH_ASSOC);
		// Object already exists
		if ($db_location) 
		{   
			$location_id = $db_location['id'];
			// Object already exists but is not a Location (objecttype 1562) cannot continue
			if ($db_location['objtype_id'] != 1562) 
			{ 
				showError("Line $row: Location " . $location . " already exists as another Objecttype, Import FAILED.");
				return FALSE;
			}
		}
		// Object does not exist, create new location
		else 
		{		
			$location_id = commitAddObject ($location, "", 1562, "", array());
			showSuccess ("Line $row: Location ".$location. " imported; object_id=".$location_id);
		}
		
	}

	//Handle Child location entry
	if (strlen($location_child) > 0) 
	{
		$location_child_id = 0;
		$result = usePreparedSelectBlade ("select o.id, o.objtype_id, o.name, e.parent_entity_id from Object o left join EntityLink e on e.child_entity_id=o.id where name ='".$location_child."';");
		$db_location_child = $result->fetch (PDO::FETCH_ASSOC);

		if ($db_location_child) {   // Object already exists
			$location_child_id = $db_location_child['id'];

			if ($db_location_child['objtype_id'] != 1562) { // Object already exists but is not a Location (objecttype 1562) cannot continue
				showError("Line $row: Location Child " . $location_child . " already exists as another Objecttype, Import FAILED.");
				return FALSE;
			}
			if ($db_location_child['parent_entity_id'] != $location_id) { // The child Location id doesnt not match with the parent location ID
				showError("Line $row: Location Child " . $location_child . " mismatch with parent location_id, Import FAILED.");
				return FALSE;
			}
		}
		else { // Location child does not exist, create new object and link to parent location
			$location_child_id = commitAddObject ($location_child, "", 1562, "", array());
			commitLinkEntities ('location', $location_id , 'location', $location_child_id );
			showSuccess ("Line $row: Child Location ".$csvdata[1]. " imported; object_id=".$location_child_id);	
		}		
	}

	//Handle Row entry
	if (strlen($rackrow) > 0) 
	{
		$result = usePreparedSelectBlade ("select o.id, o.objtype_id, o.name, e.parent_entity_id from Object o left join EntityLink e on e.child_entity_id=o.id where name ='".$rackrow."';");
		$db_rackrow = $result->fetch (PDO::FETCH_ASSOC);
		// Object already exists
		if ($db_rackrow) 
		{   
			$rackrow_id = $db_rackrow['id'];
			// Object already exists but is not a Row (objecttype 1561) cannot continue
			if ($db_rackrow['objtype_id'] != 1561) 
			{ 
				showError("Line $row: Row " . $rackrow. $db_rackrow['objtype_id'] . " already exists as another Objecttype, Import FAILED.");
				return FALSE;
			}
			// The Row doesnt not match with the parent or child location ID
			if (($db_rackrow['parent_entity_id'] != $location_id) & ($db_rackrow['parent_entity_id'] != $location_child_id))   
			{ 
				showError("Line $row: Row " . $rackrow . " mismatch with parent location_id, Import FAILED.". $db_rackrow['parent_entity_id']. " , " . $location_id . " , " . $location_child_id);
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
			showSuccess ("Line $row: Row ".$rackrow. " imported; object_id=".$rackrow_id);
		}			
	}

	//Handle Rack entry
	if (strlen($rack) > 0) 
	{
		$result = usePreparedSelectBlade ("SELECT  Object.id, Object.objtype_id from Object where Object.name='".$rack."';");
		$db_rack = $result->fetch (PDO::FETCH_ASSOC);

		// Rack Object already exists
		if ($db_rack) 
		{   
			$rack_id = $db_rack['id'];
			// Object already exists but is not a Location (objecttype 1562) cannot continue
			if ($db_rack['objtype_id'] != 1560) 
			{ 
				showError("Line $row: Rack " . $rack . " already exists as another Objecttype, Import FAILED.");
				return FALSE;
			}
		}
		//  Rack Object does not exist, create new rack
		else 
		{		
			$rack_id = commitAddObject ($rack, "", 1560, "", array());	// Object type 1560 = rack		
			commitLinkEntities ('row', $rackrow_id  , 'rack', $rack_id );
			commitUpdateAttrValue ($rack_id, 27, $rack_height);		// attribute type 27 = height
			showSuccess ("Line $row: Rack ".$rack. " imported; object_id=".$rack_id);
		}
			
	}
}// Todo

// Cable link import

?>