<?php

/*
This plugin implements assigning custom attributes to IP networks.
To make it working, apply these changes to the SQL database:

CREATE TABLE `AttributeValue_IPv4` (
  `net_id` int(10) unsigned DEFAULT NULL,
  `object_tid` int(10) unsigned NOT NULL DEFAULT '49000',
  `attr_id` int(10) unsigned DEFAULT NULL,
  `string_value` char(255) DEFAULT NULL,
  `uint_value` int(10) unsigned DEFAULT NULL,
  `float_value` float DEFAULT NULL,
  UNIQUE KEY `net_id` (`net_id`,`attr_id`),
  KEY `attr_id-uint_value` (`attr_id`,`uint_value`),
  KEY `attr_id-string_value` (`attr_id`,`string_value`(12)),
  KEY `id-tid` (`net_id`,`object_tid`),
  KEY `object_tid-attr_id` (`net_id`,`attr_id`),
  KEY `AttributeValue_IPv4-FK-map` (`object_tid`,`attr_id`),
  CONSTRAINT `AttributeValue_IPv4-FK-map` FOREIGN KEY (`object_tid`, `attr_id`) REFERENCES `AttributeMap` (`objtype_id`, `attr_id`),
  CONSTRAINT `AttributeValue_IPv4-FK-object` FOREIGN KEY (`net_id`) REFERENCES `IPv4Network` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE `AttributeValue_IPv6` (
  `net_id` int(10) unsigned DEFAULT NULL,
  `object_tid` int(10) unsigned NOT NULL DEFAULT '49001',
  `attr_id` int(10) unsigned DEFAULT NULL,
  `string_value` char(255) DEFAULT NULL,
  `uint_value` int(10) unsigned DEFAULT NULL,
  `float_value` float DEFAULT NULL,
  UNIQUE KEY `net_id` (`net_id`,`attr_id`),
  KEY `attr_id-uint_value` (`attr_id`,`uint_value`),
  KEY `attr_id-string_value` (`attr_id`,`string_value`(12)),
  KEY `id-tid` (`net_id`,`object_tid`),
  KEY `object_tid-attr_id` (`net_id`,`attr_id`),
  KEY `AttributeValue_IPv6-FK-map` (`object_tid`,`attr_id`),
  CONSTRAINT `AttributeValue_IPv6-FK-map` FOREIGN KEY (`object_tid`, `attr_id`) REFERENCES `AttributeMap` (`objtype_id`, `attr_id`),
  CONSTRAINT `AttributeValue_IPv6-FK-object` FOREIGN KEY (`net_id`) REFERENCES `IPv6Network` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

INSERT INTO Dictionary (dict_key, chapter_id, dict_sticky, dict_value) VALUES
 (49000, 1, 'no', 'IPv4 network (dummy)'),
 (49001, 1, 'no', 'IPv6 network (dummy)');

*/

$page['flatip']['title'] = 'IP networks';
$page['flatip']['parent'] = 'index';
$tabhandler['flatip']['default'] = 'renderFlatIP';
registerHook ('modifyEntitySummary', 'addAttributesToNetworkSummary', 'chain');

foreach (array ('ipv4net', 'ipv6net') as $net_realm)
{
	registerTabHandler ($net_realm, 'properties', 'renderNetworkEditAttrs');
	registerOpHandler ($net_realm, 'properties', 'updateAttrs', 'handleNetworkAttrsChange');
	registerOpHandler ($net_realm, 'properties', 'clearSticker', 'handleNetworkStickerClear');
}

// Pseudo-object type ids.
// Network attributes are assigned to these object types.
$netobject_type_id = array
(
	'ipv4net' => 49000,
	'ipv6net' => 49001,
);

// Special page 'flatip' handler that lists networks like the 'depot' page lists object.
// Lists both IP families on the same page.
// No network hierarchy is displayed, that's why 'flat'.
function renderFlatIP()
{
	if (isset ($_REQUEST['attr_id']) && isset ($_REQUEST['attr_value']))
	{
		$params = array ('attr_id' => $_REQUEST['attr_id'], 'attr_value' => $_REQUEST['attr_value']);
		$av = $_REQUEST['attr_value'];
		if ($av === 'NULL')
			$av = NULL;
		$nets = fetchNetworksByAttr ($_REQUEST['attr_id'], $av, TRUE);
	}
	else
	{
		$params = array();
		$nets = array_merge (listCells ('ipv4net'), listCells ('ipv6net'));
	}
	$cf = getCellFilter();
	$nets = filterCellList ($nets, $cf['expression']);
	echo "<table border=0 class=objectview>\n";
	echo "<tr><td class=pcleft>";
	startPortlet (sprintf ("Networks (%d)", count ($nets)));
	echo '<ol>';
	foreach ($nets as $network)
	{
		echo '<li>';
		renderCell ($network);
		echo '</li>';
	}
	echo '</ol>';
	finishPortlet();
	echo '</td><td class=pcright>';
	renderCellFilterPortlet ($cf, 'ipv4net', $nets, $params);
	echo '</td></tr></table>';
}

