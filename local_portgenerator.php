<?php
//
// This extension for RackTables is used to generate ports based on object type and hardware type
//
// Version 1.1
//
// Created by Jeroen Benda
// 04-2010
// http://racktablescode.j-tools.net
//
// Please do not remove the credentials if you alter this file.
//

/*
This port generator is a replacement for the built-in auto port generator. I found the built-in version to be to limited. This port generator
uses not only the object type but also the hardware type to determine the ports needed. The definition used to generate the ports is also used
maintainable through the same interface. It will also allow you to use the attribute 'number of ports' when generating ports which can be
convenient for example with patch panels.

The way it works is easy. A new table is created which holds the definition per dictionary key. When an object has no ports defined yet
(and the object type is not in the exclude-from-port-generator list ($noPortGenerator)) a tab will pop up for the selected object.

If there already is a definition for a specific hardware type the ports that will be generated is shown. With one click those ports are
generated. You can also chose to update the definition (or create one if the object type or hardware type does not already have one.

A definition consists of one or more semicolon separated port definitions. Each definition has 4 or optionally 5 parts which are separated by a |
The 5 parts are:
Start number : at which number should the range start. If there is only 1 port this can be set at anything
Count        : the number of ports of this type (this is where you can use %n for the value of the attribute number of ports)
Port name    : the name of the port. If the count is greater than 1 you should include %u which is replaced by the the number.
Port type id : the port type id of this group of ports
Label        : optional, the label for the port. Again you can should use %u if you have more than 1 port. If you do not include it, the label
  will be left blank

An example for a server:
1|2|pwr%u|16;0|2|eth%u|24|%u

This will generate the following ports:
pwr1 of type 16
pwr2 of type 16
eth0 of type 24 with label 0
eth1 of type 24 with label 1

An example for a patch panel:
1|%n|eth%u|24|%u;1|%n|eth%ulink|50198

This will generate the following ports:
eth1 of type 24 with label 1
eth2 of type 24 with label 2
....
ethx of type 24 with label x (where x is the number of ports defined in the attribute)
eth1link of type 50198 (the type we use for utpLink cable)
....
ethxlink of type 50198 (where x is the number of ports defined in the attribute)

Example for a Port Innerinterface + Outerinterface: 
1|2|G%u|4-1077|gigabitEthernet0/%u

This will generate the following ports:
G1 of inner Type 4 and outer type 1077 (SFP-1000 with empty SFP-1000 inside)
G2 of inner Type 4 and outer type 1077 (SFP-1000 with empty SFP-1000 inside)

*/

//
// This php file depends on 2 variables and 2 constants that have to be set before this file is included:
//
// $tablePortGenerator is the name of the table used
// $noPortGenerator contains dictionary keys that will not have a portgenerator
// _portGeneratorHWType the attribute key for the hardware type attribute, in the default installation this is 2
// _portGeneratorNumberOfPorts the attribute key for the number of ports attribute, in default installation this is 6
//

//
// It also depends on  a new table which contains all the autoport configurations
// (the plugin creates the table automatically).
//

//
// Set up the RackTables variables that create the tab
// The tab only shows up if there are no ports yet defined
//
// The functions referred to in the handlers and trigger are all in this php file
//-----------------------------------
//Version 1.2
//Revised by Jorge Sanchez
//04-2011
//------------------------------------
//Changes:
//------------------------------------
//****The php file has been changed to work with 0.18.7 and the 0.19.x**** 
//
//****Note: you need to place process.php into the wwwroot directory from 0.18.7 for local_portgenerator to work.****
//
//****Also, make sure to include port_generator in the local.php file****
//
//First: $result variable no longers equals function useSelectBlade but uses function usePreparedSelectBlade because of the changes since
//0.17.x files for rendering objects. Also with that, the FUNCTION included with useSelectBlade has been removed
//
//Second: The orientation of the table has been altered to have each dictionary entry be rendered
//in a cell instead of fifteen in one cell. This makes the table look more neat and easier to read
//
//Third: Information of how to use the port generator has been added such as an EXAMPLE, an EXPLANATION of the EXAMPLE, and a note to allow port generator to render
//to port after the link has been pressed. 
//
//Fourth: the autoport dialog box has been moved from below the table to the top of the table for easier accessibility
//
//Fifth: for some reason, the label would not update when the link was pressed so no labels would be present even if you added them to the Port Generator. To fix this, 
//at line 443, inside of the commitaddPort variable, needed to add $aPort['label'] to allow it to function correctly
//----------------------------------
//Working On
//----------------------------------
//One: the padding with whitespace for each needs to be incorperated so that each cell is the same size. This is due to the browser more than anything else.
//  
//Version 1.3
//Revised by Jorge Sanchez
//04-2011
//------------------------------------
//Changes:
//------------------------------------
//***No longer needs the process.php file to work. ***
//
//Version 1.4
//Revised by Marian Stetina, Viktor Daniel
//02-2012
//------------------------------------
//Changes:
//------------------------------------
//*** Added support for PortInnterInterface ***
//
//
//Version 1.5
//Revised by Marian Stetina
//09-2014
//------------------------------------
//Changes:
//------------------------------------
//*** Compatibility with RT v.0.20.8, because DB-Model was changed (RactTables Changelog: update: move port outer interfaces from the dictionary)
//*** Port listing with InnerInterface ID (Example: 10-1588	QSFP+ => empty QSFP+)
//*** some HTML Tags bugfixed
//
/*
Version 1.6
Revised by Denis Ovsienko 2020-11-22
Changes: see the git commit message.
*/

