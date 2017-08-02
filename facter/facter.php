<?php
/*
 * Facter (Puppet) plugin for racktables 0.20.1 (and probably newer)
*
* This file is both a web GUI and REST webservice for auto create and update machines based on facter (puppet) files.
*
* REST example:
* curl -F "userfile=@/root/facter.txt" -u username:password "http://racktables/index.php?module=redirect&page=depot&tab=facter&op=Update"
*
* Usage instructions:
*  Add to plugins folder along with manifest file
*  To get VMs to auto add you have to create a facter function to return a list like:
*  export FACTER_VMs=$(virsh list | awk '$3 == "running" {printf $2","}' | sed -e 's/,$//');
*  Whatever you use for VMs it should return a list like: vms => vm1,vm2
*
*
* Author: Torstein Hansen <huleboer@users.sourceforge.net>, sponsored by eniro.no
*
* This script is based on yaml_import for racktables by Tommy Botten Jensen
*
* 2011-08-25 modified by Neil Scholten <neil.scholten@gamigo.com>
* - adjusted path for racktables > 0.19.1
* - modified .yaml parsing to match to 'facter -py' format
* - modified interface-type detection to use virtual port on VMs
* - modified OS detection to match more better default sets (Testcase: CentOS).
*
* 2012-12-13 modified by Daniel Kasen <djtech@gmail.com>
* - added generic looping to easially add fields
* - Corrected issues with VM's breaking script
* - reverted .yaml parsing due to strings in facter not parsing right for mem. options
* - added error checking to ignore unusable lines in manifest file
* - fixed ip additions for 20.1
* - added VM auto adding to Parent
*
* 2015-02-10 modified by Gjermund Jensvoll <gjerjens@gmail.com>
* - RackTables >= 0.20.8 compatability (Fixing error "Argument 'port_type_id' of value NULL is invalid (format error)")
*
*/


// Depot Tab for objects.
$tab['depot']['facter'] = 'Facter';
$tabhandler['depot']['facter'] = 'ViewHTML';
$ophandler['depot']['facter']['Update'] = 'Update';

