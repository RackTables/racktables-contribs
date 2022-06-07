<?php
	/*
	This file is part of Graphvis.

    Graphvis is free software: you can redistribute it and/or modify it under
	the terms of the GNU General Public License as published by the Free
	Software Foundation, either version 3 of the License, or (at your option)
	any later version.

    Graphvis is distributed in the hope that it will be useful, but WITHOUT ANY
	WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS
	FOR A PARTICULAR PURPOSE. See the GNU General Public License for more details.
    
	You should have received a copy of the GNU General Public License along with
	Graphvis. If not, see <https://www.gnu.org/licenses/>.
	*/
	function plugin_graphvis_info() {
		return array(
			"name" => "graphvis",
			"longname" => "graphvis : network topology",
			"version" => "1.1",
			"home_url" => "https://github.com/Breloc/graphvis",
		);
	}

	function plugin_graphvis_init() {
		//Inserting plugin page into RackTables index
		global $indexlayout;
		global $page;
		global $tab;
		global $tabHandler;
		global $image;
		
		array_push($indexlayout, array('graphvis'));
		addCSSText(file_get_contents(dirname(__FILE__).'/vis-network.min.css'));
		addCSSText(file_get_contents(dirname(__FILE__).'/graphvis.css'));
		addJSText(file_get_contents(dirname(__FILE__).'/vis-network.min.js'));
	
		$page['graphvis']['title'] = 'Topology';
		$page['graphvis']['parent'] = 'index';
	
		$tab['graphvis']['default'] = 'Logical';
		registerTabHandler ('graphvis', 'default', 'graphvisRenderLogical');
		$tab['graphvis']['Physical'] = 'Physical';
		registerTabHandler ('graphvis', 'Physical', 'graphvisRenderPhysical');
	
		$image['graphvis']['srctype'] = 'dataurl';
		$image['graphvis']['data'] = 'image/png;base64,'.base64_encode(file_get_contents(dirname(__FILE__).'/graphvis-logo.png'));
		$image['graphvis']['width'] = 218;
		$image['graphvis']['height'] = 200;

	}

	function plugin_graphvis_install() {
		addConfigVar('GRAPHVIS_DEFLT_LOGICAL_TAGFILTER', '', 'string', 'yes', 'no', 'yes', 'Default tag ID to display on graphvis logical tab. Ie. "24" for tag id 24');
		addConfigVar('GRAPHVIS_LOGICAL_TAGS_ROOT', '', 'string', 'yes', 'no', 'yes', 'Children of this tag will be offered as tag filter for devices in Logical view');
		addConfigVar('GRAPHVIS_PHYSICAL_ROWSTOEXCLUDE', '', 'string', 'yes', 'no', 'yes', 'Comma-separated list of row IDs to hide on physical tab');
		addConfigVar('GRAPHVIS_HIDE_CHILDREN_OBJECTS', 'yes', 'string', 'yes', 'no', 'yes', 'Boolean indicating if container\'s hierarchy must be merged in root parent in logical topology. "yes" for TRUE, all other values are FALSE');
		return TRUE;
	}

	function plugin_graphvis_uninstall() {
		deleteConfigVar('GRAPHVIS_DEFLT_LOGICAL_TAGFILTER');
		deleteConfigVar('GRAPHVIS_LOGICAL_TAGS_ROOT');
		deleteConfigVar('GRAPHVIS_PHYSICAL_ROWSTOEXCLUDE');
		deleteConfigVar('GRAPHVIS_HIDE_CHILDREN_OBJECTS');
		return TRUE;
	}

	function plugin_graphvis_upgrade() {
		$db_info = getPlugin('graphvis');
		$v1 = $db_info['db_version'];
		$code_info = plugin_graphvis_info ();
		$v2 = $code_info['version'];

		if ($v1 == $v2)
			throw new RackTablesError ('Versions are identical', RackTablesError::INTERNAL);

		// find the upgrade path to be taken
		$versionhistory = array
		(
			'1.0',
			'1.1',
		);
		$skip = TRUE;
		$path = NULL;
		foreach ($versionhistory as $v)	{
			if ($skip and $v == $v1) {
				$skip = FALSE;
				$path = array();
				continue;
			}
			if ($skip)
				continue;

			$path[] = $v;
			if ($v == $v2)
				break;
		}
		if ($path === NULL or !count ($path))
			throw new RackTablesError ('Unable to determine upgrade path', RackTablesError::INTERNAL);

		// Run update
		foreach ($path as $batchid) {
			switch ($batchid) {
				case '1.1':
					addConfigVar('GRAPHVIS_HIDE_CHILDREN_OBJECTS', 'yes', 'string', 'yes', 'no', 'yes', 'Boolean indicating if container\'s hierarchy must be merged in root parent in logical topology. "yes" for TRUE, all other values are FALSE');
					break;
				default:
					throw new RackTablesError ("Preparing to upgrade to $batchid failed", RackTablesError::INTERNAL);
			}
		}

		return TRUE;
	}

	
	
	function getObjectRootParentID($objid, &$objlist) {
		if (!array_key_exists($objid, $objlist))
			return $objid;
		$curobj = &$objlist[$objid];
		while(!is_null($curobj['container_id']))
			$curobj = &$objlist[$curobj['container_id']];
		return $curobj['id'];
	}


	function graphVisGetLogicalData() {
		$racks = getRackSearchResult('%');
		$netobjs = graphvisGetNetObjects();
		$netobjs_children = array();
		$links = array();
		$hideChildren = (strtolower(getConfigVar('GRAPHVIS_HIDE_CHILDREN_OBJECTS')) == 'yes');

		// Register network tags
		$networktags = Array("-1" => ""); // Filled with "no tag filter"
		$tagsarray = graphvisGetTags(getConfigVar('GRAPHVIS_LOGICAL_TAGS_ROOT'));
		foreach($tagsarray as $id => $tagarray) {
			$networktags[$id] = $tagarray['tag'];
		}
		

		foreach($netobjs as &$netobj) {
			// Register location, row and rack names in netobj
			$netobj['rack_name'] = $racks[$netobj['rack_id']]['name'];
			$netobj['row_name'] = $racks[$netobj['rack_id']]['row_name'];
			$netobj['location_name'] = $racks[$netobj['rack_id']]['location_name'];

			// Register objects in a container to hide them
			if (!is_null($netobj['container_id']))
				$netobjs_children[$netobj['id']] = 'hide me !';

			// Register tags in netobj
			$netobj['tags'] = Array();
			foreach(array_keys($netobj['etags']) as $id) {
				if (in_array($id, array_keys($networktags))) {
					$netobj['tags'][] = $id;
				}
			}
			
			$ports = graphvisGetPorts($netobj['id']);
			foreach($ports as $port) {
				// Generate link chain, and retrieve first and last objects of chain
				$linkchain = new lm_linkchain($port['id']);
				$portA = $linkchain->getport($linkchain->first);
				$portB = $linkchain->getport($linkchain->last);
				
				if ($hideChildren) {
					$portARootObjId = getObjectRootParentID($portA['object_id'], $netobjs);
					$portBRootObjId = getObjectRootParentID($portB['object_id'], $netobjs);
				}
				else {
					$portARootObjId = $portA['object_id'];
					$portBRootObjId = $portB['object_id'];
				}

				$objIdA = min($portARootObjId, $portBRootObjId);
				$objIdB = max($portARootObjId, $portBRootObjId);
				if ($objIdA != $portARootObjId) {
					// Swap portA and portB
					$tmp = $portA;
					$portA = $portB;
					$portB = $tmp;
				}
				
				// Ensure remote object is another netobject in our filtered objects list
				if ($objIdA != $objIdB && array_key_exists($objIdA, $netobjs) && array_key_exists($objIdB, $netobjs)) {
					$portsNamesID = "{$portA['name']} portsNamesID {$portB['name']}";
					$link = [
						'objIdA'    => $objIdA,
						'objIdB'    => $objIdB,
						'linktype'  => graphVisGetSlowestLinktype($portA, $portB),
						'linkcount' => 1,
						'portsNames' => [$portsNamesID => [$portA['name'], $portB['name']]]
					];
					$linkid = $link['objIdA'].'-'.$link['objIdB'];
					
					if (!array_key_exists($linkid, $links)) {
						// New link
						$links[$linkid] = $link;
					}
					else {
						// Update existing link
						$links[$linkid]['linkcount']++;
						$links[$linkid]['linktype'] = graphVisGetFastestLinktype($links[$linkid]['linktype'], $link['linktype']);
						$links[$linkid]['portsNames'][$portsNamesID] = $link['portsNames'][$portsNamesID];
					}
				}
			}
		}

		if ($hideChildren)
			$netobjs = array_diff_key($netobjs, $netobjs_children);
		return Array('netobjs' => $netobjs, 'links' => $links, 'tags' => json_encode($networktags));
	}


	function graphvisRenderLogical() {
		include('settings.php');

		$tags = graphvisGetTags(getConfigVar('GRAPHVIS_LOGICAL_TAGS_ROOT'));

		// Retrieve network data
		$objs = graphVisGetLogicalData();

		// Tag filter list
		$jstags = $objs['tags'];
		
		$jsnodes = Array();
		foreach($objs['netobjs'] as $netobj) {
			$jsnode  = "{";
			// Vis.js attributes
			$jsnode .= "\n\t\t\t\t\tid: '{$netobj['id']}',";
			$jsnode .= "\n\t\t\t\t\tlabel: '{$netobj['name']}',";
			$jsnode .= "\n\t\t\t\t\ttitle: '{$netobj['row_name']}',";
			$jsnode .= "\n\t\t\t\t\tgroup: '{$NETOBJ_JS_GROUPS[$netobj['objtype_id']]}',";
			// graphvis attributes
			$jsnode .= "\n\t\t\t\t\ttags: ".json_encode($netobj['tags']).",";
			$jsnode .= "\n\t\t\t\t}";
			$jsnodes[] = $jsnode;
		}
		$jsnodelist = implode(",\n\t\t\t\t", $jsnodes);
		
		$jsedges = Array();
		foreach($objs['links'] as $link) {
			$portstable = "<table border=\"1\"><thead><tr><th>{$objs['netobjs'][$link['objIdA']]['name']}</th><th>{$objs['netobjs'][$link['objIdB']]['name']}</th></tr></thead><tbody>";
			foreach($link['portsNames'] as $ports) {
				$portstable .= "<tr><td>{$ports[0]}</td><td>{$ports[1]}</td></tr>";
			}
			$portstable .= "</tbody></table>";
			$jsedge  = "{";
			// Vis.js attributes
			$jsedge .= "\n\t\t\t\t\tfrom: {$link['objIdA']},";
			$jsedge .= "\n\t\t\t\t\tto: {$link['objIdB']},";
			$jsedge .= "\n\t\t\t\t\tcolor: {color:'{$link['linktype']['color']}'},";
			$jsedge .= "\n\t\t\t\t\twidth: {$link['linktype']['width']},";
			$jsedge .= "\n\t\t\t\t\tlabel:'".($link['linkcount']/2)."',";
			$jsedge .= "\n\t\t\t\t\ttitle: htmlTitle(";
			$jsedge .= "\n\t\t\t\t\t\t'Type : {$link['linktype']['type']}<BR />' + ";
			$jsedge .= "\n\t\t\t\t\t\t'Fastest speed : {$link['linktype']['humanspeed']}<BR />' + ";
			$jsedge .= "\n\t\t\t\t\t\t'Number of links : ".($link['linkcount']/2)."<BR/>' + ";
			$jsedge .= "\n\t\t\t\t\t\t'<BR/>$portstable'";
			$jsedge .= "\n\t\t\t\t\t),";
			$jsedge .= "\n\t\t\t\t\tfont: {background: '#ffffff'},";
			// graphvis attributes
			$jsedge .= "\n\t\t\t\t\tlinkid: '{$link['linktype']['id']}',";
			$jsedge .= "\n\t\t\t\t\tlinktype: '{$link['linktype']['type']}',";
			$jsedge .= "\n\t\t\t\t\tlinkspeed: {$link['linktype']['speed']},";
			$jsedge .= "\n\t\t\t\t\tlinkhumanspeed: '{$link['linktype']['humanspeed']}',";
			$jsedge .= "\n\t\t\t\t}";
			$jsedges[] = $jsedge;
		}
		$jsedgelist = implode(",\n\t\t\t\t", $jsedges);

		graphvisRenderLogicalHTML($jstags, $jsnodelist, $jsedgelist);
	}
	
	
	function graphVisGetPhysicalData() {
		include('settings.php');
		
		$racks = getRackSearchResult('%');
		$rows = getRowSearchResult('%');
		$locations = getLocationSearchResult('%');
		$netobjs = graphvisGetPatchPanels();
		$links = array();
		
		foreach ($rows as &$row) {
			$rowlocation = $locations[$row['location_id']];
			$row['location_name'] = $rowlocation['name'];
			if ($rowlocation['refcnt'] < 2) {
				$row['name'] = $rowlocation['name'];
			}
		}

		foreach($netobjs as &$netobj) {
			// Register location, row and rack names in netobj
			$netobj['rack_name'] = $racks[$netobj['rack_id']]['name'];
			$netobj['row_name'] = $racks[$netobj['rack_id']]['row_name'];
			$netobj['row_id'] = $racks[$netobj['rack_id']]['row_id'];
			$netobj['location_name'] = $racks[$netobj['rack_id']]['location_name'];
			
			$ports = graphvisGetPorts($netobj['id'], FALSE);
			foreach($ports as $port) {
				$back = lm_getPortInfo($port['id'], TRUE)[0];
				
				if (is_null($back['remote_object_id'])) {
					continue;
				}
				$portA = $back['id'];
				$portB = $back['remote_id'];
				$objIdA = $back['object_id'];
				$objIdB = $back['remote_object_id'];
				
				
				// Ensure remote object is another netobject in our filtered objects list
				if ($objIdA != $objIdB && array_key_exists($objIdA, $netobjs) && array_key_exists($objIdB, $netobjs)) {
					// Ensure remote object is in another row
					$rowidA = $racks[$netobjs[$objIdA]['rack_id']]['row_id'];
					$rowidB = $racks[$netobjs[$objIdB]['rack_id']]['row_id'];
					if ($rowidA == $rowidB) {
						continue;
					}

					// Retrieve if connected on front on both sides
					$front = lm_getPortInfo($portA, FALSE)[0];
					$frontRemote = lm_getPortInfo($portB, FALSE)[0];
					$linkused = 0;
					$linkfree = 0;
					$linkpartial = 0;
					
					$isPortAConnectedOrReserved = ($front['remote_id'] || $front['reservation_comment']);
					$isPortBConnectedOrReserved = ($frontRemote['remote_id'] || $frontRemote['reservation_comment']);
					
					if ($isPortAConnectedOrReserved && $isPortBConnectedOrReserved) {
						// Connected or reserved on both sides
						$linkused = 1;
					}
					else if ( ($isPortAConnectedOrReserved && !$isPortBConnectedOrReserved) || (!$isPortAConnectedOrReserved && $isPortBConnectedOrReserved) ) {
						// Connected or reserved only on one side
						$linkpartial = 1;
					}
					else {
						// Unconnected on both sides
						$linkfree = 1;
					}
					


					if (array_key_exists($back['oif_name'], $PHYSICALLINKTYPES))
						$linktype = $PHYSICALLINKTYPES[$back['oif_name']];
				      	else
						$linktype = $PHYSICALLINKTYPES[NULL];
					$linktype['humanspeed'] = graphvisGetHumanSpeed($linktype['speed']);
						
					$link = [
						'objIdA'      => min($rowidA, $rowidB),
						'objIdB'      => max($rowidA, $rowidB),
						'linktype'    => $linktype,
						'linkcount'   => 1,
						'linkused'    => $linkused,
						'linkfree'    => $linkfree,
						'linkpartial' => $linkpartial,
					];
					$linkid = $link['objIdA'].'-'.$link['objIdB'].'-'.$link['linktype']['id'];
					
					if (!array_key_exists($linkid, $links)) {
						// New link
						$links[$linkid] = $link;
					}
					else {
						// Update existing link
						$links[$linkid]['linkcount']++;
						$links[$linkid]['linkused'] += $linkused;
						$links[$linkid]['linkfree'] += $linkfree;
						$links[$linkid]['linkpartial'] += $linkpartial;
					}
				}
			}
		}

		return Array('netobjs' => $rows, 'links' => $links);
	}
	

	function graphvisRenderPhysical() {
		$excludeRows = getConfigVar('GRAPHVIS_PHYSICAL_ROWSTOEXCLUDE');
		
		// Retrieve network data
		$objs = graphVisGetPhysicalData();

		$jsnodes = Array();
		foreach($objs['netobjs'] as $netobj) {
			$jsnode  = "{";
			$jsnode .= "\n\t\t\t\t\tid: '{$netobj['id']}',";
			$jsnode .= "\n\t\t\t\t\tlabel: '{$netobj['name']}',";
			$jsnode .= "\n\t\t\t\t\ttitle: htmlTitle('ID : {$netobj['id']}<br />Location : {$netobj['location_name']}')";
			$jsnode .= "\n\t\t\t\t}";
			$jsnodes[] = $jsnode;
		}
		$jsnodelist = implode(",\n\t\t\t\t", $jsnodes);
		
		$jsedges = Array();
		foreach($objs['links'] as $link) {
			$jsedge  = "{";
			// Vis.js attributes
			$jsedge .= "\n\t\t\t\t\tfrom: {$link['objIdA']},";
			$jsedge .= "\n\t\t\t\t\tto: {$link['objIdB']},";
			$jsedge .= "\n\t\t\t\t\tcolor: {color:'{$link['linktype']['color']}'},";
			$jsedge .= "\n\t\t\t\t\twidth: {$link['linktype']['width']},";
			$jsedge .= "\n\t\t\t\t\tlabel:'".($link['linkcount']/2)."',";
			$jsedge .= "\n\t\t\t\t\ttitle: htmlTitle(";
			$jsedge .= "\n\t\t\t\t\t\t'Type : {$link['linktype']['type']}<BR />' + ";
			$jsedge .= "\n\t\t\t\t\t\t'Fastest speed : {$link['linktype']['humanspeed']}<BR />' + ";
			$jsedge .= "\n\t\t\t\t\t\t'Number of links : ".($link['linkcount']/2)."<BR />' + ";
			$jsedge .= "\n\t\t\t\t\t\t'Number of links used : ".($link['linkused']/2)."<BR />' + ";
			$jsedge .= "\n\t\t\t\t\t\t'Number of links free : ".($link['linkfree']/2)."<BR />' + ";
			$jsedge .= "\n\t\t\t\t\t\t'Number of links connected only at one side : ".($link['linkpartial']/2)."'";
			$jsedge .= "\n\t\t\t\t\t),";
			$jsedge .= "\n\t\t\t\t\tfont: {background: '#ffffff'},";
			// graphvis attributes
			$jsedge .= "\n\t\t\t\t\tlinkid: '{$link['linktype']['id']}',";
			$jsedge .= "\n\t\t\t\t\tlinktype: '{$link['linktype']['type']}',";
			$jsedge .= "\n\t\t\t\t\tlinkspeed: {$link['linktype']['speed']},";
			$jsedge .= "\n\t\t\t\t\tlinkhumanspeed: '{$link['linktype']['humanspeed']}',";
			$jsedge .= "\n\t\t\t\t\tlinkcounttotal: ".($link['linkcount']/2).",";
			$jsedge .= "\n\t\t\t\t\tlinkcountused: ".($link['linkused']/2).",";
			$jsedge .= "\n\t\t\t\t\tlinkcountfree: ".($link['linkfree']/2).",";
			$jsedge .= "\n\t\t\t\t\tlinkcountpartial: ".($link['linkpartial']/2).",";
			$jsedge .= "\n\t\t\t\t}";
			$jsedges[] = $jsedge;
		}
		$jsedgelist = implode(",\n\t\t\t\t", $jsedges);

		graphvisRenderPhysicalHTML($excludeRows, $jsnodelist, $jsedgelist);
	}

	
	function graphVisGetFastestLinktype($linktypeA, $linktypeB) {
		if ( $linktypeA['speed'] > $linktypeB['speed'] ) {
			$linktype = $linktypeA;
		}
		else {
			$linktype = $linktypeB;
		}
		return $linktype;
	}


	function graphVisGetSlowestLinktype($portA, $portB) {
		include('settings.php');
		$linktype = $LOGICALLINKTYPES[NULL];
		$linktypeA = $LOGICALLINKTYPES[NULL];
		$linktypeB = $LOGICALLINKTYPES[NULL];

		if ( array_key_exists($portA['oif_name'], $LOGICALLINKTYPES) ) {
			$linktypeA = $LOGICALLINKTYPES[$portA['oif_name']];
		}
		if ( array_key_exists($portB['oif_name'], $LOGICALLINKTYPES) ) {
			$linktypeB = $LOGICALLINKTYPES[$portB['oif_name']];
		}

		if ( $linktypeA['speed'] < $linktypeB['speed'] ) {
			$linktype = $linktypeA;
		}
		else {
			$linktype = $linktypeB;
		}

		$linktype['humanspeed'] = graphvisGetHumanSpeed($linktype['speed']);
		return $linktype;
	}


	function graphvisGetPorts($objid, $limitToConnected=TRUE) {
		$objs = getObjectPortsAndLinks($objid);
		if ( $limitToConnected == FALSE ) {
			return $objs;
		}

		$filtererdobjs = Array();
		foreach($objs as $id => $obj) {
			if (isset($obj['remote_object_id'])) {
				$filtererdobjs[$id] = $obj;
			}
		}

		return $filtererdobjs;
	}


	function graphvisGetTags($parentTag="") {
		$tags = getTagList();
		if ($parentTag != "") {
			// Find ParentId
			$parentId = NULL;
			foreach($tags as $tag) {
				if ($tag['tag'] == $parentTag) {
					$parentId = $tag['id'];
					break;
				}
			}
			if (is_null($parentId)) {
				return Array();
			}

			// Filter tags
			$newtags = Array();
			foreach($tags as $tag) {
				if ($tag['parent_id'] == $parentId) {
					$newtags[$tag['id']] = $tag;
				}
			}

			$tags = $newtags;
		}
		return $tags;
	}


	function graphvisGetPatchPanels($tagfilter="") {
		/* 9 : 'PatchPanel' */
		$filter = '{$typeid_9}';
		if ($tagfilter != "") {
			$filter = "($filter) and {{$tagfilter}}";
		}
		
		$objs = scanRealmByText('object', $filter);
		return $objs;
	}


	function graphvisGetNetObjects($tagfilter="") {
		include('settings.php');
		$filter = '{$typeid_'.implode('} or {$typeid_', array_keys($NETOBJ_JS_GROUPS)).'}';

		if ($tagfilter != "") {
			$filter = "($filter) and {{$tagfilter}}";
		}
		
		$objs = scanRealmByText('object', $filter);
		return $objs;
	}


	function graphvisGetHumanSpeed($speedbps) {
		$speeds = [
			1000000000000000000000000 => "Ybps",
			1000000000000000000000 => "Zbps",
			1000000000000000000 => "Ebps",
			1000000000000000 => "Pbps",
			1000000000000 => "Tbps",
			1000000000 => "Gbps",
			1000000 => "Mbps",
			1000 => "kbps",
		];

		foreach($speeds as $factor => $unit) {
			if ($speedbps >= $factor)
				return ($speedbps/$factor) . $unit;
		}

		return $speedbps . "bps";
	}


	function graphvisPrintObjs($objs) {
		foreach($objs as $id => $obj) {
			graphvisPrintObj($obj, $id);
		}
	}
	
	
	function graphvisPrintObj($obj, $id=NULL) {
		echo "<pre style='background-color: #eee'>\n";
		if (! is_null($id)) {
			echo "$id - ";
		}
		print_r($obj);
		echo "\n</pre>\n";
	}


	function graphvisRenderLogicalHTML($jstags, $jsnodelist, $jsedgelist) {
		include('settings.php');
		$DEFAULTLOGICALTAGFILTER = getConfigVar('GRAPHVIS_DEFLT_LOGICAL_TAGFILTER');
		$now = date("d/m/Y H:i");

		// Generate groups
		$groups="";
		foreach($NETOBJS_DISPLAY_SETTINGS as $group => $settings) {
			$groups .= "\t\t\t\t\t$group: {\n";
			$groups .= "\t\t\t\t\t\tfont: {\n";
			$groups .= "\t\t\t\t\t\t\tsize: ".$settings['font-size'].",\n";
			$groups .= "\t\t\t\t\t\t},\n";
			$groups .= "\t\t\t\t\t\tcolor: {\n";
			$groups .= "\t\t\t\t\t\t\tborder: '".$settings['border']."',\n";
			$groups .= "\t\t\t\t\t\t\tbackground: '".$settings['background']."',\n";
			$groups .= "\t\t\t\t\t\t\thighlight: {\n";
			$groups .= "\t\t\t\t\t\t\t\tborder: '".$settings['highlight-border']."',\n";
			$groups .= "\t\t\t\t\t\t\t\tbackground: '".$settings['highlight-background']."',\n";
			$groups .= "\t\t\t\t\t\t\t},\n";
			$groups .= "\t\t\t\t\t\t\thover: {\n";
			$groups .= "\t\t\t\t\t\t\t\tborder: '".$settings['hover-border']."',\n";
			$groups .= "\t\t\t\t\t\t\t\tbackground: '".$settings['hover-background']."',\n";
			$groups .= "\t\t\t\t\t\t\t},\n";
			$groups .= "\t\t\t\t\t\t},\n";
			$groups .= "\t\t\t\t\t},\n";
		}

		echo <<<EOL

		<div>
			<fieldset style="display: flex; flex-flow: row wrap; align-items: flex-start; padding:15px;">
				<legend>Parameters:</legend>

				<div style="margin: 0 1em;">
					<label for="tagfilter">Tag filter:</label> <br />
					<select id="tagfilter" name="tagfilter"></select>
				</div>
				
				<div style="margin: 0 1em;">
					<label style="margin: 5px 10px 5px 0;" for="nodelist" style="vertical-align: top;">Nodes to hide</label> <br />
					<select class="multiplelist" style="margin: 5px 10px 5px 0; padding: 10px;" id="nodelist" name="nodelist[]" multiple size="10"></select>
				</div>
				
				<div style="margin: 0 1em;">
					<label style="margin: 5px 10px 5px 0;" for="hideOrphanNodes" style="vertical-align: top;">Hide unlinked nodes</label> <br />
					<input style="margin: 5px 10px 5px 0; padding: 10px;" type="checkbox" id="hideOrphanNodes" name="hideOrphanNodes" style="vertical-align: top;" />
				</div>
				
				<div style="margin: 0 1em;">
					<label style="margin: 5px 10px 5px 0;" for="linktypes" style="vertical-align: top;">Link types to hide</label> <br />
					<select class="multiplelist" style="margin: 5px 10px 5px 0; padding: 10px;" id="linktypes" name="linktypes[]" multiple size="10"></select>
				</div>
				
				<div style="margin: 0 1em;">
					<label style="margin: 5px 10px 5px 0;" for="linkspeeds" style="vertical-align: top;">Link speeds to hide</label> <br />
					<select class="multiplelist" style="margin: 5px 10px 5px 0; padding: 10px;" id="linkspeeds" name="linkspeeds[]" multiple size="10"></select>
				</div>
				
				<div style="margin: 0 1em;">
					<input type="button" id="updateMap" name="updateMap" value="Update topo" onclick="buildNetwork()">
				</div>
			</fieldset>
		</div>

		<div id="graphvisContainer">
			<div class="graphvisDate">$now</div>
			<div id="graphvisLogicalNetwork"></div>
		</div>
		
		<script type="text/javascript">
			function htmlTitle(html) {
				const container = document.createElement("div");
				container.innerHTML = html;
				return container;
			}

			var tags = $jstags;
			var defaulttag = "$DEFAULTLOGICALTAGFILTER";
			
			var hiddenNodes = [];

			var allnodes = [
				$jsnodelist
			];
			
			var alledges = [
				$jsedgelist
			];

			var dsoptions = {queue: true};
			var nodes = new vis.DataSet(allnodes, dsoptions);
			var edges = new vis.DataSet(alledges, dsoptions);

			// create a network
			var container = document.getElementById('graphvisLogicalNetwork');
			var data = {
				nodes: nodes,
				edges: edges
			};
			var options = {
				autoResize: false,
				nodes: {
					shape: 'box',
					size: 30,
					font: {
						size: 12,
						color: '#000000'
					},
					borderWidth: 2
				},
				edges: {
					width: 1
				},
				layout: {
					randomSeed: 0
				},
				groups: {
					// useDefaultGroups: false,
$groups
				}
			};
			network = null;
		</script>
		
		<script>
			function deleteOrphanNodes() {
				edges.flush();
				nodes.flush();
				nodes.forEach(function(node) {
					if (network.getConnectedEdges(node.id).length == 0) {
						nodes.remove(node.id);
					}
				});
			}
			
			function deleteUntaggedNodes() {
				var tag = parseInt($("#tagfilter").val(), 10);
				
				if (tag == -1) {
					return;
				}
				
				nodes.flush();
				nodes.forEach(function(node) {
					if ( $.inArray(tag, node.tags) < 0 ) {
						nodes.remove(node.id);
					}
				});
			}
			
			function buildNetwork() {
				nodes.clear()
				edges.clear()
				nodes.add(allnodes);
				edges.add(alledges);
				
				// Delete nodes to hide
				$('#nodelist > option:selected').each(function() {
					nodes.remove($(this).val());
				});
				
				deleteUntaggedNodes();
				
				edges.flush();
				
				// Link types
				$('#linktypes > option:selected').each(function() {
					var selectedItem = $(this).val();
					var edgesToDelete = edges.getIds({
						filter: function (item) {
							return (item.linktype == selectedItem);
						}
					});
					edges.remove(edgesToDelete);
				});
				
				// Link speeds
				$('#linkspeeds > option:selected').each(function() {
					var selectedItem = $(this).val();
					var edgesToDelete = edges.getIds({
						filter: function (item) {
							return (item.linkhumanspeed == selectedItem);
						}
					});
					edges.remove(edgesToDelete);
				});
				
				// Delete unconnected nodes
				if ( $('#hideOrphanNodes').is(':checked') ) {
					deleteOrphanNodes();
				}
				
				nodes.flush();
				edges.flush();
				
				network = new vis.Network(container, data, options);
				network.on("doubleClick", function (params) {
					if (params.nodes.length == 1) {
						var node = nodes.get(params.nodes[0]);
						window.open('index.php?page=object&object_id=' + node.id);
					}
					else if (params.edges.length == 1) {
						var edge = edges.get(params.edges[0]);
						window.open('index.php?page=object&tab=linkmgmt&object_id=' + edge.from);
					}
      				});
			}

			function fillTags() {
				var select = $("#tagfilter");
				$.each(tags, function(id, text) {
					select.append($("<option />").val(id).text(text));
				});
				
				// sort list
				select.html(select.find('option').sort(function(x, y) {
					// to change to descending order switch "<" for ">"
					return $(x).text() > $(y).text() ? 1 : -1;
				}));
				
				// select default tag
				select.val(defaulttag);
			}

			function fillLinkTypes() {
				var select = $("#linktypes");
				$.each(edges.distinct('linktype').sort(), function(i, item) {
					select.append($("<option />").val(item).text(item));
				});
			}

			function fillLinkSpeeds() {
				var select = $("#linkspeeds");
				$.each(edges.distinct('linkhumanspeed').sort(), function(i, item) {
					select.append($("<option />").val(item).text(item));
				});
			}
			
			// Increase canvas resolution before printing
			window.onbeforeprint = function() {
				network.setSize('5840px', '4000px');
				network.redraw();
			};
			
			// Restore canvas resolution after printing
			window.onafterprint = function() {
				network.setSize('1188px', '840px');
				network.redraw();
			};
			
			
			$(document).ready(function() {
				var nodelist = $("#nodelist");
				nodes.forEach(function(node) {
					nodelist.append($("<option />").val(node.id).text(node.label));
				}, {order: 'label'});
				
				hiddenNodes.forEach(function(nodeId) {
					$("#nodelist > option:[value='" + nodeId + "']").attr('selected', true);
				});
				
				fillTags();
				
				buildNetwork();
				
				fillLinkTypes();
				fillLinkSpeeds();
				
				// Avoid CTRL use for list selection
				$('.multiplelist > option').mousedown(function(e) {
					e.preventDefault();
					$(this).attr('selected', !$(this).attr('selected'));
					return false;
				});
				
			});
      </script>
EOL;
	}

	



	function graphvisRenderPhysicalHTML($excludeRows, $jsnodelist, $jsedgelist) {
		$now = date("d/m/Y H:i");
		echo <<<EOL

		<div>		
			<fieldset style="display: flex; flex-flow: row wrap; align-items: flex-start; padding:15px;">
				<legend>Parameters:</legend>
				
				<div style="margin: 0 1em;">
					<label style="margin: 5px 10px 5px 0;" for="nodelist" style="vertical-align: top;">Nodes to hide</label> <br />
					<select style="margin: 5px 10px 5px 0; padding: 10px;" id="nodelist" name="nodelist[]" multiple size="10"></select>
				</div>
				
				<div style="margin: 0 1em;">
					<label style="margin: 5px 10px 5px 0;" for="hideOrphanNodes" style="vertical-align: top;">Hide unlinked nodes</label> <br />
					<input style="margin: 5px 10px 5px 0; padding: 10px;" type="checkbox" id="hideOrphanNodes" name="hideOrphanNodes" style="vertical-align: top;" />
				</div>
				
				<div style="margin: 0 1em;">
					<label style="margin: 5px 10px 5px 0;" for="linktypes" style="vertical-align: top;">Link types to hide</label> <br />
					<select style="margin: 5px 10px 5px 0; padding: 10px;" id="linktypes" name="linktypes[]" multiple size="10"></select>
				</div>
				
				<div style="margin: 0 1em;">
					<label style="margin: 5px 10px 5px 0;" for="linkspeeds" style="vertical-align: top;">Link speeds to hide</label> <br />
					<select style="margin: 5px 10px 5px 0; padding: 10px;" id="linkspeeds" name="linkspeeds[]" multiple size="10"></select>
				</div>
				
				<div style="margin: 0 1em;">
					<input type="button" id="updateMap" name="updateMap" value="Update topo" onclick="buildNetwork()">
				</div>
			</fieldset>
		</div>

		<div id="graphvisContainer">
                        <div class="graphvisDate">$now</div>
                        <div id="graphvisPhysicalNetwork"></div>
                </div>
		
		<script type="text/javascript">
			function htmlTitle(html) {
				const container = document.createElement("div");
				container.innerHTML = html;
				return container;
			}

			var hiddenNodes = [$excludeRows];
			var allnodes = [
				$jsnodelist
			];
			
			var alledges = [
				$jsedgelist
			];

			var dsoptions = {queue: true};
			var nodes = new vis.DataSet(allnodes, dsoptions);
			var edges = new vis.DataSet(alledges, dsoptions);

			// create a network
			var container = document.getElementById('graphvisPhysicalNetwork');
			var data = {
				nodes: nodes,
				edges: edges
			};
			var options = {
				autoResize: false,
				nodes: {
					shape: 'box',
					size: 30,
					font: {
						size: 18,
						color: '#000000'
					},
					borderWidth: 2
				},
				edges: {
					width: 1
				},
				layout: {
					randomSeed: 0
				}
			};
			var network = null;
		</script>
		
		<script>
			function deleteOrphanNodes() {
				edges.flush();
				nodes.flush();
				nodes.forEach(function(node) {
					if (network.getConnectedEdges(node.id).length == 0) {
						nodes.remove(node.id);
					}
				});
			}
			
			function buildNetwork() {
				nodes.clear()
				edges.clear()
				nodes.add(allnodes);
				edges.add(alledges);
				
				// Delete nodes to hide
				$('#nodelist > option:selected').each(function() {
					nodes.remove($(this).val());
				});
				
				edges.flush();
				
				// Link types
				$('#linktypes > option:selected').each(function() {
					var selectedItem = $(this).val();
					var edgesToDelete = edges.getIds({
						filter: function (item) {
							return (item.linktype == selectedItem);
						}
					});
					edges.remove(edgesToDelete);
				});
				
				// Link speeds
				$('#linkspeeds > option:selected').each(function() {
					var selectedItem = $(this).val();
					var edgesToDelete = edges.getIds({
						filter: function (item) {
							return (item.linkhumanspeed == selectedItem);
						}
					});
					edges.remove(edgesToDelete);
				});
				
				// Delete unconnected nodes
				if ( $('#hideOrphanNodes').is(':checked') ) {
					deleteOrphanNodes();
				}
				
				nodes.flush();
				edges.flush();
				
				network = new vis.Network(container, data, options);
				network.on("doubleClick", function (params) {
					if (params.nodes.length == 1) {
						var node = nodes.get(params.nodes[0]);
						window.open('index.php?page=row&row_id=' + node.id);
					}
      				});
			}

			function fillLinkTypes() {
				var linktypeslist = $("#linktypes");
				$.each(edges.distinct('linktype').sort(), function(i, item) {
					linktypeslist.append($("<option />").val(item).text(item));
				});
			}

			function fillLinkSpeeds() {
				var linktypeslist = $("#linkspeeds");
				$.each(edges.distinct('linkhumanspeed').sort(), function(i, item) {
					linktypeslist.append($("<option />").val(item).text(item));
				});
			}
			
			// Increase canvas resolution before printing
			window.onbeforeprint = function() {
				network.setSize('5840px', '4000px');
				network.redraw();
			};
			
			// Restore canvas resolution after printing
			window.onafterprint = function() {
				network.setSize('1188px', '840px');
				network.redraw();
			};
			
			
			$(document).ready(function() {
				var nodelist = $("#nodelist");
				nodes.forEach(function(node) {
					nodelist.append($("<option />").val(node.id).text(node.label));
				}, {order: 'label'});
				
				hiddenNodes.forEach(function(nodeId) {
					$("#nodelist > option:[value='" + nodeId + "']").attr('selected', true);
				});
				
				buildNetwork();
				
				fillLinkTypes();
				fillLinkSpeeds();
				
				// Avoid CTRL use for list selection
				$('option').mousedown(function(e) {
					e.preventDefault();
					$(this).attr('selected', !$(this).attr('selected'));
					return false;
				});
				
			});
      </script>
EOL;
	}
?>