$tab['object']['portgenerator'] = 'Port generator';
$trigger['object']['portgenerator'] = 'localtrigger_PortGenerator';
$tabhandler['object']['portgenerator'] = 'localfunc_PortGenerator';
$ophandler['object']['portgenerator']['updateportgenerator'] = 'updateconfig_PortGenerator';
$ophandler['object']['ports']['addports'] = 'localexecute_PortGenerator';

//
// Check whether the variables are set or otherwise set the default values.
//
global $noPortGenerator;
if (!isset($noPortGenerator)) {
  $noPortGenerator = array();
}
global $tablePortGenerator;
if (!isset($tablePortGenerator)) {
  $tablePortGenerator = "AutoPort";
}
if (!defined("_portGeneratorHWType")) {
  define("_portGeneratorHWType",2);
}
if (!defined("_portGeneratorNumberOfPorts")) {
  define("_portGeneratorNumberOfPorts",6);
}
//
// Check whether the table exists. If not, create it
//
function checkForTable ()
{
  global $tablePortGenerator;
  $result = usePreparedSelectBlade ("SHOW TABLES LIKE '{$tablePortGenerator}'");
  if ($result==NULL) { print_r($dbxlink->errorInfo()); die(); }
  if (!($row = $result->fetch (PDO::FETCH_NUM))) {
    $q = "CREATE TABLE IF NOT EXISTS `{$tablePortGenerator}` (
    `dict_key` int(10) unsigned NOT NULL auto_increment,
    `autoportconfig` text NOT NULL,
    UNIQUE KEY `dict_key` (`dict_key`)
    ) ENGINE=MyISAM  DEFAULT CHARSET=utf8;";
    $result = usePreparedSelectBlade ($q);
    if ($result==NULL) { print_r($dbxlink->errorInfo()); die(); }
  }
}

//
// function to check whether a tab Port generator should be shown
// If there are already ports configured or the object type id is in $noPortGenerator
// (which is an array of object type ids) then do not show the tab
//
function localtrigger_PortGenerator()
{
  global $noPortGenerator;
  $object = spotEntity ('object', getBypassValue());
  if ($object['nports'] == 0 && ! in_array ($object['objtype_id'], $noPortGenerator))
    return 'std';
  return '';
}

