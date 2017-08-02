<?php

/*
 * Configs tab by John Millington 
 * Version 0.5
 * 
 * A tab to store key levels of configuration such as base, build doc and staging
 *
 * REQUIREMENTS:
 *    PHP 5
 *		Racktables 0.20.5+
 *
 * INSTALL:
 *      1. create ObjectConfigs Table in your RackTables database
 *
		CREATE TABLE IF NOT EXISTS `ObjectConfigs` (
		`id` int(10) unsigned NOT NULL AUTO_INCREMENT,
		`object_id` int(10) unsigned NOT NULL,
		`config` longtext DEFAULT NULL,
		`comments` longtext DEFAULT NULL,
		`date` text DEFAULT NULL,
		PRIMARY KEY (`id`),
		KEY `object_id` (`object_id`),
		CONSTRAINT `ObjectConfigs-FK-object_id` FOREIGN KEY (`object_id`) REFERENCES 
		`Object` (`id`) ON DELETE CASCADE
		) 
		ENGINE=InnoDB  DEFAULT CHARSET=utf8; 

 *      2. copy configs.php to plugins directory
 *      3. chmod 644 configs.php

 */

$tab['object']['configs'] = 'Config';
$tabhandler['object']['configs'] = 'ConfigTabHandler';

       
// Adding new Config
function commitNewConfig ($object_id, $config, $comments, $date)
{
	return usePreparedExecuteBlade
		(
		"INSERT INTO ObjectConfigs (object_id, config, comments, date) VALUES (?, ?, ?, ?)",
		array ($object_id, $config, $comments, $date)
		);
}


// Updating existing configs
function commitUpdateConfig ($conf_id, $config, $comments, $date)
{
	return usePreparedExecuteBlade
		(
                "UPDATE ObjectConfigs SET config=?,comments = ?,date =? WHERE id = ?",
                array ($config, $comments, $conf_id, $date)
		);
} 


// Deleting configs
function commitDeleteConfig ($conf_id)
{
	return usePreparedExecuteBlade
		(
		"DELETE FROM ObjectConfigs WHERE id = ?",
		array ($conf_id)
		);
}


// Main
function ConfigTabHandler ()
{
	$dateadd = date("Y-m-d H:i:s");

	// Add show/hide JS functionality for existing configuration fields
	echo "<SCRIPT language=\"JavaScript\">
	<!--
	function toggle_visibility(id){
	var e = document.getElementById(id);
	if(e.style.display == 'block')
		e.style.display = 'none';
	else
		e.style.display = 'block';
	}
	//-->
	</script>
";

	// Print markup content for config tab
	$display ="<center>\n";
	if (isset($_POST['op'])) {
		if ($_POST['op'] == "addConfig") {
			commitNewConfig($_POST['object_id'], $_POST['config'], $_POST['comments'], $_POST['dateadd']);
			}
		if ($_POST['op'] == "editConfig") {
			commitUpdateConfig($_POST['conf_id'], $_POST['config'], $_POST['comments'], $_POST['dateadd']);
		}
	}
	if (isset($_GET['op'])) {
		if ($_GET['op'] == "delConfig") {
			commitDeleteConfig($_GET['conf_id']);
		}
	}


	// Table header
	$display .= "<table cellspacing=0 cellpadding='5' align='center' class='widetable'>";
	$display .= "<tr><th>&nbsp;</th>";
	$display .= "<th class=tdleft></br>Saved Configurations</th>";
	//$display .= "<th class=tdleft>Comment</th>";
	$display .= "<th>&nbsp;</th></tr>";
        
	assertUIntArg ('object_id', __FUNCTION__);
	$object = spotEntity ('object', $_REQUEST['object_id']);


	// Existing configs
	$query = "SELECT * FROM ObjectConfigs WHERE object_id = '$object[id]'";
	$result = NULL;
		$result = usePreparedSelectBlade ($query);
	while ($row = $result->fetch (PDO::FETCH_ASSOC)) {
		$conf_id = $row['id'];
		$object_id = $row['object_id'];
		$config = $row['config'];
		$comments = $row['comments'];
		$date = $row['date'];
				
	$display .= "<form method=post id=editConfig name=editConfig autocomplete=off action=\"\">";
	$display .= "<input type=hidden name=\"conf_id\" value=\"".$conf_id."\">";
	$display .= "<input type=hidden name=\"date\" value=\"".$date."\">";
	$display .= "<input type=hidden name=\"op\" value=\"editConfig\">";
	$display .= "<input type=hidden name=\"object_id\" value=\"".$object_id."\">";
	$display .= "<tr><td><a href='?page=object&tab=configs&object_id=".$object_id."&op=delConfig&conf_id=".$conf_id."'onclick=\"javascript:return confirm('Are you sure you want to delete this Config?')\">";
	$display .= "<img src='?module=chrome&uri=pix/tango-list-remove.png' width=16 height=16 border=0 title='Delete this Config'></a></td>";
	$display .= "<td><a href=# onclick=toggle_visibility('$conf_id');>$date &nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp Comments:&nbsp&nbsp$comments</a>";
	$display .= "</br><div id=$conf_id style=display:none;><textarea style=background-color:#CCCCCC form=editConfig name=\"config\" cols=125 rows=15>".$config."</textarea></div></td><td></td>";
	//$display .= "<td class='tdleft' NOWRAP><textarea name=comment value='".$comments."' cols=25 maxlength=254></textarea></td>";
	$display .= "<td><input type=image name=submit class=icon src='?module=chrome&uri=pix/tango-document-save-16x16.png' border=0 title='Save' onclick=\"javascript:return confirm('Are you sure you want to overwrite this Config?')\"></td></form></tr>";

       }        
	   
	// New config
	$display .= "<form action=\"\" method=post autocomplete=off id=\"addConfig\" name=\"addConfig\">";
	$display .= "<input type=hidden name=\"object_id\" value=\"".$object['id']."\">";
	$display .= "<input type=hidden name=\"dateadd\" value=\"".$dateadd."\">";
	$display .= "<input type=hidden name=\"op\" value=\"addConfig\">";
	$display .= "<tr><td><input type=image name=submit class=icon src='?module=chrome&uri=pix/tango-list-add.png' border=0  title='Add a Config'></td>";
	$display .= "<td class='tdleft'></br></br><p style=font-weight:bold;> Add New Configurations</p>Comment: <input cols=40 name=comments tabindex=102></br><textarea cols=125 rows=20 name=config tabindex=100 required></textarea></br></td></tr>";
	$display .= "</form></br></table></br></center>";

	// Output all .display strings to markup		
	echo $display;

}
