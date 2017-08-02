<?php
/*
This is a version of local.php file, which implements 'oneshot'
extension for RackTables. This extension makes it possible to
forward a user to a RackTables object page without knowing the
internal ID of that object. This is mainly intended to be used
together with other systems like Nagios, which only share
object's name in common with RackTables. For example:

http://example.com/racktables/index.php?page=oneshot&realm=object&q=myserver

(This would instantly redirect the user to myserver's page in
RackTables, if "myserver" exists.)

This revision of extension has been tested to work with RackTables
versions 0.18.x and 0.19.x.

*/

$page['oneshot']['handler'] = 'handleOneShotRequest';

function handleOneShotRequest ()
{
	assertStringArg ('realm');
	assertStringArg ('q');
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