function addAttributesToNetworkSummary ($ret, $cell, $summary)
{
	if (!isset ($cell['realm']) || $cell['realm'] !== 'ipv4net' && $cell['realm'] !== 'ipv6net')
		return $ret;

	foreach (getAttrValuesForNetwork ($cell) as $record)
		if
		(
			strlen ($record['value']) and
			permitted (NULL, NULL, NULL, array (array ('tag' => '$attr_' . $record['id'])))
		)
		{
			if (! isset ($record['key']))
				$value = formatAttributeValue ($record);
			else
			{
				$href = makeHref
				(
					array
					(
						'page' => 'flatip',
						'tab' => 'default',
						'attr_id' => $record['id'],
						'attr_value' => $record['key'],
						'clear-cf' => ''
					)
				);
				$value = '<a href="' . $href . '">' . $record['a_value'] . '</a>';
			}

			$ret['{sticker}' . $record['name']] = $value;
		}
	return $ret;
}

// source: renderEditObjectForm
function renderNetworkEditAttrs()
{
	global $pageno, $netobject_type_id;
	$network = spotEntity ($pageno === 'ipv4net' ? 'ipv4net' : 'ipv6net', getBypassValue());
	$values = getAttrValuesForNetwork ($network);

	echo '<p>';
	startPortlet ("Attributes");
	printOpFormIntro ('updateAttrs');
	// optional attributes
	echo '<table border=0 cellspacing=0 cellpadding=3 align=center>';
	$suggest_records = array();
	if (count($values) > 0)
	{
		$i = 0;
		foreach ($values as $record)
		{
			if (! permitted (NULL, NULL, NULL, array (
						array ('tag' => '$attr_' . $record['id']),
						array ('tag' => '$any_op')
					))
				)
				continue;
			echo "<input type=hidden name=${i}_attr_id value=${record['id']}>";
			echo '<tr><td>';
			if (strlen ($record['value']))
			{
				echo "<a href='".makeHrefProcess(array('op'=>'clearSticker', 'id'=>$network['id'], 'attr_id'=>$record['id']))."'>";
				printImageHREF ('clear', 'Clear value');
				echo '</a>';
			}
			else
				echo '&nbsp;';
			echo '</td>';
			echo "<th class=sticker>${record['name']}:</th><td class=tdleft>";
			switch ($record['type'])
			{
				case 'uint':
				case 'float':
				case 'string':
					echo "<input type=text name=${i}_value value='${record['value']}'>";
					break;
				case 'dict':
					$opts = array ('0' => '(none)') + readChapter ($record['chapter_id'], 'o');
					printSelect ($opts, array ('name' => "{$i}_value"), $record['key']);
					break;
				case 'date':
					$date_value = $record['value'] ? date(getConfigVar('DATETIME_FORMAT'), $record['value']) : '';
					echo "<input type=text name=${i}_value value='${date_value}'>";
					break;
			}
			$i++;
			echo '<input type=hidden name=num_attrs value=' . $i . ">\n";
		}
	}
	echo '</table>';
	printImageHREF ('SAVE', 'Save changes', TRUE);
	echo '</form>';
	finishPortlet();
}

// source: updateObject
function handleNetworkAttrsChange()
{
	genericAssertion ('num_attrs', 'uint0');
	global $dbxlink, $sic, $pageno;
	$network = spotEntity ($pageno === 'ipv4net' ? 'ipv4net' : 'ipv6net', getBypassValue());

	$dbxlink->beginTransaction();

	// Update optional attributes
	$oldvalues = getAttrValuesForNetwork ($network);
	for ($i = 0; $i < $_REQUEST['num_attrs']; $i++)
	{
		genericAssertion ("${i}_attr_id", 'uint');
		$attr_id = $_REQUEST["${i}_attr_id"];
		if (! array_key_exists ($attr_id, $oldvalues))
			throw new InvalidRequestArgException ('attr_id', $attr_id, 'malformed request');
		$value = $_REQUEST["${i}_value"];

		if ('date' == $oldvalues[$attr_id]['type']) {
			assertDateArg ("${i}_value", TRUE);
			if ($value != '')
				$value = strtotime ($value);
		}

		# Delete attribute and move on, when the field is empty or if the field
		# type is a dictionary and it is the "--NOT SET--" value of 0.
		if ($value == '' || ($oldvalues[$attr_id]['type'] == 'dict' && $value == 0))
		{
			if (permitted (NULL, NULL, NULL, array (array ('tag' => '$attr_' . $attr_id))))
				commitUpdateAttrForNetwork ($network, $attr_id);
			else
				showError ('Permission denied, "' . $oldvalues[$attr_id]['name'] . '" left unchanged');
			continue;
		}

		// The value could be uint/float, but we don't know ATM. Let SQL
		// server check this and complain.
		assertStringArg ("${i}_value");
		switch ($oldvalues[$attr_id]['type'])
		{
			case 'uint':
			case 'float':
			case 'string':
			case 'date':
				$oldvalue = $oldvalues[$attr_id]['value'];
				break;
			case 'dict':
				$oldvalue = $oldvalues[$attr_id]['key'];
				break;
			default:
		}
		if ($value === $oldvalue) // ('' == 0), but ('' !== 0)
			continue;
		if (permitted (NULL, NULL, NULL, array (array ('tag' => '$attr_' . $attr_id))))
			commitUpdateAttrForNetwork ($network, $attr_id, $value);
		else
			showError ('Permission denied, "' . $oldvalues[$attr_id]['name'] . '" left unchanged');
	}

	$dbxlink->commit();
	return showSuccess ("Attributes were updated successfully");

}

