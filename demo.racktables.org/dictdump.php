#!/usr/bin/php

/* This command-line script should be run in a directory with
 * an unpacked RackTables tarball. It will print a set of SQL
 * queries, which can be piped into command-line MySQL client
 * to INSERT records into Dictionary table. This can be used
 * to reload a demo database of RackTables.
 */

<?php

if (@readfile ('install/init-dictvendors.sql'))
	exit;
include ('./inc/config.php');
include ('./inc/dictionary.php');

switch (CODE_VERSION)
{
case '0.17.2':
	foreach (reloadDictionary (1, 1150) as $query)
		echo $query . ";\n";
	break;
default:
	foreach (reloadDictionary () as $query)
		echo $query . ";\n";
	break;
}

?>
