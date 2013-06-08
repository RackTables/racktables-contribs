<?php
/*
**************
*  Overview  *
**************
Version 0.2
Written by adoom42
Integrate with NodePing: https://nodeping.com/
  - Display check information when viewing an object.
  - A single check may be associated with multiple objects.
  - Multiple accounts are supported.
It has been tested with RackTables 0.20.4.

******************
*  Installation  *
******************
1. Place this file in the RackTables plugins directory.

2. Download the NodePing API from https://github.com/NodePing/NodePing_php.
   Place the file in the plugins directory, and name it nodeping_api.inc
   so RackTables does not treat it as a module.
   You may need to uncomment this line within the NodePingRequest class:
     curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);

3. Create these tables:
CREATE TABLE `NodePingAccount` (
  `id` int(10) unsigned NOT NULL auto_increment,
  `name` char(50) NOT NULL,
  `token` char(255) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB;

CREATE TABLE `NodePingCheck` (
  `id` int(10) unsigned NOT NULL auto_increment,
  `account_id` int(10) unsigned NOT NULL,
  `np_check_id` char(255) NOT NULL,
  `label` char(255) NOT NULL,
  `type` char(50) NOT NULL,
  `target` text NOT NULL,
  `check_interval` smallint(3) unsigned NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `np_check_id` (`np_check_id`),
  CONSTRAINT `NodePingCheck-FK-account_id` FOREIGN KEY (`account_id`) REFERENCES `NodePingAccount` (`id`)
) ENGINE=InnoDB;

CREATE TABLE `NodePingLink` (
  `id` int(10) unsigned NOT NULL auto_increment,
  `check_id` int(10) unsigned NOT NULL,
  `object_id` int(10) unsigned NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `NodePingLink-unique` (`check_id`,`object_id`),
  CONSTRAINT `NodePingLink-FK-check_id` FOREIGN KEY (`check_id`) REFERENCES `NodePingCheck` (`id`) ON DELETE CASCADE,
  CONSTRAINT `NodePingLink-FK-object_id` FOREIGN KEY (`object_id`) REFERENCES `Object` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB;


******************
*     Usage      *
******************
1. Navigate to 'Configuration -> NodePing' and add your account(s).

2. Navigate to the NodePing tab of an object and add a new check.
   You will now be able to associate the check with other objects.
   Click the link in the Type column to see more details.


******************
*    History     *
******************
0.1 - 2013-06-02 - Initial version by adoom42
0.2 - 2013-06-03 - Visual layout changes 
*/

require_once 'nodeping_api.inc';

$tab['object']['nodeping'] = 'NodePing';
$tabhandler['object']['nodeping'] = 'renderNodePingChecks';
$trigger['object']['nodeping'] = 'triggerNodePingChecks';
$ophandler['object']['nodeping']['add'] = 'addNodePingCheck';
$ophandler['object']['nodeping']['upd'] = 'updateNodePingCheck';
$ophandler['object']['nodeping']['link'] = 'tableHandler';
$ophandler['object']['nodeping']['unlink'] = 'tableHandler';
$ophandler['object']['nodeping']['del'] = 'tableHandler';

$page['nodeping']['title'] = 'NodePing';
$page['nodeping']['parent'] = 'config';
$tab['nodeping']['default'] = 'View';
$tab['nodeping']['accounts'] = 'Edit accounts';
$tabhandler['nodeping']['default'] = 'renderNodePingAccountsViewer';
$tabhandler['nodeping']['accounts'] = 'renderNodePingAccountsEditor';
$ophandler['nodeping']['accounts']['add'] = 'tableHandler';
$ophandler['nodeping']['accounts']['upd'] = 'tableHandler';
$ophandler['nodeping']['accounts']['del'] = 'tableHandler';

$opspec_list['nodeping-accounts-add'] = array
(
	'table' => 'NodePingAccount',
	'action' => 'INSERT',
	'arglist' => array
	(
		array ('url_argname' => 'name', 'assertion' => 'string'),
		array ('url_argname' => 'token', 'assertion' => 'string'),
	),
);
$opspec_list['nodeping-accounts-del'] = array
(
	'table' => 'NodePingAccount',
	'action' => 'DELETE',
	'arglist' => array
	(
		array ('url_argname' => 'id', 'assertion' => 'uint'),
	),
);
$opspec_list['nodeping-accounts-upd'] = array
(
	'table' => 'NodePingAccount',
	'action' => 'UPDATE',
	'set_arglist' => array
	(
		array ('url_argname' => 'name', 'assertion' => 'string'),
		array ('url_argname' => 'token', 'assertion' => 'string'),
	),
	'where_arglist' => array
	(
		array ('url_argname' => 'id', 'assertion' => 'uint'),
	),
);
$opspec_list['object-nodeping-link'] = array
(
	'table' => 'NodePingLink',
	'action' => 'INSERT',
	'arglist' => array
	(
		array ('url_argname' => 'check_id', 'assertion' => 'uint'),
		array ('url_argname' => 'object_id', 'assertion' => 'uint'),
	),
);
$opspec_list['object-nodeping-unlink'] = array
(
	'table' => 'NodePingLink',
	'action' => 'DELETE',
	'arglist' => array
	(
		array ('url_argname' => 'link_id', 'table_colname' => 'id', 'assertion' => 'uint'),
	),
);
$opspec_list['object-nodeping-del'] = array
(
	'table' => 'NodePingCheck',
	'action' => 'DELETE',
	'arglist' => array
	(
		array ('url_argname' => 'check_id', 'table_colname' => 'id', 'assertion' => 'uint'),
	),
);

