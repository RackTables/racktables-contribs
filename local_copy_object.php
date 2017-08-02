<?php

/*
(c) 2014 Maximilian Mensing <max@maximilian-mensing.de>
(c) 2011 Manon Goo <manon@dg-i.net>
*/

defineIfNotDefined ('TABLE_BORDER', 0);

$tab['object']['objectcopier'] = 'Object Copier ';
$tabhandler['object']['objectcopier'] = 'localfunc_ObjectCopier';
$ophandler['object']['objectcopier']['copyLotOfObjects'] = 'copyLotOfObjects';

function amplifyCell_object_Backend_Port (&$record, $dummy = NULL)
{
	switch ($record['realm'])
	{
	case 'object':
		$record['BackendPorts'] = getObjectBackendPortsAndLinks ($record['id']);
		break;
	default:
	}
}

function getObjectBackendPortsAndLinks ($object_id)
{
	$ret = fetchBackendPortList ("Port.object_id = ?", array ($object_id));
	return sortPortList ($ret, TRUE);
}

function fetchBackendPortList ($sql_where_clause, $query_params = array())
{
	$query = <<<END
SELECT
	Port.id,
	Port.name,
	Port.object_id,
	Object.name AS object_name,
	Port.l2address,
	Port.label,
	Port.reservation_comment,
	Port.iif_id,
	Port.type AS oif_id,
	(SELECT PortInnerInterface.iif_name FROM PortInnerInterface WHERE PortInnerInterface.id = Port.iif_id) AS iif_name,
	(SELECT Dictionary.dict_value FROM Dictionary WHERE Dictionary.dict_key = Port.type) AS oif_name,
	IF(lba.porta, lba.cable, lbb.cable) AS cableid,
	IF(lba.porta, pa.id, pb.id) AS remote_id,
	IF(lba.porta, pa.name, pb.name) AS remote_name,
	IF(lba.porta, pa.object_id, pb.object_id) AS remote_object_id,
	IF(lba.porta, oa.name, ob.name) AS remote_object_name,
	(SELECT COUNT(*) FROM PortLog WHERE PortLog.port_id = Port.id) AS log_count,
	PortLog.user,
	UNIX_TIMESTAMP(PortLog.date) as time
FROM
	Port
	INNER JOIN Object ON Port.object_id = Object.id
	LEFT JOIN LinkBackend AS lba ON lba.porta = Port.id
	LEFT JOIN Port AS pa ON pa.id = lba.portb
	LEFT JOIN Object AS oa ON pa.object_id = oa.id
	LEFT JOIN LinkBackend AS lbb on lbb.portb = Port.id
	LEFT JOIN Port AS pb ON pb.id = lbb.porta
	LEFT JOIN Object AS ob ON pb.object_id = ob.id
	LEFT JOIN PortLog ON PortLog.id = (SELECT id FROM PortLog WHERE PortLog.port_id = Port.id ORDER BY date DESC LIMIT 1)
WHERE
	$sql_where_clause
END;

	$result = usePreparedSelectBlade ($query, $query_params);
	$ret = array();
	while ($row = $result->fetch (PDO::FETCH_ASSOC))
	{
		$row['l2address'] = l2addressFromDatabase ($row['l2address']);
		$row['linked'] = isset ($row['remote_id']) ? 1 : 0;

		// last changed log
		$row['last_log'] = array();
		if ($row['log_count'])
		{
			$row['last_log']['user'] = $row['user'];
			$row['last_log']['time'] = $row['time'];
		}
		unset ($row['user']);
		unset ($row['time']);

		$ret[] = $row;
	}
	return $ret;
}


function varDumpToString ($var)
{
    ob_start();
    var_dump($var);
    $result = ob_get_clean();
    return $result;
}

function localfunc_ObjectCopier($object_id)
{
$object = spotEntity ('object', $object_id );
amplifyCell($object);
global $virtual_obj_types, $taglist, $target_given_tags;
$typelist = readChapter (CHAP_OBJTYPE, 'o');
$typelist[0] = 'select type...';
$typelist = cookOptgroups ($typelist);
$max = getConfigVar ('MASSCOUNT');
$tabindex = 100;

echo "\n";
echo "\n<!-- printOpFormIntro ('copyLotOfObjects') -->\n";
printOpFormIntro ('copyLotOfObjects');
echo "\n";
startPortlet ('Make many copies of this object');
echo "\n" . sprintf('<table border=%s align=center>', TABLE_BORDER);
echo "\n" . '<tr><th align=left>name or "name","label","asset_no" (no csv escaping)<br><br>';
echo 'Example:<br> "server.example.com","server.example.com","12345"<br>www.example.com<br>testmachine<br>';
echo '</th><th>Copy Tags</th></tr>';
//echo "<tr><td><input type=submit name=got_very_fast_data value='Go!'></td><td></td></tr>\n";
echo "\n" . "<tr><td valign=top ><textarea name=namelist cols=60 rows=35>\n</textarea></td>";
echo "<td valign=top>";
printf ("<input type=hidden name=global_type_id value='%s'>\n", $object['objtype_id']);
//renderCopyEntityTagsPortlet ('Tag tree', getTagTree(), $target_given_tags, $etype_by_pageno[$pageno]);
renderCopyEntityTags($object);
echo "</td></tr>";
echo "<tr><td colspan=2><input type=submit name=got_very_fast_data value='Go!'></td></tr></table>\n";
echo "</form>\n";
finishPortlet();

}

