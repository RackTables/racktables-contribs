<?php
//
// Local file for a link with Nagios & LiveStatus
// Copyright 2010 Jeroen Benda <Jeroen.Benda@tomtom.com>
//
// Variables needed:
// $livestatusServer = The server where LiveStatus is running (the Nagios server)
// $livestatusServerPort = Port where LiveStatus listens on
// $nagiosURL = The full url to link to a server in Nagios (variable replacement %x% can be used)
// $noNagiosCheck = array of object type id codes that do not require a Nagios check (i.e. patch panel and such)
//

$tab['object']['Link2Nagios'] = 'Nagios';
$trigger['object']['Link2Nagios'] = 'localtrigger_Link2Nagios';
$tabhandler['object']['Link2Nagios'] = 'localfunc_Link2Nagios';

$trigger['renderRack']['state'] = 'formtrigger_NagiosRack';

//
// Constants used for LiveStatus status results
//
define("_statusNagiosOK",0); // green
define("_statusNagiosWarning",1); // yellow
define("_statusNagiosCritical",2); // red
define("_statusNagiosUnknown",3); // orange

define("_statusNagiosOKText","ok");
define("_statusNagiosWarningText","warning");
define("_statusNagiosCriticalText","critical");
define("_statusNagiosUnknownText","unknown");

//
// This function is called when a tab is clicked
// It forwards to Nagios for that server
//
function localfunc_Link2Nagios()
{
  global $nagiosURL;
	assertUIntArg('object_id',__FUNCTION__);
	$object = spotEntity('object',$_REQUEST['object_id']);
  forwardToURL(str_replace(array("%objectname%"),array($object['name']),$nagiosURL));
}

//
// This function determines whether a host exists in Nagios through a LiveStatus query
// If it does, the color returned is the color of the status of that object in Nagios
//
function localtrigger_Link2Nagios()
{
  global $noNagiosCheck;
	assertUIntArg('object_id',__FUNCTION__);
	$object = spotEntity('object',$_REQUEST['object_id']);
  if (!in_array($object['objtype_id'],$noNagiosCheck)) {
    $trigger = getNagiosState($object['name']);
    return $trigger;
	} else {
		return '';
	}
}

//
// The function the sends a LiveStatus query to the server and returns the result given
//
function getLiveStatusQuery ($xQuery) {
  global $livestatusServer, $livestatusServerPort, $allLiveStatusResults;
  $allLiveStatusResults = array();
  $theCommand = "echo -e \"{$xQuery}\" | netcat {$livestatusServer} {$livestatusServerPort}";
  $temp = exec($theCommand,$allLiveStatusResults,$a1);
  if (strlen($temp)>0 && $temp[0]=="-") {
    $temp = "";
  }
  return $temp;
}

//
// This function uses LiveStatus to determine whether a host exists and Nagios and if so
// returns the status as text
//
function getNagiosState ($xName) {
  $nagiosState = "";
  $temp1 = getLiveStatusQuery("GET hosts\nColumns: state\nFilter: name = {$xName}");
  if (strlen($temp1)>0) {
    if ($temp1>0) {
      $nagiosState = _statusNagiosUnknownText;
    } else {
      $temp = getLiveStatusQuery(
        "GET services\nStats: state = 0\nStats: state = 1\nStats: state = 2\nStats: state = 3\nFilter: host_name = {$xName}");
      $temp2 = explode(";",$temp);
      if (count($temp2)<3) {
        $nagiosState = _statusNagiosUnknownText;
      } else {
        if ($temp2[_statusNagiosCritical]>0) {
          $nagiosState = _statusNagiosCriticalText;
        } else if ($temp2[_statusNagiosWarning]>0) {
          $nagiosState = _statusNagiosWarningText;
        } else if ($temp2[_statusNagiosUnknown]>0) {
          $nagiosState = _statusNagiosUnknownText;
        } else {
          $nagiosState = _statusNagiosOKText;
        }
      }
    }
  }
  return $nagiosState;
}

//
// This function determines the color of the item in the rack layout
// based on the state of the object in Nagios
//
function formtrigger_NagiosRack ($locItem) {
  global $noNagiosCheck;
  $state = $locItem['state'];
  if ($state=="T") {
    $temp = spotEntity('object',$locItem['object_id']);
    if (strlen($temp['name'])>0 && !in_array($temp['objtype_id'],$noNagiosCheck)) {
      $nagiosstate = getNagiosState($temp['name']);
      if (strlen($nagiosstate)!=0) {
        $state = $nagiosstate;
      }
    }
  }
  return $state;
}