//
// This function checks whether there is a configuration available for the selected object type id
// (and if necessary for the hardware type id which is found as attribute id _portGeneratorHWType
function localverify_PortGenerator($object) {
  global $tablePortGenerator, $errorText, $lookFor, $portList, $genText, $valueConfiguration, $searchIt;
  checkForTable();
  $foundError = true;
  $record = getObjectPortsAndLinks ($object['id']);
  $object['objtype_name'] = decodeObjectType ($object['objtype_id']);
  //
  // Make sure that there are no ports configured
  //
  if (count($record)==0) {
    //
    // Check whether the object type for the selected object has an attribute hardware type
    // If it does, use the hardware type for configuration. Otherwise use the generic object type
    //
    $lookFor = "Hardware type";
    $q = "SELECT * FROM AttributeMap WHERE attr_id="._portGeneratorHWType." AND objtype_id={$object['objtype_id']} ";
    $result = usePreparedSelectBlade ($q);
    if ($result==NULL) { print_r($dbxlink->errorInfo()); die(); }
    if ($row = $result->fetch (PDO::FETCH_NUM)) {
      //
      // There is a hardware type available for this object type. Check whether it is set
      // If it is, search for the specific port configuration for that hardware type
      // If it is not set, use the generic port configuration for the object type
      //
      $q = "SELECT uint_value, dict_value FROM AttributeValue, Dictionary ";
      $q .= "WHERE attr_id="._portGeneratorHWType." AND object_id={$object['id']} AND dict_key=uint_value ";
      $result = usePreparedSelectBlade ($q);
      if ($result==NULL) { print_r($dbxlink->errorInfo()); die(); }
      if ($row = $result->fetch (PDO::FETCH_NUM)) {
        $searchIt = $row[0];
        $searchText = "OK (based on hardware type)";
        $searchType = 3;
        $genText = "{$object['objtype_name']}: {$row[1]}";
        $genText = str_replace(array("%GPASS%")," ",$genText);
      } else {
        $searchIt = $object['objtype_id'];
        $searchText = "Based on object type, hardware type not set";
        $searchType = 2;
        $genText = "<b>GENERIC</b> {$object['objtype_name']}";
      }
    } else {
      $searchIt = $object['objtype_id'];
      $searchText = "OK (based on object type)";
      $searchType = 1;
      $genText = "{$object['objtype_name']}";
    }
    $lookFor = "Autoport configuration";
    $q = "SELECT autoportconfig FROM {$tablePortGenerator} WHERE dict_key={$searchIt} ";
    $result = usePreparedSelectBlade ($q);
    if ($result==NULL) { print_r($dbxlink->errorInfo()); die(); }
    //
    // Check if there is an autoport configuration for the requested key
    //
    if ($valueConfiguration = $result->fetch (PDO::FETCH_NUM)) {
      $lookFor = "Configuration";
      $q = "SELECT uint_value FROM AttributeValue ";
      $q .= "WHERE attr_id="._portGeneratorNumberOfPorts." AND object_id={$_REQUEST['object_id']} ";
      //
      // Check for the value of the number of ports. If it is not found set it to 0
      //
      $result = usePreparedSelectBlade ($q);
      if ($result==NULL) { print_r($dbxlink->errorInfo()); die(); }
      if ($valueNumberOfPorts = $result->fetch (PDO::FETCH_NUM)) {
      } else {
        $valueNumberOfPorts[0] = 0;;
      }
      //
      // $portList will contain the list of ports to be generated
      //
      $portList = array();
      //
      // $portOrders array will be filled with individual port generation schemes
      // <start port #>|<port count, use %n for number of ports>|<port name, use %u for number>|<port type id>[|<port label, use %u for number>]
      // The configuration contains a semicolon separated list of the schemes
      //
      // An example of this would be:
      // 1|2|pwr%u|16;1|%n|eth%u|24|%u
      //
      $portOrders = explode(";",$valueConfiguration[0]);
      if (count($portOrders)>0) {
        $orderCnt = 0;
        foreach ($portOrders as $aPortOrder) {
          //
          // Split up each scheme and check for errors
          // If there are not errors, populate the $portList
          //
          $orderCnt++;
          $thisOrder = explode("|",$aPortOrder);
          if (count($thisOrder)==4 || count($thisOrder)==5) {
            if ($thisOrder[1]!="%n" || $valueNumberOfPorts[0]!=0) {
              if ($thisOrder[1]=="%n") {
                $thisOrder[1] = $valueNumberOfPorts[0];
              }
              if ($thisOrder[1]==1 || strpos($thisOrder[2],"%u")!==false) {
               if (preg_match ('/^([[:digit:]]+)-([[:digit:]]+)$/', $thisOrder[3], $matches)) 
                  $oif_id = $matches[2];
               else 
                  $oif_id = $thisOrder[3];
                $q = "SELECT oif_name FROM PortOuterInterface WHERE id='$oif_id'";
                $result = usePreparedSelectBlade ($q);
                if ($result==NULL) { print_r($dbxlink->errorInfo()); die(); }
                if ($row3 = $result->fetch (PDO::FETCH_NUM)) {
                  for ($i=1;$i<=$thisOrder[1];$i++) {
                    $ii = $thisOrder[0]+$i-1;
                    $name = str_replace("%u",$ii,$thisOrder[2]);
                    if (count($thisOrder)==5) {
                      $label = str_replace("%u",$ii,$thisOrder[4]);
                    } else {
                      $label = "";
                    }
                    $portList[] = array("name"=>$name,"port_id"=>$thisOrder[3],"port_name"=>$row3[0],"label"=>$label);
                  }
                } else {
                  $errorText = "Port type {$thisOrder[3]} is not found or not a port type.";
                }
              } else {
                $errorText = "Config part {$orderCnt} wants more than 1 port without using %u parameter.";
              }
            } else {
              $errorText = "Config part {$orderCnt} refers to <i>HW Number of Ports</i> but that is not defined or 0.";
            }
          } else {
            $errorText = "Config part {$orderCnt} does not have 4 parts separated by a |";
          }
        }
        if (!isset($errorText)) {
          $foundError = false;
        }
      } else {
        $errorText = "Autoport configuration for this dictionary key ({$searchIt}) is empty.";
      }
    } else {
      $errorText = "Autoport configuration for this dictionary key ({$searchIt}) not found.";
    }
  } else {
    $errorText = "Port generator only works if no ports have been configured yet.";
  }
  return !$foundError;
}

