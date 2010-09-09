<?php
//
// Puppets YAML import
// Version 0.1
//
// Written by Tommy Botten Jensen
//
// The purpose of this plugin is to automatically import objects from puppets YAML files.
// History
// Version 0.1:  Initial release
//
// Installation:
// 1)  Copy script to inc folder as yaml_import.php
// 2)  Add include to inc/local.php: include("yaml_iplog.php");
// 3)  Include the 'spyc.php' into inc/ . Get it from http://code.google.com/p/spyc/

# YAML Parser library.
require_once 'inc/spyc.php';


// Depot Tab for objects.
$tab['depot']['yaml_import'] = 'Import objects';
$tabhandler['depot']['yaml_import'] = 'ImportTab';
$ophandler['depot']['yaml_import']['RunImport'] = 'RunImport';


// Set variables
$Version = "0.1";
$username = $_SERVER['PHP_AUTH_USER'];
$nextorder['odd'] = 'even';
$nextorder['even'] = 'odd';

// The ophandler to insert objects (if any)
function RunImport()
{
  $objectnames = $_POST['objectname'];
  global $dbxlink;
  global $username;
  $log = emptyLog();

  foreach($objectnames as $objectname) {
   // FIXME: This reads the entire directory for each object. Not very efficient.
   if ($handle = opendir('./yamls/')) {
      while (false !== ($file = readdir($handle))) {
        # puppet names the files $FQDN.yaml
        if($file == $objectname . ".yaml") {
            # SPYC is not happy with the puppet header. Hence read it as string, strip the header and feed it to SPYC
            $file_contents = file_get_contents("./yamls/$file");
            $file_contents = substr($file_contents, (strpos($file_contents, "\n")+1));
            
            # I Also remove all entries with ID00X seems to annoy SPYC.
            $file_contents = preg_replace('/\*id00/','',$file_contents);
    
            $yaml_file_array = Spyc::YAMLLoadString($file_contents);
	    // FIXME: Is this the correct way to narrow in on an array?
	    // At this point, $yaml_file_array contains all the data from the YAML files in a indexed array.
	    $yaml_file_array = $yaml_file_array['parameters'];
	    
  	   $object = getSearchResultByField
  		(
  			'RackObject',
  			array ('id'),
  			'name',
  			$objectname,
			'',
			2
  		);
  
  	   if($object) {
  		# Object exists. Modify.
  		$id = $object[0]['id'];	
        		$log = mergeLogs ($log, oneLiner (202, array ("$objectname exists.Modification not yet implemented ")));
  	   }
  
  	   else {
  		// Object does not exist. Create new.
		//Syntax: commitAddObject ($new_name, $new_label, $new_barcode, $new_type_id,$new_asset_no, $taglist = array())
		// Type is 4, server.
            $new_yamlobject = commitAddObject ($yaml_file_array['fqdn'],'','',4,$yaml_file_array['serialnumber']);

       	// Hardware type. i.e. ProLiant DL380 G6a
      	$hw_dict_key = getdict($hw=$yaml_file_array['productname'], $chapter=11 );
		commitUpdateAttrValue ($object_id = $new_yamlobject, $attr_id = '2', $value = $hw_dict_key);
      	// Operating system string. I.e. 
       	$osrelease = $yaml_file_array['operatingsystem'] . $yaml_file_array['operatingsystemrelease'];
       	$os_dict_key = getdict($hw=$osrelease, $chapter=13);
		commitUpdateAttrValue ($object_id = $new_yamlobject, $attr_id = '4', $value = $os_dict_key);

       // FIXME: The IDs should be looked up, and not preset.
      // Architecture. Attribute ID is '10000'.
	commitUpdateAttrValue ($object_id = $new_yamlobject, $attr_id = '10000', $value = $yaml_file_array['hardwareisa']);
      // Memory. Attribute ID is 17.
	commitUpdateAttrValue ($object_id = $new_yamlobject, $attr_id = '17', $value = (int)$yaml_file_array['memorysize']);
      // CPU. Attribute ID is 100001
	$cpu = $yaml_file_array['processorcount'] . " x " . $yaml_file_array['processor0'];
	commitUpdateAttrValue ($object_id = $new_yamlobject, $attr_id = '10001', $value = $cpu);
      // FQDN. Attribute ID is '3'.
	commitUpdateAttrValue ($object_id = $new_yamlobject, $attr_id = '3', $value = $yaml_file_array['fqdn']);

       // NICS
	$nics = explode(',',$yaml_file_array['interfaces'],9);
	$count = count($nics);
       
	for ($i = 0; $i < $count; $i++) {
	 // We generally don't monitor sit interfaces.
	  if ($nics[$i] == "sit0")
	        break;
	
	  if (isset($yaml_file_array['ipaddress_' . $nics[$i]]))
	          $ip = $yaml_file_array['ipaddress_' . $nics[$i]];
	  if (isset($yaml_file_array['macaddress_' . $nics[$i]]))
	        $mac = $yaml_file_array['macaddress_' . $nics[$i]];
		// Add a port to an object. Type 24 is 1000Base-T	
	  commitAddPort($object_id = $new_yamlobject, $nics[$i], 24,'Ethernet port',"$mac");
		// Add an IP to an object.
          if($ip) {
	    bindIpToObject($ip , $new_yamlobject, $nics[$i],'regular');
          }
	  unset($ip);
	  unset($mac);
	  }

		// Create a URL for the log message.
           $url=makeHref (array (
                    'page' => 'object',
                    'tab' => 'default',
                    'object_id' => $new_yamlobject
            ));
    	   	$loginstance = "<a href=\"$url\">" . $objectname .  "</a>";
    		$log = mergeLogs ($log, oneLiner (80, array ("$loginstance")));
  	   }
  
        }
      }
    }
  }
  return buildWideRedirectURL ($log);
}

