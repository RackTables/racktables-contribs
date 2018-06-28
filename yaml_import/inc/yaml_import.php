<?php
//
// Puppets YAML import
// Version 0.2
//
// Written by Tommy Botten Jensen
//   Modified by Loïs Taulelle
//
// The purpose of this plugin is to automatically import objects from puppets YAML files.
// History
// Version 0.1:  Initial release
// Version 0.2:  Adaptation to 0.19.x, with additionnal specs from PSMN (see skel.yaml)
// $Id: yaml_import.php 110 2011-06-10 10:09:57Z gruiick $
//
// Installation:
// 1)  Copy script to inc folder as yaml_import.php
// 2)  Add include to inc/local.php: include("yaml_import.php");
// 3)  Include the 'spyc.php' into inc/ . Get it from http://code.google.com/p/spyc/

// YAML Parser library.
require_once 'inc/spyc.php';

// Depot Tab for objects.
$tab['depot']['yaml_import'] = 'Import yaml objects';
$tabhandler['depot']['yaml_import'] = 'ImportTab';
$ophandler['depot']['yaml_import']['RunImport'] = 'RunImport';
//$ophandler['depot']['addmore']['addLotOfObjects'] = 'addLotOfObjects'; # ref

// Set variables
$Version = "0.2";

// The ophandler to insert objects (if any)
function RunImport()
{
  $taglist = isset ($_REQUEST['taglist']) ? $_REQUEST['taglist'] : array();
  $objectnames = $_POST['objectname'];

  global $dbxlink;
  
  $log = emptyLog();

  foreach($objectnames as $objectname) 
  {

  // FIXME: This reads the entire directory for each object. Not very efficient.
    if ($handle = opendir('./yamls/'))
    {
      while (false !== ($file = readdir($handle)))
      {
        # puppet names the files $FQDN.yaml, not PSMN
        if($file == $objectname . ".yaml") 
        {
          # SPYC is not happy with the puppet header. Hence read it as string, strip the header and feed it to SPYC
          $file_contents = file_get_contents("./yamls/$file");
          $file_contents = substr($file_contents, (strpos($file_contents, "\n")+1));

          $yaml_file_array = Spyc::YAMLLoadString($file_contents);
	        // FIXME: Is this the correct way to narrow in on an array?
	        // At this point, $yaml_file_array contains all the data from the YAML files in a indexed array.
          $yaml_name = $yaml_file_array['name'];
	        // switch to the 2nd part of the array
          $yaml_file_array = $yaml_file_array['parameters'];

	        // getSearchResultByField ($tname, $rcolumns, $scolumn, $terms, $ocolumn = '', $exactness = 0|1|2)
          $object = getSearchResultByField
          (
            'RackObject',
            array ('id'),
            'name',
            $yaml_name,
            '',
            2
          );

          if($object) 
          {
            # Object exists. Do NOT modify.
            $id = $object[0]['id'];	
            $log = mergeLogs ($log, oneLiner (202, array ("$objectname exists. No modifications!")));
          }

          else 
          {
            // Object does not exist. Create new.
            // Syntax: commitAddObject ($new_name, $new_label, $new_type_id, $new_asset_no, $taglist = array())
            // Type is 4, server, by default.
            $new_yamlobject = commitAddObject ($yaml_name,'',4,$yaml_file_array['serialnumber'],$taglist);

            // Hardware type (i.e. ProLiant DL380 G6a), Dict Chapter ID is '11';
            $hw_dict_key = getdict($hw=$yaml_file_array['productname'], $chapter=11 );
            commitUpdateAttrValue ($object_id = $new_yamlobject, $attr_id = '2', $value = $hw_dict_key);
            // Operating system string, Dict Chapter ID is '13'.
            $osrelease = $yaml_file_array['operatingsystem'] . " " . $yaml_file_array['operatingsystemrelease'];
            $os_dict_key = getdict($hw=$osrelease, $chapter=13);
            commitUpdateAttrValue ($object_id = $new_yamlobject, $attr_id = '4', $value = $os_dict_key);
/*
            // FIXME: The IDs should be looked up, and not preset.
            // Architecture. Attribute ID is '10000'.
            commitUpdateAttrValue ($object_id = $new_yamlobject, $attr_id = '10000', $value = $yaml_file_array['hardwareisa']);
            // Memory. Attribute ID is 17.
            commitUpdateAttrValue ($object_id = $new_yamlobject, $attr_id = '17', $value = (int)$yaml_file_array['memorysize']);
            // CPU. Attribute ID is 100001
            $cpu = $yaml_file_array['processorcount'] . " x " . $yaml_file_array['processor0'];
            commitUpdateAttrValue ($object_id = $new_yamlobject, $attr_id = '10001', $value = $cpu);
*/
            // OEM S/N 1. Attribute ID is '1'.
            commitUpdateAttrValue ($object_id = $new_yamlobject, $attr_id = '1', $value = $yaml_file_array['serialnumber']);
            // FQDN. Attribute ID is '3'.
            commitUpdateAttrValue ($object_id = $new_yamlobject, $attr_id = '3', $value = $yaml_file_array['fqdn']);
            // UUID. Attribute ID is '25'.
            commitUpdateAttrValue ($object_id = $new_yamlobject, $attr_id = '25', $value = $yaml_file_array['uuid']);
            // Hypervisor. Attribute ID is '26', Dict Chapter ID is '29'.
            // Hypervisor key does not exist in standard Puppet yaml file, added by PSMN
            if(isset($yaml_file_array['hypervisor'])) 
            {
              $hv_dict_key = getdict($hw=$yaml_file_array['hypervisor'], $chapter=29);
              commitUpdateAttrValue ($object_id = $new_yamlobject, $attr_id = '26', $value = $hv_dict_key);
            }

            // NICS
// Warning! This part only work if default Configuration is modified
// Go to "MainPage -> Configuration -> User Interface"
// Modify "AutoPorts configuration": Change "4 = 1*33*kvm + 2*24*eth%u;15 = 1*446*kvm" to "15 = 1*446*kvm"
// Ref: http://www.freelists.org/post/racktables-users/Automatic-insertions-of-servers-in-the-db,7

            $nics = explode(',',$yaml_file_array['interfaces'],9);
            $count = count($nics);

            for ($i = 0; $i < $count; $i++) 
            {
              switch ($nics[$i])
              {
              case "sit0":
                break 1;

              case "ib0": // infiniband
                if (isset($yaml_file_array['ipaddress_' . $nics[$i]]))
                {
                  $ip = $yaml_file_array['ipaddress_' . $nics[$i]];
                }
// do NOT import infiniband MAC for now
//                if (isset($yaml_file_array['macaddress_' . $nics[$i]]))
//                {
//                  $mac = $yaml_file_array['macaddress_' . $nics[$i]];
//                }
                // Add port to object. Type 40 is 10GBase-CX4, MAC can be NULL
                commitAddPort($object_id = $new_yamlobject, $nics[$i], 40,'infiniband',$mac);    
                // Add IP to object.
                if(isset($ip)) 
                {
                  bindIpToObject($ip , $new_yamlobject, $nics[$i],'regular');
                }
                break 1;

              default:
                if (preg_match("eth", $nics[$i]) === 0) break 1; 
                # this one might be bad for non-linux OSes ?
                if (isset($yaml_file_array['ipaddress_' . $nics[$i]]))
                {
                  $ip = $yaml_file_array['ipaddress_' . $nics[$i]];
                }
                if (isset($yaml_file_array['macaddress_' . $nics[$i]]))
                {
                  $mac = $yaml_file_array['macaddress_' . $nics[$i]];
                }
                // Add port to object. Type 24 is 1000Base-T	
                commitAddPort($object_id = $new_yamlobject, $nics[$i], 24,'Ethernet port',"$mac");
                // Add IP to object.
                if(isset($ip)) 
                {
                  bindIpToObject($ip , $new_yamlobject, $nics[$i],'regular');
                }
                break 1;

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
  return showSuccess ($log);
}

// Display the import page.
function ImportTab()
{
  global $nextorder;
  global $Version;
  global $taglist;
?>

  <style type='text/css'>
  tr.has_problems 
  {
    background-color: #ffa0a0;
  }
  </style>

  <!--Legend-->
  <table align=right>
  <tr class=trerror><td colspan=2>Unknown object</td></tr>
  <tr><td class=row_even>Existing </td><td class=row_odd> object</td></tr>
  </table>

  <center><h1>Import yaml objects </h1><h2>from /yamls/</h2></center>
<?php
  startPortlet();
  echo "<table with=90% align=center border=0 cellpadding=5 cellspacing=0 align=center class=cooltable><tr valign=top>";


  echo "<form method=post name=ImportObject action='?module=redirect&page=depot&tab=yaml_import&op=RunImport'>";
  echo "<tr valign=top><th>Assign tags</th><th align=center>Name</th><th align=center>Import ?</th></tr>";

// taglist on display - left handed
  echo "<tr valign=top><td rowspan=\"0\">";
  renderNewEntityTags('object');
  echo "</td></tr>";

  $order = 'odd';
  # Find and read loop through all .yaml files in the yaml directory.
//  if ($handle = opendir('./yamls/'))
  if ($files = scandir('./yamls/'))  
  {
//    while (false !== ($file = readdir($handle))) 
    foreach($files as $file)
    {
      # Since puppet names the files $FQDN.yaml, we don't have to inspect the file during the first run.
      if(preg_match('/\.yaml/',$file)) 
      {
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

        if($object) 
        {
          $url=makeHref 
          (array 
            (
              'page' => 'object',
              'tab' => 'default',
              'object_id' => $object[0]['id']
            )
          );

          echo "<tr class=row_${order}><td align=left><a href=\"$url\">" . $name .  "</a></td>\n";
        }
        else 
        {
          echo "<tr class=trerror><td align=left>" . $name . "</td>\n";
        }
        echo "<td align=center> <input type=checkbox name=objectname[] value=$name></td></tr>\n";
        $order = $nextorder[$order];
      }
    }
  }
// tags ?
//  echo "<tr><td rowspan=\"0\">";
//  renderNewEntityTags('object');
//  echo "</td></tr>";

  echo "<tr><td align=left><font size=1em color=gray>version ${Version}</font></td><td align=right><input type=submit name=got_very_fast_data value='Import selected items'></td><td></td></tr></table></td></tr>";
  echo "</form>";
  echo "</table>";
  finishPortlet();
}

// Gets the dict_key for a specified chapter_id . If it does not exist, we create a dictionary entry.
// a bit redundant with existing dict entries, won't hurt.
// Table Dictionary: (chapter_id,dict_key,'dict_value')
function getdict ($hw,$chapter) 
{
  try 
  {
    global $dbxlink;
    $query = "select dict_key from Dictionary where chapter_id='$chapter' AND dict_value LIKE '%$hw%' LIMIT 1";
    $result = usePreparedSelectBlade ($query);
    $array = $result->fetchAll (PDO::FETCH_ASSOC);

    if($array) 
    {
      return $array[0]['dict_key'];
    }
    else 
    {
      $dbxlink->exec("INSERT INTO Dictionary (chapter_id,dict_value) VALUES ('$chapter','$hw')");
    
      $squery = "select dict_key from Dictionary where dict_value ='$hw' AND chapter_ID ='$chapter' LIMIT 1";
      $sresult = usePreparedSelectBlade ($squery);
      $sarray = $sresult->fetchAll (PDO::FETCH_ASSOC);

      if($sarray) 
      {
        return $sarray[0]['dict_key'];
      }
      else 
      {
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