function triggerNodePingChecks ()
{
	if (! count (getNodePingAccounts ()))
		return '';
	return 'std';
}

function renderNodePingAccountsViewer ()
{
	$accounts = getNodePingAccounts ();
	startPortlet ('NodePing accounts (' . count ($accounts) . ')');
	echo '<table cellspacing=0 cellpadding=5 align=center class=widetable>';
	echo '<tr><th>Name</th><th>API Token</th><th>Checks</th></tr>';
	foreach ($accounts as $account)
	{
		echo "<tr><td>${account['name']}</td>";
		echo "<td>${account['token']}</td>";
		echo "<td class=tdcenter>${account['num_checks']}</td></tr>\n";
	}
	echo "</table>\n";
	finishPortlet();
}

function renderNodePingAccountsEditor ()
{
	echo "<table cellspacing=0 cellpadding=5 align=center class=widetable>\n";
	echo "<tr><th>&nbsp;</th><th>Name</th><th>API Token</th><th>Checks</th><th>&nbsp;</th></tr>\n";
	printOpFormIntro ('add');
	echo '<tr><td>';
	printImageHREF ('add', 'add account', TRUE);
	echo '</td>';
	echo '<td><input type=text size=20 name=name tabindex=101></td>';
	echo '<td><input type=text size=40 name=token tabindex=102></td>';
	echo '<td>&nbsp;</td><td>';
	printImageHREF ('add', 'add account', TRUE);
	echo "</td></tr></form>\n";

	foreach (getNodePingAccounts () as $account)
	{
		printOpFormIntro ('upd', array ('id' => $account['id']));
		echo '<tr><td>';
		if ($account['num_checks'])
			printImageHREF ('nodelete', 'cannot delete, checks exist');
		else
		{
			echo '<a href="' . makeHrefProcess (array ('op' => 'del', 'id' => $account['id'])) . '">';
			echo getImageHREF ('delete', 'delete this account') . '</a>';
		}
		echo '</td>';
		echo '<td><input type=text size=20 name=name value="' . $account['name'] . '"></td>';
		echo '<td><input type=text size=40 name=token value="' . $account['token'] . '"></td>';
		echo "<td class=tdcenter>${account['num_checks']}</td>";
		echo '<td>' . getImageHREF ('save', 'update this account', TRUE) . '</td>';
		echo '</tr></form>';
	}
	echo "</table>\n";
}