// Display the import page.
function ImportTab()
{
    global $nextorder;
    global $username;
    global $Version;
?>

<style type='text/css'>
tr.has_problems {
background-color: #ffa0a0;
}
</style>

<table align=right>
  <tr class=trerror><td>Non-existing object: </td></tr>
</table>

<center><h1>Import puppet objects</h1><h2>yaml objects from ./yaml/</h2></center>
<?php
    startPortlet();
    echo "<table with=90% align=center border=0 cellpadding=5 cellspacing=0 align=center class=cooltable><tr valign=top>";
    echo "<form method=post name=ImportObject action='process.php?page=depot&tab=yaml_import&op=RunImport'>";
    echo "<tr><th align=center>Name</th><th align=center>Import?</th></tr>";
    $order = 'odd';
    # Find and read loop through all .yaml files in the yaml directory.
    if ($handle = opendir('./yamls/')) {
       while (false !== ($file = readdir($handle))) {
	 # Since puppet names the files $FQDN.yaml, we don't have to inspect the file during the first run.
         if(preg_match('/\.yaml/',$file)) {
	   $name = preg_replace('/\.yaml/','',$file);
	   # Do a search on the row 'name' passing the one name, and retrieving the ID.
	   $object = getSearchResultByField
		(
			'RackObject',
			array ('id'),
			'name',
			$name,
			'',
			2
		);

	   if($object) {
       $url=makeHref (array (
                'page' => 'object',
                'tab' => 'default',
                'object_id' => $object[0]['id']
        ));

	   echo "<tr class=row_${order}><td align=left><a href=\"$url\">" . $name .  "</a></td>\n";
	   }

 	   else {
	   echo "<tr class=trerror><td align=left>" . $name . "</td>\n";
	   }
	   echo "<td align=center> <input type=checkbox name=objectname[] value=$name></td></tr>\n";
           $order = $nextorder[$order];

	 }
       }
    }

    echo "</select>";
    echo "</th>";
    echo "<tr><td align=left><font size=1em color=gray>version ${Version}</font></td><td align=right><input type=submit name=got_very_fast_data value='Import selected items'></td></tr></table></td></tr>";
    echo "</form>";
    echo "</table>";
    finishPortlet();
}

// Gets the dict_key for a specified chapter_id . If it does not exist, we create a dictionary entry.
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
