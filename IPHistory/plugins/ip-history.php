<?php

/*
    IP history plug-in for RackTables
    Copyright (C) 2015  Alexey Andriyanov <alan@al-an.info>

    This program comes with ABSOLUTELY NO WARRANTY.
    This is free software, and you are welcome to redistribute it
    under certain conditions.

    Licence: GPL
 */

foreach (array ('reports', 'ipv4net', 'ipv6net') as $iph_page)
{
	$tab[$iph_page]['iphistory'] = 'IP history';
	$tabhandler[$iph_page]['iphistory'] = 'iph_renderIpHistory';
}

registerOpHandler ('ipv4space', 'manage', 'del', 'iph_backupNet', 'before');
registerOpHandler ('ipv6space', 'manage', 'del', 'iph_backupNet', 'before');
registerOpHandler ('ipv4net', 'properties', 'del', 'iph_backupNet', 'before');
registerOpHandler ('ipv6net', 'properties', 'del', 'iph_backupNet', 'before');
registerOpHandler ('ipv4net', 'tags', 'saveTags', 'iph_backupNet', 'before');
registerOpHandler ('ipv6net', 'tags', 'saveTags', 'iph_backupNet', 'before');
registerOpHandler ('ipv4net', 'properties', 'editRange', 'iph_backupNet', 'before');
registerOpHandler ('ipv6net', 'properties', 'editRange', 'iph_backupNet', 'before');

registerOpHandler ('ipv4space', 'newrange', 'add', 'iph_newNet', 'after');
registerOpHandler ('ipv6space', 'newrange', 'add', 'iph_newNet', 'after');

registerOpHandler ('ipv4space', 'manage', 'del', 'iph_delNet', 'after');
registerOpHandler ('ipv6space', 'manage', 'del', 'iph_delNet', 'after');

registerOpHandler ('ipv4net', 'properties', 'del', 'iph_delNet', 'after');
registerOpHandler ('ipv6net', 'properties', 'del', 'iph_delNet', 'after');

registerOpHandler ('ipv4net', 'tags', 'saveTags', 'iph_editNet', 'after');
registerOpHandler ('ipv6net', 'tags', 'saveTags', 'iph_editNet', 'after');

registerOpHandler ('ipv4net', 'properties', 'editRange', 'iph_editNet', 'after');
registerOpHandler ('ipv6net', 'properties', 'editRange', 'iph_editNet', 'after');

function iph_spotCurrNet()
{
	global $pageno;

	$id = array_fetch ($_REQUEST, 'id', NULL);
	if ($pageno == 'ipv6space' || $pageno == 'ipv6net')
		$realm = 'ipv6net';
	elseif ($pageno == 'ipv4space' || $pageno == 'ipv4net')
		$realm = 'ipv4net';

	if (isset ($realm) && isset ($id))
		return spotEntity ($realm, $id);
}

function iph_backupNet()
{
	global $iph_net_backup;
	$iph_net_backup = iph_spotCurrNet();
}

function iph_delNet()
{
	global $iph_net_backup;
	if (isset ($iph_net_backup))
		iph_addIpHistoryRow ($iph_net_backup, NULL);
}

function iph_editNet()
{
	global $iph_net_backup;
	if (isset ($iph_net_backup))
	{
		$new_net = spotEntity ($iph_net_backup['realm'], $iph_net_backup['id'], TRUE);
		iph_addIpHistoryRow ($iph_net_backup, $new_net);
	}
}

function iph_newNet()
{
	$cidr = assertStringArg ('range');
	$range_array = explode ('/', trim ($cidr));
	if (count ($range_array) != 2)
		return;
	$ip = $range_array[0];
	$mask = $range_array[1];
	$net = spotNetworkByIP (ip_parse ($ip), $mask + 1);
	if ($net['mask'] == $mask)
		iph_addIpHistoryRow (NULL, $net);
}