function renderNodePingChecks ($object_id)
{
	$accounts = getNodePingAccounts ();
	$account_options = array();
	foreach ($accounts as $account)
		$account_options[$account['id']] = $account['name'];
	startPortlet ('Add new check');
	echo "<table cellspacing=0 cellpadding=5 align='center'>\n";
	echo "<tr><th>&nbsp;</th><th>Account</th><th>Check ID</th><th></th><th>&nbsp;</th></tr>\n";
	printOpFormIntro ('add');
	echo '<tr><td>';
	printImageHREF ('add', 'add check', TRUE);
	echo '</td><td>' . getSelect ($account_options, array ('name' => 'account_id'));
	echo '</td><td><input type=text size=25 name=np_check_id tabindex=101></td><td>';
	printImageHREF ('add', 'add check', TRUE);
	echo "</td></tr></form></table>\n";
	finishPortlet();

	$checks = getUnlinkedNodePingChecks ($object_id);
	if (count ($checks) > 0)
	{
		$check_options = array();
		foreach ($checks as $check)
			$check_options[$check['id']] = sprintf("%s - %s", $check['label'], $check['type']);
		startPortlet ('Link existing check (' . count ($checks) . ')');
		echo "<table cellspacing=0 cellpadding=5 align='center'>\n";
		printOpFormIntro ('link');
		echo '<tr><td>' . getSelect ($check_options, array ('name' => 'check_id'));
		echo '</td><td class=tdleft>';
		printImageHREF ('ATTACH', 'Link check', TRUE);
		echo "</td></tr></form></table>\n";
		finishPortlet();
	}

	addJs (<<<END
function toggleVisibility(tbodyId) {
	$("#" + tbodyId).toggle();
}
END
	, TRUE);
	$checks = getNodePingChecks ($object_id);
	startPortlet ('NodePing checks (' . count ($checks) . ')');
	if (count ($checks))
	{
		echo "<table cellspacing=0 cellpadding=5 align=center class=widetable>\n";
		echo "<tr><th>&nbsp;</th><th>Type</th><th>Label</th><th>Interval</th><th>Reason</th><th>Result</th><th>Unlink</th><th>&nbsp;</th></tr>\n";
		$token = '';
		foreach ($checks as $check)
		{
			printOpFormIntro ('upd', array ('check_id' => $check['check_id']));
			echo '<tr><td><a href="' . makeHrefProcess (array ('op' => 'del', 'check_id' => $check['check_id'])) . '">';
			echo getImageHREF ('delete', 'Unlink and delete this check') . '</a></td>';
			echo "<td><a href=\"#\" onclick=\"toggleVisibility('${check['check_id']}');\">${check['type']}</a></td>";
			echo "<td>${check['label']}</td>";
			echo "<td>${check['check_interval']}</td>";
			// re-use a nodeping object if it already exists and is using the same token as this check
			if ($check['token'] != $token)
				$nodeping = new NodePingClient (array ('token' => $check['token']));
			$token = $check['token'];
			$np_result_raw = $nodeping->result->get (array ('id' => $check['np_check_id'], 'limit' => 5, 'clean' => true));
			if (isset ($np_result_raw['error']))
				echo "<td colspan=5>Error: ${check_status_raw['error']}</td>";
			else
			{
				$np_result = $np_result_raw[0];
				if ($np_result['su'])
				{
					$reason = '';
					$result_str = 'PASS';
					$result_class = 'msg_success';
				}
				else
				{
					$reason = $np_result['sc'];
					$result_str = 'FAIL';
					$result_class = 'msg_error';
				}
				echo "<td>${reason}</td>";
				echo "<td><span class='${result_class}'>${result_str}</span></td>";
			}
			echo '<td class=center><a href="' . makeHrefProcess (array ('op' => 'unlink', 'link_id' => $check['link_id'])) . '">';
			echo getImageHREF ('cut', 'Unlink this check') . '</a></td>';
			echo '<td class=tdleft>';
			printImageHREF ('save', 'Save changes', TRUE);
			echo "</td></tr>\n";
			echo "<tbody id='${check['check_id']}' style='display:none;'><tr><td colspan=8>";
			echo '<table cellspacing=0 cellpadding=5 align=left>';
			// override the td styling so it doesn't have a border
			echo '<tr><th>Account</th><td align=left style="border-top: 0px;">'. getSelect ($account_options, array ('name' => 'account_id'), $check['account_id']) . '</td></tr>';
			echo '<tr><th>Check ID</th><td align=left style="border-top: 0px;"><input type=text size=25 name=np_check_id value="' . $check['np_check_id'] . '"></td></tr>';
			echo "<tr><th>Target</th><td align=left style=\"border-top:0px; word-wrap:break-word; max-width:250px;\">${check['target']}</td></tr>";
			echo '</table></form>';
			echo '<table cellspacing=0 cellpadding=5 align=right>';
			echo '<tr><th colspan=5>Last 5 Results</th></tr>';
			echo '<tr><th>Time</th><th>Loc</th><th>Run Time</th><th>Response</th><th>Result</th></tr>';
			foreach ($np_result_raw as $np_row)
			{
				// time is reported in miliseconds, so trim off the last 3 digits
				printf ('<tr><td>%s</td>', date ('H:i:s A', substr ($np_row['s'], 0, -3)));
				printf ('<td>%s</td>', strtoupper ($np_row['l'][$np_row['s']]));
				if ($np_row['su'])
				{
					$result_str = 'PASS';
					$result_class = 'msg_success';
				}
				else
				{
					$result_str = 'FAIL';
					$result_class = 'msg_error';
				}
				echo "<td>${np_row['rt']}</td>";
				echo "<td>${np_row['sc']}</td>";
				echo "<td><span class='${result_class}'>${result_str}</span></td></tr>";
			}
			echo '</table>';
			echo "</td></tr></tbody>\n";
		}
		echo "</table>\n";
	}
	finishPortlet();
}

function getNodePingAccount ($account_id)
{
	$result = usePreparedSelectBlade ('SELECT * FROM NodePingAccount WHERE id = ?',	array ($account_id));
	return $result->fetch (PDO::FETCH_ASSOC);
}

