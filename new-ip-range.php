<?php

/*
   This plug-in adds tab 'Spare search' to the IPv4 space page.
   It is used to search for a spare IP range of the given size
   in the given subset of parent networks (using tag-based filter).
*/

$tab['ipv4space']['newiprange'] = 'Spare search';
registerTabHandler ('ipv4space', 'newiprange', 'renderSearchNewIP4Range');

/*
SPARE_SEARCH_PREDICATE is a predicate name describing aggregates to search in.
It is useful to define a predicate like '[RIPE allocation]' to search only there.
Define this constant in your secret.php like this:

define ('SPARE_SEARCH_PREDICATE', 'RIPE allocation');
*/

function renderSearchNewIP4Range()
{
	global $pTable;

	// prepare $cellfilter
	$cellfilter = getCellFilter();
	if ($cellfilter['is_empty'] || ! isset ($_REQUEST['cfp']))
		if (defined ('SPARE_SEARCH_PREDICATE') && isset ($pTable[SPARE_SEARCH_PREDICATE]))
		{
			$_REQUEST['cfp'] = array (SPARE_SEARCH_PREDICATE);
			$cellfilter = getCellFilter();
		}
	$mask = NULL;
	if (! empty ($_REQUEST['pref_len']))
		$mask = intval($_REQUEST['pref_len']);
	
	$nets = array();
	foreach (filterCellList (listCells ('ipv4net'), $cellfilter['expression']) as $net)
	{
		if (! isset ($mask))
			$nets[] = $net;
		elseif ($net['mask'] <= $mask)
		{
			$is_aggregate = FALSE;
			foreach ($net['atags'] as $atag)
				if ($atag['tag'] == '$aggregate')
					$is_aggregate = TRUE;
				elseif (preg_match ('/^\$spare_(\d+)$/', $atag['tag'], $m) && $mask >= $m[1])
				{
					$nets[] = $net;
					continue 2;
				}
			if (! $is_aggregate)
				$nets[] = $net;
		}
	}
	$filter = getOutputOf ('renderCellFilterPortlet', $cellfilter, 'ipv4net', $nets);
	
	echo '<table width="100%"><tr valign=top>';
	echo '<td>';
	startPortlet ("Results (" . count ($nets) . ")");
	echo '<ul class="spare-nets">';
	foreach ($nets as $net)
	{
		echo '<li>';
		renderNetCellForAlloc ($net, $mask);
		echo '</li>';
	}
	echo '</ul>';
	finishPortlet();
	echo '</td>';

	echo '<td width="33%">';
	echo preg_replace_callback ('/(<form[^<>]*>)/', 'generatePrefixLengthInput', $filter);
	echo '</td>';

	echo '</tr></table>';
	
	addCSS(<<<END
ul.spare-nets {
	list-style: none;
	padding: 0px;
}
ul.spare-nets li {
	margin: 5px 0px;
}

END
	, TRUE);
}

function renderNetCellForAlloc ($cell, $needed_mask = NULL)
{
	if (empty ($cell['spare_ranges']) and $cell['kidc'] == 0 and $cell['mask'] < 31)
	{
		$cell['spare_ranges'][$cell['mask'] + 1][] = $cell['ip_bin'];
		$cell['spare_ranges'][$cell['mask'] + 1][] = (ip_last ($cell) & ip4_mask ($cell['mask'] + 1));
	}
	$ranges = array_keys ($cell['spare_ranges']);
	sort ($ranges, SORT_NUMERIC);
	foreach ($ranges as &$range)
	{
		$suffix = (count ($cell['spare_ranges'][$range]) <= 1) ? '' : '<small> x ' . count ($cell['spare_ranges'][$range]) . '</small>';
		$range = '<a href="' .
			makeHref (array
			(
				'page' => 'ipv4space',
				'tab' => 'newrange',
				'set-prefix' => ip_format ($cell['spare_ranges'][$range][0]) . '/' . $range,
			)) .
			'">/' . $range . '</a>' . $suffix;
	}
	
	$spare_cidr = NULL;
	if (isset ($needed_mask))
		for ($i = $needed_mask; $i > 0; $i--)
			if (! empty ($cell['spare_ranges'][$i]))
			{
				$spare_cidr = ip_format ($cell['spare_ranges'][$i][0]) . '/' . $needed_mask;
				break;
			}

	echo "<table class='slbcell vscell'><tr><td rowspan=3 width='5%'>";
	printImageHREF ('NET');
	echo '</td>';
	echo "<td><a href='index.php?page={$cell['realm']}&id=${cell['id']}'>${cell['ip']}/${cell['mask']}</a>";
	echo getRenderedIPNetCapacity ($cell);
	echo '</td></tr>';

	echo "<tr><td>";
	if ($cell['name'] != '')
		echo "<strong>" . stringForTD ($cell['name']) . "</strong>";
	else
		echo "<span class=sparenetwork>no name</span>";
	// render VLAN
	echo '<div class="vlan">' . implode(', ', $ranges) . '</div>';
	renderNetVLAN ($cell);
	echo "</td></tr>";
	echo '<tr><td>';
	echo count ($cell['etags']) ? ("<small>" . serializeTags ($cell['etags']) . "</small>") : '&nbsp;';
	if (isset ($spare_cidr))
		echo "<div class='vlan'><a href=\"" . makeHref (array ('page' => 'ipv4space', 'tab' => 'newrange', 'set-prefix' => $spare_cidr)) . "\">Allocate /$needed_mask</a></div>";
	echo "</td></tr></table>";
}

function generatePrefixLengthInput($m)
{
	static $count = 0;
	if (++$count > 1)
		return $m[1] . '<input type="hidden" name="pref_len" value="">';
	else
		return $m[1] . '<label>Prefix length:<br><input type="text" name="pref_len" value="' . htmlspecialchars (@$_REQUEST['pref_len'], ENT_QUOTES) .  '"></label><p>';
}
