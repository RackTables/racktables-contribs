<?php
// NOTE: Requires RackTables v0.20.14 or greater

registerHook ('commitUpdateObjectAfter_hook', 'demo_commitUpdateObjectAfter_hook');

function demo_commitUpdateObjectAfter_hook ($object_id)
{
	static $in_function;
	if ($in_function) 
	{
		return; // Prevent endless recursion
	}
	$in_function = true;
	$object = spotEntity ('object', $object_id, true);
	if 
	(
		4 == $object['objtype_id'] &&  // 4 = Server
		!empty ($object['name'])
	)  
	{
		if ( empty($attrs[3]['value']) )  // 3 = FQDN
		{  
			commitUpdateAttrValue
			(
				$object_id, 
				3, 
				strtolower($object['name']).'.example.com'
			);
		}
		if ( empty ($object['asset_no']) ) 
		{
			$object['asset_no'] = $object['name'];
			commitUpdateObject  // Recursion
			(
				$object['id'],
				$object['name'],
				$object['label'],
				$object['has_problems'],
				$object['asset_no'],
				$object['comment']
			);
		}
	}
	$in_function = false;
}