// source: clearSticker
function handleNetworkStickerClear()
{
	global $sic, $pageno;
	assertUIntArg ('attr_id');
	if (permitted (NULL, NULL, NULL, array (array ('tag' => '$attr_' . $sic['attr_id']))))
	{
		commitUpdateAttrForNetwork (spotEntity ($pageno === 'ipv4net' ? 'ipv4net' : 'ipv6net', getBypassValue()), $sic['attr_id']);
		showSuccess ("Attribute value cleared successfully");
	}
	else
	{
		$oldvalues = getAttrValues (getBypassValue());
		showError ('Permission denied, "' . $oldvalues[$sic['attr_id']]['name'] . '" left unchanged');
	}
}

// returns an array of attribute values for a given network.
// result is indexed by attr_id
// source: fetchAttrsForObjects
function getAttrValuesForNetwork ($network)
{
	global $netobject_type_id;

	switch ($network['realm'])
	{
		case 'ipv4net':
			$av_table = 'AttributeValue_IPv4';
			$o_table = 'IPv4Network';
			break;
		case 'ipv6net':
			$av_table = 'AttributeValue_IPv6';
			$o_table = 'IPv6Network';
			break;
		default:
			throw new InvalidArgException ('realm', $network['realm'], "Unknown realm");
	}

	$ret = array();
	$query =
		"select AM.attr_id, A.name as attr_name, A.type as attr_type, C.name as chapter_name, " .
		"C.id as chapter_id, AV.uint_value, AV.float_value, AV.string_value, D.dict_value, O.id as object_id from " .
		"$o_table as O left join AttributeMap as AM on AM.objtype_id = ? " .
		"left join Attribute as A on AM.attr_id = A.id " .
		"left join $av_table as AV on AV.attr_id = AM.attr_id and AV.net_id = O.id " .
		"left join Dictionary as D on D.dict_key = AV.uint_value and AM.chapter_id = D.chapter_id " .
		"left join Chapter as C on AM.chapter_id = C.id " .
		" WHERE O.id = ?";
	$query .= " order by O.ip, O.mask";

	$result = usePreparedSelectBlade ($query, array ($netobject_type_id[$network['realm']] ,$network['id']));
	while ($row = $result->fetch (PDO::FETCH_ASSOC))
	{
		$object_id = $row['object_id'];

		# Objects with zero attributes also matter due to the LEFT JOIN. Create
		# keys for them too to enable negative caching.
		if ($row['attr_id'] == NULL)
			continue;

		$record = array();
		$record['id'] = $row['attr_id'];
		$record['name'] = $row['attr_name'];
		$record['type'] = $row['attr_type'];
		switch ($row['attr_type'])
		{
			case 'dict':
				$record['chapter_id'] = $row['chapter_id'];
				$record['chapter_name'] = $row['chapter_name'];
				$record['key'] = $row['uint_value'];
				// fall through
			case 'uint':
			case 'float':
			case 'string':
				$record['value'] = $row[$row['attr_type'] . '_value'];
				parseWikiLink ($record);
				break;
			case 'date':
				$record['value'] = $row['uint_value'];
				break;
			default:
				$record['value'] = NULL;
				break;
		}
		$ret[$row['attr_id']] = $record;
	}
	return $ret;
}

