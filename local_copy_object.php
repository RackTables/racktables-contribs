<?php

# "object copier" plugin by Manon Goo

$tab['object']['objectcopier'] = 'Object Copier ';
$trigger['object']['objectcopier'] = 'localtrigger_ObjectCopier';
$tabhandler['object']['objectcopier'] = 'localfunc_ObjectCopier';
$ophandler['object']['objectcopier']['updateobjectcopier'] = 'updateconfig_ObjectCopier';
$ophandler['object']['copyobject'] = 'localexecute_ObjectCopier';
$ophandler['object']['objectcopier']['copyObjects'] = 'copyMultipleObjects';
$ophandler['object']['objectcopier']['copyLotOfObjects'] = 'copyLotOfObjects';

//
// function to check whether a ObjectCopier generator should be shown
//
function localtrigger_ObjectCopier()
{
	return 'std';
}

function localexecute_ObjectCopier()
{
	echo "<h1>localexecute_ObjectCopier</h1>";
}

function localfunc_ObjectCopier($object_id)
{
	$object = spotEntity ('object', $object_id );
	amplifyCell($object);


	global $virtual_obj_types;
	$typelist = readChapter (CHAP_OBJTYPE, 'o');
	$typelist[0] = 'select type...';
	$typelist = cookOptgroups ($typelist);
	$max = getConfigVar ('MASSCOUNT');
	$tabindex = 100;


	echo "\n";
	echo "\n<!-- printOpFormIntro ('copyLotOfObjects') -->\n";
	printOpFormIntro ('copyLotOfObjects');
	echo "\n";
	startPortlet ('Same type, same tags');
	echo "\n" . '<table border=0 align=center>';
	echo "\n" . '<tr><th>name or "name","label","asset_no" (no csv escaping) </th><th>type</th></tr>';
	echo "<tr><td><input type=submit name=got_very_fast_data value='Go!'></td><td></td></tr>\n";
	echo "\n" . "<tr><td><textarea name=namelist cols=60 rows=40>\n</textarea></td>";
	echo "<td valign=top>";
	printf ("<input type=hidden name=global_type_id value='%s'>\n", $object['objtype_id']);
	renderNewEntityTags ('object');
	echo "</td></tr>";
	echo "<tr><td colspan=2><input type=submit name=got_very_fast_data value='Go!'></td></tr></table>\n";
	echo "</form>\n";
	finishPortlet();
	// EOF_Stolen
	/*
	startPortlet ('debug');
 	var_dump (getPortListPrefs() );
 	var_dump (getNewPortTypeOptions() );
	echo "</pre>";
	finishPortlet();
	*/

}
function copyLotOfObjects($template_object)
{
	global $dbxlink;
	$dbrollback = 0;
	if (! $dbxlink->beginTransaction() ) 
		throw new  RTDatabaseError ("can not start transaction");
	$log = emptyLog();
	$taglist = isset ($_REQUEST['taglist']) ? $_REQUEST['taglist'] : array();
	assertUIntArg ('global_type_id', TRUE);
	assertStringArg ('namelist', TRUE);
	$global_type_id = $_REQUEST['global_type_id'];
	$source_object_id = $_REQUEST['object_id'];
	$source_object = spotEntity ('object', $source_object_id);
	amplifyCell($source_object);
	if ($global_type_id == 0 or !strlen ($_REQUEST['namelist']))
		$log = mergeLogs ($log, oneLiner (186));
	else
	{
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
		foreach ($names2 as $name)
		{
			$label = '';
			$asset_no = '';
			$object_name = '';
			$regexp='/^\"([^\"]*)\","([^\"]*)\","([^\"]*)\"/';
			$object_name = htmlspecialchars_decode($name, ENT_QUOTES);	
			error_log( "$regexp $object_name" );
			if (preg_match($regexp, $object_name, $matches) ) 
			{
				$object_name = $matches[1];
				$label = $matches[2];
				$asset_no = $matches[3];
			} 
			else 
				$object_name = $name;
			try
			{
				$object_id = commitAddObject ($object_name, $label, $global_type_id, $asset_no, $taglist);
				if (!$object_id)
					throw new RTDatabaseError("could not create $object_name");
				$info = spotEntity ('object', $object_id);
				amplifyCell ($info);
				$name_by_id = array();
				foreach ($source_object['ports'] as $source_port)
				{
					$update_port=0;
					foreach ($info['ports'] as $existing_port) 
					{
						if ($existing_port['name'] == $source_port['name'] ) 
						{
							commitUpdatePort ($object_id, $existing_port['id'], $existing_port['name'], $existing_port['oif_id'], $source_port['label'], "" );
							$update_port=1;
						}
					}
					if ($update_port)
						true;
					else	
						commitAddPort ( $object_id, $source_port['name'], sprintf("%s-%s", $source_port['iif_id'], $source_port['oif_id']), $source_port['label'], "" );
				}
				// Copy links
				$info = spotEntity ('object', $object_id);
				amplifyCell ($info);
				$name_by_id = array();
				foreach ($source_object['ports'] as $source_port)
				{
					foreach ($info['ports'] as $existing_port) 
					{
						foreach ( array( BACKEND_PROT_ID) as $link_type)
						{
							$name_by_id[$existing_port['name']] = $existing_port['id'];
							if ( $source_port[$link_type]['remote_object_id'] == $source_object_id )
							{
								if ($existing_port['name'] == $source_port[$link_type]['remote_name'] ) 
								{
									linkPorts($existing_port['id'], $name_by_id[$source_port['name']], $source_port[$link_type][cabelid], $link_type);
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
					{
						if (empty($value))
						{
							commitUpdateAttrValue ($object_id, $record['id'] );
						}
						else
						{
							commitUpdateAttrValue ($object_id, $record['id'], $value ) ;
						}
					}
					else
					{
						showError ('Permission denied, "' . $record['id'] . '"can not be set');
					}

				}

				$log = mergeLogs ($log, oneLiner (5, array ('<a href="' . makeHref (array ('page' => 'object', 'tab' => 'default', 'object_id' => $object_id)) . '">' . $info['dname'] . '</a>')));
			}
			catch (RTDatabaseError $e)
			{
				error_log("rolling back DB");
				$dbrollback = 1;
				$dbxlink->rollBack();
				$log = mergeLogs ($log, oneLiner (147, array ($object_name)));
				throw new RTDatabaseError ($e);
			}
		}
	}
	if (! $dbrollback )
		$dbxlink->commit();
	return buildWideRedirectURL ($log);
}


function updateconfig_ObjectCopier()
{
	true;
}

?>