// The ophandler to insert objects (if any)
function Update()
{
	// Read uploaded file
	$lines = file($_FILES['userfile']['tmp_name']);

	// add file contents to facter array
	foreach ($lines as $line_num => $line)
	{
		$tmpfacter=explode("=>",$line,2);
		$facter[trim($tmpfacter[0])]=str_replace('"', '',trim($tmpfacter[1]));
	}

	// Fix fqdn since all fields have \n inn them
	$facter['fqdn']=str_replace("\n","", $facter['fqdn']);

	// Check if it's an existing machine
	// 2011-08-31 <neil.scholten@gamigo.com>
	// * expanded query to try to match via facter Serialnumber to be able to
	//   match unnamed HW assets. Serial is more precise and less likely to be changed.
	if (
		array_key_exists('serialnumber', $facter) &&
		strlen($facter['serialnumber']) > 0 &&
		$facter['serialnumber'] != 'Not Specified' ) {
		$query = "select id from RackObject where name = \"$facter[fqdn]\" OR asset_no = \"$facter[serialnumber]\" LIMIT 1";
	} else {
		$query = "select id from RackObject where name = \"$facter[fqdn]\" LIMIT 1";
	}
	unset($result);
	$result = usePreparedSelectBlade ($query);
	$resultarray = $result->fetchAll (PDO::FETCH_ASSOC);
	if($resultarray) {
		$id=$resultarray[0]['id'];
	}
	// If it's a new machine
	if (! isset($id))
	{
		// Check to see if it's a physical machine and get the correct id for Server
		if ($facter['is_virtual']=="false")
		{
			// Find server id
			$query = "select dict_key from Dictionary where dict_value='Server' LIMIT 1";
			unset($result);
			$result = usePreparedSelectBlade ($query);
			$resultarray = $result->fetchAll ();
			if($resultarray)
			{
				$virtual=$resultarray[0]['dict_key'];
			}
		}
		// Check to see if it's a virtual machine and get the correct id for VM
		else
		{
			// Find virtual id
			$query = "select dict_key from Dictionary where dict_value='VM' LIMIT 1";
			unset($result);
			$result = usePreparedSelectBlade ($query);
			$resultarray = $result->fetchAll (PDO::FETCH_ASSOC);
			if($resultarray)
			{
				$virtual=$resultarray[0]['dict_key'];
			}
		}
		// Add the new machine
		$newmachine=commitAddObject($facter['fqdn'],"",$virtual,$value = "");
		$type_id = getObjectTypeID($newmachine);
	}
	// If it's an existing machine
	else
	{
		// Just set some fields I use later down for updating
		$newmachine=$id;
		$machineupdate=1;
		$type_id = getObjectTypeID($newmachine);
	}


	// 2011-08-31 <neil.scholten@gamigo.com>
	// * Update (unique) name of object.
	if (
		array_key_exists('serialnumber', $facter) &&
		strlen($facter['serialnumber']) > 0 &&
		$facter['serialnumber'] != 'Not Specified' ) {
		unset($result);
		$query				= "select * from RackObject where asset_no = \"$facter[serialnumber]\" LIMIT 1";
		$result				= usePreparedSelectBlade ($query);
		$resultarray 	= $result->fetchAll (PDO::FETCH_ASSOC);
		if($resultarray) {
			$id			= $resultarray[0]['id'];
			$label	= $resultarray[0]['label'];
			// Update FQDN
			commitUpdateObject($id, $facter['fqdn'], $label, 'no', $facter['serialnumber'], 'Facter Import::Update Common Name');
		}
	}

	// Find HW type id
	// 2011-08-31 <neil.scholten@gamigo.com>
	// * adjust format to match default Dictionary Sets
	$update_hw_type = true;
	if (isset($_GET['update_hw_type']) && $_GET['update_hw_type'] == 'false')
	{
		$update_hw_type = false
	}
	if ($facter['is_virtual']=="false" && $update_hw_type)
	{
		$iHWTemp				= preg_match('([a-zA-Z]{1,})', $facter['manufacturer'], $matches);
		$sManufacturer	= $matches[0];
		$sHW						= preg_replace('(\ )', '\1%GPASS%', $facter['productname']);
		$sHWType				= $sManufacturer.' '.$sHW;
		$query					= "select id from Attribute where name='HW type' LIMIT 1";
		unset($result);
		$result = usePreparedSelectBlade ($query);
		$resultarray = $result->fetchAll (PDO::FETCH_ASSOC);
		if($resultarray) {
			$id=$resultarray[0]['id'];
			// Update HW type
			$hw_dict_key = getdict($sHWType, $chapter=11 );
			commitUpdateAttrValue ($object_id = $newmachine, $attr_id = $id, $value = $hw_dict_key);
		}
		//Also Check if HYPERVISOR
		if (isset($facter['is_hypervisor']))
		{
			$query = "select id from Attribute where name REGEXP '^ *Hypervisor Type$' LIMIT 1";
			unset($result);
        		$result = usePreparedSelectBlade ($query);
        		$resultarray = $result->fetchAll (PDO::FETCH_ASSOC);
        		if($resultarray) {
              		$id=$resultarray[0]['id'];
					// Update Hypervisor type
					$hypervisor_type = $facter['is_hypervisor'];
        	      	$hypervisor_type_dict_key = getdict($hw=$hypervisor_type, $chapter=10005);
                	commitUpdateAttrValue ($object_id = $newmachine, $attr_id = $id, $value = $hypervisor_type_dict_key);
			}
			//Set Value to Yes
			$query = "select id from Attribute where name REGEXP '^ *Hypervisor' LIMIT 1";
			unset($result);
        	$result = usePreparedSelectBlade ($query);
        	$resultarray = $result->fetchAll (PDO::FETCH_ASSOC);
        	if($resultarray) {
				$id=$resultarray[0]['id'];
				// Update Hypervisor type
				$hypervisor = "Yes";
				$hypervisor_dict_key = getdict($hypervisor, $chapter=29);
               	commitUpdateAttrValue ($object_id = $newmachine, $attr_id = $id, $value = $hypervisor_dict_key);
			}
            //Find Running VMs
            $vms = explode(',',$facter['vms']);
            $vm_count = count($vms);
            for ($i = 0; $i < $vm_count; $i++) {
				//addToParent
				addVmToParent ($vms[$i], $newmachine);
			}
		} else {
			$query = "select id from Attribute where name REGEXP '^ *Hypervisor' LIMIT 1";
        	        unset($result);
                        $result = usePreparedSelectBlade ($query);
                        $resultarray = $result->fetchAll (PDO::FETCH_ASSOC);
                        if($resultarray) {
                                $id=$resultarray[0]['id'];
                                // Update Hypervisor type
                                $hypervisor = "No";
                                $hypervisor_dict_key = getdict($hypervisor, $chapter=29);
                                commitUpdateAttrValue ($object_id = $newmachine, $attr_id = $id, $value = "");
			}
		}
	}

	// Find SW type id (OS)
	$query = "select id from Attribute where name REGEXP '^ *SW type$' LIMIT 1";
	unset($result);
	$result = usePreparedSelectBlade ($query);
	$resultarray = $result->fetchAll (PDO::FETCH_ASSOC);
	if($resultarray) {
		$id=$resultarray[0]['id'];
		// Update SW type (OS)
		// Ubuntu LTS - https://wiki.ubuntu.com/LTS
		// Ubuntu LTS can be detected by an even OS Major and an OS minor of '04'.
		// Example Ubuntu 'operatingsystemrelease' values: '16.10', '15.04'.
		$os_release_lts = "";
		$facter_osrelease = explode(".", $facter['operatingsystemrelease'])
		if ($facter['operatingsystem'] == 'Ubuntu' && $facter_osrelease[0] % 2 == 0 && $facter_osrelease[1] == '04') {
			$os_release_lts = " LTS";
		}
		$osrelease = $facter['operatingsystem'] . '%GSKIP%' . $facter['operatingsystem'] . ' ' . $facter['operatingsystemrelease'] . $os_release_lts;
		$os_dict_key = getdict($hw=$osrelease, $chapter=13);
		commitUpdateAttrValue ($object_id = $newmachine, $attr_id = $id, $value = $os_dict_key);
	}

	//Generic to read in from file
	global $racktables_plugins_dir;
	$manifest_file = fopen($racktables_plugins_dir . "/manifest", "r") or die("Could not open manifest, make sure it is in the websrv root and called manifest \n");
	while ( ! feof ($manifest_file)) {
		$tmp_line = fgets($manifest_file);
		if (!empty($tmp_line) && !preg_match("/\/\//",$tmp_line)) {
			@list($Fact, $Attr, $Chapter) = array_map('trim', (explode(',', $tmp_line, 3)));
			//check for multi-facter names
			if(strstr($Fact, '.')) {
				@list($Fact1, $Fact2) = array_map('trim', (explode('.', $Fact)));
				$value = $facter[$Fact1] .' '. $facter[$Fact2];
				if(!isset($facter[$Fact1]) || !isset($facter[$Fact2])) {
					echo "WARNING: $Fact1 or $Fact2 does not exist in Facter for this object \n";
					continue;
				}
			} else {
				if(!isset($facter[$Fact])) {
					echo "WARNING: $Fact does not exist in Facter for this object \n";
					continue;
				} else {
					$value = $facter[$Fact];
				}
			}
			$query = "select id from Attribute where name REGEXP '^ *$Attr' LIMIT 1";
			unset($result);
			$result = usePreparedSelectBlade ($query);
			$resultarray = $result->fetchAll (PDO::FETCH_ASSOC);
			$id=$resultarray[0]['id'];
			if (!valid($type_id, $id)) {
				echo "WARNING: Not a valid Mapping for $Fact to $Attr for objectType $type_id \n";
			}
			else if($resultarray) {
				if(!empty($Chapter)) {
					$name = $value;
					$name_dict_key = getdict($hw=$name, $chapter=$Chapter);
					commitUpdateAttrValue ($object_id = $newmachine, $attr_id = $id, $value = $name_dict_key);
				} else if (preg_match("/[0-9]{1,2}\/[0-9]{1,2}\/[0-9]{4}/",$value) || preg_match("/[0-9]{1,2}\-[0-9]{1,2}\-[0-9]{4}/",$value) || preg_match("/[0-9]{4}\-[0-9]{1,2}\-[0-9]{1,2}/",$value))  {
					//handle dates
					commitUpdateAttrValue ($newmachine, $id, strtotime($value) );
				} else {
					commitUpdateAttrValue ($newmachine, $id, $value );
				}
			}
		}
	}
	fclose($manifest_file);

	// Add network interfaces

	// Create an array with interfaces
	$nics = explode(',',$facter['interfaces']);

	// Go through all interfaces and add IP and MAC
	$count = count($nics);
	for ($i = 0; $i < $count; $i++) {
		// Remove newline from the field
		$nics[$i]=str_replace("\n","", $nics[$i]);

		// We generally don't monitor sit interfaces.
		// We don't do this for lo interfaces, too
		// 2011-08-31 <neil.scholten@gamigo.com>
		// * Only Document real interfaces, dont do bridges, bonds, vlan-interfaces
		//   when they have no IP defined.
		if ( preg_match('/(_|^(docker|lo|sit|vnet|virbr|veth|peth))/',$nics[$i]) != 0 ) {
			// do nothing
		} else {
			// Get IP
			if (isset($facter['ipaddress_' . $nics[$i]]))
			$ip = $facter['ipaddress_' . $nics[$i]];

			// Get MAC
			if (isset($facter['macaddress_' . $nics[$i]]))
			$mac = $facter['macaddress_' . $nics[$i]];

			//check if VM or not
			if ($facter['is_virtual']=="false")
			{
				// Find 1000Base-T id
				$query = "select id from PortOuterInterface where oif_name REGEXP '^ *1000Base-T$' LIMIT 1";
			}
			else
			{
				// Find virtual port id
				$query = "select id from PortOuterInterface where oif_name REGEXP '^ *virtual port$' LIMIT 1";
			}
			unset($result);
			$result = usePreparedSelectBlade ($query);
			$resultarray = $result->fetchAll (PDO::FETCH_ASSOC);
			if($resultarray) {
				$nictypeid=$resultarray[0]['id'];
			}

			// Remove newline from ip
			$ip=str_replace("\n","", $ip);

			// Check to se if the interface has an ip assigned
			$query = "SELECT object_id FROM IPv4Allocation where object_id=$newmachine and name=\"$nics[$i]\"";
			unset($result);
			$result = usePreparedSelectBlade ($query);
			$resultarray = $result->fetchAll (PDO::FETCH_ASSOC);

			if($resultarray) {
				unset($id);
				$ipcheck=$resultarray;
			}
			// Check if it's been configured a port already
			$query = "SELECT id,iif_id FROM Port where object_id=$newmachine and name=\"$nics[$i]\"";
			unset($result);
			$result = usePreparedSelectBlade ($query);
			$resultarray = $result->fetchAll (PDO::FETCH_ASSOC);

			if($resultarray) {
				$portid = $resultarray[0]['id'];
				unset($id);
				$portcheck=$resultarray;
			}
			// Add/update port
			// 2011-08-31 <neil.scholten@gamigo.com>
			// * Don't touch already existing complex ports
			if ( $resultarray[0]['type'] != 9 ) {
				if ( count($portcheck) == 1 ) {
					commitUpdatePort($newmachine,$portid, $nics[$i], $nictypeid, "Ethernet port", "$mac", NULL);
				}
				else
				{
					commitAddPort($object_id = $newmachine, $nics[$i], $nictypeid,'Ethernet port',"$mac");
				}
			} else {
				//We've got a complex port, don't touch it, it raises an error with 'Database error: foreign key violation'
			}
			if (count($ipcheck) == 1 ) {
				if( $ip ) {
					updateAddress(ip_parse($ip) , $newmachine, $nics[$i],'regular');
				}
			}
			else
			{
				if( $ip ) {
					bindIpToObject(ip_parse($ip), $newmachine, $nics[$i],'regular');
				}
			}
			unset($portcheck);
			unset($ipcheck);
			unset($ip);
			unset($mac);
		}
	}
	//uncomment to start using auto tags
	//addTagToObject($facter, $newmachine);
	return buildRedirectURL ();
}