// set/update/delete attribute value
// source: commitUpdateAttrValue
function commitUpdateAttrForNetwork ($network, $attr_id, $value = '')
{
	switch ($network['realm'])
	{
		case 'ipv4net':
			$av_table = 'AttributeValue_IPv4';
			break;
		case 'ipv6net':
			$av_table = 'AttributeValue_IPv6';
			break;
		default:
			throw new InvalidArgException ('realm', $network['realm'], "Unknown realm");
	}
	$key = array ('net_id' => $network['id'], 'attr_id' => $attr_id);

	$result = usePreparedSelectBlade
	(
		"SELECT type AS attr_type, av.* FROM Attribute a " .
		"LEFT JOIN $av_table av ON a.id = av.attr_id AND av.net_id = ?" .
		"WHERE a.id = ?",
		array ($network['id'], $attr_id)
	);
	if (! $row = $result->fetch (PDO::FETCH_ASSOC))
		throw new InvalidArgException ('$attr_id', $attr_id, 'No such attribute #'.$attr_id);
	$attr_type = $row['attr_type'];
	unset ($result);
	switch ($attr_type)
	{
		case 'uint':
		case 'float':
		case 'string':
			$column = $attr_type . '_value';
			break;
		case 'dict':
		case 'date':
			$column = 'uint_value';
			break;
		default:
			throw new InvalidArgException ('$attr_type', $attr_type, 'Unknown attribute type found in ' . $network['realm'] . ' #' . $network['id'] . ', attribute #'.$attr_id);
	}
	$ret = 0;
	if (isset ($row['attr_id']))
	{
		// AttributeValue row present in table
		if ($value == '')
			$ret = usePreparedDeleteBlade ($av_table, $key);
		else
			$ret = usePreparedUpdateBlade ($av_table, array ($column => $value), $key);
	}
	elseif ($value != '')
		$ret = usePreparedInsertBlade ($av_table, $key + array ($column => $value));
	return $ret;
}

// returns an array of network rows with attr_value filtered by attribute key or value
// if $attribute_value is NULL, returns rows w/o specified attriute_id set
// if $use_key is TRUE, $attribute_value is treated as dict_key, otherwise - as dict_value or actual value
// if dont_filter is TRUE, all network rows are fetched. Useful to fetch all the values of a given attribute.
function fetchNetworkRowsByAttr ($attribute_id, $attribute_value, $use_key = FALSE, $dont_filter = FALSE)
{
	global $netobject_type_id, $SQLSchema;

	// get attribute type
	static $map;
	if (! isset ($map))
		$map = getAttrMap();
	if (! array_key_exists ($attribute_id, $map))
		throw new InvalidArgException ('attribute_id', $attribute_id, "No such attribute");
	$attribute = $map[$attribute_id];

	// get realms
	$realms = array();
	foreach ($attribute['application'] as $application)
		foreach ($netobject_type_id as $realm => $type)
			if ($application['objtype_id'] == $type)
				$realms[] = $realm;

	$join_side = ($dont_filter && $attribute_value !== NULL) ? 'INNER' : 'LEFT';

	$join = '';
	$field = '';
	switch ($attribute['type'])
	{
		case 'string':
			$field = 'AV.string_value';
			break;
		case 'uint':
			$field = 'AV.uint_value';
			break;
		case 'float':
			$field = 'AV.float_value';
			break;
		case 'date':
			$field = 'AV.uint_value';
			break;
		case 'dict':
			if ($use_key)
				$field = 'AV.uint_value';
			else
			{
				$join = 'LEFT JOIN Dictionary D ON D.dict_key = AV.uint_value';
				$field = 'D.dict_value';
			}
			break;
		default:
			throw new RackTablesError ();
	}
	$subqueries = array();
	$params = array();
	foreach (array ('ipv4net' => 'AttributeValue_IPv4', 'ipv6net' => 'AttributeValue_IPv6') as $realm => $table)
		if (in_array ($realm, $realms))
		{
			$main_table = $SQLSchema[$realm]['table'];
			$subquery = "
SELECT
 MT.id as net_id,
 MT.ip,
 MT.mask,
 ? as realm,
 $field as attr_value
FROM
 `$main_table` MT
 $join_side JOIN `$table` AV ON MT.id = AV.net_id AND AV.attr_id = ?
 $join
";
			$params[] = $realm;
			$params[] = $attribute_id;

			if (! $dont_filter)
			{
				if (isset ($attribute_value))
				{
					$subquery .= " WHERE $field = ?";
					$params[] = $attribute_value;
				}
				else
					$subquery .= " WHERE $field IS NULL";
			}
			$subqueries[] = $subquery;
		}
	$query = implode (' UNION ', $subqueries);
	$result = usePreparedSelectBlade ($query, $params);
	return $result->fetchAll (PDO::FETCH_ASSOC);
}

function fetchNetworksByAttr ($attribute_id, $attribute_value, $use_key = FALSE)
{
	$ret = array();
	foreach (fetchNetworkRowsByAttr ($attribute_id, $attribute_value, $use_key) as $row)
	{
		$net_cell = spotEntity ($row['realm'], $row['net_id']);
		$ret[$net_cell['realm'] . '-' . $net_cell['id']] = $net_cell;
	}
	return $ret;
}
