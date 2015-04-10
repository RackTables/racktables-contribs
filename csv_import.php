<?php

# Copyright (c) 2014, Erik Ruiter, SURFsara BV, Amsterdam, The Netherlands
# All rights reserved.
#
# Redistribution and use in source and binary forms, with or without modification,
# are permitted provided that the following conditions are met:
#
# 1. Redistributions of source code must retain the above copyright notice, this list of conditions
# and the following disclaimer.
#
# 2. Redistributions in binary form must reproduce the above copyright notice, this list of conditions
# and the following disclaimer in the documentation and/or other materials provided with the distribution.
#
# THIS SOFTWARE IS PROVIDED BY THE COPYRIGHT HOLDERS AND CONTRIBUTORS "AS IS" AND ANY EXPRESS OR IMPLIED
# WARRANTIES, INCLUDING, BUT NOT LIMITED TO, THE IMPLIED WARRANTIES OF MERCHANTABILITY AND FITNESS FOR A
# PARTICULAR PURPOSE ARE DISCLAIMED. IN NO EVENT SHALL THE COPYRIGHT HOLDER OR CONTRIBUTORS BE LIABLE FOR
# ANY DIRECT, INDIRECT, INCIDENTAL, SPECIAL, EXEMPLARY, OR CONSEQUENTIAL DAMAGES (INCLUDING, BUT NOT LIMITED
# TO, PROCUREMENT OF SUBSTITUTE GOODS OR SERVICES; LOSS OF USE, DATA, OR PROFITS; OR BUSINESS INTERRUPTION)
# HOWEVER CAUSED AND ON ANY THEORY OF LIABILITY, WHETHER IN CONTRACT, STRICT LIABILITY, OR TORT
# (INCLUDING NEGLIGENCE OR OTHERWISE) ARISING IN ANY WAY OUT OF THE USE OF THIS SOFTWARE, EVEN IF ADVISED
# OF THE POSSIBILITY OF SUCH DAMAGE.

