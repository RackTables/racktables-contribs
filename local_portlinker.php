<?php
//
// This extension for RackTables is used to mass connect ports (usefull for connecting back ends of patch panels)
//
// Version 2.0
//
// Created by Jeroen Benda
// 04-2010
// http://racktablescode.j-tools.net
//
// Please do not remove the credentials if you alter this file.
//

/*
Port linker allows you to connect the back ends of a patch panel to each other. That means you have created 2 ports per port on your
patch panel. One for the actual port and one for the cable coming out at the back.
The port type of the cable coming out at the back has to be in the array $portLinkerPortTypes to work (see below). So what needs to be done is
the setup of these backend cable types. So far I came up with 3 different cable types that I use
- utpLink
- fiberLinkSingleMode
- fiberLinkMultiMode
There is no real need for the difference between the last 2 but from the network team wanted this difference since the backend is different

After you've done that you need to set up a naming convention for your patch panels (if you have not already done so). With any object in
RackTables, the common name has to be unique. This extension uses the common name to identify which panels have to be connected so it cannot
be left blank. For example our naming convention is <rackname>-<rownumber where panel is mounted>.

You can use the Port Generator extension (can be found at racktablescode.j-tools.net) to generate the ports for a patch panel, both front and 
back. Afterwards you can connect the end cables with this Port Linker.

To tell this extension which ends to connect you use the label field. If none of the link ports have been connected yet and there are link 
ports defined, the port linker tab will show up.

Different checks are done before the link to connect the ports is actually shown. You have to make sure that the number of ports match,
the types match etc.

So now for some examples:
Let's say you have panel S1-42 with 24 ports and a panel N1-37 with 24 ports that need to be connected. You should create the ports on both
panels (or generate them) so you have a front port and a cable port per port on your patch panel:
S1-42 eth1
S1-42 eth1link
S1-42 eth2
S1-42 eth2link
....
N1-37 eth1
N1-37 eth1link
N2-37 eth2
N2-37 eth2link
.....
You than tell the port linker which panels to link. You do this by putting in the label field the destination of the patch panel. In this example
the label for S1-42 will be N1-37 AND the label for N1-37 will be S1-42.
Common name     Label
S1-42           N1-37
N1-37           S1-42

If you select the tab port linker, you get an overview of the ports
that will be linked and a link to confirm that you actually want to connect them. If you connect on the link, they will be linked.

A little more difficult example:
Let's say you have a panel S1-42 with 24 ports and 2 panels, N1-37 and N2-37 both with 12 ports (a server rack patch panel going to 2 network
racks). The labels will be as followed:
Common name     Label
N1-37           S1-42
N2-37           S1-42
S1-42           N1-37 N2-37

Notice that the label for S1-42 has 2 names seperated by a space. This means that the ports of S1-42 will be split evenly. Because there are
2 destinations, the number of ports will be divided by 2. Half the number of ports (12) will go to one and the other will go to other. In this
example, the FIRST half of ports will go to N1-37 and the SECOND half of ports will go to N2-37
For the network racks all ports will go to S1-42 so no need for anything else in the label.
When you click on port linker now, you will get a list that looks like this:

S1-42 eth1link       N1-37 eth1link
S1-42 eth2link       N1-37 eth2link
...
S1-42 eth13link      N2-37 eth1link
...
S1-42 eth24link      N2-37 eth12link

Finally an even more complex (but normal) example:
You have 2 server racks S1 and S2 which both have a patch panel at the top. Half of the 24 ports go to one network N1 and the other go to another
network rack N2. In this example, the network racks also have 24 ports patch panels and on each row their receive patch panels from 2 server racks.
Let's say that in this case the panel in the network racks at row 37 connects to S1 and S2, the first 12 ports of the patch panel in each network
rack go to S1 and the second 12 to S2.
This will result in the following labels:
Common name     Label
S1-42           N1-37 N2-37
S2-42           N1-37 N2-37
N1-37           S1-42 S2-42
N2-37           S1-42 S2-42

The label is always written from the perspective of the patch panel. In this example the ports for S1 go to N1 and N2 in that order. The same is
true for the ports of S2 so the labels are the same. In the network racks you see the same thing from the other side. Even though the labels are
the same, this will not result in a connecting conflict because port linker looks from both ends to make sure the cables are connected properly.
If we just look at ports 1 and 13 in this example you get:

S1-42 eth1link      N1-37 eth1link
S1-42 eth13link     N2-37 eth1link
S2-42 eth1link      N1-37 eth13link
S2-42 eth13link     N2-37 eth13link

*/