function copyLotOfObjects()//$template_object)
{
	global $dbxlink;
	$dbrollback = 0;
	if (! $dbxlink->beginTransaction() )
		throw new  RTDatabaseError ("can not start transaction");
	// do we need this ?
	$log = emptyLog();
	$taglist = isset ($_REQUEST['taglist']) ? $_REQUEST['taglist'] : array();
	assertUIntArg ('global_type_id', TRUE);
	assertStringArg ('namelist', TRUE);
	$global_type_id = $_REQUEST['global_type_id'];
	$source_object_id = $_REQUEST['object_id'];
	$source_object = spotEntity ('object', $source_object_id);
	amplifyCell($source_object);
	// only call amplifyCell_object_Backend_Port if we have function linkmgmt_linkPorts from linkmgmt.php
	if ( function_exists ( 'amplifyCell_object_Backend_Port' ) && function_exists ('linkmgmt_linkPorts') )
	{
		amplifyCell_object_Backend_Port($source_object);
	}
	if ($global_type_id == 0 or !strlen ($_REQUEST['namelist']))
	{
		// Log something reasonable with showError Here
		// We do not have names to copy our object to !
		// Pls check what makes $global_type_id == 0  an error
		$log = mergeLogs ($log, oneLiner (186));
		return ;
	}
	// The name extractor below was stolen from ophandlers.php:addMultiPorts()
	$names1 = explode ("\n", $_REQUEST['namelist']);
	$names2 = array();
	foreach ($names1 as $line)
	{
		$parts = explode ('\r', $line);
		reset ($parts);
		if (!strlen ($parts[0]))
			continue;
		else
			$names2[] = rtrim ($parts[0]);
	}
	foreach ($names2 as $name_or_csv)
	{
		$label = '';
		$asset_no = '';
		$object_name = '';
		$regexp='/^\"([^\"]*)\","([^\"]*)\","([^\"]*)\"/';
		$object_name_or_csv = htmlspecialchars_decode($name_or_csv, ENT_QUOTES);
		// error_log( "$regexp $object_name" );
		if (preg_match($regexp, $object_name_or_csv, $matches) )
		{
			$object_name = $matches[1];
			$label = $matches[2];
			$asset_no = $matches[3];
		}
		else
			$object_name = $name_or_csv;
		try
		{
			$object_id = commitAddObject ($object_name, $label, $global_type_id, $asset_no, $taglist);
			if (!$object_id)
				throw new RTDatabaseError("could not create $object_name");
			$info = spotEntity ('object', $object_id);
			amplifyCell ($info);
			foreach ($source_object['ports'] as $source_port)
			{
				$update_port=0;
				foreach ($info['ports'] as $new_port)
				{
					if ($new_port['name'] == $source_port['name'] )
					{
						commitUpdatePort ($object_id, $new_port['id'], $new_port['name'], $new_port['oif_id'], $source_port['label'], "" );
						$update_port=1;
					}
				}
				if ($update_port)
					true;
				else
					commitAddPort ( $object_id, $source_port['name'], sprintf("%s-%s", $source_port['iif_id'], $source_port['oif_id']), $source_port['label'], "" );
			}
			// Copy Backendlinks only start if we ghave function linkmgmt_linkPorts from linkmgmt.php
			if ( function_exists ( 'amplifyCell_object_Backend_Port' ) && function_exists ('linkmgmt_linkPorts')  )
			{
				$info = spotEntity ('object', $object_id);
				amplifyCell ($info);
				amplifyCell_object_Backend_Port($info);

			/*	 showError( '<div align="left"><pre>\n===== Source Object ======\n\n' .
				 		 varDumpToString ( $source_object ) .
						'\n\n===== New Object ======\n\n' .
						 varDumpToString ( $info )  .  '</pre></div>' );
			*/
				$name_by_id = array();
				foreach ($info['BackendPorts'] as $new_be_port)
				{
					$name_by_id[$new_be_port['name']] = $new_be_port['id'];
				}

				$linked_ports = array();
				foreach ($source_object['BackendPorts'] as $source_be_port)
				{
					if ( $source_be_port['object_id'] == $source_be_port['remote_object_id'] )
					{
						// We have a Port that has the own object as remote object we want to copy this type of Linko
						// We have backend Links
						$new_be_port_a = $name_by_id[$source_be_port['name']] ;
						$new_be_port_b = $name_by_id[$source_be_port['remote_name']] ;
						if ( $new_be_port_a && $new_be_port_b  && ! array_key_exists($new_be_port_a, $linked_ports ) && ! array_key_exists($new_be_port_b, $linked_ports ) )
						{
							// error_log ( sprintf ('new_be_port_a %s // new_be_port_b %s // cableid %s', $new_be_port_a  , $new_be_port_b, $source_be_port['cableid'] ));
							$ret_val = linkmgmt_linkPorts( $new_be_port_a  , $new_be_port_b , 'back', $source_be_port['cableid'] );
							// error_log ( sprintf (' linkmgmt_linkPorts ret val: "%s" ', $ret_val)) ;
							if ($ret_val)
							{
								throw new RTDatabaseError("could not copy Backend Links for $object_name because: $ret_val");
							}
							else
							{
								$linked_ports[$new_be_port_a] = True;
								$linked_ports[$new_be_port_b] = True;
							}
						}
					}
				}
			}
			// Copy attributes
			foreach (getAttrValues ($source_object_id) as $record)
			{
				$value = $record['value'];
				switch ($record['type'])
				{
					case 'uint':
					case 'float':
					case 'string':
						$value = $record['value'];
						break;
					case 'dict':
						$value = $record['key'];
						break;
					default:
				}

				if (permitted (NULL, NULL, NULL, array (array ('tag' => '$attr_' . $record['id'] ))))
					if (empty($value))
						commitUpdateAttrValue ($object_id, $record['id'] );
					else
						commitUpdateAttrValue ($object_id, $record['id'], $value ) ;
				else
					showError ('Permission denied, "' . $record['name'] . '" can not be set');

			}

			//$log = mergeLogs ($log, oneLiner (5, array ('<a href="' .
			//	makeHref (array ('page' => 'object', 'tab' => 'default', 'object_id' => $object_id)) .
			//	'">' . $info['dname'] . '</a>'))
			//);
			showSuccess (sprintf ("Copied Object %s ; new Object: %s", $source_object['name']  , formatPortLink($object_id, $info['dname'], 1, '', '')) );
		}
		catch (RTDatabaseError $e)
		{
			error_log("rolling back DB");
			$dbrollback = 1;
			$dbxlink->rollBack();
			$log = mergeLogs ($log, oneLiner (147, array ($object_name)));
			throw new RTDatabaseError ( $e->getMessage() . sprintf(' (%s)', $name_or_csv  ));
		}
	}
	if (! $dbrollback )
		$dbxlink->commit();
	// return buildWideRedirectURL ($log);
}