/*
-----------------------------------------

csv_import.php

Description:

This is a Racktables plugin which enables loading batches of data into racktables.
It adds an CSV import page on the configuration menu screen.
From here you can choose to either import a CSV file, or paste some manual CSV lines in a textbox.
CSV files can contain multiple types of import data.
The script currently supports importing of Objects, Racks, VLANs and IP space.
It also supports linking ports, and assigning rackspace to objects.

The newest version of this plugin can be found at: https://github.com/sara-nl/racktables-contribs/

Usage:

* Importing Objects:

 Syntax: OBJECT; Objecttype ; Common name ; Visible label ; Asset tag; portname,portname,etc ; porttype,porttype,etc

 Value 1, OBJECT
 Value 2, Objectype: Can be one of the predefined types (SERVER, PATCHPANEL,SWITCH), or a numeric value indicating the object type from Racktables
 Value 3, Common name: Common name string
 Value 4, Asset tag: Asset tag string
 Value 5, Port array: This is an optional field where you can create ports for the objects, separated by a comma. When you use this , you also need to add the Port type array
 An individual port field can be a range of ports. Eg. 'eth[0-9]' creates ten ports ranging from eth0 to eth9.
 Value 6, Port type array: This is an array which maps to the previous port array. This allows you to specify the interface type of the ports. It takes the form 'a-b'. Where a is the inner interface type, and b the outer interface type. Both a and b are numeric values.
 New inner / outer interface pair types can be linked using the configuration -> Enabled port types page. When the 'a-' value is ommited, the inner port type defaults to 'hardwire'. 
 Examples: 
 
 OBJECT;SERVER;myServer;www.server.com;SRV001;IPMI,eth[0-2];1-24,3-24
 Creates a Server object named myServer having 4x 1000-Base-T interfaces, named IPMI (hardwired inner iface, 1gbps), eth0, eth1 and eth2 (gbic inner iface, 1gbps)

 OBJECT;SWITCH;myAccessSwitch1;testswitch;SW0001;ge-0/0/[0-11],fe-0/1/[0-11];24,19
 Creates a Switch object named myAccessSwitch1 having 12x 1000-Base-T interfaces named ge-0/0/0 to ge-0/0/11. And also 12x 100-Base-TX interfaces named fe-0/1/0 to fe-0/1/11.


* Importing Racks

 Syntax: RACK; Location ; Location child ; Row ; Rack; Height
 Value 1, RACK
 Value 2, Location ; Specifies the location where the rack is to be placed. This can be the location name string of an existing location. If the location does not exist, it will be created.
 Value 3, Location child ; Specifies the child location (eg room) where the rack is to be placed. This can be the name of an existing location. If the location does not exist, it will be created.
 Value 4, Row: Specifies the row where the rack is to be placed. This can be the name of an existing row. If the row does not  exist, it will be created
 Value 5, Rack: Name of the rack that is to be created.
 Value 6, Height: Sets the Height of the rack in rackunits. When omitted, the default value is 46 units.
 
 Examples:

 RACK;Datacenter AMS01; Room 0.08; R01; AA-1
 Creates a rack named AA-1 in Room 0.08 of location Datacenter AMS01, with a height of 46 units.


* Assigning Rackspace

 Syntax: RACKASSIGNMENT; Object name ;Rack; units ; fib
 Value 1, RACKASSIGNMENT
 Value 2, Object name: Name of the Racktables object
 Value 3, Rack; NAme of the rack where the object is to be placed.
 Value 4, units: List of units to be assigned to the object. The unit numbers are separated by a comma.
 Value 5, fib: List of Front / Interior / Back indication. This list maps directly to the previous unit list.

 Examples:

 RACKASSIGNMENT;myServer;AA-1;32,33,34,35;fi,fi,fi,fi
 Mounts the myServer object in Rack AA-1 on rackunits 32-35, using front and interior part of the rack units.


* Linking ports

 Syntax:CABLELINK; Objectname A; Portname A; Objectname B; Portname B; Cable ID
 Value 1, CABLELINK
 Value 2, Objectname A: Specifies the name of the object on the A-side of the link
 Value 3, Portname A: Specifies the name of the port on the A-side of the link
 Value 4, Objectname B: Specifies the name of the object on the B-side of the link
 Value 5, Portname B: Specifies the name of the port on the B-side of the link 
 Value 6, Cable ID: Specifies the Cable ID. It can be numeric or string.

 Examples:

 CABLELINK;myServer;eth1;myAccessSwitch1;ge-0/0/1;0080123
 Connects the eth1 port of myServer to the ge-0/0/1 port of myAccessSwitch1, using cable ID 0080123


* Importing VLANs

 Syntax: VLAN; VLAN domain; VLAN name; VLAN ID ; Propagation; Attached IP
 Value 1, VLAN
 Value 2, VLAN domain: Specifies the name of the VLAN domain where the VLAN is to be added. If the domain does not exist, it will be created.
 Value 3, VLAN name: Specifies the name of the to be created VLAN.
 Value 4, Propagation: Sets the Racktables propagation feature for the VLAN, options are ondemand or compulsory. When ommitted the value defaults to compulsory.
 Value 5, Attached IP: This is an optional list of existing IPv4/IPv6 networks which can be assigned to the VLAN. The ranges should not have netmasks, and each range is separated by a comma.

 Examples:

 VLAN;Private;Netops;1020;compulsory;10.1.3.0,2001:610:1020::0
 Creates VLAN 1020, named Netops having the IPv4 range 10.1.3.0 and the IPv6 range 2001:610:1020::0 attached.


* Importing IP space

 Syntax: IP; Prefix; Name; is_connected; VLAN domain; VLAN ID
 Value 1, IP
 Value 2, Prefix: Specifies the IPv4 / IPv6 prefix of the network, including netmask.
 Value 3, Name: Specifies the name of the network which is to be added.
 Value 4, is_connected: Specifies if broadcast and network address in the subnet need to be reserved. Can be TRUE or FALSE. When omitted, the default is FALSE
 Value 5, VLAN domain: This is an optional value which can be used to set the VLAN domain of the network. You have to specifiy the name of the VLAN domain.
 Value 6, VLAN ID: This is an optional numeric value setting the VLAN ID of the network. It is to be used in conjunction with the previous VLAN domain value.

 Examples:

 IP;10.1.3.0/24;Netops network;TRUE;SURFsara;1020
 Creates the IP network 10.1.3.0/24 called 'Netops network' and attaches it to VLAN 1020 in the SURFsara VLAN domain.


* Importing Object IP interfaces

 Syntax: OBJECTIP; Objectname; OS Interface name; IP address; Type
 Value 1, OBJECTIP
 Value 2, Objectname: Specifies the name of the object
 Value 3, OS Interface name: Specifies the name of the interface to be added
 Value 4, IP address: Specifies the ip address of the interface to b e added (IPv4 or Ipv6) no subnet mask required
 Value 5, Type: Chooses the type of interface to be added. Can be: regular, virtual, shared, router, point2point. The default type is: router

 Examples:

 OBJECTIP;myRouter;eth0;10.1.3.1;regular
 Creates an IP interface name eth0, with address 10.1.3.1 and type 'regular', which is added to the myRouter object.

* Setting Object Attributes:
 
  Syntax: OBJECTATTRIBUTE
  Value 1, OBJECTATTRIBUTE
  Value 2, Objectname: Specifies the name of the object
  Value 3, attribute id
  Value 4, attribute value

  Examples:
  OBJECTATTRIBUTE;myRouter;3,mgmt.myrouter.com
  Sets the FQDN (3)  for the myRouter object.
-----------------------------------------
*/