//
// This php file depends on 2 variables that have to be set before this file is included:
//
// $portLinkerPortTypes contains port types that can be connected (these are the types that represent the cables at the back end 
//  of the patch panel)
// $portLinkerObjectTypes contains object types that can be connected (this is usually the patch panel and maybe a self created ODF panel)
//

//
// Set up the RackTables variables that create the tab
// The tab only shows up if there are ports defined and no ports already linked
//
// The functions referred to in the handlers and trigger are all in this php file
//
$tab['object']['portlinker'] = 'Port linker';
$trigger['object']['portlinker'] = 'localtrigger_PortLinker';
$tabhandler['object']['portlinker'] = 'localfunc_PortLinker';
$ophandler['object']['default']['linknow'] = 'execute_PortLinker';

//
// Check whether the variables are set or otherwise set the default values.
// This is for this extension rather useless because you need extra port types that represent the cables at the back of the patch panel
//------------------------
//Version 1.2
//Revised by Jorge Sanchez
//04-2011
//------------------------
//Changes
//------------------------
//Only one change has been added to this file. The $result variable is no longer equal to useSelectBlade($q,__FUNCTION__); but to usePreparedSelectBlade($q); 
//This is due to the changes since 0.17.x to 0.18.7 to the rendering of objects
//

global $portLinkerPortTypes;
if (!isset($portLinkerPortTypes)) {
  $portLinkerPortTypes = array(50015);
}


global $portLinkerObjectTypes;
if (!isset($portLinkerObjectTypes)) {
  $portLinkerObjectTypes = array(9);
}


function determinePortSplit ($object_label,$portCount) {
  $labels = explode(" ",$object_label);
  $splits = array();
  if (count($labels)==0) {
  } else if (count($labels)==1) {
    $splits[$labels[0]] = array();
    $splits[$labels[0]]['start'] = 1;
    $splits[$labels[0]]['count'] = $portCount;
    $splits[$labels[0]]['end'] = $portCount;
  } else {
    $sum = 0;
    foreach($labels as $aLabel) {
      $splits[$aLabel] = array();
      $splits[$aLabel]['start'] = $sum+1;
      $splits[$aLabel]['count'] = floor($portCount/count($labels));
      $splits[$aLabel]['end'] = $sum+floor($portCount/count($labels));
      $sum += $splits[$aLabel]['count'];
    }
    if ($sum<$portCount) {
      $temp = array_keys($splits);
      end($temp);
      $splits[current($temp)]['count'] += $portCount-$sum;
      $splits[current($temp)]['end'] = $portCount;
    }
  }
  return $splits;
}

//
// This function returns the number of ports that have a type in the array $portLinkerPortTypes
//
function countPorts ($object_id) {
  global $portLinkerPortTypes;
  $record = getObjectPortsAndLinks($object_id);
  $count = 0;
  foreach ($record as $aPort) {
    if (in_array($aPort['oif_id'],$portLinkerPortTypes)) {
      $count++;
    }
  }
  return $count;
}

//
// This function returns the integer found in a string. It keeps the first digits found.
// Example: eth3link => 3
//          fbr15link => 15
//          fbr1link2 => 1
//          12a => 12
//
function str2int ($xIn) {
  $theNumber = "";
  $allowedToAdd = true;
  $inNumber = false;
  $i = 0;
  while ($i<strlen($xIn)) {
    if ($allowedToAdd) {
      if (is_numeric($xIn{$i})) {
        $inNumber = true;
        $theNumber .= $xIn{$i};
      } else {
        if ($inNumber) {
          $inNumber = false;
          $allowedToAdd = false;
        }
      }
    }
    $i++;
  }
  return intval($theNumber);
}

