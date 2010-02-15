<?php
// Objectlog
// Add datetime stamped logs to objects with username
// The purpose is to keep notes for future reference like case numbers
// by Ernest Shaffer
// Version 0.1

// Installation:
// 1)  Add include to inc/local.php: include("objectlog.php";
// 2)  Create new table in the racktables database
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

$tab['depot']['mytab4'] = 'Object Logs';
$tabhandler['depot']['mytab4'] = 'getMydeviceLog4';
$ophandler['depot']['mytab4']['addObjectlog'] = 'addObjectlog';

$username = $_SERVER['PHP_AUTH_USER'];
$nextorder['odd'] = 'even';
$nextorder['even'] = 'odd';

$logentry = $_POST['logentry'];
$objectid = $_POST['objectid'];

// The ophandler to insert new log
function addObjectlog ()
{
    global $logentry;
    global $objectid;
    global $username;
    $log = emptyLog();
    $datetime = date("Y-m-d H:i:s");
    $query= "INSERT INTO  `racktables`.`Objectlog` (`object_id`,`user`,`date`,`content`) VALUES(";
    $query .= "'$objectid',  '$username',  '$datetime',  '$logentry');";
    $result = NULL;
	$result = useSelectBlade ($query, __FUNCTION__);
    $log = mergeLogs ($log, oneLiner (80, array ('log entry')));
    return buildWideRedirectURL ($log);
}



// Display form and log entries
function getMydeviceLog4 ()
{
    $serverSelect = getNarrowObjectList();
    global $nextorder;
    global $username;
    global $logentry;
    global $objectid;
    $query = "SELECT o.id, r.name, o.content, o.date, o.user, r.id as object_id FROM Objectlog o Left JOIN RackObject r ON o.object_id = r.id ORDER BY o.date DESC";
    $result = NULL;
    $result = useSelectBlade ($query, __FUNCTION__);

    echo "<style type='text/css'>\n";
    echo "tr.has_problems {\n";
    echo "background-color: #ffa0a0;\n";
    echo "}\n";
    echo "</style>\n";

    echo " <center> <h3> Add Object Log</h3><br>";
    echo "<table align=center border=0 cellpadding=5 cellspacing=0 align=center class=cooltable><tr valign=top>";
    echo "<th align=left>Name</th>";
    echo "<th align=left>Log</th>";
    echo "<form method=post name=addOjectlog action='process.php?page=depot&tab=mytab4&op=addObjectlog'>";
    echo "<tr><td valign=top align=left>";
    echo "<select name=objectid>";
    foreach ($serverSelect as $k => $v) { echo "<option value=$k>$v</option>"; }
    echo "</select>";
    echo "</td>";
    echo "<td align=left><textarea name=logentry rows=3 cols=40></textarea><input type=submit name=got_very_fast_data value='Go!'></td></tr>";
    echo "</form>";
    echo "</table>";

    echo " <center> <h3> Object Logs</h3><br>";
    echo "<table width=80% align=center border=0 cellpadding=5 cellspacing=0 align=center class=cooltable><tr valign=top>";

    echo "<th align=left>Name</th>";
    echo "<th align=left>Log</th>";
    echo "<th align=left>Date</th>";
    echo "<th align=left>User</th>";

    $order = 'odd';
    while ($row = $result->fetch (PDO::FETCH_ASSOC))
    {
        echo "<tr class=row_${order} valign=top>";
        echo "<td><a href='".makeHref(array('page'=>'object', 'object_id'=>$row['object_id']))."'>${row['name']}</a></td>";
        echo "<td>".nl2br($row['content'])."</td>";
        echo "<td>".date("m/d/y g:i A",strtotime($row['date']))."</td>";
        echo "<td>".$row['user']."</td>";
        echo "</tr>\n";
        $order = $nextorder[$order];
    }
    echo "</table>\n";

}
?>
