<?php
/*
 * Facter (Puppet) plugin for racktables 0.19.1 (and probably newer)
*
* This file is both a web GUI and REST webservice for auto create and update machines based on facter (puppet) files.
*
* REST examples:
* curl -k -F myfile=@facter.txt -u username:password https://facter/racktables/process.php?page=depot&tab=facter&op=Update
* curl -F myfile=@facter.txt -u username:password http://facter/racktables/process.php?page=depot&tab=facter&op=Update
*
* Usage instructions:
*  * Just symlink it to your inc folder
*  * add it to the local.php file inn your inc folder: echo "<?php include_once 'facter.php'; ?>" >> inc/local.php
*  * If you want you can add the CPU attribute and it will show you your cpu's aswell
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
		$tmpfacter=explode(":",$line,2);
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
		$newmachine=commitAddObject($facter['fqdn'],$facter['fqdn'],$virtual,$value = "");
	}
	// If it's an existing machine
	else
	{
		// Just set some fields I use later down for updating
		$newmachine=$id;
		$machineupdate=1;
	}



	// Add lot's of attributes to the machine. Next version use an array (from config file) and do a loop for most of these fields.....

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

	// Find FQDN id
	$query = "select id from Attribute where name='FQDN' LIMIT 1";
	unset($result);
	$result = usePreparedSelectBlade ($query);
	$resultarray = $result->fetchAll (PDO::FETCH_ASSOC);
	if($resultarray) {
		$id=$resultarray[0]['id'];
		// Update FQDN
		commitUpdateAttrValue ($object_id = $newmachine, $attr_id = $id, $value = $facter['fqdn']);
	}


	// Find HW type id
	// 2011-08-31 <neil.scholten@gamigo.com>
	// * adjust format to match default Dictionary Sets
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


	// Find SW type id (OS)
	$query = "select id from Attribute where name='SW type' LIMIT 1";
	unset($result);
	$result = usePreparedSelectBlade ($query);
	$resultarray = $result->fetchAll (PDO::FETCH_ASSOC);
	if($resultarray) {
		$id=$resultarray[0]['id'];
		// Update SW type (OS)
		$osrelease = $facter['lsbdistid'] . '%GSKIP%' . $facter['lsbdistid'] . ' V' . $facter['lsbmajdistrelease'];
		$os_dict_key = getdict($hw=$osrelease, $chapter=13);
		commitUpdateAttrValue ($object_id = $newmachine, $attr_id = $id, $value = $os_dict_key);
	}


	// Find OEM S/N 1
	$query = "select id from Attribute where name='OEM S/N 1' LIMIT 1";
	unset($result);
	$result = usePreparedSelectBlade ($query);
	$resultarray = $result->fetchAll (PDO::FETCH_ASSOC);
	if($resultarray) {
		$id=$resultarray[0]['id'];
		// Update serial number
		commitUpdateAttrValue ($object_id = $newmachine, $attr_id = $id, $value = $facter['serialnumber']);
	}


	// Find Architecture id
	$query = "select id from Attribute where name='Architecture' LIMIT 1";
	unset($result);
	$result = usePreparedSelectBlade ($query);
	$resultarray = $result->fetchAll (PDO::FETCH_ASSOC);
	if($resultarray) {
		$id=$resultarray[0]['id'];
		// Update Architecture id
		commitUpdateAttrValue ($object_id = $newmachine, $attr_id = $id, $value = $facter['architecture']);
	}



	// Find Memory id
	$query = "select id from Attribute where name='DRAM, GIB' LIMIT 1";
	unset($result);
	$result = usePreparedSelectBlade ($query);
	$resultarray = $result->fetchAll (PDO::FETCH_ASSOC);
	if($resultarray) {
		$id=$resultarray[0]['id'];
		// Update Memory id
		commitUpdateAttrValue ($object_id = $newmachine, $attr_id = $id, $value = $facter['memorysize']);
	}


	// Find CPU id (custom field you'll need to add yourself)
	$cpu = $facter['processorcount'] . " x " . $facter['processor0'];
	$query = "select id from Attribute where name='CPU' LIMIT 1";
	unset($result);
	$result = usePreparedSelectBlade ($query);
	$resultarray = $result->fetchAll (PDO::FETCH_ASSOC);
	if($resultarray) {
		$id=$resultarray[0]['id'];
		// Update CPU id
		$cpu = $facter['processorcount'] . " x " . $facter['processor0'];
		commitUpdateAttrValue ($object_id = $newmachine, $id, $value = $cpu);
	}

	// Add network interfaces

	// Create an array with interfaces
	$nics = explode(',',$facter['interfaces']);

	// Go thew all interfaces and add IP and MAC
	$count = count($nics);
	for ($i = 0; $i < $count; $i++) {
		// Remove newline from the field
		$nics[$i]=str_replace("\n","", $nics[$i]);

		// We generally don't monitor sit interfaces.
		// We don't do this for lo interfaces, too
		// 2011-08-31 <neil.scholten@gamigo.com>
		// * Only Document real interfaces, dont do bridges, bonds, vlan-interfaces
		//   when they have no IP defined.
		if ( preg_match('(_|^(br|bond|lo|sit|vnet|virbr))',$nics[$i]) != 0 && !isset($facter['ipaddress_' . $nics[$i]]) ) {
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
				$query = "select dict_key from Dictionary where dict_value='1000Base-T' LIMIT 1";
			}
			else
			{
				// Find virtual port id
				$query = "select dict_key from Dictionary where dict_value='virtual port' LIMIT 1";
			}
			unset($result);
			$result = usePreparedSelectBlade ($query);
			$resultarray = $result->fetchAll (PDO::FETCH_ASSOC);
			if($resultarray) {
				$nictypeid=$resultarray[0]['dict_key'];
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
	
			// Add/update ip
			if (count($ipcheck) == 1 ) {
				if( $ip ) {
					updateAddress($ip , $newmachine, $nics[$i],'regular');
				}
			}
			else
			{
				if( $ip ) {
					bindIpToObject($ip , $newmachine, $nics[$i],'regular');
				}
			}
	
			unset($portcheck);
			unset($ipcheck);
			unset($ip);
			unset($mac);
		}
	}
	return buildWideRedirectURL ();
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
?>