// Build Navigation
$page['import']['title'] = 'Import csv data';
$page['import']['parent'] = 'config';
$tab['import']['default'] = 'Import csv data';
$tabhandler['import']['default'] = 'import_csv_data';
// Work in progress
//$tab['import']['delete'] = 'Delete csv data';
//$tabhandler['import']['delete'] = 'delete_csv_data'; 
$ophandler['import']['default']['importData'] = 'importData';
$ophandler['import']['delete']['importData'] = 'deleteData';

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
			$csvdata = str_getcsv($dataitem,";","B");
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
	$row_number = 1;
	// if the manual input is empty, load the selected file
	if (strlen(($_REQUEST['csv_text'])) == 0) 
	{   
		ini_set("auto_detect_line_endings", true);
			
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
			$csvdata = str_getcsv($dataitem,";","B");
			$csvdata[0] = trim($csvdata[0]);
			if ($csvdata[0] == "OBJECT") 			addObject($csvdata,$row_number);
			if ($csvdata[0] == "RACK")				addRackImport($csvdata,$row_number);
			if ($csvdata[0] == "RACKASSIGNMENT") 	addRackAssignment($csvdata,$row_number);
			if ($csvdata[0] == "VLAN") 				addVLAN($csvdata,$row_number);
			if ($csvdata[0] == "CABLELINK") 		addCableLink($csvdata,$row_number);
			if ($csvdata[0] == "IP") 				addIP($csvdata,$row_number);
			if ($csvdata[0] == "OBJECTIP") 			addObjectIP($csvdata,$row_number);
			if ($csvdata[0] == "OBJECTATTRIBUTE") 	setObjectAttributes($csvdata,$row_number);
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
	if (is_numeric($object_type)) 
	{
		$result = usePreparedSelectBlade ("SELECT  Dictionary.dict_value from Dictionary where Dictionary.dict_key=".$object_type.";");
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
		$result = usePreparedSelectBlade ("SELECT  Object.id, Object.objtype_id from Object where Object.name='".$location."';");
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
		$result = usePreparedSelectBlade ("select o.id, o.objtype_id, o.name, e.parent_entity_id from Object o left join EntityLink e on e.child_entity_id=o.id where name ='".$location_child."';");
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
		$result = usePreparedSelectBlade ("select o.id, o.objtype_id, o.name, e.parent_entity_id from Object o left join EntityLink e on e.child_entity_id=o.id where name ='".$rackrow."';");
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
		$result = usePreparedSelectBlade ("SELECT  Object.id, Object.objtype_id from Object where Object.name='".$rack."';");
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

	// Check if object_a and port_a exist, if not; stop and return false
	$result = usePreparedSelectBlade ("SELECT Port.*, Object.name FROM Port, Object WHERE Port.object_id = Object.id AND Port.name='".$port_a."' AND Object.name='".$object_a."';");
	$db_result_a = $result->fetch (PDO::FETCH_ASSOC);
	if (!$db_result_a)
	{
		showError("line $row_number: Import CableLink ". $cable_id. " FAILED; The object-port combination ".$object_a." ".$port_a." does not exist.");
		return FALSE;
	}

	// Check if object_a and port_a exist, if not; stop and return false
	$result = usePreparedSelectBlade ("SELECT Port.*, Object.name FROM Port, Object WHERE Port.object_id = Object.id AND Port.name='".$port_b."' AND Object.name='".$object_b."';");
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
		if($linkresult)
		{
			showError("line $row_number: Import CableLink ". $cable_id." FAILED. $object_a $port_a -> $object_b $port_b \"".$linkresult."\"");
			return FALSE;
		}
	}
	catch (Exception $e) 
	{ 
		showError("line $row_number: Import CableLink ". $cable_id." FAILED. Possible porttype mismatch. Complete Expeption data: ".$e);
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

	// Check if VLAN domain exists
	$result = usePreparedSelectBlade ("SELECT id from VLANDomain where description='". $vlan_domain . "';");
	$db_result = $result->fetch (PDO::FETCH_ASSOC);

	// If VLAN domain does not exists, create domain
	if (!$db_result)
	{
		usePreparedInsertBlade ('VLANDomain', array ('description' => $vlan_domain));
		$result = usePreparedSelectBlade ("select id from VLANDomain where description ='". $vlan_domain . "';");
		$db_result = $result->fetch (PDO::FETCH_ASSOC);
		showSuccess ("Line $row_number: VLAN Domain ".$vlan_domain. " imported; object_id=".$db_result['id']);	
	}
	$domain_id = $db_result['id'];

	// Create VLAN
	try 
	{
		usePreparedInsertBlade ("VLANDescription", array('domain_id' => $domain_id , 'vlan_id' => $vlan_id, 'vlan_type' => $vlan_propagation, 'vlan_descr' => $vlan_name));
	}
	catch (Exception $e) 
	{ 
		showError("line $row_number: Import ". $vlan_name. " vlan_id ".$vlan_id. " FAILED; VLAN already exists");
		return FALSE;
	}
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
			if (strpos($ip_range,".")) commitSupplementVLANIPv4 ($domain_id."-".$vlan_id, $net['id']);
			if (strpos($ip_range,":")) commitSupplementVLANIPv6 ($domain_id."-".$vlan_id, $net['id']);
			showSuccess ("Line $row_number: VLAN ".$vlan_name. " attached to IP range ".$ip_range);
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
		$result = usePreparedSelectBlade ("SELECT VLANDescription.*, VLANDomain.description FROM VLANDescription, VLANDomain WHERE VLANDomain.id = VLANDescription.domain_id AND VLANDescription.vlan_id=".$vlan_id." AND VLANDomain.description ='".$vlan_domain."';");
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
		showError("line $row_number: Import IP ". $prefix." FAILED. Complete Expeption data: ".$e);
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
	$result = usePreparedSelectBlade ("SELECT  Object.id from Object where Object.name='".$objectName."';");
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

// This function adds Rack assignment info for an object
function setObjectAttributes($csvdata,$row_number) 
{

	$object = 	trim ($csvdata[1]);
	$attr_id = 	trim ($csvdata[2]);
	$attr_value = trim ($csvdata[3]);
	
	if (strlen($object ) > 0) 
	{

		$result = usePreparedSelectBlade ("SELECT  Object.id, Object.objtype_id from Object where Object.name='".$object."';");
		$db_object = $result->fetch (PDO::FETCH_ASSOC);

		// Go ahead when object exists
		if ($db_object) 
		{
			commitUpdateAttrValue ($db_object['id'], $attr_id, $attr_value);
			showSuccess("line $row_number: attribute for  ".$object. ": ".$attr_id." ".$attr_value." updated");	
		}
	}
}

?>
