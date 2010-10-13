<?php
//
// Objectlog
// Version 0.6
//
// Created by Ernest Shaffer
//
// The purpose is to keep notes for future reference like case numbers
// History
// Version 0.7:  Added if isset for $logentry and $objectid to stop warnings
// Version 0.6:  Updated message code to display better success/failure notices
//               Global Object logs tab name link goes directly to that object's logs tab
//               Added URL detection to enable clickable links in logs
//               Fixed bug double or single quotes now allowed in log entries
// Version 0.5:  Updated queries to be compatible with 0.17 & 0.18 Branches
// Version 0.4:  Bug fix removed blank line at end of file that caused Rack images to not display
// Version 0.4:  Fixed typo finshPortlet to finishPortlet
// Version 0.3:  Auto create Object table if not exist
// Version 0.3:  Ability to delete logs at the object level
// Version 0.2:  Add datetime stamped logs to objects with username
// Version 0.1:  Initial release
//
// Installation:
// 1)  Copy script to inc folder as objectlog.php
// 2)  Add include to inc/local.php: include("objectlog.php");
// Table should automatically be created if needed

/**
CREATE TABLE IF NOT EXISTS `Objectlog` (
  `id` int(10) NOT NULL auto_increment,
  `object_id` int(10) NOT NULL,
  `user` varchar(40) NOT NULL,
  `date` datetime NOT NULL,
  `content` text NOT NULL,
  PRIMARY KEY  (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=0 ;
**/

// Depot Tab for all objects
$tab['depot']['objectlog'] = 'Object Logs';
$tabhandler['depot']['objectlog'] = 'allObjectLogs';
$ophandler['depot']['objectlog']['addObjectlog'] = 'addObjectlog';

//Object Tab for only one object
$tab['object']['objectlog'] = 'Object Logs';
$tabhandler['object']['objectlog'] = 'ObjectLogs';
$ophandler['object']['objectlog']['addObjectlog'] = 'addObjectlog';
$ophandler['object']['objectlog']['deleteLog'] = 'deleteLog';

//
// Set variables
//

$Version = "0.7";
$username = $_SERVER['PHP_AUTH_USER'];
$nextorder['odd'] = 'even';
$nextorder['even'] = 'odd';

if (isset($_POST['logentry'])) $logentry = $_POST['logentry'];
if (isset($_POST['objectid'])) $objectid = $_POST['objectid'];

//
// Check whether the table exists. If not, create it
// Function is called only display functions to cut down on queries
//
function tableCheck($tableName)
{
global $dbxlink;
$result = NULL;
$result = $dbxlink->query("SHOW TABLES LIKE '{$tableName}'");
if ($result==NULL) { print_r($dbxlink->errorInfo()); die(); }
if (!($row = $result->fetch (PDO::FETCH_NUM))) {
  $q = "CREATE TABLE IF NOT EXISTS `{$tableName}` (
  	`id` int(10) NOT NULL auto_increment,
  	`object_id` int(10) NOT NULL,
  	`user` varchar(40) NOT NULL,
  	`date` datetime NOT NULL,
  	`content` text NOT NULL,
  	PRIMARY KEY  (`id`)
  	) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=0 ;";
  $result = $dbxlink->query($q);
  if ($result==NULL) { print_r($dbxlink->errorInfo()); die(); }
}
}

// The ophandler to delete a log entry
$msgcode['deleteLog']['OK'] = 77;
$msgcode['deleteLog']['ERR'] = 100;

//
// The ophandler to delete log entry
//
function deleteLog ()
{
    global $logentry;
    global $objectid;
    global $username;
    $logid = $_GET['logid'];
    $log = emptyLog();
    $result = usePreparedExecuteBlade("DELETE FROM `Objectlog` WHERE `id` = ".$logid." LIMIT 1");
    $log = mergeLogs ($log, oneLiner (77, array ('log entry')));
    return buildWideRedirectURL ($log);
}

//
// The ophandler to insert new log
//
$msgcode['addObjectlog']['OK'] = 0;
$msgcode['addObjectlog']['ERR'] = 100;
function addObjectlog ()
{
    global $logentry;
    global $objectid;
    global $username;
    $oi = spotEntity ('object', $objectid);
    $log = emptyLog();
    $datetime = date("Y-m-d H:i:s");
	$result = usePreparedExecuteBlade('INSERT INTO Objectlog SET object_id=?, user=?, date=?, content=?', array($objectid, $username, $datetime, $logentry));
	$ob_url = makeHref (array ('page' => 'object', 'tab' => 'objectlog', 'object_id' => $objectid));
    return buildRedirectURL (__FUNCTION__, 'OK', array ("Log entry for <a href=" . ${ob_url} . ">${oi['dname']}</a> added by $username"));

}