//
// This function returns an array of all available ports of a type that is listed in the array $portLinkerPortTypes
// of an object where the port number (as defined by parsing the name through str2int) is between $portStart and ($portStart+$portCount)
//
function getPorts ($object_id, $portCount, $portStart) {
  global $portLinkerPortTypes;
  $record = getObjectPortsAndLinks($object_id);
  $foundPorts = array();
  foreach ($record as $aPort) {
    if (in_array($aPort['oif_id'],$portLinkerPortTypes) && strlen($aPort['reservation_comment'])==0 && $aPort['remote_id']==0) {
      $num = str2int($aPort['name']);
      if ($num>=$portStart && $num<$portStart+$portCount) {
        $foundPorts[$num] = array();
        $foundPorts[$num]['id'] = $aPort['id'];
        $foundPorts[$num]['name'] = $aPort['name'];
      }
    }
  }
  ksort($foundPorts);
  return $foundPorts;
}

//
// This is the function that actually executes the linking of the ports
// It uses determine_PortLinker function to see if it is allowed to link
//
$msgcode['execute_PortLinker']['OK'] = 0;
$msgcode['execute_PortLinker']['ERR'] = 100;
function execute_PortLinker () {
  global $localSplit, $remoteSplit;
  $errorText = determine_PortLinker();
  $object = spotEntity ('object', $_REQUEST['object_id']);
  if (strlen($errorText)==0) {
    $count = 0;
    // loop through all remote objects as defined
    foreach ($localSplit as $aKey=>$aValue) {
      $numAdd = $remoteSplit[$aKey][$object['name']]['start']-$aValue['start'];
      foreach ($aValue['ports'] as $aPortNum=>$aPort) {
        linkPorts($aPort['id'],$aValue['remote_ports'][$aPortNum+$numAdd]['id']);
        $count++;
      }
    }
  }
  if (strlen($errorText)==0) {
    return buildRedirectURL (__FUNCTION__, 'OK', array("{$count} ports successfully linked"));
  } else {
    return buildRedirectURL (__FUNCTION__, 'ERR', array($errorText));
  }
}

function determine_PortLinker() {
  global $localSplit, $remoteSplit, $portLinkerObjectTypes;
  $errorText = "";
  assertUIntArg ('object_id', __FUNCTION__);
  $object = spotEntity ('object', $_REQUEST['object_id']);
  $linkok = localpretrigger_PortLinker();
  if ($linkok==2) {
    if (in_array($object['objtype_id'],$portLinkerObjectTypes)) {
      $localPortCount = countPorts($object['id']);
      $remotePortCount = array();
      $remoteObject = array();
      $remoteSplit = array();
      $localSplit = determinePortSplit($object['label'],$localPortCount);
      $current = 1;
      foreach ($localSplit as $aKey=>$aValue) {
        if (strlen($errorText)==0) {
          $q = "SELECT id FROM RackObject WHERE name='{$aKey}' ";
          $result = usePreparedSelectBlade ($q);
          if ($result==NULL) { print_r($dbxlink->errorInfo()); die(); }
          if ($row = $result->fetch (PDO::FETCH_NUM)) {
            $remotePortCount[$aKey] = countPorts($row[0]);
            $remoteObject[$aKey] = spotEntity('object',$row[0]);
            $remoteSplit[$aKey] = determinePortSplit($remoteObject[$aKey]['label'],$remotePortCount[$aKey]);
          } else {
            $errorText = "Could not find object <b>{$aKey}</b>";
          }
        }
      }
      if (strlen($errorText)==0) {
        foreach ($localSplit as $aKey=>$aValue) {
          if (strlen($errorText)==0 && !isset($remoteSplit[$aKey][$object['name']]['count'])) {
            $errorText = "Object <b>{$aKey}</b> does not list this object in the label field as a remote panel";
          }
          if (strlen($errorText)==0 && $remoteSplit[$aKey][$object['name']]['count']!=$aValue['count']) {
            $errorText = "Port count does not match for object <b>{$aKey}</b>";
          }
        }
        if (strlen($errorText)==0) {
          foreach ($localSplit as $aKey=>$aValue) {
            if (strlen($errorText)==0) {
              $localSplit[$aKey]['ports'] = getPorts($object['id'],$aValue['count'],$aValue['start']);
              if (count($localSplit[$aKey]['ports'])!=$aValue['count']) {
                $errorText = "Not all ports available on this object";
              }
            }
            if (strlen($errorText)==0) {
              $localSplit[$aKey]['remote_ports'] = getPorts($remoteObject[$aKey]['id'],$remoteSplit[$aKey][$object['name']]['count'],
                $remoteSplit[$aKey][$object['name']]['start']);
              if (count($localSplit[$aKey]['ports'])!=$remoteSplit[$aKey][$object['name']]['count']) {
                $errorText = "Not all ports available on this object";
              }
            }
          }
        }
      }
    } else {
      $errorText = "Object type should be PatchPanel or ODFPanel";
    }
  } else {
    switch ($linkok) {
      case "-1" : $errorText = "There are no ports configured yet, so nothing to link to."; break;
      case "0" : $errorText = "Some link ports are already linked to another port."; break;
      case "1" : $errorText = "No ports found that end in link."; break;
      default : $errorText = "Unknown error.";
    }
  }
  return $errorText;
}