// Display the import page.
function ViewHTML()
{
	startPortlet();
	echo "<table with=90% align=center border=0 cellpadding=5 cellspacing=0 align=center class=cooltable><tr valign=top>";
	echo "<form method=post enctype=\"multipart/form-data\" action='index.php?module=redirect&page=depot&tab=facter&op=Update'>";
	echo "<input type=\"hidden\" name=\"MAX_FILE_SIZE\" value=\"30000\" />";
	echo "Upload a facter file: <input name=\"userfile\" type=\"file\" /><br />";
	echo "<input type=\"submit\" value=\"Upload File\" />";
	echo "</td></tr></table></td></tr>";
	echo "</form>";
	echo "</table>";
	finishPortlet();
}

function getdict ($hw,$chapter) {
	try {
		global $dbxlink;
		$query = "select dict_key from Dictionary where chapter_id='$chapter' AND dict_value ='$hw' LIMIT 1";
		$result = usePreparedSelectBlade ($query);
		$array = $result->fetchAll (PDO::FETCH_ASSOC);

		if($array) {
			return $array[0]['dict_key'];
		}
		else {
			// Chapter ID for hardware is 11.
			$dbxlink->exec("INSERT INTO Dictionary (chapter_id,dict_value) VALUES ('$chapter','$hw')");

			$squery = "select dict_key from Dictionary where dict_value ='$hw' AND chapter_ID ='$chapter' LIMIT 1";
			$sresult = usePreparedSelectBlade ($squery);
			$sarray = $sresult->fetchAll (PDO::FETCH_ASSOC);

			if($sarray) {
				return $sarray[0]['dict_key'];
			}

			else {
				// If it still has not returned, we are up shit creek.
				return 0;
			}
		}
		$dbxlink = null;
	}
	catch(PDOException $e)
	{
		echo $e->getMessage();
	}
}