//
// Display object level logs
//
function ObjectLogs ()
{

    tableCheck("Objectlog"); // check if table exists if not create
  	global $dbxlink;
    global $nextorder;
    global $username;
    global $logentry;
    global $Version;
    $object = $_REQUEST['object_id'];
    $query = "SELECT o.id as logid, r.name, o.content, o.date, o.user, r.id as object_id FROM Objectlog o Left JOIN RackObject r ON o.object_id = r.id WHERE r.id = $object ORDER BY o.date DESC";
	$result = NULL;
    $result = $dbxlink->query($query);
    $count = $result->rowCount();
    echo "<style type='text/css'>\n";
    echo "tr.has_problems {\n";
    echo "background-color: #ffa0a0;\n";
    echo "}\n";
    echo "</style>\n";

    startPortlet ('Add Object Log');
    echo "<center><a href=?page=depot&tab=objectlog>All Logs</a>";
    echo "<table with=80% align=center border=0 cellpadding=5 cellspacing=0 align=center class=cooltable><tr valign=top>";
    echo "<th align=left>Enter new log</th>";
    echo "<form method=post name=addOjectlog action='process.php?page=object&tab=objectlog&op=addObjectlog&object_id=".$object."'>";
    echo "<tr>";
    echo "<input type=hidden value=$object name=objectid>";
    echo "<td align=left><table border=0 cellpadding=0 cellspacing=0><tr><td colspan=2><textarea name=logentry rows=3 cols=80></textarea></td><tr><td align=left><font size=1em color=gray>version ${Version}</font></td><td align=right><input type=submit name=got_very_fast_data value='Post'></td></tr></table></td></tr>";
    echo "</form>";
    echo "</table>";
    finishPortlet ();
    
    startPortlet ('Object Logs (' . $count . ')');
    echo "<table width=80% align=center border=0 cellpadding=5 cellspacing=0 align=center class=cooltable><tr valign=top>";

    echo "<th align=left>Name</th>";
    echo "<th align=left>Log</th>";
    echo "<th align=left>Date</th>";
    echo "<th align=left>User</th>";
    echo "<th>&nbsp;</th>";

    $order = 'odd';
    while ($row = $result->fetch (PDO::FETCH_ASSOC))
    {
        echo "<tr class=row_${order} valign=top>";
        #echo "<td align=left> id: ".$row['logid']." <a href='".makeHref(array('page'=>'object', 'object_id'=>$row['object_id']))."'>${row['name']}</a></td>";
        echo "<td align=left><a href='".makeHref(array('page'=>'object', 'tab'=>'objectlog', 'object_id'=>$row['object_id']))."'>${row['name']}</a></td>";
        echo "<td align=left>".nl2br(string_insert_hrefs($row['content']))."</td>";
        echo "<td align=left>".date("m/d/y g:i A",strtotime($row['date']))."</td>";
        echo "<td align=left>".$row['user']."</td>";
		echo "<td align=left><a href=\"".makeHrefProcess(array('op'=>'deleteLog', 'logid'=>$row['logid'], 'object_id'=>$row['object_id']))."\">";
			printImageHREF ('destroy', 'Delete log entry');
		echo "</a></td>";
        echo "</tr>\n";
        $order = $nextorder[$order];
    }
    echo "</table>\n";
    finishPortlet ();

}



//
// Display form and All log entries
//
function allObjectLogs ()
{
    tableCheck("Objectlog"); // check if table exists if not create
    $serverSelect = getNarrowObjectList();
	global $dbxlink;
    global $nextorder;
    global $username;
    global $logentry;
    global $objectid;
    global $Version;
    $query = "SELECT o.id as logid, r.name, o.content, o.date, o.user, r.id as object_id FROM Objectlog o Left JOIN RackObject r ON o.object_id = r.id ORDER BY o.date DESC";
    $result = $dbxlink->query($query);
    $count = $result->rowCount();
    echo "<style type='text/css'>\n";
    echo "tr.has_problems {\n";
    echo "background-color: #ffa0a0;\n";
    echo "}\n";
    echo "</style>\n";

    startPortlet('Add Object Log');
    echo "<table with=80% align=center border=0 cellpadding=5 cellspacing=0 align=center class=cooltable><tr valign=top>";
    echo "<form method=post name=addOjectlog action='process.php?page=depot&tab=objectlog&op=addObjectlog'>";
    echo "<th align=left>Name";
    echo "<select name=objectid>";
    foreach ($serverSelect as $k => $v) { echo "<option value=$k>$v</option>"; }
    echo "</select>";
    echo "</th>";
    echo "<tr><td align=left><table with=100% border=0 cellpadding=0 cellspacing=0><tr><td colspan=2><textarea name=logentry rows=3 cols=80></textarea></td></tr>";
    echo "<tr><td align=left><font size=1em color=gray>version ${Version}</font></td><td align=right><input type=submit name=got_very_fast_data value='Post'></td></tr></table></td></tr>";
    echo "</form>";
    echo "</table>";
    finishPortlet();

    //startPortlet ('Objects (' . count ($objects) . ')');
    startPortlet('Object Logs (' . $count . ')');
    echo "<table width=80% align=center border=0 cellpadding=5 cellspacing=0 align=center class=cooltable><tr valign=top>";

    echo "<th align=left>Name</th>";
    echo "<th align=left>Log</th>";
    echo "<th align=left>Date</th>";
    echo "<th align=left>User</th>";
    

    $order = 'odd';

    while ($row = $result->fetch (PDO::FETCH_ASSOC))
    {
        echo "<tr class=row_${order} valign=top>";
        echo "<td align=left><a href='".makeHref(array('page'=>'object', 'tab'=>'objectlog', 'object_id'=>$row['object_id']))."'>${row['name']}</a></td>";
        echo "<td align=left>".nl2br(string_insert_hrefs($row['content']))."</td>";
        echo "<td align=left>".date("m/d/y g:i A",strtotime($row['date']))."</td>";
        echo "<td align=left>".$row['user']."</td>";
        echo "</tr>\n";
        $order = $nextorder[$order];
    }
    echo "</table>\n";
    finishPortlet();

}
?>