function localfunc_PortGenerator()
{
  global $errorText, $lookFor, $portList, $genText, $valueConfiguration, $searchIt;
	assertUIntArg ('object_id', __FUNCTION__);
	$object = spotEntity ('object', $_REQUEST['object_id']);
  startPortlet("Port generator");
  print "<center><br>";
  if (!localverify_PortGenerator($object)) {
    //
    // Autoport configuration did not work. Show this and show the error
    //
    print "{$lookFor} :&nbsp; &nbsp; &nbsp;Error\n";
    if (isset($errorText)) {
      print $errorText;
    }
  } else {
    //
    // Show the list of ports that will be generated and provide a link to actually do so
    //
    print "<a href='".makeHrefProcess(array('op'=>'addports','page'=>'object','object_id'=>$object['id'],'tab'=>'ports')).
      "'>Generate the ports for <b>{$object['name']}</b> as listed below NOW</a><br>\n";
    print $genText."<p>";
    print "<table cellspacing=0 cellpadding='5' align='center' class='widetable'>";
    print "<tr><th>Port name&nbsp;&nbsp;</th><th>Port label&nbsp;&nbsp;</th><th>Port type</th></tr>\n";
    foreach ($portList as $aPort) {
      print "<tr><td>{$aPort['name']}</td><td>{$aPort['label']}</td><td>{$aPort['port_name']}</td></tr>\n";
    }
    print "</table>";
  }
  print "<br></center>";
  finishPortlet();
  //
  // Check whether the user is allowed to do an update of the port configurator
  //
  // if you do not want this, make sure you add a
  //   deny {$op_updateportgenerator}
  // for the apprioriate groups (or any allow first and deny for the rest
  //
  if (permitted('object','portgenerator',null,array(array ('tag' => '$op_updateportgenerator')))) {
    startPortlet("Update autoport configuration");
    //
    // Description of the config rules
    //
    print "<center>";
    print $genText."<p>\n";
    print "<b>USAGE</b><br><br>";
    print "&lt;list1&gt;;&lt;list2&gt;;.... where &lt;listx&gt; is<br>";
    print "&lt;start port #&gt;|&lt;port count, use %n for number of ports&gt;|";
    print "&lt;port name, use %u for number&gt;|[&lt;port innerinterface id&gt;-]&lt;port type id&gt;[|&lt;port label, use %u for number&gt;]<br><br>";
    print "<b>EXAMPLE (for Force10 S4810)</b><br><br>0|48|%u|9-1084|TenGigabitEthernet0/%u;48|4|%u|10-1588|FortyGigE0/48;52|4|%u|10-1588|FortyGigE0/52;56|4|%u|10-1588|FortyGigE0/56;60|4|%u|10-1588|FortyGigE0/60;1|1|RS-232|29|Serial port;1|1|Ethernet|24|ManagementEthernet0/%u;1|2|AC%u|16<br><br>";
    print "<b>EXPLANATION (for AC-in ports)</b><br><br> <b>1</b> = starting number,
    <b>2</b> = number of generated ports,  
    <b>AC%u</b> = will begin with <b>starting number</b> and create up to the <b>number of generated ports</b>, 
    <b>16</b> = the value displayed on the chart below <b>in bold</b> (Notice: Default innerinterface ID is 1 /hardwired/, so the real value is <b>1-16</b>)<br><br>"; //explains example
    print "<b>PLEASE NOTE</b><br><br> If you do not add the port that is selected <b>(dictionary value)</b> to the default list in the <b>Ports</b> Tab,  
    you will get a <b>foreign key violation</b> error. 
    You must go to the <b>Configuration</b> area on the main page, go to <b>Enable port types</b>
    and hit the <b>Edit</b> tab to place the selected port in either <b>GBIC</b>, <b>hardwired</b>, or any other configuration 
    on the page so that the error is not received.<br><br><br>"; //Very important to have a successful implementation of port generator 
    //
    // The form that can update the configuration
    // On top of the table of ports avialabe instead of beneath it
	//
    printOpFormIntro ('updateportgenerator', array ('yId' => $searchIt));
    print "<b>Autoport Configuration:</b><br><br><input type='text' style='width:100%; font-size: 16px' name='yConfig' value='";
    if ($valueConfiguration) {
      print $valueConfiguration[0];
    }
    print "'><br><br>\n";
    print "<input type='submit' name='autoportconfig' value='";
    if ($valueConfiguration) {
      print "Update";
    } else {
      print "Create";
    }
    print "'>\n";
    print "</form>\n";
    print "</center><br>";
     
    print "<table border='2' rules=all>\n<tr class=row_odd>";
    $isfirst = true;
    $i = 0;
    //
    // List all available port types with their dictionary key
    //
    $q = "SELECT IF(iif_id,CONCAT(i.id,'-',o.id),o.id) as ID , IF(iif_id,CONCAT(i.iif_name,' => ',o.oif_name),o.oif_name) as name FROM PortOuterInterface o left join PortInterfaceCompat c on o.id = c.oif_id left join PortInnerInterface i on c.iif_id = i.id order by i.id,o.id";
    $result = usePreparedSelectBlade ($q);//Changed for new configeration in versions after 0.17.x
    if ($result==NULL) { print_r($dbxlink->errorInfo()); die(); }
    while ($row4 = $result->fetch (PDO::FETCH_NUM)) {
      if (!$isfirst && $i%10==0) {
         print "</td></tr>\n<tr class=".( ($i%4==0) ? "row_odd" : "row_even" ).">";
      } else {
      $isfirst = false;
      }
      print "<td><b>{$row4[0]}</b><br>";//separated values to make them easier to read
      print "{$row4[1]}\n</td>"; 
      $i++;
    }
    print "</td>\n";
    print "</tr></table>\n";
   
    finishPortlet();
  }
}