function localfunc_PortLinker()
{
  global $localSplit, $remoteSplit;
  startPortlet("Port linker");
  print "<center>";
  $errorText = determine_PortLinker();
  $object = spotEntity ('object', $_REQUEST['object_id']);
  if (strlen($errorText)>0) {
    print "Trying to link this object to : {$object['label']}<br><br>\n";
    print $errorText;
  } else {
    echo "<a href='".
    makeHrefProcess(array('op'=>'linknow','page'=>'object','tab'=>'default','object_id'=>$_REQUEST['object_id'])).
    "'>Connect the following ports now:</a><p>\n";

    print "<table cellspacing=0 cellpadding='5' align='center' class='widetable'>";
    print "<tr><th>Object name</th><th>Port name</th><th>&nbsp;&nbsp;&nbsp;</th><th>Remote object name</th><th>Remote port name</th></tr>\n";
    foreach ($localSplit as $aKey=>$aValue) {
      $numAdd = $remoteSplit[$aKey][$object['name']]['start']-$aValue['start'];
      foreach ($aValue['ports'] as $aPortNum=>$aPort) {
        print "<tr><td>{$object['name']}</td><td>{$aPort['name']}</td><td>&nbsp;</td><td>{$aKey}</td><td>";
        print $aValue['remote_ports'][$aPortNum+$numAdd]['name']."</td></tr>\n";
      }
    }
    print "</table>";
  }
  print "</center>";
  finishPortlet();
}

function localpretrigger_PortLinker() {
  global $portLinkerPortTypes;
  $record = getObjectPortsAndLinks ($_REQUEST['object_id']);
  if (count($record)>0) {
    $linkok = 1;
    foreach ($record as $aPort) {
      if (in_array($aPort['oif_id'],$portLinkerPortTypes)) {
        if ($linkok==1) {
          $linkok = 2;
        }
        if (strlen($aPort['remote_id'])>0) {
          $linkok = 0;
        }
      }
    }
  } else {
    $linkok = -1;
  }
  return $linkok;
}


function localtrigger_PortLinker()
{
  global $portLinkerObjectTypes;
  assertUIntArg ('object_id', __FUNCTION__);
  $object = spotEntity ('object', $_REQUEST['object_id']);
  if (in_array($object['objtype_id'],$portLinkerObjectTypes)) {
    $linkok = localpretrigger_PortLinker();
  } else {
    $linkok = 0;
  }
  if ($linkok==2)
    return 1;
  else
  {
    return '';
  }
}

function test_debug()
{
global $portLinkerObjectTypes;
print $portLinkerObjectTypes;
}
?>
