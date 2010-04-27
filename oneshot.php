<?php
/*
This is a version of local.php file, which implements 'oneshot'
extension for RackTables. This extension makes it possible to
forward a user to a RackTables object page without knowing the
internal ID of that object. This is mainly intended to be used
together with other systems like Nagios, which only share
object's name in common with RackTables. For example:

http://example.com/racktables/index.php?page=oneshot&q=myserver

(This would instantly redirect the user to myserver's page in
RackTables, if "myserver" exists.)

This extension has been tested to work with RackTables 0.17.x.

*/

// Return entity ID, if its named column equals to provided string, or NULL
// otherwise (nothing found or more, than one row returned by query).
function lookupEntityByString ($realm, $value, $column = 'name')
{
	global $SQLSchema;
	if (!isset ($SQLSchema[$realm]))
		return NULL;
	$SQLinfo = $SQLSchema[$realm];
	$query = "SELECT ${SQLinfo['keycolumn']} as id FROM ${SQLinfo['table']} WHERE ${SQLinfo['table']}.${column} = '${value}' limit 2";
	$result = useSelectBlade ($query, __FUNCTION__);
	$rows = $result->fetchAll (PDO::FETCH_ASSOC);	
	unset ($result);
	if (count ($rows) != 1)
		return NULL;
	return $rows[0]['id'];
}

function handleOneShotRequest ()
{
	assertStringArg ('realm', __FUNCTION__);
	assertStringArg ('q', __FUNCTION__);
	switch ($_REQUEST['realm'])
	{
	case 'object':
		if (NULL === ($id = lookupEntityByString ($_REQUEST['realm'], $_REQUEST['q'])))
			echo "<h2>Nothing found for '${_REQUEST['q']}'</h2>";
		else
			echo "<script language='Javascript'>document.location='index.php?page=object&object_id=${id}';//</script>";
		break;
	default:
		dragon();
		break;
	}
}

?>