//new check to make sure the object type allows the string
function valid ($type_id, $id) {
	//Check to see if this combination exists in the AttributeMap
	$valid = "SELECT * from AttributeMap WHERE objtype_id = '$type_id' AND attr_id = '$id' LIMIT 1";
	unset($result);
	$result = usePreparedSelectBlade ($valid);
	$resultarray = $result->fetchAll (PDO::FETCH_ASSOC);
	$exists = $resultarray[0]['objtype_id'];
	if (!empty($exists))
		return 1;
	else
		return 0;
}

function getObjectTypeID ($newmachine) {
	$objtype = "SELECT objtype_id from RackObject WHERE id = $newmachine LIMIT 1";
	unset($result);
	$result = usePreparedSelectBlade ($objtype);
	$resultarray = $result->fetchAll (PDO::FETCH_ASSOC);
	return $resultarray[0]['objtype_id'];
}

//Find Parent of VM Object
function addVmToParent ($vms, $newmachine) {
	$search_for_child = "select id from RackObject WHERE name REGEXP '^ *$vms\\\.'";
	unset($result);
	$result = usePreparedSelectBlade ($search_for_child);
	$resultarray = $result->fetchAll (PDO::FETCH_ASSOC);
	$child = $resultarray[0]['id'];
	if (!empty($child)){
		//make sure the association doesn't exist already or deal with it
		$current_container = "SELECT parent_entity_id from EntityLink WHERE child_entity_id = $child";
		unset($result);
		$result = usePreparedSelectBlade ($current_container);
		$resultarray = $result->fetchAll (PDO::FETCH_ASSOC);
		$current_parent = $resultarray[0]['parent_entity_id'];
		if ( ($current_parent != $newmachine ) && !empty($current_parent)){
			commitUpdateEntityLink('object',$current_parent,'object',$child,'object',$newmachine,'object',$child);
		} else if (empty($current_parent)) {
			commitLinkEntities('object', $newmachine,'object',$child);
		}
	} else {
		echo "WARNING: The $vms VM does not exist for this Parent \n";
	}
}

//Auto Tagging
//Must enable this function in Update function and ensure "$facter[KEY]" exists
function addTagToObject ($facter, $newmachine) {
	$tags = array($facter['machinetype'], $facter['domain']);
	$count_tags = count($tags);
	for ($i = 0; $i < $count_tags; $i++) {
		rebuildTagChainForEntity ('object', $newmachine, array (getTagByName($tags[$i])));
	}
}
