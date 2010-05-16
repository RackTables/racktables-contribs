<?php
//
// Objectlog
// Version 0.4
//
// Created by Ernest Shaffer
//
// The purpose is to keep notes for future reference like case numbers
// History
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

$username = $_SERVER['PHP_AUTH_USER'];
$nextorder['odd'] = 'even';
$nextorder['even'] = 'odd';

$logentry = $_POST['logentry'];
$objectid = $_POST['objectid'];


//
// Check whether the table exists. If not, create it
// Function is called only display functions to cut down on queries
//
function tableCheck($tableName)
{
$result = NULL;
$result = useSelectBlade ("SHOW TABLES LIKE '{$tableName}'", __FUNCTION__);
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
  $result = useSelectBlade ($q, __FUNCTION__);
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
    $query = "DELETE FROM `Objectlog` WHERE `id` = ".$logid." LIMIT 1";
    $result = NULL;
    $result = useSelectBlade ($query, __FUNCTION__);
    $log = mergeLogs ($log, oneLiner (77, array ('log entry')));
    return buildWideRedirectURL ($log);
}

//
// The ophandler to insert new log
//
function addObjectlog ()
{
    global $logentry;
    global $objectid;
    global $username;
    $log = emptyLog();
    $datetime = date("Y-m-d H:i:s");
    $query= "INSERT INTO  `Objectlog` (`object_id`,`user`,`date`,`content`) VALUES(";
    $query .= "'$objectid',  '$username',  '$datetime',  '$logentry');";
    $result = NULL;
	$result = useSelectBlade ($query, __FUNCTION__);
    $log = mergeLogs ($log, oneLiner (80, array ('log entry')));
    return buildWideRedirectURL ($log);
}

//
// Display object level logs
//
function ObjectLogs ()
{

    tableCheck("Objectlog"); // check if table exists if not create
    global $nextorder;
    global $username;
    global $logentry;
    $object = $_REQUEST['object_id'];
    $query = "SELECT o.id as logid, r.name, o.content, o.date, o.user, r.id as object_id FROM Objectlog o Left JOIN RackObject r ON o.object_id = r.id WHERE r.id = $object ORDER BY o.date DESC";
	$result = NULL;
    $result = useSelectBlade ($query, __FUNCTION__);

    echo "<style type='text/css'>\n";
    echo "tr.has_problems {\n";
    echo "background-color: #ffa0a0;\n";
    echo "}\n";
    echo "</style>\n";

    startPortlet ('Add Object Log');
    echo "<table align=center border=0 cellpadding=5 cellspacing=0 align=center class=cooltable><tr valign=top>";
    echo "<th align=left>Enter new log</th>";
    echo "<form method=post name=addOjectlog action='process.php?page=object&tab=objectlog&op=addObjectlog&object_id=".$object."'>";
    echo "<tr>";
    echo "<input type=hidden value=$object name=objectid>";
    echo "<td align=left><textarea name=logentry rows=3 cols=40></textarea>&nbsp;&nbsp;<input type=submit name=got_very_fast_data value='Go!'></td></tr>";
    echo "</form>";
    echo "</table>";
    finishPortlet ();
    
    startPortlet ('Object Logs');
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
        echo "<td align=left><a href='".makeHref(array('page'=>'object', 'object_id'=>$row['object_id']))."'>${row['name']}</a></td>";
        echo "<td align=left>".nl2br($row['content'])."</td>";
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
    global $nextorder;
    global $username;
    global $logentry;
    global $objectid;
    $query = "SELECT o.id as logid, r.name, o.content, o.date, o.user, r.id as object_id FROM Objectlog o Left JOIN RackObject r ON o.object_id = r.id ORDER BY o.date DESC";
    $result = NULL;
    $result = useSelectBlade ($query, __FUNCTION__);
    echo "<style type='text/css'>\n";
    echo "tr.has_problems {\n";
    echo "background-color: #ffa0a0;\n";
    echo "}\n";
    echo "</style>\n";

    startPortlet('Add Object Log');
    echo "<table align=center border=0 cellpadding=5 cellspacing=0 align=center class=cooltable><tr valign=top>";
    echo "<th align=left>Name</th>";
    echo "<th align=left>Log</th>";
    echo "<form method=post name=addOjectlog action='process.php?page=depot&tab=objectlog&op=addObjectlog'>";
    echo "<tr><td valign=top align=left>";
    echo "<select name=objectid>";
    foreach ($serverSelect as $k => $v) { echo "<option value=$k>$v</option>"; }
    echo "</select>";
    echo "</td>";
    echo "<td align=left><textarea name=logentry rows=3 cols=40></textarea>&nbsp;&nbsp;<input type=submit name=got_very_fast_data value='Go!'></td></tr>";
    echo "</form>";
    echo "</table>";
    finishPortlet();

    startPortlet('Object Logs');
    echo "<table width=80% align=center border=0 cellpadding=5 cellspacing=0 align=center class=cooltable><tr valign=top>";

    echo "<th align=left>Name</th>";
    echo "<th align=left>Log</th>";
    echo "<th align=left>Date</th>";
    echo "<th align=left>User</th>";
    

    $order = 'odd';
    while ($row = $result->fetch (PDO::FETCH_ASSOC))
    {
        echo "<tr class=row_${order} valign=top>";
        echo "<td align=left><a href='".makeHref(array('page'=>'object', 'object_id'=>$row['object_id']))."'>${row['name']}</a></td>";
        echo "<td align=left>".nl2br($row['content'])."</td>";
        echo "<td align=left>".date("m/d/y g:i A",strtotime($row['date']))."</td>";
        echo "<td align=left>".$row['user']."</td>";
        echo "</tr>\n";
        $order = $nextorder[$order];
    }
    echo "</table>\n";
    finishPortlet();

}
?>