//
// The actual port generator
//
function localexecute_PortGenerator()
{
  global $errorText, $portList;
  $linkok = localtrigger_PortGenerator();
  if ($linkok) {
    assertUIntArg ('object_id', __FUNCTION__);
    $object = spotEntity ('object', $_REQUEST['object_id']);
    if (localverify_PortGenerator($object)) {
      $cnt = 0;
      foreach ($portList as $aPort) {
        commitAddPort($_REQUEST['object_id'],$aPort['name'],$aPort['port_id'],$aPort['label'],"");
        $cnt++;
      }
    }
  } else {
    $errorText = "Port generator not allowed";
  }
  if ($linkok) {
    showSuccess ("Successfully added {$cnt} ports");
  } else {
    showError ("Error adding the ports ({$errorText})");
  }
}

//
// Update the configuration scheme
//
function updateconfig_PortGenerator()
{
  global $tablePortGenerator;
  checkForTable();
  $q = "SELECT autoportconfig FROM {$tablePortGenerator} WHERE dict_key={$_REQUEST['yId']} ";
  $result = usePreparedSelectBlade ($q);
  if ($result==NULL) { print_r($dbxlink->errorInfo()); die(); }
  if ($row = $result->fetch (PDO::FETCH_NUM)) {
    $q = "UPDATE {$tablePortGenerator} SET autoportconfig='{$_REQUEST['yConfig']}' WHERE dict_key={$_REQUEST['yId']} ";
  } else {
    $q = "INSERT INTO {$tablePortGenerator} (dict_key,autoportconfig) VALUES ({$_REQUEST['yId']},'{$_REQUEST['yConfig']}') ";
  }
  $result = usePreparedSelectBlade ($q);
  if ($result==NULL) { print_r($dbxlink->errorInfo()); die(); }
  showSuccess ('Successfully updated auto port configuration');
}