function iph_initDb()
{
	try
	{
		usePreparedSelectBlade ("SELECT id FROM _IpHistory LIMIT 1");
		return;
	}
	catch (PDOException $e)
	{
		if ($e->getCode() !== '42S02') // Base table or view not found
			throw $e;

		usePreparedExecuteBlade("
CREATE TABLE _IpHistory (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `time` int(10) unsigned NOT NULL,
  `user` varchar(255) NOT NULL,
  `ip` varbinary(16) NOT NULL,
  `mask` int(10) unsigned NOT NULL,
  `last_ip` varbinary(16) NOT NULL,
  `action` text NOT NULL,
  `tag_ids` varchar(255) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `time` (`time`),
  KEY `ip` (`ip`, `last_ip`),
  KEY `user` (`user`, `time`)
)
		");
	}
}

function iph_formatTagChain ($chain)
{
	$ret = implode (', ',
		array_map (
			function ($ti)
			{
				return $ti['tag'];
			},
			$chain)
	);
	return count ($chain) > 1 ? "[$ret]" : "'$ret'";
}

function iph_addIpHistoryRow ($prev_net, $curr_net)
{
	global $remote_username;

	if (! isset ($prev_net))
	{
		$range = $curr_net;
		$tags = $curr_net['etags'];
		$action = sprintf ("created, name='%s', tags=%s", $range['name'], iph_formatTagChain ($tags));
	}
	elseif (! isset ($curr_net))
	{
		$range = $prev_net;
		$tags = $prev_net['etags'];
		$action = "deleted";
	}
	else
	{
		// net changed
		$range = $curr_net;
		$tags = array_merge ($prev_net['etags'], $curr_net['etags']);

		$act_list = array();
		// handle name change
		if ($prev_net['name'] !== $curr_net['name'])
			$act_list[] = "name '{$prev_net['name']}' changed  to '{$curr_net['name']}'";

		// handle tags change
		$prev_tids = buildTagIdsFromChain ($prev_net['etags']);
		$curr_tids = buildTagIdsFromChain ($curr_net['etags']);
		$tid_add = array_diff ($curr_tids, $prev_tids);
		$tid_del = array_diff ($prev_tids, $curr_tids);
		if ($tid_add && !$tid_del)
			$act_list[] = "tags assigned: " . iph_formatTagChain (buildTagChainFromIds ($tid_add));
		elseif (! $tid_add && $tid_del)
			$act_list[] = "tags removed: " . iph_formatTagChain (buildTagChainFromIds ($tid_del));
		elseif ($tid_add && $tid_del)
			$act_list[] = sprintf ("tags changed from %s to %s",
									iph_formatTagChain ($prev_net['etags']),
									iph_formatTagChain ($curr_net['etags']));

		if (! $act_list)
			return; // nothing changed
		$action = implode ('; ',$act_list);
	}

	$tid_list = array();
	foreach (array_merge ($tags, getImplicitTags ($tags)) as $taginfo)
		$tid_list[$taginfo['id']] = $taginfo['id'];

	return usePreparedInsertBlade ('_IpHistory', array(
		'time' => time(),
		'user' => $remote_username,
		'ip' => $range['ip_bin'],
		'mask' => $range['mask'],
		'last_ip' => ip_last ($range),
		'action' => $action,
		'tag_ids' => implode (',', $tid_list),
	));
}

function iph_getInput ($name, $value, $size = NULL)
{
	$ret = '<input type="text"';
	if ($size)
		$ret .= ' size="' . htmlspecialchars ($size, ENT_QUOTES) . '"';
	$ret .= ' name="' . htmlspecialchars ($name, ENT_QUOTES) . '"';
	$ret .= ' value="' . htmlspecialchars ($value, ENT_QUOTES) . '"';
	$ret .= '>';
	return $ret;
}

function iph_selectHistory ($filters = array(), $page, $page_size)
{
	$query = 'SELECT SQL_CALC_FOUND_ROWS * FROM _IpHistory';
	$cond = array();
	$params = array();

	if (! empty ($filters['fdate']) and is_numeric ($filters['fdate']))
	{
		$cond[] = "time > UNIX_TIMESTAMP(DATE_ADD(NOW(), INTERVAL ? DAY))";
		$params[] = - $filters['fdate'];
	}

	if (! empty ($filters['faction']))
	{
		$cond[] = "action LIKE ?";
		$params[] = '%' . $filters['faction'] . '%';
	}

	if (! empty ($filters['fuser']))
	{
		$cond[] = "user LIKE ?";
		$params[] = $filters['fuser'] . '%';
	}

	if (! empty ($filters['fnet']))
	{
		$fields = explode ('/', $filters['fnet']);
		if (count ($fields) == 2 and $ip_bin = ip_checkparse ($fields[0]))
		{
			// cidr
			$range = constructIPRange ($ip_bin, $fields[1]);
			$cond[] = "LENGTH(ip) = ? AND ip BETWEEN ? AND ? AND mask >= ?";
			$params[] = strlen ($ip_bin);
			$params[] = $range['ip_bin'];
			$params[] = ip_last ($range);
			$params[] = $range['mask'];
		}
		elseif ($ip_bin = ip_checkparse ($filters['fnet']))
		{
			// ip
			$cond[] = "? BETWEEN ip and last_ip";
			$params[] = $ip_bin;
		}
		else
			throw new InvalidRequestArgException ('filters[fnet]', $filters['fnet'], 'Neither IP nor CIDR');
	}
	if ($cond)
		$query .= " WHERE " . implode (' AND ', $cond);
	$query .= " ORDER BY time DESC";
	if ($page)
		$query .= sprintf (" LIMIT %d,%d", $page_size * ($page - 1), $page_size * $page);

	return usePreparedSelectBlade ($query, $params);
}

function iph_renderIpHistory()
{
	iph_initDb();

	$net_cache = array();

	// init pager
	$page_size = 50;
	$page = array_fetch ($_REQUEST, 'p', 1);
	if (! is_numeric ($page) || $page < 0)
		$page = 1;

	// init filtering
	$filter = array_intersect_key ($_REQUEST, array_flip (array('fdate', 'faction', 'fuser', 'fnet')));
	$orig_filter = $filter;
	$filter_net = iph_spotCurrNet();
	// filter by current network
	if ($filter_net)
		$filter['fnet'] = $filter_net['ip'] . '/' . $filter_net['mask'];
	// set default maximum age to 14 days
	if (! isset ($filter['fdate']))
		$filter['fdate'] = 14;

	// enable table sorter
	addJS('js/ip-history/jquery.tablesorter.min.js');
	addCSS('js/ip-history/theme-blue/style.css');
	addJS('$(document).ready(function() { $("#iph	").tablesorter(); });', TRUE);

	startPortlet ("IP history");

	// render filter
	echo '<form method=get>';
	foreach (makePageParams() as $k => $v)
		echo "<input type=hidden name='$k' value='" . htmlspecialchars ($v, ENT_QUOTES) . "'>";

	echo '<table class=tdleft cellspacing=7><tr>';
	echo '<td>Days:';
	echo '<td>' . iph_getInput ('fdate', array_fetch ($filter, 'fdate', ''));
	echo '<td>Action:';
	echo '<td>' . iph_getInput ('faction', array_fetch ($filter, 'faction', ''));
	echo '<td>Net or IP:';
	echo '<td>';
	if (! $filter_net)
		echo iph_getInput ('fnet', array_fetch ($filter, 'fnet', ''));
	else
		echo mkCellA ($filter_net);
	echo '<td>Username:';
	echo '<td>' . iph_getInput ('fuser', array_fetch ($filter, 'fuser', ''));
	echo '<td>' . getImageHREF ('save', 'filter history', TRUE);
	echo '</tr></table>';
	echo '</form>';

	//render table header
	echo '<table id="iph" class=tablesorter cellspacing=1 cellpadding=0>';
	echo '<thead><tr>';
	echo '<th>date';
	echo '<th>action';
	echo '<th>net';
	echo '<th>user';
	echo '</tr></thead>';
	try
	{
		$result = iph_selectHistory ($filter, $page, $page_size);
		$cnt_res = usePreparedSelectBlade ("SELECT FOUND_ROWS()");
		$row_cnt = $cnt_res->fetch (PDO::FETCH_COLUMN, 0);
		unset ($cnt_res);

		global $nextorder;
		$order = 'odd';
		while ($row = $result->fetch (PDO::FETCH_ASSOC))
		{
			$cidr = ip_format ($row['ip']) . '/' . $row['mask'];
			if (array_key_exists($cidr, $net_cache))
				$net = $net_cache[$cidr];
			else
			{
				$net = spotNetworkByIP ($row['ip'], $row['mask'] + 1);
				if (!$net or $net['mask'] != $row['mask'])
					$net = NULL;
				$net_cache[$cidr] = $net;
			}
			echo "<tr class='$order'>";
			$order = $nextorder[$order];
			echo "<td>" . '<span title="' . strftime ('%Y-%m-%d %H:%M:%S', $row['time']) . '">' . formatAge ($row['time']) . '</span>';
			echo "<td>" . $row['action'];
			echo "<td>" . ($net ? mkCellA ($net) : $cidr);
			echo "<td>" . $row['user'];
			echo "</tr>\n";
		}
	}
	catch (InvalidArgException $e)
	{
		showError ($e->getMessage());
	}

	echo "</table>";

	// print pager
	if ($page and $page_size and $row_cnt > $page_size)
	{
		echo '<p>';
		$n_pages = intval (($row_cnt + $page_size - 1) / $page_size);
		$href = makeHref ($orig_filter + makePageParams());
		if ($page > 1)
			echo '<a href="' . $href . "&p=" . ($page - 1) . '">&larr;</a>';
		echo " page $page of $n_pages ";
		if ($page < $n_pages)
			echo '<a href="' . $href . "&p=" . ($page + 1) . '">&rarr;</a>';
		echo '</p>';
	}

	finishPortlet();
}
