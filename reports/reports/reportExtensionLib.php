<?php
// Custom Racktables Report v.0.3.3
// Library file

// 2016-02-04 - Mogilowski Sebastian <sebastian@mogilowski.net>

error_reporting(E_ERROR | E_PARSE);

/**
 * Get the location of an RackTables object
 *
 * @param array $aObject
 * @return string
 */
function getLocation($aObject) {
    $sRowName = 'unknown';
    $sRackName = 'unknown';

    # Location parsing for other realms than objects
    if ($aObject['realm'] == 'rack') {
    	$sLocation = $aObject["location_name"] . ': '. $aObject["row_name"];
    	return $sLocation;
    }

    if ($aObject['realm'] == 'row') {
    	$sLocation = $aObject["location_name"];
    	return $sLocation;
    }

    if ($aObject['realm'] == 'location') {
    	if ($aObject["parent_name"] == null)
    		return '';
    	$sLocation = $aObject["parent_name"];
    	return $sLocation;
    }

    # Try to read the mount informations of the object
    if ( function_exists('getMountInfo') ) {
        $mountInfo = getMountInfo (array($aObject['id']));

        if ( isset( $mountInfo[$aObject['id']][0]["rack_name"] ) )
            $sRackName = $mountInfo[$aObject['id']][0]["rack_name"];

        $sRowName = 'unknown';
        if ( isset( $mountInfo[$aObject['id']][0]["row_name"] ) )
            $sRowName = $mountInfo[$aObject['id']][0]["row_name"];
    }
    else {
        if ( isset( $aObject["Row_name"] ) )
            $sRowName = $aObject["Row_name"];

        if ( isset( $aObject["Rack_name"] ) )
            $sRackName = $aObject["Rack_name"];
    }

    # No mount information available - check for a container
    if ( ( $sRowName == 'unknown' ) && ( $sRackName == 'unknown' ) && ( isset( $aObject['container_id'] ) ) ) {
        $sContainerName = '<a href="'. makeHref ( array( 'page' => 'object', 'object_id' => $aObject['container_id']) )  .'">'.$aObject['container_name'].'</a>';

    	$attributes = getAttrValues ($aObject['id']);
    	if ( isset($attributes['28']['a_value']) && $attributes['28']['a_value'] != '' )
    	    $sLocation = $sContainerName.': Slot '.$attributes['28']['a_value'];
    	else
           $sLocation = $sContainerName;

        # Get mount info of the container
        $sContainerRowName = 'unknown';
        $sContainerRackName = 'unknown';

        if ( function_exists('getMountInfo') ) {

            $containerMountInfo = getMountInfo (array($aObject['container_id']));

            if ( isset( $containerMountInfo[$aObject['container_id']][0]["rack_name"] ) )
                $sContainerRackName = $containerMountInfo[$aObject['container_id']][0]["rack_name"];

            if ( isset( $containerMountInfo[$aObject['container_id']][0]["row_name"] ) )
            $sContainerRowName = $containerMountInfo[$aObject['container_id']][0]["row_name"];

            $sLocation .= '<br/>' . $sContainerRowName.': '.$sContainerRackName;
        }
    }
    else {
        $sLocation = $sRowName.': '.$sRackName;
    }

    return $sLocation;

}

 /**
  * Create hyperlinks in text
  *
  * @param string $sText
  * @return string
  */
 function makeLinksInText($sText)
 {
 	# prepend http:// to www.xyz.com strings
 	$sText = preg_replace("/([^\/](www\.))(([^(\s|,)<]{4,68})[^(\s|,)<]*)/", ' http://$2$3', $sText);

 	# add html hyperlink to http:// and https:// strings
 	$sText = preg_replace("/(http:\/\/|https:\/\/)(([^(\s|,)<]{4,68})[^(\s|,)<]*)/", '<a href="$1$2" target="_blank">$2$4</a>', $sText);

        # try to add mailto: links in text containing an '@' with a domain name
        $sText = preg_replace("/([a-zA-Z0-9._-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,4})/", '<a href="mailto:$1" target="_blank">$1</a>', $sText);

    return $sText;
 }


 # Need for backward compatibility - Define function from Racktables version 0.20.x
if ( !function_exists('ip6_format') )
{

    function ip6_format ($ip_bin) {
        // maybe this is IPv6-to-IPv4 address?
        if (substr ($ip_bin, 0, 12) == "\0\0\0\0\0\0\0\0\0\0\xff\xff")
            return '::ffff:' . implode ('.', unpack ('C*', substr ($ip_bin, 12, 4)));

        $result = array();
        $hole_index = NULL;
        $max_hole_index = NULL;
        $hole_length = 0;
        $max_hole_length = 0;

        for ($i = 0; $i < 8; $i++) {

            $unpacked = unpack ('n', substr ($ip_bin, $i * 2, 2));
            $value = array_shift ($unpacked);
            $result[] = dechex ($value & 0xffff);

            if ($value != 0) {

                unset ($hole_index);
                $hole_length = 0;

            }
            else {

                if (! isset ($hole_index))
                    $hole_index = $i;
                if (++$hole_length >= $max_hole_length) {
                    $max_hole_index = $hole_index;
                    $max_hole_length = $hole_length;
                }

            }

        }

        if (isset ($max_hole_index)) {
             array_splice ($result, $max_hole_index, $max_hole_length, array (''));

            if ($max_hole_index == 0 && $max_hole_length == 8)
                return '::';
            elseif ($max_hole_index == 0)
                return ':' . implode (':', $result);
            elseif ($max_hole_index + $max_hole_length == 8)
                return implode (':', $result) . ':';
        }

        return implode (':', $result);
    }

}

/**
 * Get the containers of an RackTables object
 *
 * @param integer $object_id
 * @return array
 */
function getObjectContainerList ($object_id) {
	$ret = array();

	$result = usePreparedSelectBlade
	(
		'SELECT el.parent_entity_id AS container_id, ro.name as container_name '.
		'FROM EntityLink AS el '.
		'INNER JOIN RackObject AS ro ON ro.id = el.parent_entity_id '.
		'WHERE el.child_entity_type = "object" AND el.parent_entity_type = "object" AND el.child_entity_id = ?',
		array ($object_id)
	);

	while ($row = $result->fetch (PDO::FETCH_ASSOC))
		$ret[$row['container_id']] = array ('container_name' => $row['container_name']);

	return $ret;
}

/**
 * Get list of child objects of an RackTables object
 *
 * @param integer $object_id
 * @return array
 */
function getObjectChildObjectList ($object_id) {
	$ret = array();

	$result = usePreparedSelectBlade
	(
		'SELECT el.child_entity_id AS object_id, ro.name as object_name '.
		'FROM EntityLink AS el  '.
		'INNER JOIN RackObject AS ro ON ro.id = el.child_entity_id '.
		'WHERE el.child_entity_type = "object" AND el.parent_entity_type = "object" AND el.parent_entity_id = ?',
		array ($object_id)
	);

	while ($row = $result->fetch (PDO::FETCH_ASSOC))
		$ret[$row['object_id']] = array ('object_name' => $row['object_name']);

	return $ret;
}

?>