function getNodePingAccounts ()
{
	$result = usePreparedSelectBlade
	(
		'SELECT NPA.id, name, token, COUNT(NPC.id) as num_checks ' .
		'FROM NodePingAccount NPA ' .
		'LEFT JOIN NodePingCheck NPC ON NPA.id = NPC.account_id ' .
		'GROUP BY NPA.id ORDER BY name'
	);
	return reindexById ($result->fetchAll (PDO::FETCH_ASSOC));
}

function getNodePingCheck ($check_id)
{
	$result = usePreparedSelectBlade ('SELECT * FROM NodePingCheck WHERE id = ?',	array ($check_id));
	$row = $result->fetch (PDO::FETCH_ASSOC);
	return $row[0];
}

function getNodePingChecks ($object_id)
{
	$result = usePreparedSelectBlade
	(
		'SELECT NPL.id AS link_id, NPL.check_id, NPC.account_id, NPA.name AS account_name, NPA.token, NPC.np_check_id, ' .
		'NPC.label, NPC.type, NPC.target, NPC.check_interval ' .
		'FROM NodePingLink NPL ' .
		'LEFT JOIN NodePingCheck NPC ON NPL.check_id = NPC.id ' .
		'LEFT JOIN NodePingAccount NPA ON NPC.account_id = NPA.id ' .
		'WHERE NPL.object_id = ?',
		array ($object_id)
	);
	return reindexById ($result->fetchAll (PDO::FETCH_ASSOC), 'link_id');
}

function getUnlinkedNodePingChecks ($object_id)
{
	$result = usePreparedSelectBlade
	(
		'SELECT id, label, target, type ' .
		'FROM NodePingCheck ' .
		'WHERE id NOT IN (SELECT check_id FROM NodePingLink WHERE object_id = ?) ' .
		'ORDER BY label, target, type',
		array ($object_id)
	);
	return reindexById ($result->fetchAll (PDO::FETCH_ASSOC));
}

$msgcode['addNodePingCheck']['OK'] = 5;
$msgcode['addNodePingCheck']['ERR1'] = 100;
function addNodePingCheck ()
{
	assertUIntArg ('account_id');
	assertStringArg ('np_check_id');
	$account = getNodePingAccount ($_REQUEST['account_id']);
	$nodeping = new NodePingClient (array ('token' => $account['token']));
	$np_check = $nodeping->check->get (array ('id' => $_REQUEST['np_check_id'], 'limit' => 1, 'clean' => true));
	if (isset ($np_check['error']))
		return showFuncMessage (__FUNCTION__, 'ERR1', array ('Error: ' . $np_check['error']));
	usePreparedInsertBlade
	(
		'NodePingCheck',
		array
		(
			'account_id' => $_REQUEST['account_id'],
			'np_check_id' => $_REQUEST['np_check_id'],
			'label' => $np_check['label'],
			'type' => $np_check['type'],
			'target' => $np_check['parameters']['target'],
			'check_interval' => $np_check['interval']
		)
	);
	$check_id = lastInsertID();
	global $sic;
	usePreparedInsertBlade
	(
		'NodePingLink',
		array
		(
			'check_id' => $check_id,
			'object_id' => $sic['object_id'],
		)
	);
	return showFuncMessage (__FUNCTION__, 'OK', array (htmlspecialchars ($np_check['label'])));
}

$msgcode['updateNodePingCheck']['OK'] = 6;
$msgcode['updateNodePingCheck']['ERR1'] = 100;
function updateNodePingCheck ()
{
	assertUIntArg ('check_id');
	assertUIntArg ('account_id');
	assertStringArg ('np_check_id');
	$check = getNodePingCheck ($_REQUEST['check_id']);
	$account = getNodePingAccount ($_REQUEST['account_id']);
	$nodeping = new NodePingClient (array ('token' => $account['token']));
	$np_check = $nodeping->check->get (array ('id' => $_REQUEST['np_check_id'], 'limit' => 1, 'clean' => true));
	if (isset ($check['error']))
		return showFuncMessage (__FUNCTION__, 'ERR1', array ('Error: ' . $np_check['error']));
	usePreparedUpdateBlade
	(
		'NodePingCheck',
		array
		(
			'account_id' => $_REQUEST['account_id'],
			'np_check_id' => $_REQUEST['np_check_id'],
			'label' => $np_check['label'],
			'type' => $np_check['type'],
			'target' => $np_check['parameters']['target'],
			'check_interval' => $np_check['interval']
		),
		array ('id' => $_REQUEST['check_id'])
	);
	return showFuncMessage (__FUNCTION__, 'OK', array (htmlspecialchars ($np_check['label'])));
}
?>