//MOD
//MOD
//MOD
function renderCopyEntityTagsPortlet ($title, $tags, $preselect, $realm)
{
        startPortlet ($title);
        echo  '<a class="toggleTreeMode" style="display:none" href="#"></a>';
        echo '<table border=0 cellspacing=0 cellpadding=1 align=center class="tagtree">';
        printTagCheckboxTable ('taglist', $preselect, array(), $tags, $realm);
        echo '<tr><td class=tdleft>';
        //echo "</form></td><td class=tdright>";
        echo '</td></tr></table>';
        finishPortlet();
}

function renderCopyEntityTags ($entity_id)
{
        global $taglist, $target_given_tags, $pageno, $etype_by_pageno;
        echo '<table border=0 width="10%"><tr>';

        if (count ($taglist) > getConfigVar ('TAGS_QUICKLIST_THRESHOLD'))
        {
                $minilist = getTagChart (getConfigVar ('TAGS_QUICKLIST_SIZE'), $etype_by_pageno[$pageno], $target_given_tags);
                // It could happen, that none of existing tags have been used in the current realm.
                if (count ($minilist))
                {
                        $js_code = "tag_cb.setTagShortList ({";
                        $is_first = TRUE;
                        foreach ($minilist as $tag)
                        {
                                if (! $is_first)
                                        $js_code .= ",";
                                $is_first = FALSE;
                                $js_code .= "\n\t${tag['id']} : 1";
                        }
                        $js_code .= "\n});\n$(document).ready(tag_cb.compactTreeMode);";
                        addJS ('js/tag-cb.js');
                        addJS ($js_code, TRUE);
                }
        }

        // do not do anything about empty tree, trigger function ought to work this out
        echo '<td class=pcright>';
        renderCopyEntityTagsPortlet ('', getTagTree(), $target_given_tags, $etype_by_pageno[$pageno]);
        echo '</td>';

        echo '</tr></table>';
}
function mergeLogs ($log1, $log2)
{
        $ret = emptyLog();
        $ret['m'] = array_merge ($log1['m'], $log2['m']);
        return $ret;
}



function emptyLog ()
{
        return array
        (
                'v' => 2,
                'm' => array()
        );
}
function oneLiner ($code, $args = array())
{
        $ret = emptyLog();
        $ret['m'][] = count ($args) ? array ('c' => $code, 'a' => $args) : array ('c' => $code);
        return $ret;
}
