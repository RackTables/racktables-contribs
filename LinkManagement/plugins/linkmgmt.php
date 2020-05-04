<?php
// TODO linkchain cytoscape create libs?
//	linkchain all objects graph cytoscape takes ages
//	highlight port cytoscape maps
/*
 * Link Management for RT >= 0.20.11
 *
 *	Features:
 *		- create links between ports
 *		- create backend links between ports
 *		- visually display links / chains
 *			 e.g.
 * 			(Object)>[port] -- front --> [port]<(Object) == back == > (Object)>[port] -- front --> [port]<(Object)
 * 		- Link object backend ports by name (e.g. handy for patch panels)
 *		- change/create CableID
 *		- change/create Port Reservation Comment
 *		- multiple backend links for supported port types (e.g. AC-in, DC)
 *		- GraphViz Maps (Objects, Ports and Links) (needs GraphViz_Image 1.3.0)
 *			- object,port or link  highligthing (just click on it)
 *			- context menu to link and unlink ports
 *
 *	Usage:
 *		1. select "Link Management" tab
 *		2. you should see link chains of all linked ports
 *		3. to display all ports hit "Show All Ports" in the left upper corner
 *		4. to link all ports with the same name of two different objects use "Link Object Ports by Name"
 *			a. select the other object you want to backend link to
 *			b. "show back ports" gives you the list of possible backend links
 *				!! Important port names have to be the same on both objects !!
 *				e.g. (Current Object):Portname -?-> Portname:(Selected Object)
 *			c. select all backend link to create (Ctrl + a for all)
 *			d. Enter backend CableID for all selected links
 *			e. "Link back" create the backend links
 *		5. If you have an backend link within the same Object the link isn't shown until
 *		   "Expand Backend Links on same Object" is hit
 *		6. "Object Map" displays Graphviz Map of current object
 *		7. To get a Graphviz Map of a single port click the port name on the left
 *
 *
 * Changes:
 *	switch to new linkchain class
 *		- nicer looking graphviz maps
 *		- better multilink support
 *
 *	add cytoscape.js maps
 *		- path higlighting
 *		- zooming
 *
 * Requirements:
 *	PHP 5 (http://php.net/)
 *	GraphViz_Image 1.3.0 or newer (http://pear.php.net/package/Image_GraphViz)
 *		GraphViz (http://www.graphviz.org/)
 *
 *	to user cytoscape js map the following is required:
 *	create plugins/linkmgmt directory and place the following 7 files in it
 *
 *	Cytoscape.js (http://js.cytoscape.org/)
 *		https://raw.githubusercontent.com/cytoscape/cytoscape.js/master/dist/cytoscape.min.js
 *	dagre.js
 *		https://raw.githubusercontent.com/cpettitt/dagre/master/dist/dagre.min.js
 *		https://raw.githubusercontent.com/cytoscape/cytoscape.js-dagre/master/cytoscape-dagre.js
 *	qtip
 *		https://cdnjs.cloudflare.com/ajax/libs/qtip2/2.2.1/jquery.qtip.min.css
 *
 *		https://cdnjs.cloudflare.com/ajax/libs/qtip2/2.2.1/jquery.qtip.min.js
 *		https://raw.githubusercontent.com/cytoscape/cytoscape.js-qtip/master/cytoscape-qtip.js
 *
 *	jquery >= 1.10.0 (qtip requirement)
 *		https://code.jquery.com/jquery-1.11.3.min.js
 *
 * INSTALL:
 *
 *	1. create LinkBackend Table in your RackTables database
 *
 * Multilink table

CREATE TABLE `LinkBackend` (
  `porta` int(10) unsigned NOT NULL DEFAULT '0',
  `portb` int(10) unsigned NOT NULL DEFAULT '0',
  `cable` char(64) DEFAULT NULL,
  PRIMARY KEY (`porta`,`portb`),
  KEY `LinkBackend_FK_a` (`porta`),
  KEY `LinkBackend_FK_b` (`portb`),
  CONSTRAINT `LinkBackend_FK_a` FOREIGN KEY (`porta`) REFERENCES `Port` (`id`) ON DELETE CASCADE,
  CONSTRAINT `LinkBackend_FK_b` FOREIGN KEY (`portb`) REFERENCES `Port` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 collate=utf8_unicode_ci;

 *	2. copy linkmgmt.php to plugins directory
 *
 *	 Ready to go!
 *
 *
 * UPDATE TABLE:
 *
 * Update from non-multilink table
 * ALTER TABLE

ALTER TABLE LinkBackend ADD KEY `LinkBackend_FK_b` (`portb`);
ALTER TABLE LinkBackend DROP INDEX porta;
ALTER TABLE LinkBackend DROP INDEX portb;

 * UPDATE to RT 0.20.7

ALTER TABLE LinkBackend CONVERT to CHARACTER SET utf8 COLLATE utf8_unicode_ci;

 *
 *
 * TESTED on FreeBSD 9.0, nginx/1.0.11, php 5.3.9
 *	GraphViz_Image 1.3.0
 *
 * (c)2012-2017 Maik Ehinger <github138@t-online.de>
 */

/**
 * The newest version of this plugin can be found at:
 *
 * https://github.com/github138/myRT-contribs/tree/develop-0.20.x
 *
 */

/*************************
 * TODO
 *
 * - code cleanups
 * - bug fixing
 *
 * - put selected object/port top left of graph
 * - multlink count for Graphviz maps empty or full dot
 *
 * - csv list
 *
 */

/* DEBUG */
//error_reporting(E_ALL);

$tab['object']['linkmgmt'] = 'Link Management';
$tabhandler['object']['linkmgmt'] = 'linkmgmt_tabhandler';
$trigger['object']['linkmgmt'] = 'trigger_ports';

$ophandler['object']['linkmgmt']['update'] = 'linkmgmt_opupdate';
$ophandler['object']['linkmgmt']['unlinkPort'] = 'linkmgmt_opunlinkPort';
$ophandler['object']['linkmgmt']['PortLinkDialog'] = 'linkmgmt_opPortLinkDialog';
$ophandler['object']['linkmgmt']['Help'] = 'linkmgmt_opHelp';

$ophandler['object']['linkmgmt']['map'] = 'linkmgmt_opmap';
$ajaxhandler['lm_mapinfo'] = 'linkmgmt_ajax_mapinfo';
$ajaxhandler['lm-upd-reservation-cable'] = 'linkmgmt_updateCableIdAJAX';

$ophandler['object']['linkmgmt']['cytoscapemap'] = 'linkmgmt_cytoscapemap';

/* ------------------------------------------------- */

defineIfNotDefined('LM_MULTILINK',TRUE);
defineIfNotDefined('LM_SHOW_CONTAINERS',TRUE);
defineIfNotDefined('LM_EXTEND_PORT_LIST',FALSE);

if (LM_EXTEND_PORT_LIST)
{
	registerHook('renderObjectPortHeaderRow','plugin_linkmgmt_renderObjectPortHeaderRow','before'); // no-op before RT 0.21.2
	registerHook('renderObjectPortRow','plugin_linkmgmt_renderObjectPortRow','before');
}

/* -------------------------------------------------- */

$lm_multilink_port_types = array(
				16, /* AC-in */
				//1322, /* AC-out */
				1399, /* DC */
				);

/* -------------------------------------------------- */

$lm_cache = array(
		'allowcomment' => TRUE, /* RackCode {$op_set_reserve_comment} */
		'allowlink' => TRUE, /* RackCode {$op_set_link} */
		'allowbacklink' => TRUE, /* RackCode {$op_set_backlink} */
		'rackinfo' => array(),
		);

/* -------------------------------------------------- */

//function linkmgmt_tabtrigger() {
//	return 'std';
//} /* linkmgmt_tabtrigger */

function plugin_linkmgmt_renderObjectPortHeaderRow()
{
	// Overloads the renderObjectPortHeaderRow() in order to add the extra volumn header
	// for the last item in the link.

	// Renders the headers for the ports table on the default page

	echo '<tr><th class=tdleft>Local name</th><th class=tdleft>Visible label</th>';
	echo '<th class=tdleft>Interface</th><th class=tdleft>L2 address</th>';
	echo '<th class=tdcenter colspan=2>Remote object and port</th>';
	echo '<th class=tdleft>Cable ID</th>';
	echo '<th class=tdcenter colspan=2>Last Port in the Link</th></tr>';

	stopHookPropagation();
}

function plugin_linkmgmt_renderObjectPortRow ($port, $is_highlighted)
{
	// highlight port name with yellow if its name is not canonical
	$canon_pn = shortenPortName ($port['name'], $port['object_id']);
	$name_class = $canon_pn == $port['name'] ? '' : 'trwarning';

	echo '<tr';
	if ($is_highlighted)
		echo ' class=highlight';
	$a_class = isEthernetPort ($port) ? 'port-menu' : '';
	echo "><td class='tdleft $name_class' NOWRAP><a name='port-${port['id']}' class='interactive-portname nolink $a_class'>${port['name']}</a></td>";
	echo "<td class=tdleft>${port['label']}</td>";
	echo "<td class=tdleft>" . formatPortIIFOIF ($port) . "</td><td class='tdleft l2address'>${port['l2address']}</td>";

	// Build the linkchain to check the last port in the link
	$lc = new lm_linkchain($port['id']);

	if ($port['remote_object_id'])
	{
		// There is a front linked port
		$dname = formatObjectDisplayedName ($port['remote_object_name'], $port['remote_object_tid']);
		echo "<td class=tdleft>" .
			formatPortLink ($port['remote_object_id'], $dname, $port['remote_id'], NULL) .
			"</td>";
		echo "<td class=tdleft>" . formatLoggedSpan ($port['last_log'], $port['remote_name'], 'underline') . "</td>";
		$editable = permitted ('object', 'ports', 'editPort')
			? 'editable'
			: '';
		echo "<td class=tdleft><span class='rsvtext $editable id-${port['id']} op-upd-reservation-cable'>${port['cableid']}</span></td>";

		// Display the last linked port,

		if ($lc->first == $port['id'])
		{
				if ($lc->last != $port['remote_id'])
				{
					// Front and Back linked with a link on the same object
					echo '<td>'.
					formatPortLink($lc->ports[$lc->last]['object_id'], $lc->ports[$lc->last]['object_name'],  $lc->ports[$lc->last]['id'], NULL).
					'</td>'.
					'<td>'.$lc->ports[$lc->last]['name'].'</td>';
				}
				else
				{
					// Front-linked only
					echo '<td>&nbsp;</td><td>&nbsp;</td>';   // End of the row
				}
		}
		else
		{
			if ($lc->first != $port['remote_id'])
			{
				// Port with front and back link
				echo '<td>'.
					formatPortLink($lc->ports[$lc->first]['object_id'], $lc->ports[$lc->first]['object_name'],  $lc->ports[$lc->first]['id'], NULL).
					'</td>'.
					'<td>'.$lc->ports[$lc->first]['name'] . '</td>';
			}
			else
			{
				echo '<td>&nbsp;</td><td>&nbsp;</td>';   // End of the row
			}
		}
	}
	else
	{
		// There's no front linked port

		// First show the port reservation
		echo implode ('', formatPortReservation ($port));

		// Now check if last port in the link is not the same as the current port and display it.
		if ($lc->first != $port['id'])
		{
			// Back-linked port without a front-link
			echo '<td>&nbsp;</td>'.
				'<td>'.
				formatPortLink($lc->ports[$lc->first]['object_id'], $lc->ports[$lc->first]['object_name'],  $lc->ports[$lc->first]['id'], NULL).
				'</td>' .
				'<td>'.$lc->ports[$lc->first]['name'] . '</td>';
		}
		else
		{
			// Port without a front or back link
			echo '<td>&nbsp;</td><td>&nbsp;</td><td>&nbsp;</td>';   // End of the row
		}
	}

	echo "</tr>";

	stopHookPropagation();
}

/* -------------------linkchain stuff------------------------------- */
class linkchain_cache
{
	public $cache = array();

	function getobject($object_id , &$rack = null)
	{
		if(!$object_id)
			return null;

		if(!isset($this->cache['o'.$object_id]))
		{
			$object = spotEntity('object', $object_id);
			$object['IPV4OBJ'] = considerConfiguredConstraint ($object, 'IPV4OBJ_LISTSRC');

			/* get more object info */

			if($object['IPV4OBJ'])
			{
				$object['portip'] = array();
				foreach(getObjectIPv4Allocations ($object_id) as $ipv4)
				{
					$object['portip'][$ipv4['osif']] = $ipv4['addrinfo']['ip'];
				}
			}

			$this->cache['o'.$object_id] = $object;
		}
		else
			$object = $this->cache['o'.$object_id];

		$rack = $this->getrack($object['rack_id']);

		return $object;
	}

	function getrack($rack_id)
	{
		// rack
		if($rack_id)
		{
			if(!isset($this->cache['r'.$rack_id]))
			{
				$rack = spotEntity('rack', $rack_id);
				$this->cache['r'.$rack_id] = $rack;
			}
			else
				$rack = $this->cache['r'.$rack_id];
		}
		else
			$rack = null;

		return $rack;

	}
}

$lc_cache = new linkchain_cache();

/* recursive class */
class lm_linkchain implements Iterator {

	const B2B_LINK_BGCOLOR = '#d8d8d8';
	const CURRENT_PORT_BGCOLOR = '#ffff99';
	const CURRENT_OBJECT_BGCOLOR = '#ff0000';
	const HL_PORT_BGCOLOR = '#A0FFA0';
	const ALTERNATE_ROW_BGCOLOR = '#f0f0f0';

	public $first = null;
	public $last = null;
	public $init = null;
	public $linked = null;
	public $linkcount = 0;

	public $loop = false;
	private $lastipobjport = null;

	public $ports = array();

	private $currentid = null;
	private $back = null;
	public $initback = null;

	//public $cache = null;
	private $object_id = null;

	private $icount = 0;
	public $exceed = false;

	public $initalign = true;

	public $portmulti = 0;
	public $prevportmulti = 0;

	private $initport = false;

	private $lids = array(); // link ids porta portb linktype

	public $pids = null; // port ids
	public $oids = null; // object ids

	/* $back = null follow front and back
	 * 		true follow back only
	 *		false follow front only
	 * $prevport first port
	 */
	function __construct($port_id, $back = null, $prevport = null, $reverse = false, &$pids = null, &$oids = null)
	{
		global $lc_cache;

		$this->init = $port_id;

		$this->initback = $back;

		if($pids === null)
			$this->pids = array();
		else
			$this->pids = &$pids;

		if($oids === null)
			$this->oids = array();
		else
			$this->oids = &$oids;

		if($back !== null)
		{
			$prevport_id = $prevport['id'];
			$prevlinktype =  $this->getlinktype(!$back);
			//if($this->setlinkid($port_id, $prevport_id, $prevlinktype))
			{
				$this->last = $this->_getlinks($port_id, $back, null, $reverse);
			}
			//else
			{
			//	$this->loop = true;
			//	echo "SUBCHAIN LID exists $port_id $prevlinktype $prevport_id<br>";
			}

			// TODO set previous port ..use _set... function()
			$this->ports[$port_id][$this->getlinktype(!$back)]['portcount'] = 1;
			$this->ports[$port_id][$this->getlinktype(!$back)]['linked'] = 1;
			$this->ports[$port_id][$this->getlinktype(!$back)]['remote_id'] = $prevport['id'];
			$this->ports[$port_id][$this->getlinktype(!$back)]['remote_object_id'] = $prevport['object_id'];
			$this->ports[$port_id][$this->getlinktype(!$back)]['remote_name'] = $prevport['name'];
			$this->ports[$port_id][$this->getlinktype(!$back)]['remote_object_name'] = $prevport['object_name'];
			$this->ports[$port_id][$this->getlinktype(!$back)]['cableid'] = $prevport['cableid'];


			$prevport = $this->_setportlink($prevport, $this->getlinktype(!$back));
			$prevport[$this->getlinktype($back)]['portcount'] = null; 
			$prevport[$this->getlinktype($back)]['remote_id'] = null; 
			$prevport[$this->getlinktype($back)]['remote_object_id'] = null; 

			$this->ports[$prevport_id] = $prevport;
			$this->first = $prevport_id;
			$this->linkcount++;

		//	self::var_dump_html($this->ports[$port_id], "PORT");
		//	self::var_dump_html($prevport, "PREVPORT");
			$this->object_id = $_GET['object_id'];
		}
		else
		{
			// Link
			$this->last = $this->_getlinks($port_id, false);

			if(!$this->loop)
				$this->first = $this->_getlinks($port_id, true, null, true);

			$this->object_id = $this->ports[$this->init]['object_id'];
		}

		if(0)
		if($this->loop)
		{
			$linktype = $this->getlinktype($back);
			$prevlinktype = $this->getlinktype(!$back);

			/* set first object */
			$object_id = $this->ports[$port_id]['object_id'];
			$object = $lc_cache->getobject($object_id);

			if($object['IPV4OBJ'])
				$this->lastipobjport = $port_id;

			$this->first = $this->lastipobjport;
		}

		$this->linked = ($this->linkcount > 0);

		if($reverse)
			$this->reverse();

	}

	function setlinkid($porta, $portb, $linktype)
	{
		if($porta > $portb)
			$lid = "${portb}_${porta}";
		else
			$lid = "${porta}_${portb}";

		$lid .= $linktype;

		if(isset($this->lids[$lid]))
			return false;

		$this->lids[$lid] = true;

		return true;
	}

	function hasport_id($port_id)
	{
		if(isset($this->pids[$port_id]))
			return true;
		else
			return false;
	}

	function hasobject_id($object_id)
	{
		if(isset($this->oids[$object_id]))
			return true;
		else
			return false;
	}

	function _setportprevlink($port, $linktype, $prevport)
	{
		$port[$linktype] = array(
					'cableid' => $prevport[$linktype]['cableid'],
					'linked' => $prevport[$linktype]['linked'],
					'portcount' => null, // TODO prev port count
					'remote_id' => $prevport['id'],
					'remote_name' => $prevport['name'],
					'remote_object_id' => $prevport['object_id'],
					'remote_object_name' => $prevport['object_name'],
				);

		return $port;
	}

	function _setportlink($port, $linktype)
	{
		$port[$linktype] = array(
					'cableid' => $port['cableid'],
					'linked' => $port['linked'],
					'portcount' => $port['portcount'],
					'remote_id' => $port['remote_id'],
					'remote_name' => $port['remote_name'],
					'remote_object_id' => $port['remote_object_id'],
					'remote_object_name' => $port['remote_object_name'],
				);

		unset($port['cableid']);
		unset($port['linked']);
		unset($port['portcount']);
		unset($port['remote_id']);
		unset($port['remote_name']);
		unset($port['remote_object_id']);
		unset($port['remote_object_name']);

		return $port;
	}

	function _getportlink($port)
	{
		$linktype = $this->getlinktype($this->back);
		return array_merge($port, $port[$linktype], array('linktype' => $linktype));
	}

	function isback($linktable)
	{
		if($linktable == 'back')
			return true;
		else
			return false;
	}

	function getlinktype($back)
	{
		return ($back ? 'back' : 'front' );
	}

	function reverse()
	{
		$tmp = $this->first;
		$this->first = $this->last;
		$this->last = $tmp;

	}


	//recursive
	function _getlinks($port_id, $back = false, $prevport_id = null, $reverse = false)
	{
		global $lc_cache;
		$linktype = $this->getlinktype($back);

		if($port_id == $this->init)
			$this->initport = true;

		$ports = lm_getPortInfo($port_id, $back);

		$portcount = count($ports);

		$port = $ports[0];

		// check for loops on multilinked ports
		// set main port to looping one
		if($portcount > 1)
			foreach($ports as $mport)
			{
				if(isset($this->ports[$mport['remote_id']]))
				{
					$port = $mport;
					break;
				}
			}

		$remote_id = $port['remote_id'];

		$port['portcount'] = $portcount;

		$object_id =  $port['object_id'];

		$rack = null;
		$object = $lc_cache->getobject($object_id, $rack);

		$this->oids[$object_id] = true;

		if($object['IPV4OBJ'])
			$this->lastipobjport = $port_id;

		if($object)
			if(isset($object['portip'][$port['name']]))
				$port['portip'] = $object['portip'][$port['name']];

		$port = $this->_setportlink($port, $linktype);

		if($prevport_id)
		{
			$prevlinktype =  $this->getlinktype(!$back);
			$port = $this->_setportprevlink($port, $prevlinktype, $this->ports[$prevport_id]);
		}

		//if(!$back)
		{
			if($prevport_id)
			{
				/* mutlilink: multiple previous links */
				$prevports = lm_getPortInfo($port_id, !$back);

				$prevportcount = count($prevports);

				if($prevportcount > 1)
				{
					if($this->initport)
						$this->initalign = false;

					$port[$this->getlinktype(!$back)]['portcount'] = $prevportcount;

					$lcs = array();
					foreach($prevports as $mport)
					{
						if($prevport_id != $mport['remote_id'])
						{
							if(isset($this->pids[$mport['remote_id']]))
								continue;

							$mport['portcount'] = 1;
							$lc = new lm_linkchain($mport['remote_id'], $back, $mport, !$reverse, $this->pids, $this->oids);
							$lcs[$mport['remote_id']] = $lc;
							$this->linkcount += $lc->linkcount;
						}
					}

					$port[$this->getlinktype(!$back)]['chains'] = $lcs;
				}
			}
		}

		if($portcount > 1)
		{
			/* mutlilink: multiple links */

			$lcs = array();
			foreach($ports as $mport)
			{
				if($remote_id != $mport['remote_id'])
				{
					if(isset($this->pids[$mport['remote_id']]))
						continue;

					$mport['portcount'] = 1;
					$lc = new lm_linkchain($mport['remote_id'], !$back, $mport, $reverse, $this->pids, $this->oids);
					$lcs[$mport['remote_id']] = $lc; 
					$this->linkcount += $lc->linkcount;
				}
			}

			$port[$linktype]['chains'] = $lcs;

		}

		if(isset($this->ports[$port_id]))
		{
			if(!isset($this->ports[$port_id][$linktype]))
			{
				$this->ports[$port_id][$linktype] = $port[$linktype];
			}
		}
		else
			$this->ports[$port_id] = $port;

		$this->pids[$port_id] = true;

		if($remote_id)
		{
			if($this->setlinkid($port_id, $remote_id, $linktype))
			{
				$this->linkcount++;
				return $this->_getlinks($remote_id, !$back, $port_id, $reverse);
			}
			else
			{
				$prevlinktype =  $this->getlinktype(!$back);
				$this->loop = true;
				if(isset($port[$prevlinktype]))
					$this->ports[$port_id][$prevlinktype] = $port[$prevlinktype];

				$this->first = $remote_id;
			}
		}

		return $port_id;
	}

	// TODO
	function getchaintext()
	{
		//$this::var_dump_html($this->ports);
		$chain = "";
		foreach($this as $id => $port)
		{
			$linktype = $port['linktype']; //$this->getlinktype();
			if($linktype == 'front')
				$arrow = ' --> ';
			else
				$arrow = ' => ';

			$text = $port['object_name']." [".$port['name']."]";

			if($id == $this->init)
				$chain .= "*$text*";
			else
				$chain .= "$text";

			$remote_id = $port['remote_id'];

			if($remote_id)
				$chain .= $arrow;

			if($this->loop && $remote_id == $this->first)
			{
				$chain .= "LOOP!";
				break;
			}
		}
		return $chain;
	}

	function getchainlabeltrstart($rowbgcolor = '#ffffff')
	{

		$port_id = $this->init;

		$initport = $this->ports[$port_id];

		$urlparams = array(
				'module' => 'redirect',
				'page' => 'object',
				'tab' => 'linkmgmt',
				'op' => 'map',
				'usemap' => 1,
				'object_id' => $initport['object_id'],
				);

		$hl_port_id = NULL;
		if($hl_port_id !== NULL)
			$urlparams['hl_port_id'] = $hl_port_id;
		else
			$urlparams['port_id'] = $port_id;

		$title = "linkcount: ".$this->linkcount."\nTypeID: ${initport['oif_id']}\nPortID: $port_id";

		$onclick = 'onclick=window.open("'.makeHrefProcess(array_merge($_GET, $urlparams)).'","Map","height=500,width=800,scrollbars=yes");';

		if($hl_port_id == $port_id)
		{
			$hlbgcolor = "bgcolor=".self::HL_PORT_BGCOLOR;
		}
		else
			$hlbgcolor = "bgcolor=$rowbgcolor";

		if($rowbgcolor == '#ffffff')
			$troutlinecolor = 'grey';
		else
			$troutlinecolor = 'white';

		/* Current Port */
		$chainlabel = '<tr '.$hlbgcolor.' style="outline: thin solid '.$troutlinecolor.';"><td nowrap="nowrap" bgcolor='.($this->loop ? '#ff9966' : self::CURRENT_PORT_BGCOLOR).' title="'.$title.
			'"><a '.$onclick.'>'.
			$initport['name'].': </a></td>';

		return $chainlabel;
	}

	function getchainrow($allback = false, $rowbgcolor = '#ffffff', $isprev = false)
	{
		$port_id = $this->init;

		$initport = $this->ports[$port_id];
		$portmulti = 0;
		$prevportmulti = 0;

		$chain = "";

		$remote_id = null;
		$portalign = false;
		$portmultis = array();
		$i=0;
		foreach($this as $id => $port)
		{
			if($this->loop && (($isprev && $id == $this->last) || ($this->initback  === null && !$isprev && $id == $this->first)))
				$chain .= '<td bgcolor=#ff9966>LOOP</td>';

			$object_text = $this->getprintobject($port);
			$port_text = $this->getprintport($port);

			if($this->initback !== null && (($isprev && $id == $this->last) || (!$isprev && $id == $this->first)))
			{
				$object_text = "";
				$port_text = "";
			}

			$linktype = $port['linktype'];
			$prevlinktype = ($linktype == 'front' ? 'back' : 'front');

			if($port[$prevlinktype]['portcount'] > 1)
			{
				$prevportmulti++;
				/* mutlilink: multiple previous links */

				$notrowbgcolor = ($rowbgcolor == lm_linkchain::ALTERNATE_ROW_BGCOLOR ? '#ffffff' : lm_linkchain::ALTERNATE_ROW_BGCOLOR );
				if($this->prevportmulti % 2)
				{
					$oddbgcolor = $rowbgcolor;
					$evenbgcolor = $notrowbgcolor;
				}
				else
				{
					$oddbgcolor = $notrowbgcolor;
					$evenbgcolor = $rowbgcolor;
				}

				$chain = "<td><table id=pmp frame=box><tr id=main><td><table id=pmmain align=right><tr>$chain</tr></table></td></tr><!-- main tr--><tr><td><table id=plcs align=right width=100%>"; // end table pmmain; end main tr

				$mi = 0;
				foreach($port[$prevlinktype]['chains'] as $mlc)
				{
					$mbgcolor = ($mi % 2 ? $evenbgcolor : $oddbgcolor);
					$chain .= "<tr bgcolor=$mbgcolor align=right><td><table id=plc><tr>".$mlc->getchainrow($allback,$mbgcolor, true)."</tr></table><!-- end plc --></td></tr>"; // close table plc
					$mi++;
				}
				$chain .= "</table><!--plcs--></td></tr></table><!--pmp--></td><td bgcolor=#ff0000></td>"; // clode tables plcs; pmp

			}

			if($port_text && !$this->loop && $id == $this->first)
			{
				$chain .= $this->_printlinkportsymbol($id, $prevlinktype);

				if($prevlinktype == 'front')
					$chain .= $this->printcomment($port);
			}

			// current port align	
			if($this->initback === null && $port_text && !$portmulti && !$prevportmulti && $this->initalign && $id == $port_id)
			{
				$portalign = true;
				$port_text = "</tr></table><!--t1 current--></td><!--end 1st td--><td id=2nd width=100%><table id=t2><tr>$port_text"; // close table t1 current; clode 1st td
			}

			$object_id = $port['object_id'];

			$prevobject_id = $port[$prevlinktype]['remote_object_id'];
			$remote_object_id = $port['remote_object_id'];

			if($linktype == 'front')
			{
			//	$arrow = ' ---> ';
				if($object_text)
				{
					if($prevobject_id != $object_id || $allback)
						$chain .= $object_text."<td>></td>";

					$chain .= $port_text;
				}

			}
			else
			{
			//	$arrow = ' ===> ';
				if($object_text)
					$chain .= $port_text."<td><</td>".$object_text;
			}

			if($port['portcount'] > 1)
			{
				
				/* mutlilink: multiple links */

				$notrowbgcolor = ($rowbgcolor == lm_linkchain::ALTERNATE_ROW_BGCOLOR ? '#ffffff' : lm_linkchain::ALTERNATE_ROW_BGCOLOR );
				if($this->portmulti % 2)
				{
					$oddbgcolor = $rowbgcolor;
					$evenbgcolor = $notrowbgcolor;
				}
				else
				{
					$oddbgcolor = $notrowbgcolor;
					$evenbgcolor = $rowbgcolor;
				}
	
				$chain .= "<td bgcolor=#ff0000></td><td><table id=mp frame=box>";

				$multichain = "<tr><td><table id=mlcs width=100%>";

				$mi = 0;
				foreach($port['chains'] as $mlc)
				{

					$mbgcolor = ($mi % 2 ? $evenbgcolor : $oddbgcolor);
					$multichain .= "<tr bgcolor=$mbgcolor><td><table id=t9><tr>".$mlc->getchainrow(false, $mbgcolor)."</tr></table></td></tr>";
					$mi++;
				}

				$multichain .= "</table><!-- mlcs --></td></tr>"; // close table mlcs

				$portmultis[] = $multichain;
				
				// main
				$chain .= "<tr id=mm><td><table id=pmm><tr>";

				$portmulti++;

			}

			$remote_id = $port['remote_id'];

			if($remote_id)
				if($object_id != $remote_object_id || $allback || $linktype == 'front')
					$chain .= $this->printlink($port, $linktype);
				else
					$chain .= "<td>></td>";

			if($port_text && !($remote_id && $this->loop) && $id == $this->last)
			{
				$chain .= $this->_printlinkportsymbol($id, $linktype);

				if($linktype == 'front')
					$chain .= $this->printcomment($port);
			}

			$i++;
		} // foreach port

		if($this->loop && $remote_id)
		{
			$chain .= '<td bgcolor=#ff9966>LOOP</td>';
			showWarning("Possible Loop on Port ($linktype) ".$initport['name']);
		}

		if($this->exceed)
		{
			$chain .= '<td bgcolor=#ff9966>LINKCOUNT EXCEEDED</td>';
			$linktype = $this->getlinktype($this->back);
			showWarning("Possible Loop linkcount(".$this->linkcount.") exceeded on Port ($linktype) ".$initport['name']);
		}

		foreach(array_reverse($portmultis) as $multitr)
		{
			$chain .= "</tr><!-- tr mm --></table><!-- pmm --></td></tr>$multitr</table><!-- mp--></td>"; // close table pmm; mp
		}	

		if($this->initback === null)
		{
			// TODO width..
			$chain = "<td id=1st".(!$portalign ? " colspan=2" : " width=1%")."><table id=t1".(!$portalign ? "" : " align=right")."><tr>$chain";
			$chain .= "</tr></table><!-- end t1/t2 --></td><!--1st/2nd td-->"; // close table t1/t2; close 1st/2nd td
		}

		$this->portmulti += $portmulti;
		$this->prevportmulti += $prevportmulti;

		return $chain;
	}

	/*
	 */
	function getprintobject($port) {
		global $lc_cache;

		$object_id = $port['object_id'];

		if($object_id == $this->object_id) {
                        $color='color: '.self::CURRENT_OBJECT_BGCOLOR;
                } else {
                        $color='';
                }

		$style = "font-size: 80%;";

		$rack = null;
		$object = $lc_cache->getobject($object_id, $rack);

		$rackinfo = $this->_getprintrack($object_id, $rack, $style);

		if($object['container_id'] && LM_SHOW_CONTAINERS == TRUE)
		{
			$container_rack = null;
			$container = $lc_cache->getobject($object['container_id'], $container_rack);
			$container_rackinfo = $this->_getprintrack($object['container_id'], $container_rack, $style);

			$txt = '<a style="font-weight:bold;'
                        .$color.'" href="'.makeHref(array('page'=>'object', 'tab' => 'linkmgmt', 'object_id' => $container['id']))
                        .'"><pre>'.$container['name'].'</pre></a><pre>'.$container_rackinfo
                        .'</pre>';

			$txt = "<tr><td><label style=\"font-size: 60%;\">Container:</label>$txt<hr></td></tr>";
		}
		else
			$txt = '';


                return '<td><table frame=box align=center cellpadding=5 cellspacing=0>'.$txt.'<tr><td align=center><a style="font-weight:bold;'
                        .$color.'" href="'.makeHref(array('page'=>'object', 'tab' => 'linkmgmt', 'object_id' => $object_id))
                        .'"><pre>'.$object['name'].'</pre></a><pre>'.$rackinfo
                        .'</pre></td></tr></table></td>';

	} /* getprintobject */

	function _getprintrack($object_id, $rack, $style)
	{
		$slot = null;
		$attrData = getAttrValues ($object_id);
		if (isset ($attrData['28'])) // slot number
		{
			$slot = $attrData['28']['value'];
			if (preg_match ('/\d+/', $slot, $matches))
				$slot = $matches[0];
		}

		if(!$slot)
			$txt = 'Unmounted';
		else
			$txt = "Slot: $slot";


		if($slot)
			$rackinfo = '<span style="'.$style.'">Slot: '.$slot.'</span>';
		else
			$rackinfo = '';

                if($rack)
		{
			if($slot)
				$rackinfo .= "<br>";

                        $rackinfo .= '<a style="'.$style.'" href='.makeHref(array('page'=>'row', 'row_id'=>$rack['row_id'])).'>'.$rack['row_name']
                                .'</a>/<a style="'.$style.'" href='.makeHref(array('page'=>'rack', 'rack_id'=>$rack['id'])).'>'
                                .$rack['name'].'</a>';
		}

		if(!$rackinfo)
			$rackinfo = '<span style="'.$style.'">Unmounted</span>';

		return $rackinfo;
	}

	function getprintport($port, $multilink = false) {
		global $lm_cache, $lm_multilink_port_types;

		/* multilink port */
		$multilink = in_array($port['oif_id'], $lm_multilink_port_types);

		/* set bgcolor for current port */
		if($this->initback === null && $port['id'] == $this->init) {
			$bgcolor = 'bgcolor='.self::CURRENT_PORT_BGCOLOR;
			$idtag = ' id='.$port['id'];
		} else {
			$bgcolor = 'bgcolor=#e0e0f8';
			$idtag = '';
		}

		$mac = trim(preg_replace('/(..)/','$1:',$port['l2address']),':');

		$title = "Label: ${port['label']}\nMAC: $mac\nTypeID: ${port['oif_id']}\nPortID: ${port['id']}";

		if(isset($port['portip']))
			$ip = "<br><p style=\"font-size: 80%\">".$port['portip']."</p>";
		else
			$ip = "";

		return '<td><table><tr><td'.$idtag.' align=center '.$bgcolor.' title="'.$title.'"><pre>[<a href="'
			.makeHref(array('page'=>'object', 'tab' => 'linkmgmt', 'object_id' => $port['object_id'], 'hl_port_id' => $port['id']))
			.'#'.$port['id']
			.'">'.$port['name'].'</a>]'.$ip.'</pre>'.($multilink && $lm_cache['allowbacklink'] ? $this->_getlinkportsymbol($port['id'], 'back') : '' ).'</td></tr></table></td>';

	} /* printport */
	
	/*
	 */
	function printlink($port, $linktype) {

		if($linktype == 'back')
			$arrow = '====>';
		else
			$arrow = '---->';

		$port_id = $port['id'];

		/* link */
		return '<td align=center>'
			.'<pre><span class="editable id1-'.$port_id.' id2-'.$port['remote_id'].' op-lm-upd-reservation-cable linktype-'.$linktype.'">'.$port['cableid']
			."</span></pre><pre>$arrow</pre>"
			.$this->_printUnLinkPort($port, $linktype)
			.'</td>';
	} /* printlink */

	/*
	 * return link symbol
	 */
	function _getlinkportsymbol($port_id, $linktype) {
		$retval = '<span onclick=window.open("'.makeHrefProcess(array_merge($_GET,
			array('op' => 'PortLinkDialog','port' => $port_id,'linktype' => $linktype ))).'","name","height=800,width=800");'
		        .'>';

                $img = getImageHREF ('plug', $linktype.' Link this port');

		if($linktype == 'back')
			$img = str_replace('<img',
				'<img style="transform:rotate(180deg);-o-transform:rotate(180deg);-ms-transform:rotate(180deg);-moz-transform:rotate(180deg);-webkit-transform:rotate(180deg);"',
				$img);

		$retval .= $img;
		$retval .= "</span>";
		return $retval;

	} /* _getlinkportsymbol */

	/*
	 * print link symbol
	 *
	 */
       function _printlinkportsymbol($port_id, $linktype = 'front') {
		global $lm_cache;

		if($linktype == 'front' && !$lm_cache['allowlink'])
			return;

		if($linktype != 'front' && !$lm_cache['allowbacklink'])
			return;

               	return "<td align=center>"
			.$this->_getlinkportsymbol($port_id, $linktype)
			."</td>";

        } /* _printlinkportsymbol */

	/*
	 */
	function printcomment($port) {

		if(!empty($port['reservation_comment'])) {
			$prefix = '<b>Reserved: </b>';
		} else
			$prefix = '';

		return '<td>'.$prefix.'</td><td><i><span class="editable op-upd-reservation-port id-'.$port['id'].'">'.$port['reservation_comment'].'</span</i></td>';

	} /* printComment */

	/*
	 * return link cut symbol
	 *
         * TODO $opspec_list
	 */
	function _printUnLinkPort($src_port, $linktype) {
		global $lm_cache;

		if($linktype == 'front' && !$lm_cache['allowlink'])
			return;

		if($linktype != 'front' && !$lm_cache['allowbacklink'])
			return;

		$dst_port = $this->ports[$src_port['remote_id']];

		return '<a href='.
                               makeHrefProcess(array(
					'op'=>'unlinkPort',
					'port_id'=>$src_port['id'],
					'remote_id' => $dst_port['id'],
					'hl_port_id' => $this->init,
					'object_id'=> $this->object_id, //$this->ports[$this->init]['object_id'],
					'tab' => 'linkmgmt',
					'linktype' => $linktype)).
                       ' onclick="return confirm(\'unlink ports '.$src_port['name']. ' -> '.$dst_port['name']
					.' ('.$linktype.') with cable ID: '.$src_port['cableid'].'?\');">'.
                       getImageHREF ('cut', $linktype.' Unlink this port').'</a>';

	} /* _printUnLinkPort */

	function printports()
	{

		$back = isset($this->ports[$this->first]['back']['remote_id']);
		//$this->getporttds($this->first, $back);
		foreach($this as $port_id => $port)
		{
			$class = 'hidden';
			if($port_id == $this->first || $port_id == $this->last)
				$class = '';

			$this->getporttds($port, $class);
		}
	}

	function getporttds($port, $class = '')
	{
		$back = ($port['linktype'] == 'front' ? false : true );

		$prev_port = $port[( !$back ? 'back' : 'front')];
		if ($prev_port['portcount'] > 1)
			echo '<td style="color: red;" class="hidden">+'.($prev_port['portcount'] - 1).'</td>';
		else
			echo '<td class="hidden"></td>';

		// see interface.php renderObjectPortRow()
		// highlight port name with yellow if it's name is not canonical
		$canon_pn = shortenPortName ($port['name'], $port['object_id']);
		$name_class = $canon_pn == $port['name'] ? '' : 'trwarning';

		echo "<td class='tdleft $class'>" .
			formatPortLink ($port['object_id'], $port['object_name'], $port['id'], NULL) .
			"</td>";

		$a_class = isEthernetPort ($port) ? 'port-menu' : '';
		echo "<td class='tdleft $class' NOWRAP><a name='port-${port['id']}' class='interactive-portname nolink $a_class'>${port['name']}</a></td>";
		echo "<td class='tdleft $class'>${port['label']}</td>";
		echo "<td class='tdleft $class'>" . formatPortIIFOIF ($port) . "</td><td class='tdleft $class'><tt>${port['l2address']}</tt></td>";

		$remote_port = $port[( $back ? 'back' : 'front')];
		if ($remote_port['remote_id'])
		{
			if($port['id'] == $this->first)
			{
				echo "<td class='tdcenter nothidden'>";

				if( $this->linkcount == 1 )
					if($back)
						echo "=${remote_port['cableid']}=>";
					else
						echo "-${remote_port['cableid']}->";
				else
					if( $this->linkcount > 1 )
						echo "*".$this->linkcount."*>";
				echo "</td>";
			}

			$editable = permitted ('object', 'ports', 'editPort') ? 'editable' : '';

			if($remote_port['portcount'] > 1)
				echo '<td style="color: red;" class="hidden">'.($remote_port['portcount'] - 1).'+</td>';
			else
				echo '<td class="hidden"></td>';

			echo "<td class='tdcenter hidden'>";
			if($back)
				echo "=${remote_port['cableid']}=>";
			else
				echo "-${remote_port['cableid']}->";

			echo "</td>";
		}
		else
		{
			if(!empty($port['reservation_comment']))
				echo implode ('', formatPortReservation ($port));
		}

	} // getporttds

	// TODO
	// html table
	function getchainhtml()
	{
		$remote_id = $this->first;

		// if not Link use LinkBackend
		$back = $this->ports[$remote_id]['front']['remote_id'];

		$chain = "<table>";

		for(;$remote_id;)
		{
			$back = !$back;
			$linktype = $port['linktype'];

			if($back)
			{
			//	$linktable = 'LinkBackend';
				$arrow = ' => ';
			}
			else
			{
			//	$linktable = 'Link';
				$arrow = ' --> ';
			}

			$port = $this->ports[$remote_id][$linktype];

			if($this->init == $remote_id)
				$chain .= "<tr><td><b>".$port['object_name']."</b></td><td><b> [".$port['name']."]</b></td>";
			else
				$chain .= "<tr><td>".$port['object_name']."</td><td> [".$port['name']."]</td>";

			if($remote_id == $this->first || $remote_id == $this->last)
				$chain .= "<td><div name=\"port${remote_id}-status\"></div></td>";
			else
				$chain .= "<td></td>";

			$remote_id = $port['remote_id'];

			if($remote_id)
				$chain .= "<td>$arrow</td></tr>";
			else
				$chain .= "<td></td></tr>";

			if($this->loop && $remote_id == $this->first)
			{
				$chain .= "LOOP!<br>";
				break;
			}

		}

		$chain .= "</table>";

		return $chain;
	}

	function getport($id)
	{
		return $this->ports[$id];
	}

	/* Iterator */
	function rewind() {
		$this->currentid = $this->first;

		$this->back = isset($this->ports[$this->currentid]['back']['remote_id']);

		$this->icount = 0;
	}

	function current() {
		return $this->_getportlink($this->ports[$this->currentid]);
	}

	function key() {
		return $this->currentid;
	}

	function next() {
		$port = $this->current();
		$remote_id = $port['remote_id'];
	
		if($this->loop && $remote_id == $this->first)
			$this->currentid = false;
		else
			$this->currentid = $remote_id;

		$this->back = !$this->back;

		$this->icount++;
	}

	function valid() {

		/* linkcout exceeded */
		if($this->icount > $this->linkcount+1)
		{
			$this->exceed = true;
			return false;
		}

		return $this->currentid;
	}

	/* for debugging only */
	function var_dump_html(&$var, $msg = "") {
		echo "<pre>------------------Start Var Dump -------------$msg------------\n";
		var_dump($var);
		echo "\n---------------------END Var Dump -----------$msg-------------</pre>";
	}
} // lm_linkchain

/*
 *   from RT database.php fetchPortList()
 *	with Link table selection
 *	and multilink changes
 */
function lm_fetchPortList ($sql_where_clause, $query_params = array(), $linktable = 'Link')
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
	(SELECT PortOuterInterface.oif_name FROM PortOuterInterface WHERE PortOuterInterface.id = Port.type) AS oif_name,

	lk.cable AS cableid,
	IF(lk.porta = Port.id, pb.id, pa.id) AS remote_id,
	IF(lk.porta = Port.id, pb.name, pa.name) AS remote_name,
	IF(lk.porta = Port.id, pb.object_id, pa.object_id) AS remote_object_id,
	IF(lk.porta = Port.id, ob.name, oa.name) AS remote_object_name,

	(SELECT COUNT(*) FROM PortLog WHERE PortLog.port_id = Port.id) AS log_count,
	PortLog.user,
	UNIX_TIMESTAMP(PortLog.date) as time
FROM
	Port
	INNER JOIN Object ON Port.object_id = Object.id

	LEFT JOIN $linktable AS lk ON lk.porta = Port.id or lk.portb = Port.id
	LEFT JOIN Port AS pa ON pa.id = lk.porta
	LEFT JOIN Object AS oa ON pa.object_id = oa.id
	LEFT JOIN Port AS pb ON pb.id = lk.portb
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
} /* lm_fetchPortList */

function lm_getPortInfo ($port_id, $back = false)
{
	$linktable = ($back ? 'LinkBackend' : 'Link');
	if($back)
		$result = lm_fetchPortList ('Port.id = ?', array ($port_id), $linktable);
	else
		$result = fetchPortList ('Port.id = ?', array ($port_id));

        //return empty ($result) ? NULL : $result[0];
        return $result;
} /* lm_getPortInfo */

/* -------------------------------------------------------------------------- */
/* -------------------------------------------------- */

function linkmgmt_opHelp() {
?>
	<table cellspacing=10><tr><th>Help</th><tr>
		<tr><td width=150></td><td width=150 style="font-weight:bold;color:<?php echo lm_linkchain::CURRENT_OBJECT_BGCOLOR; ?>">Current Object</td></tr>
		<tr><td></td><td bgcolor=<?php echo lm_linkchain::CURRENT_PORT_BGCOLOR; ?>>[current port]</td></tr>
		<tr><td>front link</td><td>[port]<(Object)</td><td>back link</td></tr>
		<tr><td>back link</td><td>(Object)>[port]</td><td>front link</td></tr>
		<tr><td></td><td><pre>----></pre></td><td>Front link</td></tr>
		<tr><td></td><td><pre>====></pre></td><td>Backend link</td></tr>
		<tr><td></td><td>Link Symbol</td><td>Create new link</td></tr>
		<tr><td></td><td>Cut Symbol</td><td>Delete link</td></tr>

	</table>

<?php
	exit;
} /* opHelp */

/* -------------------------------------------------- */

function linkmgmt_ajax_mapinfo() {

	$object_id = NULL;
	$port_id = NULL;
	$remote_id = NULL;
	$linktype = NULL;

	if(isset($_REQUEST['object_id']))
		$object_id = $_REQUEST['object_id'];

	if(isset($_REQUEST['port_id']))
		$port_id = $_REQUEST['port_id'];

	if(isset($_REQUEST['remote_id']))
		$remote_id = $_REQUEST['remote_id'];

	if(isset($_REQUEST['linktype']))
		$linktype = $_REQUEST['linktype'];

	$debug = NULL;
	if(isset($_REQUEST['debug']))
		$debug['value'] = $_REQUEST['debug'];

	$info = array();

	echo "<table style=\"font-size:12;\"><tr>";

	if($port_id != NULL)
	{
		$port = new linkmgmt_RTport($port_id);

		echo "<td>";
		$port->printtable('both');
		echo "</td>";

		if($debug)
			$debug['port'] = &$port;

		if($remote_id != NULL)
		{

			$remote_port = new linkmgmt_RTport($remote_id);

			echo "<td><table align=\"center\">";

			// TODO cableid
			echo "<tr><td><pre>".($linktype == 'back' ? ' ===> ' : ' ---> ')."</pre></td></tr>";

			$port->printunlinktr($linktype, $remote_port);

			echo "</table></td>";


			echo "<td>";
			$remote_port->printtable('both');
			echo "</td>";

			if($debug)
				$debug['remote_port'] = &$remote_port;

		}
		else
			$port->printunlinktr();


	}
	echo "</tr><tr>";

	echo "<td>";
	$object = linkmgmt_RTport::printobjecttable($object_id);
	echo "</td>";

	if($debug)
		$debug['object'] = &$object;

	if($remote_id != NULL)
	{

		echo "<td></td>"; /* link */
		echo "<td>";
		$remote_object = linkmgmt_RTport::printobjecttable($remote_port->port['object_id']);
		echo "</td>";

		if($debug)
			$debug['remote_object'] = &$remote_object;
	}

	echo "</tr></table>";

	if($debug)
	{
		echo "<pre>--- Debug ---";
		var_dump($debug);
		echo "</pre>";
	}

	exit;
}

/* -------------------------------------- */
function lm_renderObjectCell ($cell)
{
	echo "<table class='slbcell vscell'><tr><td rowspan=2 width='5%'>";
	printImageHREF ('OBJECT');
	echo '</td><td>';
	echo mkA ('<strong>' . stringForLabel ($cell['dname']) . '</strong>', 'object', $cell['id']);
	echo '</td></tr><tr><td>';
	echo count ($cell['etags']) ? ("<small>" . serializeTags ($cell['etags']) . "</small>") : '&nbsp;';
	echo "</td></tr></table>";
}
/* -------------------------------------- */

// TODO replace..
class linkmgmt_RTport {

	private $port_id = NULL;

	public $port = false;

	function __construct($port_id) {

		$this->port = getPortInfo($port_id);

		if($this->port === false)
			return;

		/* successfully get port info */
		$this->port_id = $port_id;

	} /* __construct */

	function isvalid() {
		return ($port_id !== NULL);
	}

	function getlinks($type = 'front') {
	} /* getlinks */

	function printtable($linktype = 'front') {

		if($this->port_id == NULL)
			return;

		echo "<table>";

		$urlparams = array(
					'module' => 'redirect',
					'page' => 'object',
					'tab' => 'linkmgmt',
					'op' => 'map',
					'object_id' => $this->port['object_id'],
					'port_id' => $this->port_id,
					'usemap' => 1,
				);

		echo '<tr><td><a title="don\'t highlight port" href="?'.http_build_query($urlparams).'">-phl</a></td>';

		$urlparams['hl'] = 'p';
		echo '<td><a title="highlight port" href="?'.http_build_query($urlparams).'">+phl</a></td></tr>';

		$this->_printinforow($this->port,
					array(
						'id' => 'Port ID',
						'name' => 'Port Name',
						'oif_name' => 'Port Type',
						'l2address' => 'MAC',
						'reservation_comment' => 'comment',
					)
		); /* printinforow */

		$this->printlinktr($linktype);

		echo "</table>";
	} /* printtable */

	function printlinktr($linktype = 'front') {
		if($this->port_id === NULL)
			return;

                $urlparams = array(
				'tab' => 'linkmgmt',
				'page' => 'object',
                                'op'=>'PortLinkDialog',
                                'port'=>$this->port_id,
                                'object_id'=>$this->port['object_id'],
				'linktype' => $linktype,
				);

		echo "<tr><td align=\"center\"><a href='".
                                makeHrefProcess($urlparams).
                        "'>";
                        printImageHREF ('plug', 'Link this port');
                        echo "</a></td></tr>";
	} /* link */

	function printunlinktr($linktype = 'front', $remote_port = NULL) {
		if($this->port_id === NULL)
			return;

		$urlparams = array(
					'tab' => 'linkmgmt',
                                        'op'=>'unlinkPort',
                                        'port_id'=>$this->port_id,
                                        'object_id'=>$this->port['object_id'],
					'linktype' => $linktype,
				);

		$confirmmsg = "unlink port ".$this->port['name'];

		if($remote_port !== NULL)
		{
			$urlparams['remote_id'] = $remote_port->port['id'];
			$confirmmsg .= ' -> '.$remote_port->port['name'];
		}

		$confirmmsg .= " ($linktype)"; // TODO cableid

		echo "<tr><td align=\"center\"><a href='".makeHrefProcess($urlparams).
		"' onclick=\"return confirm('$confirmmsg');\">";
		printImageHREF ('cut', 'Unlink this port');
		echo "</a></td></tr>";

	} /* unlink */

	/* TODO move to object class */
	static function printobjecttable($object_id = NULL) {

		if($object_id === NULL)
			return;

		$object = spotEntity ('object', $object_id);

		if($object === false)
			return;

		if($object['rack_id'])
		{
			$rack = spotEntity('rack', $object['rack_id']);

			$object['row_name'] = $rack['row_name'];
			$object['rack_name'] = $rack['name'];
		}

		echo "<table><tr><td>";
		lm_renderObjectCell($object);
		echo "</td></tr><tr><td><table>";

		self::_printinforow($object,
				array(
					'id' => 'ID',
					'dname' => 'Name',
					'label' => 'Label',
					'rack_name' => 'Rack',
					'row_name' => 'Row',
				)

		); /* printinforow */

		$urlparams = array(
					'module' => 'redirect',
					'page' => 'object',
					'tab' => 'linkmgmt',
					'op' => 'map',
					'object_id' => $object_id,
					'usemap' => 1,
				);

		echo '<tr><td><a title="don\'t highlight object" href="?'.http_build_query($urlparams).'">-ohl</a></td>';

		$urlparams['hl'] = 'o';
		echo '<td><a title="highlight object" href="?'.http_build_query($urlparams).'">+ohl</a></td></tr>';

		echo "</table></td></tr></table>";

		return $object;

	} /* printobjecttable */

	static function _printinforow(&$data, $config) {

		foreach($config as $key => $name)
		{
			if(isset($data[$key]))
			{
				$value = $data[$key];
				if(!empty($value))
					echo "<tr><td align=\"right\" nowrap=\"nowrap\" style=\"font-size:10;\">$name:</td><td nowrap=\"nowrap\">$value</td></tr>";
			}
		}

	} /* _printinforow */
} /* class RTport */

/* -------------------------------------------------- */

function linkmgmt_opmap() {

	/* display require errors  "white screen of death" */
	$errorlevel = error_reporting();
	error_reporting(E_ALL);

	require_once 'Image/GraphViz.php';

/*
 *
 */
class lm_Image_GraphViz extends Image_GraphViz {

	/* extend renderDotFile with additional output file
	 */
    function renderDotFile($dotfile, $outputfile, $format = 'svg',
                           $command = null, $outputfile2 = null, $format2 = null)
    {
        if (!file_exists($dotfile)) {
            if ($this->_returnFalseOnError) {
                return false;
            }
            $error = PEAR::raiseError('Could not find dot file');
            return $error;
        }

        $oldmtime = file_exists($outputfile) ? filemtime($outputfile) : 0;

        switch ($command) {
        case 'dot':
        case 'neato':
            break;
        default:
            $command = $this->graph['directed'] ? 'dot' : 'neato';
        }
        $command_orig = $command;

        $command = $this->binPath.(($command == 'dot') ? $this->dotCommand
                                                       : $this->neatoCommand);

        $command .= ' -T'.escapeshellarg($format)
                    .' -o'.escapeshellarg($outputfile)
                    .($format2 !== null && $outputfile2 !== null ? ' -T'.escapeshellarg($format2).' -o'.escapeshellarg($outputfile2) : '')
                    .' '.escapeshellarg($dotfile)
                    .' 2>&1';

        exec($command, $msg, $return_val);

        clearstatcache();
        if (file_exists($outputfile) && filemtime($outputfile) > $oldmtime
            && $return_val == 0) {
            return true;
        } elseif ($this->_returnFalseOnError) {
            return false;
        }
        $error = PEAR::raiseError($command_orig.' command failed: '
                                  .implode("\n", $msg));
        return $error;
    }
    // renderDotFile


	/*
	 */
    function fetch($format = 'svg', $command = null, $format2 = null, &$data2 = null)
    {

        $file = $this->saveParsedGraph();
        if (!$file || PEAR::isError($file)) {
            return $file;
        }

        $outputfile = $file . '.' . $format;

	if($format2 != null && $data2 !== null)
		$outputfile2 = $file . '.' . $format2;
	else
		$outputfile2 = null;

        $rendered = $this->renderDotFile($file, $outputfile, $format,
                                         $command, $outputfile2, $format2);
        if ($rendered !== true) {
            return $rendered;
        }

        @unlink($file);

	if($format2 !== null && $data2 !== null) {
		$fp = fopen($outputfile2, 'rb');

		if ($fp) {
			$data = fread($fp, filesize($outputfile2));
			fclose($fp);
			@unlink($outputfile2);

			$data2 = $data;
		} else {
			return $error;
		}
	}


        $fp = fopen($outputfile, 'rb');

        if (!$fp) {
            if ($this->_returnFalseOnError) {
                return false;
            }
            $error = PEAR::raiseError('Could not read rendered file');
            return $error;
        }

        $data = fread($fp, filesize($outputfile));
        fclose($fp);
        @unlink($outputfile);

        return $data;
    }
    // fetch



} /* class lm_Image_GraphViz */

	error_reporting($errorlevel);

	$object_id = NULL;
	$port_id = NULL;
	$remote_id = NULL;
	$allports = false;
	$usemap = false;
	$command = NULL;

	/* highlight object */
	$hl = NULL;
	if(isset($_REQUEST['hl']))
	{
		$hl = $_REQUEST['hl'];
		unset($_REQUEST['hl_object_id']);
		unset($_REQUEST['hl_port_id']);

		if($hl == 'o')
		{
			unset($_GET['port_id']);
			unset($_GET['remote_id']);
		}

	}

	if(!$hl && isset($_REQUEST['hl_object_id']))
	{
		$hl = 'o';
		$object_id = $_REQUEST['hl_object_id'];
		$_REQUEST['object_id'] = $object_id;
		unset($_REQUEST['hl_object_id']);
		unset($_REQUEST['hl_port_id']);
		unset($_REQUEST['port_id']);
	}

	if(isset($_REQUEST['object_id']))
		$object_id = $_REQUEST['object_id'];

	if(isset($_REQUEST['type']))
	{
		$type = $_REQUEST['type'];
	}
	else
		$type = 'gif';

	/* highlight port */
	if(!$hl && isset($_REQUEST['hl_port_id']))
	{
		$hl = 'p';
		$port_id = $_REQUEST['hl_port_id'];
		$_REQUEST['port_id'] = $port_id;
		unset($_REQUEST['hl_port_id']);
	}

	if(isset($_REQUEST['allports']))
	{
		$allports = $_REQUEST['allports'];
	}

	if(isset($_REQUEST['port_id']))
	{
		$port_id = $_REQUEST['port_id'];
	}

	if(isset($_REQUEST['usemap']))
		$usemap = $_REQUEST['usemap'];

	if($hl == 'p' && $port_id === NULL)
	{
		unset($_GET['hl']);
		unset($_GET['port_id']);
		unset($_GET['remote_id']);
	}

	if($hl == 'o')
		unset($_GET['remote_id']);

	if(isset($_REQUEST['remote_id']))
		$remote_id = $_REQUEST['remote_id'];

	/* show all objects */
	if(isset($_REQUEST['all']))
	{
		$object_id = NULL;
		$port_id = NULL;
		$hl = NULL;
		unset($_GET['hl']);
	}

	if(isset($_REQUEST['cmd']))
		$command = $_REQUEST['cmd'];

	if(isset($_REQUEST['debug']))
		$debug = $_REQUEST['debug'];
	else
		$debug = False;

	if($debug) echo "-- DEBUG --<br>";


	switch($type) {
		case 'gif':
		case 'png':
		case 'bmp':
		case 'jpeg':
		case 'tif':
		case 'wbmp':
			$ctype = "image/$type";
			break;
		case 'jpg':
			$ctype = "image/jpeg";
			break;
		case 'svg':
			$ctype = 'image/svg+xml';
			break;
		case 'pdf':
			$ctype = 'application/pdf';
			break;
		case 'cmapx':
			$ctype = 'text/plain';
			break;
	}

	$start = microtime(true);
	$gvmap = new linkmgmt_gvmap($object_id, $port_id, $allports, $hl, $remote_id);
	$stop = microtime(true);

	if($debug)
		echo "gvmap Time: ".( $stop - $start )."<br>";

	if($debug) echo "-- after gvmap --<br>";

	if($usemap)
	{

		if($debug) echo "-- usemap --<br>";

		/* add context menu to Ports, Objects, Links, ...
		 */

		echo "<script>
			function initcontextmenu() {
				var maps = document.getElementsByTagName('map');
                                for(var i=0;i<maps.length;i++) {
					var areas = maps[i].childNodes;

					for(j=0;j<areas.length;j++) {
						if(areas[j].nodeType == 1)
						{
						//	console.log(areas[j].id);
						//	attr = document.createAttribute('onmouseover','ahh');
						//	areas[j].setAttribute(attr);
						//	areas[j].onmouseover = 'menu(this);';

							areas[j].addEventListener('contextmenu',menu,false);
						//	areas[j].oncontextmenu = 'menu(this, event);';
						//	console.log(areas[j].oncontextmenu);
						}
					}

                                }

			};

			function menu(event) {

			//	console.log('Menu');

				if(!event)
					event = window.event;

				var parent = event.target;

			//	console.log('--' + parent);

				var ids = parent.id.split('-');

				if(ids[0] == 'graph1')
					return false;

				var object_id = ids[0];

				var url ='?module=ajax&ac=lm_mapinfo&object_id=' + object_id;

			//	links ='<li><a href=' + object_id + '>Object</a></li>';

				if(ids[1] != '')
				{
					var port_id = ids[1];
					url += '&port_id=' + port_id;
				//	links += '<li><a href=' + port_id + '>Port</a></li>';

					if(ids[2] != '')
					{
						var remote_id = ids[2];

						if(ids[3] != '')
						{
							var linktype = ids[3];
							url += '&remote_id=' + remote_id + '&linktype=' + linktype;
						//	links += '<li><a href=' + port_id + '_' + remote_id + '_' + linktype + '>Unlink</a></li>';
						}
					}

				}


				var xmlHttp = new XMLHttpRequest();
				xmlHttp.open('GET', url, false);
				xmlHttp.send(null);

				var infodiv = document.getElementById('info');
				infodiv.innerHTML = xmlHttp.responseText;

		//		linkdiv = document.getElementById('link');
		//		linkdiv.innerHTML = links;

				var menudiv = document.getElementById('menu');
				menudiv.style.position  = 'absolute';
				menudiv.style.top  = (event.clientY + document.body.scrollTop) + 'px';
				menudiv.style.left  = (event.clientX + document.body.scrollLeft) + 'px';
				menudiv.style.display  = '';

				return false;
			};

			function mousedown(event) {
				//	console.log('mouse down');

				if(!event)
					event = window.event;

				if(event.button != 2)
					return true;

				var menudiv = document.getElementById('menu');

				menudiv.style.display = 'none';

				return false;
			};

			</script>";

		echo "<body oncontextmenu=\"return false\" onmousedown=\"mousedown(event);\" onload=\"initcontextmenu();\">";

		echo "<div id=\"menu\" style=\"display:none; background-color:#ffff90\">
				<div id=\"info\"></div>
				<ul id=\"link\" style=\"list-style-type:none\"></ul>
			</div>";

		if($debug)
			$gvmap->setFalseOnError(False);

		$data2 = '';

		$start = microtime(true);
		$data = $gvmap->fetch($type, $command, 'cmapx', $data2);
		$stop = microtime(true);

		if($debug)
			echo "DOT time: ".( $stop - $start )."<br>";

		if($data === false)
			echo "ERROR Fetching image data!<br>";

		if(PEAR::isError($data))
			echo $data->getMessage();

		//echo $gvmap->fetch('cmapx', $command);
		echo $data2;

		if($debug) echo "-- after map gvmap --<br>";

		echo "<img src=\"data:$ctype;base64,".
			base64_encode($data).
			"\" usemap=#map$object_id />";

		if($debug)
		{
			echo "<pre>";
			echo $gvmap->export();
			echo "</pre>";

			echo "<pre>".$gvmap->parse()."</pre>";
		}
	}
	else
	{
		$gvmap->image($type);
	}

	exit;

} /* linkmgmt_opmap */

class cytoscapedata
{
	public $objects = array();

	public $pids = array();

	public $parents = array();
	public $nodes = array();

	public $edges = NULL;

	public $debug = null;

	function __construct()
	{

		$this->edges['parents'] = array();
		$this->edges['nodes'] = array();
	}

	function addnode($id, $values = NULL, &$arr = NULL)
	{
		$data = array( 'id' => $id );

		if($values != NULL)
			$data = $data + $values;

		$node['data'] = $data;

		$this->objects[] = array('group' => 'nodes')  + $node;

		switch($values['type'])
		{
			case 'port':
				$this->nodes[$id] = array('group' => 'nodes')  + $node;
				break;
			case 'object':
			case 'container':
				$this->parents[$id] = array('group' => 'nodes')  + $node;
				break;
		}

		if($arr !== NULL)
			$arr[] = array('group' => 'nodes')  + $node;
	}

	function addedge($id, $source, $target, $values = NULL, &$arr = NULL)
	{
		$data = array(
				'id' => $id,
				'source' => $source,
				'target' => $target
			 );

		if($values != NULL)
			$data = $data + $values;

		$edge['data'] = $data;

		//$this->elements['edges'][] = $edge;

		if($arr !== NULL)
		{
			$arr[$id] = array('group' => 'edges') + $edge;
		}
		else
			$this->objects[] = array('group' => 'edges') + $edge;

		//$this->edges[] = array('group' => 'edges') + $edge;
	}

	function _addobjectnode($object_id, $type = 'object')
	{
			global $lc_cache;

			if(!isset($this->parents["o$object_id"]))
			{
				$rack = null;
				$object = $lc_cache->getobject($object_id, $rack);

				$clustertitle = "${object['dname']}";

				//has_problems
				//if($object['has_problems'] != 'no')

				$rack_text = "";
				if(!empty($rack['row_name']) || !empty($rack['name']))
				{
					$rack_text = "${rack['row_name']} / ${rack['name']}";
				}

				$data = array('label' => $object['name'], 'text' => $rack_text, 'type' => $type, 'has_problems' => $object['has_problems']);

				$container_id = $object['container_id'];
				if($container_id)
				{
					$data['parent'] = "o$container_id";
					$this->_addobjectnode($container_id, 'container');
				}

				$this->addnode('o'.$object_id, $data);
			}

	}

	// cytoscape
	function addlinkchain($linkchain, $index) {

		foreach($linkchain as $id => $port)
		{

			if(!$linkchain->linked)
				continue;

			$this->_addobjectnode($port['object_id']);

			$text = (isset($port['portip']) ? $port['portip'] : "" );
			$nodedata = array('label' => $port['name'], 'parent' => 'o'.$port['object_id'], 'text' => $text, 'type' => 'port', 'index' => $index , 'loop' => ($linkchain->loop ? '1' : '0'));

			//$this->addnode('l_'.$port['id'], array( 'label' => $port['name'], 'parent' => 'p'.$port['id'], 'text' => $text ));

			if($port['portcount'] > 1)
				foreach($port['chains'] as $mlc)
				{
					$this->addlinkchain($mlc, 0); // TODO index
				}

			$prevlinktype = ($port['linktype'] == 'front' ? 'back' : 'front');
			if($port[$prevlinktype]['portcount'] > 1)
			{
				foreach($port[$prevlinktype]['chains'] as $mlc)
				{
					$this->addlinkchain($mlc, 0, false); // TODO index
				}
			}

			if($port['remote_id'])
			{

				$this->_addobjectnode($port['remote_object_id']);

				$linktype = $port['linktype'];
				$edgedata = array('label' => $port['cableid'], 'type' => $linktype, 'loop' => ($linkchain->loop ? '1' : '0'));

				if($linkchain->loop && $port['remote_id'] == $linkchain->first)
				{
					$nodedata['loopedge'] = array('group' => 'edges', 'data' => array( 'id' => 'le'.$port['id']."_".$port['remote_id'], 'source' => 'p'.$port['id'], 'target' => 'p'.$port['remote_id']) +  $edgedata);
				}
				else
				{

					$this->addedge('e'.$port['id']."_".$port['remote_id'], 'p'.$port['id'], 'p'.$port['remote_id'], $edgedata);
					$this->addedge('e'.$port['id']."_".$port['remote_id'], 'p'.$port['id'], 'p'.$port['remote_id'], $edgedata, $this->edges['nodes']);
					$id1 = $port['object_id'];
					if($id1 > $port['remote_object_id'])
					{
						$id1 = $port['remote_object_id'];
						$id2 = $port['object_id'];
					}
					else
						$id2 = $port['remote_object_id'];

					$peid = "pe".$id1."_".$id2;

					if(isset($this->edges['parents'][$peid]))
						$this->edges['parents'][$peid]['data']['linkcount']++;
					else
						$this->addedge($peid, 'o'.$id1, 'o'.$id2, array('type' => $linktype, 'linkcount' => 1), $this->edges['parents']);
				}
			}

			$this->addnode('p'.$port['id'], $nodedata);
		}

		if(0)
		if($linkchain->first != $linkchain->last )
		{
				$first = $linkchain->first;
				$last = $linkchain->last;
				$this->addedge("l${first}_${last}",'p'.$first, 'p'.$last, array('type' => 'logical', 'label' => "logical"));
		}

		//lm_linkchain::var_dump_html($this->parents);
	}

	function getlinkchains($object_id) {

		$this->objects = array();
		$this->parents = array();
		$this->nodes = array();
		$this->edges['parents'] = array();
		$this->edges['nodes'] = array();

		$this->_getlinkchains($object_id);
	}

	function getelements()
	{
		//lm_linkchain::var_dump_html($this);
		return array('parents' => array_values($this->parents),
			'nodes' =>  array_values($this->nodes),
			'edges' =>  array(
					'parents' => array_values($this->edges['parents']),
					'nodes' => array_values($this->edges['nodes'])
					),
			'debug' => $this->debug
			);

	}

	function gettest()
	{
		return array_merge(array_values($this->parents), array_values($this->edges['parents']));
		//return array_merge(array_values($this->parents), array_values($this->edges['parents']), array_values($this->nodes), array_values($this->edges['nodes']));
	}

	function _getlinkchains($object_id) {


		// container
	//	$object = spotEntity('object', $object_id);
		$object['ports'] = getObjectPortsAndLinks ($object_id);

		$i = 0;
		foreach($object['ports'] as $key => $port)
		{

			if(isset($this->pids[$port['id']]))
				continue;

			$i++;
			$lc = new lm_linkchain($port['id']);

			$this->pids += $lc->pids;

			$this->addlinkchain($lc, $i);

		}

		$children = getEntityRelatives ('children', 'object', $object_id);

		foreach($children as $child)
			$this->_getlinkchains($child['entity_id']);
	}

	function allobjects()
	{

		$this->objects = array();
		$this->parents = array();
		$this->nodes = array();
		$this->edges['parents'] = array();
		$this->edges['nodes'] = array();

		$objects = listCells('object');

		$i = 0;
		foreach($objects as $object)
		{
			//echo $object['id']."<br>";
			$this->_getlinkchains($object['id']);
			$i++;
			if($i > 20 ) break;
		}
	}
}

function linkmgmt_cytoscapemap() {


	$object_id = $_GET['object_id'];

	if(isset($_GET['json']))
	{
		ob_start();
		$data = new cytoscapedata();
		$data->getlinkchains($object_id);
		//$data->allobjects(); // ugly graph;
		//echo json_encode($data->objects);

		if(ob_get_length())
			$data->debug = ob_get_contents();

		ob_end_clean();

		echo json_encode($data->getelements());

		exit;
	}

	echo (<<<HTMLEND
<!DOCTYPE html>
<html>
<head>
<style>
body {
  font: 14px helvetica neue, helvetica, arial, sans-serif;
}

#cy {
  height: 100%;
  width: 100%;
  position: absolute;
  left: 0;
  top: 0;
}
</style>
<meta charset=utf-8 />
<meta name="viewport" content="user-scalable=no, initial-scale=1.0, minimum-scale=1.0, maximum-scale=1.0, minimal-ui">
<title>Compound nodes</title>
<!--<script src="js/jquery-1.4.4.min.js"></script>-->

<script src="?module=chrome&uri=linkmgmt/jquery-1.11.3.min.js"></script>
<script src="?module=chrome&uri=linkmgmt/cytoscape.min.js"></script>
<script src="?module=chrome&uri=linkmgmt/dagre.min.js"></script>
<script src="?module=chrome&uri=linkmgmt/cytoscape-dagre.js"></script>
<link rel="stylesheet" type="text/css" href="?module=chrome&uri=linkmgmt/jquery.qtip.min.css">
<script src="?module=chrome&uri=linkmgmt/jquery.qtip.min.js"></script>
<script src="?module=chrome&uri=linkmgmt/cytoscape-qtip.js"></script>

<!--<script src="?module=chrome&uri=linkmgmt/cytoscape-css-renderer_mod.js"></script>-->
<!--<script src="?module=chrome&uri=linkmgmt/cytoscape.js-navigator.js_mod"></script>-->
<script>
var cy = null;
var cy2 = null;
var layout = null;
var data = null;

$(function(){ // on dom ready
  var cystyle = [
    {
      selector: 'node',
      css: {
        'content': 'data(id)',
	'text-wrap': 'wrap'
      },
      style: {
        'background-color': '#666',
        'label': 'data(label)',
	'width': 'label',
	'min-zoomed-font-size' : 8,
        'text-valign': 'center',
        'text-halign': 'center',
	'text-wrap': 'wrap',
	'shape': function(ele) {
			if(ele.data('type') != 'port')
				return 'rectangle';
			else
				return 'ellipse';
		},

      }
    },
    {
      selector: '\$node > node',
      css: {
        'padding-top': '10px',
        'padding-left': '10px',
        'padding-bottom': '10px',
        'padding-right': '10px',
        'text-valign': 'top',
        'text-halign': 'center',
        'background-color': '#bbb',
	'min-zoomed-font-size' : 6,
      }
    },
    {
      selector: 'edge',
      css: {
        'target-arrow-shape': 'triangle',
	'line-color': function(ele){
				if(ele.data('type') == 'front')
					return 'black';
				else
					return 'grey';
			 },
	'line-style': function(ele){
				if(ele.data('type') == 'front')
					return 'solid';
				else
					return 'dashed';
			 },
	'width': function(ele){
				var ret = 1;
				if(ele.data('type') == 'front')
					ret = 3;
				else
					ret = 5;

				if(1)
				if(ele.data('linkcount'))
				{
					ret = (ele.data('linkcount') * ret);
				}

				//console.log(ele.data('id') + ret);
				return ret;
			 },
//	'curve-style': 'segments',
	'font-size': '8',
	'min-zoomed-font-size' : 8,
        'label': 'data(label)',
	'edge-text-rotation': 'autorotate',
	'source-arrow-shape': 'none',
	'target-arrow-shape': 'none'
      }
    },
	{
		selector: '.logical',
		css: {
			'line-color': '#0000ff',
			'width': 5,
			'curve-style': 'segments',
			'z-index': 0
		}
	},
    {
      selector: ':selected',
      css: {
        'background-color': 'black',
        'line-color': 'black',
        'target-arrow-color': 'black',
        'source-arrow-color': 'black'
      }
    },
    {
	selector: '.highlighted',
	css: {
        'line-color': '#ff0000',
        'background-color': '#ff0000'
      }
	},
    {
	selector: '.clhighlighted',
	css: {
        'line-color': '#00ff00',
        'background-color': '#00ff00'
	}
   }
  ];

var cylayout = { name: 'dagre', nodeSep: 3, ready: layoutready, stop: layoutstop };

function highlight(evt) {

	//cy = evt.cy;

	var hlclass = evt.data.hlclass;
	var ele = evt.cyTarget;

	if(!ele.data)
		return;

	if(ele.data('source'))
		ele = cy.$( '#' + ele.data('source'));

	var id = ele.data('id');

	if(ele.isParent())
	{
	//	var childs = cy.$('#' + id).children()
	//	childs.layout({name:'grid', cols: 2, condense: true});
		return;
	}


//	console.log('Event ' + hlclass + ' ' + id );

	// remove existing highlights
	cy.$('.' + hlclass).removeClass(hlclass);

	var hleles = ele.closedNeighborhood();

	var hlcount = hleles.length;

	// max 100
	for(i=0;i<100;i++)
	{
		hleles = hleles.closedNeighborhood();

		if(hlcount == hleles.length)
			break;

		hlcount = hleles.length;

	}
	//console.log('End Loop ' + i );
	hleles.addClass(hlclass);

//	console.log('Event Type: ' + evt.originalEvent.type)
//	console.log('Event Type: ' + evt.type)

	if(evt.type != 'click')
		return;

	var hleles2 = hleles.clone();
	hleles2 = hleles2.add(hleles.parents().clone());

	//var cy2 = evt.data.cy2;

	//var ret = evt.data.ret;

	//var j = ret.parents.concat(ret.nodes).concat(ret.edges.nodes);

	cy2.remove(cy2.elements());
	cy2.add(hleles2);
	//cy2.add(j);
	cy2.layout({name: 'dagre', rankDir: 'LR', ready: layoutready});
}

function cytoscapeswitch(evt)
{
	var b = evt.target;

	var j = null;
	if(b.value == "1")
	{
		j = data.parents.concat(data.edges.parents);
		b.value = "0";
		b.innerHTML = "Ports";
	}
	else
	{
		j = data.parents.concat(data.nodes).concat(data.edges.nodes);
		b.value = "1";
		b.innerHTML = "Objects";
	}

	//layout.stop();
	cy.remove(cy.elements());

	cy.add(j);

	cy.layout(cylayout);
	//layout.run();
}

$.ajax({
	type: "GET",
	url: "{$_SERVER['PHP_SELF']}",
	data: { module: 'redirect',
		page: 'object',
		tab: 'linkmgmt',
		object_id: $object_id,
		op: 'cytoscapemap',
		json: 'json'
		},
	dataTye: 'json',
	error: function(){ alert("Error loading"); },
	success: function(jdata) {

			data = JSON.parse(jdata);

			if(data.debug)
				$('#debug').html(data.debug);

			//j = data.parents.concat(data.edges.parents);
			j = data.parents.concat(data.nodes).concat(data.edges.nodes);

			if(j.length == 0)
			{
				alert("No Links to display. Closing Window");
				window.close();
				return;
			}

			cy2 = cytoscape({
				container: document.getElementById('cy2'),
				//renderer: { name: 'css' },

				boxSelectionEnabled: false,
				autounselectify: true,
				style: cystyle,
				wheelSensitivity: 0.1,
			});
			cy2.style().selector('node').style('label', function(node) { return node.data('label') + '\\n' + node.data('text'); });

			cystyle.push({
				selector: '.loopedge',
				css: {
					'curve-style': 'segments',
				}
			});

			cy = cytoscape({
				container: document.getElementById('cy'),

				boxSelectionEnabled: false,
				autounselectify: true,
				style: cystyle,
				wheelSensitivity: 0.1,
				elements: j,
				layout: cylayout,
			});

			cy.on('mouseover', { hlclass: 'highlighted' }, highlight );
			cy.on('click', { hlclass: 'clhighlighted' }, highlight );

			$('#switch').click(cytoscapeswitch);

			/*
				TODO: node ranking
			*/

			if(0)
			cy.layout({
				name: 'dagre',
				/*
				nodeSep: 5,
				rankDir: 'TB',
				*/
				//edgeSep: 10,
				/*
				edgeWeight: function(edge) {
						if(edge.data('type') == 'front')
							return 10;
						else
							return 1;
						
					},
				*/
				ready: layoutready,
				stop: layoutstop
				});

			if(1)
			cy.$(':child').qtip({

				content: function() {

						return this.id() + '<br>' 
						+ 'Index: ' + this.data('index') + '<br>'
						+ 'Name: ' + this.data('label') + '<br>'
						+ 'Text: ' + this.data('text');
					},
				position: {
					my: 'top center',
					at: 'bottom center'
					},
				style: {
					classes: 'qtip-bootstrap',
					tip: {
						width: 16,
						height: 8
						},
					}
			}); // qtip
		} // success function
	});


}); // on dom ready

function layoutready(evt) {
	var _cy = evt.cy;

	// highlight current object
	var object = _cy.$('#o$object_id');
	if(object.data('has_problems') == 'no')
		object.style('background-color','#ffcccc');

	// highlight object with problems
	_cy.$('node[type = "object"][has_problems != "no"]').style('background-color','#ff0000');

	var e = _cy.$('node[loop = "1"]').style('background-color','#ff6666');

//	console.log(e[0].data('loop'));

//	cy.add({group: 'edges', data: {id:'l691_2991', source: 'p691', target: 'p2991', label: 'test'}, classes: 'logical'});

}

function layoutstop(evt) {
	var _cy = evt.cy;
//	cy.elements().locked = true;
//	cy.add({group: 'nodes', data: {id:'l2493', parent:'p2943', label: 'test'}});


	var les = _cy.$('[loopedge]');

	if(les)
	{
		_cy.batch( function() {
			les.each(function(i, ele) {
				var le = ele.data('loopedge');
				var edge = _cy.add(le);
				edge.addClass('loopedge');
				//edge.style('line-color', '#ffffff'); // TypeError text-transform undefined
			});	
		});
		_cy.$('edge[loop = "1"]').style('line-color','#ff6666');
	}
}
</script>
</head>
<body>
<div id="cy" style="position: absolute; height: 80%; width: 100%; left: 0; top: 20%;"></div>
<div id="cy2" style="position: absolute; height: 20%; width: 100%; left: 0; top: 0%;"></div>
<div id="debug"></div>
<button type="button" id="switch" style="position: absolute;" value="1">Object</button>
</body>
</html>
HTMLEND
); // echo
	exit;
}

/* ------------------------------------- */
class linkmgmt_gvmap {

	private $object_id = NULL;
	private $port_id = NULL;
	private $remote_id = NULL;
	private $hl = NULL;

	private $gv = NULL;

	private $ports = array();

	private $allports = false;
	private $back = NULL;

	private $alpha = 'ff';

	private $errorlevel = NULL;

	public $data = NULL;

	private $pids = array();

	function addlinkchainsobject($object_id)
	{

		$object['ports'] = getObjectPortsAndLinks ($object_id);

		if(empty($object['ports']))
		{

			$hl = false;
			$alpha = $this->alpha;
			if($this->hl == 'o' && $this->object_id == $object_id)
			{
				$hl = true;
				$this->alpha = 'ff';
			}

			$this->_addCluster($object_id, $hl, empty($object['ports']));

			$this->alpha = $alpha;
			return;
		}

		$i = 0;
		foreach($object['ports'] as $key => $port)
		{

			if(isset($this->pids[$port['id']]))
				continue;

			$i++;
			$lc = new lm_linkchain($port['id']);

			$this->pids += $lc->pids;

			if($this->allports ||($lc->linkcount > 0))
				$this->addlinkchain($lc, $i);
		}

	}

	function __construct($object_id = NULL, $port_id = NULL, $allports = false, $hl = NULL, $remote_id = NULL) {
		$this->allports = $allports;

		$this->object_id = $object_id;
		$this->port_id = $port_id;
		$this->remote_id = $remote_id;
		$this->hl = $hl;

		$hllabel = "";

		/* suppress strict standards warnings for Image_GraphViz and PHP 5.4.0
		 * output would corrupt image data
		 */
		$this->errorlevel = error_reporting();
		error_reporting($this->errorlevel & ~E_STRICT);

		$graphattr = array(
					'rankdir' => 'LR',
				//	'ranksep' => '0',
					'nodesep' => '0',
				//	'overlay' => false,
				);

		unset($_GET['module']);

		$_GET['all'] = 1;

		$graphattr['URL'] = $this->_makeHrefProcess($_GET);

		unset($_GET['all']);

		switch($hl)
		{
			case 'o':
			case 'p':
				$this->alpha = '30';
				break;
		}

		//$this->gv = new Image_GraphViz(true, $graphattr, "map".$object_id);
		$this->gv = new lm_Image_GraphViz(true, $graphattr, "map".$object_id);

		/* --------------------------- */
		if($object_id === NULL)
		{
			/* all objects ! */
			unset($_GET['all']);
			$_GET['hl'] = 'o';

			$this->gv->addAttributes(array(
						'label' => 'Showing all objects'.$hllabel,
						'labelloc' => 't',
						)
				);

			$objects = listCells('object');

			foreach($objects as $obj)
				//$this->addlinkchainsobject($obj['id']); // longer runtimes !!
				$this->_add($this->gv, $obj['id'], NULL); // for all still faster and nicer looking graph

			return;
		}
		else
		{
			$object = spotEntity ('object', $object_id);

			$this->gv->addAttributes(array(
						'label' => "Graph for ${object['dname']}$hllabel",
						'labelloc' => 't',
						)
				);

			$this->addlinkchainsobject($object_id);
			//$this->_add($this->gv, $object_id, $port_id);

			$children = getEntityRelatives ('children', 'object', $object_id); //'entity_id'

			foreach($children as $child)
				$this->addlinkchainsobject($child['entity_id']);
			//	$this->_add($this->gv, $child['entity_id'], NULL);
		}

		/* add hl label */
		$this->gv->addAttributes(array(
			'label' =>  $this->gv->graph['attributes']['label'].$hllabel,
				));

	//	lm_linkchain::var_dump_html($this->gv);
//		lm_linkchain::var_dump_html($this->data);

//		echo json_encode($this->data);

	//	$this->gv->saveParsedGraph('/tmp/graph.txt');
	//	error_reporting( E_ALL ^ E_NOTICE);
	 } /* __construct */

	function __destruct() {
		error_reporting($this->errorlevel);
	}

	function _addCluster($object_id, $hl = false, $adddummy = false)
	{
			global $lc_cache;

			$cluster_id = "c".$object_id;

			if(
				!isset($this->gv->graph['clusters'][$cluster_id]) &&
				!isset($this->gv->graph['subgraphs'][$cluster_id])
				|| $hl
			) {
				$rack = null;
				$object = $lc_cache->getobject($object_id, $rack);

			//	$object['attr'] = getAttrValues($object_id);

				$clusterattr = array();

				$this->_getcolor('cluster', 'default', $this->alpha, $clusterattr, 'color');
				$this->_getcolor('cluster', 'default', $this->alpha, $clusterattr, 'fontcolor');

				if($this->object_id == $object_id)
				{
					$clusterattr['rank'] = 'source';

					$this->_getcolor('cluster', 'current', $this->alpha, $clusterattr, 'color');
					$this->_getcolor('cluster', 'current', $this->alpha, $clusterattr, 'fontcolor');
				}

				$clustertitle = htmlspecialchars($object['dname']);
				$clusterattr['tooltip'] = $clustertitle;

				unset($_GET['module']); // makeHrefProcess adds this
				unset($_GET['port_id']);
				unset($_GET['remote_id']);
				$_GET['object_id'] = $object_id;
				//$_GET['hl'] = 'o';

				$clusterattr['URL'] = $this->_makeHrefProcess($_GET);

				//has_problems
				if($object['has_problems'] != 'no')
				{
					$clusterattr['style'] = 'filled';
					$this->_getcolor('cluster', 'problem', $this->alpha, $clusterattr, 'fillcolor');
				}

				if(!empty($object['container_name']))
					$clustertitle .= "<BR/>(${object['container_name']})";

				if(!empty($rack['row_name']) || !empty($rack['name']))
					$clustertitle .= "<BR/><FONT point-size=\"10\">{$rack['row_name']} / {$rack['name']}</FONT>";

				$embedin = $object['container_id'];
				if(empty($embedin))
					$embedin = 'default';
				else
				{
					$embedin = "c$embedin"; /* see cluster_id */

					// TODO
					/* add container / cluster if not already exists */
					$this->addlinkchainsobject($object['container_id']);
				}

				$clusterattr['id'] = "$object_id----"; /* used for js context menu */

				$this->gv->addCluster($cluster_id, $clustertitle, $clusterattr, $embedin);

				/* needed because of  gv_image empty cluster bug (invalid foreach argument) */
				if($adddummy)
					$this->gv->addNode("dummy$cluster_id", array(
					//	'label' =>'No Ports found/connected',
						'label' =>'',
						'fontsize' => 0,
						'size' => 0,
						'width' => 0,
						'height' => 0,
						'shape' => 'point',
						'style' => 'invis',
						), $cluster_id);


			}
	 } // _addCluster

	function _addEdge($port, $linkchain, $loopedge = false)
	{
		global $lm_multilink_port_types;
		if(
			!isset($this->gv->graph['edgesFrom'][$port['id']][$port['remote_id']]) &&
			!isset($this->gv->graph['edgesFrom'][$port['remote_id']][$port['id']])
			|| $loopedge
		) {
			$remote_id = $port['remote_id'];

			$linktype = $port['linktype'];

			$edgetooltip = $port['object_name'].':'.$port['name'].
					' - '.$port['cableid'].' -> '.
					$port['remote_name'].':'.$port['remote_object_name'];

			$edgeattr = array(
					'fontsize' => 8,
					'label' => htmlspecialchars($port['cableid']),
					'tooltip' => $edgetooltip,
					'sametail' => $linktype,
					'samehead' => $linktype,
					'arrowhead' => 'none',
					'arrowtail' => 'none',
				);

			$this->_getcolor('edge', ($linkchain->loop ? 'loop' : 'default'), $this->alpha, $edgeattr, 'color');
			$this->_getcolor('edge', 'default', $this->alpha, $edgeattr, 'fontcolor');

			if($linktype == 'back' )
			{
				$edgeattr['style'] =  'dashed';

				/* multilink ports */
				if(in_array($port['oif_id'], $lm_multilink_port_types))
				{
					$edgeattr['dir'] = 'both';
					$edgeattr['arrowtail'] = 'dot';
				}

				if(in_array($linkchain->ports[$remote_id]['oif_id'], $lm_multilink_port_types))
				{
					$edgeattr['dir'] = 'both';
					$edgeattr['arrowhead'] = 'dot';
				}
			}

			if(
				($port['id'] == $this->port_id && $port['remote_id'] == $this->remote_id) ||
				($port['id'] == $this->remote_id && $port['remote_id'] == $this->port_id)
			)
			{
				$this->_getcolor('edge', 'highlight', 'ff', $edgeattr, 'color');
				$edgeattr['penwidth'] = 2; /* bold */
			}

			unset($_GET['module']);
			$_GET['object_id'] = $port['object_id'];
			$_GET['port_id'] = $port['id'];
			$_GET['remote_id'] = $port['remote_id'];

			$edgeattr['URL'] = $this->_makeHrefProcess($_GET);

			$edgeattr['id'] = $port['object_id']."-".$port['id']."-".$port['remote_id']."-".$linktype; /* for js context menu  */

			if($loopedge)
			{
					$edgeattr = array_merge($edgeattr, array(
					'sametail' => 'loop',
					'samehead' => 'loop',
					'dir' => 'both',
					'arrowhead' => 'invodot',
					'arrowtail' => 'invodot',
					));
			}

			$this->gv->addEdge(array($port['id'] => $port['remote_id']),
						$edgeattr,
						array(
							$port['id'] => $linktype,
							$port['remote_id'] => $linktype,
						)
					);

		}
	} // _addEdge

	function addlinkchain($linkchain, $index)
	{
		global $lm_multilink_port_types;

		$hl = false;
		$alpha = $this->alpha;
		if(
			($this->hl == 'p' && $linkchain->hasport_id($this->port_id))
			|| ($this->hl == 'o' && $linkchain->hasobject_id($this->object_id))
		)
		{
			$hl = true;
			$this->alpha = 'ff';
		}

		$remote_id = null;
		foreach($linkchain as $id => $port)
		{
			$this->_addCluster($port['object_id'], $hl);

			$nodelabel = htmlspecialchars("${port['name']}");
			$text = $nodelabel;

			if($port['iif_id'] != '1' )
			{
				$nodelabel .= "<BR/><FONT POINT-SIZE=\"8\">${port['iif_name']}</FONT>";
				$text .= "\n".$port['iif_name'];
			}

			$nodelabel .= "<BR/><FONT POINT-SIZE=\"8\">${port['oif_name']}</FONT>";
			$text .= "\n".$port['oif_name'];

			// add ip address
			if(isset($port['portip']))
				$nodelabel .= "<BR/><FONT POINT-SIZE=\"8\">".$port['portip']."</FONT>";

			$nodeattr = array(
					'label' => $nodelabel,
					);

			$this->_getcolor('port', ($linkchain->loop ? 'loop' : 'default'),$this->alpha, $nodeattr, 'fontcolor');
			$this->_getcolor('oif_id', $port['oif_id'],$this->alpha, $nodeattr, 'color');

			if($this->port_id == $port['id']) {
				$nodeattr['style'] = 'filled';
				$nodeattr['fillcolor'] = $this->_getcolor('port', 'current', $this->alpha);
			}

			if($this->remote_id == $port['id']) {
				$nodeattr['style'] = 'filled';
				$nodeattr['fillcolor'] = $this->_getcolor('port', 'remote', $this->alpha);
			}

			$nodeattr['tooltip'] = htmlspecialchars("${port['name']}");

			unset($_GET['module']);
			unset($_GET['remote_id']);
			$_GET['object_id'] = $port['object_id'];
			$_GET['port_id'] = $port['id'];
			$_GET['hl'] = 'p';

			$nodeattr['URL'] = $this->_makeHrefProcess($_GET);
			$nodeattr['id'] = "${port['object_id']}-${port['id']}--"; /* for js context menu */

			$this->gv->addNode($port['id'],
					$nodeattr,
					"c${port['object_id']}"); /* see cluster_id */

			$remote_id = $port['remote_id'];

			if($port['portcount'] > 1)
				foreach($port['chains'] as $mlc)
				{
					$this->addlinkchain($mlc, 0); // TODO index
				}

			$prevlinktype = ($port['linktype'] == 'front' ? 'back' : 'front');
			if($port[$prevlinktype]['portcount'] > 1)
			{
				foreach($port[$prevlinktype]['chains'] as $mlc)
				{
					$this->addlinkchain($mlc, 0, false); // TODO index
				}
			}

			if($remote_id)
				$this->_addEdge($port, $linkchain);

		} //foreach

		if($linkchain->loop && $remote_id)
		{
			// TODO separate loop link
			// add loop edge
			$this->_addEdge($port, $linkchain, true);
		}

		// reset alpha to start value
		$this->alpha = $alpha;
	}

	function setFalseOnError($newvalue)
	{
		$this->gv->_returnFalseOnError = $newvalue;
	}

	function _makeHrefProcess($array)
	{
		return str_replace('&','&amp;',makeHrefProcess($array));
	}

	// !!!recursiv !!!
	function _add($gv, $object_id, $port_id = NULL) {
		global $lm_multilink_port_types;

		if($port_id !== NULL) {
			if(isset($this->ports[$port_id])) {
				return;
			}
		}

		if($this->back != 'front' || $port_id === NULL || $this->allports)
		$front = $this->_getObjectPortsAndLinks($object_id, 'front', $port_id, $this->allports);
		else
		$front = array();

		if($this->back != 'back' || $port_id === NULL || $this->allports)
		$backend = $this->_getObjectPortsAndLinks($object_id, 'back', $port_id, $this->allports);
		else
		$backend = array();

		$ports = array_merge($front,$backend);

		/* used only for Graphviz ...
		 * !! numeric ids cause Image_Graphviz problems on nested clusters !!
		 */
		$cluster_id = "c$object_id";

		if(empty($ports))
		{
			/* needed because of  gv_image empty cluster bug (invalid foreach argument) */
			$gv->addNode("dummy$cluster_id", array(
					//	'label' =>'No Ports found/connected',
						'label' =>'',
						'fontsize' => 0,
						'size' => 0,
						'width' => 0,
						'height' => 0,
						'shape' => 'point',
						'style' => 'invis',
						), $cluster_id);

			/* show objects without ports */
			if($object_id === NULL)
				return;
		}

		$object = NULL;
		if($object_id !== NULL) {
			if(
				!isset($gv->graph['clusters'][$cluster_id]) &&
				!isset($gv->graph['subgraphs'][$cluster_id])
			) {

				$object = spotEntity ('object', $object_id);

				$object['portip'] = array();
				foreach(getObjectIPv4Allocations ($object_id) as $ipv4)
				{
					$object['portip'][$ipv4['osif']] = $ipv4['addrinfo']['ip'];
				}

			//	$object['attr'] = getAttrValues($object_id);

				$clusterattr = array();

				$this->_getcolor('cluster', 'default', $this->alpha, $clusterattr, 'color');
				$this->_getcolor('cluster', 'default', $this->alpha, $clusterattr, 'fontcolor');

				if($this->object_id == $object_id)
				{
					$clusterattr['rank'] = 'source';

					$this->_getcolor('cluster', 'current', $this->alpha, $clusterattr, 'color');
					$this->_getcolor('cluster', 'current', $this->alpha, $clusterattr, 'fontcolor');
				}

				$clustertitle = "${object['dname']}";
				$text = "${object['dname']}";
				$clusterattr['tooltip'] = $clustertitle;

				unset($_GET['module']); // makeHrefProcess adds this
				unset($_GET['port_id']);
				unset($_GET['remote_id']);
				$_GET['object_id'] = $object_id;
				//$_GET['hl'] = 'o';

				$clusterattr['URL'] = $this->_makeHrefProcess($_GET);

				//has_problems
				if($object['has_problems'] != 'no')
				{
					$clusterattr['style'] = 'filled';
					$this->_getcolor('cluster', 'problem', $this->alpha, $clusterattr, 'fillcolor');
				}

				if(!empty($object['container_name']))
				{
					$clustertitle .= "<BR/>${object['container_name']}";
					$text .= "\n${object['container_name']}";
				}

				if($object['rack_id'])
				{
					$rack = spotEntity('rack', $object['rack_id']);

					if(!empty($rack['row_name']) || !empty($rack['name']))
					{
						$clustertitle .= "<BR/>${rack['row_name']} / ${rack['name']}";
						$text .= "\n${rack['row_name']} / ${rack['name']}";
					}
				}

				$embedin = $object['container_id'];
				if(empty($embedin))
					$embedin = 'default';
				else
				{
					$embedin = "c$embedin"; /* see cluster_id */

					/* add container / cluster if not already exists */
					$this->_add($gv, $object['container_id'], NULL);
				}

				$clusterattr['id'] = "$object_id----"; /* used for js context menu */

				$gv->addCluster($cluster_id, $clustertitle, $clusterattr, $embedin);

			} /* isset cluster_id */
		} /* object_id !== NULL */

		foreach($ports as $key => $port) {

			$this->back = $port['linktype'];

			if(!isset($this->ports[$port['id']])) {


				$nodelabel = htmlspecialchars("${port['name']}");
				$text = $nodelabel;

				if($port['iif_id'] != '1' )
				{
					$nodelabel .= "<BR/><FONT POINT-SIZE=\"8\">${port['iif_name']}</FONT>";
					$text .= "\n".$port['iif_name'];
				}

				$nodelabel .= "<BR/><FONT POINT-SIZE=\"8\">${port['oif_name']}</FONT>";
				$text .= "\n".$port['oif_name'];

				// add ip address
				if($object)
					if(isset($object['portip'][$port['name']]))
					{
						$nodelabel .= "<BR/><FONT POINT-SIZE=\"8\">".$object['portip'][$port['name']]."</FONT>";
						$text .= "\n".$object['portip'][$port['name']];
					}

				$nodeattr = array(
							'label' => $nodelabel,
						);

				$this->_getcolor('port', 'default',$this->alpha, $nodeattr, 'fontcolor');
				$this->_getcolor('oif_id', $port['oif_id'],$this->alpha, $nodeattr, 'color');

				if($this->port_id == $port['id']) {
					$nodeattr['style'] = 'filled';
					$nodeattr['fillcolor'] = $this->_getcolor('port', 'current', $this->alpha);
				}

				if($this->remote_id == $port['id']) {
					$nodeattr['style'] = 'filled';
					$nodeattr['fillcolor'] = $this->_getcolor('port', 'remote', $this->alpha);
				}

				$nodeattr['tooltip'] = htmlspecialchars("${port['name']}");

				unset($_GET['module']);
				unset($_GET['remote_id']);
				$_GET['object_id'] = $port['object_id'];
				$_GET['port_id'] = $port['id'];
				$_GET['hl'] = 'p';

				$nodeattr['URL'] = $this->_makeHrefProcess($_GET);
				$nodeattr['id'] = "${port['object_id']}-${port['id']}--"; /* for js context menu */

				$gv->addNode($port['id'],
						$nodeattr,
						"c${port['object_id']}"); /* see cluster_id */

				$this->ports[$port['id']] = true;

			} /* isset port */

			if(!empty($port['remote_id'])) {

				if($this->object_id !== NULL)
					$this->_add($gv, $port['remote_object_id'], $port['remote_id']);

				if(
					!isset($gv->graph['edgesFrom'][$port['id']][$port['remote_id']]) &&
					!isset($gv->graph['edgesFrom'][$port['remote_id']][$port['id']])
				) {

					$linktype = $port['linktype'];

					$edgetooltip = $port['object_name'].':'.$port['name'].
							' - '.$port['cableid'].' -> '.
							$port['remote_name'].':'.$port['remote_object_name'];

					$edgeattr = array(
							'fontsize' => 8,
							'label' => htmlspecialchars($port['cableid']),
							'tooltip' => $edgetooltip,
							'sametail' => $linktype,
							'samehead' => $linktype,
						);

					$this->_getcolor('edge', 'default', $this->alpha, $edgeattr, 'color');
					$this->_getcolor('edge', 'default', $this->alpha, $edgeattr, 'fontcolor');

					if($linktype == 'back' )
					{
						$edgeattr['style'] =  'dashed';
						$edgeattr['arrowhead'] = 'none';
						$edgeattr['arrowtail'] = 'none';

						/* multilink ports */
						if(in_array($port['oif_id'], $lm_multilink_port_types))
						{
							$edgeattr['dir'] = 'both';
							$edgeattr['arrowtail'] = 'dot';
						}

						if(in_array($port['remote_oif_id'], $lm_multilink_port_types))
						{
							$edgeattr['dir'] = 'both';
							$edgeattr['arrowhead'] = 'dot';
						}
					}

					if(
						($port['id'] == $this->port_id && $port['remote_id'] == $this->remote_id) ||
						($port['id'] == $this->remote_id && $port['remote_id'] == $this->port_id)
					)
					{
						$this->_getcolor('edge', 'highlight', 'ff', $edgeattr, 'color');
						$edgeattr['penwidth'] = 2; /* bold */
					}

					unset($_GET['module']);
					$_GET['object_id'] = $port['object_id'];
					$_GET['port_id'] = $port['id'];
					$_GET['remote_id'] = $port['remote_id'];

					$edgeattr['URL'] = $this->_makeHrefProcess($_GET);

					$edgeattr['id'] = $port['object_id']."-".$port['id']."-".$port['remote_id']."-".$linktype; /* for js context menu  */

					$gv->addEdge(array($port['id'] => $port['remote_id']),
								$edgeattr,
								array(
									$port['id'] => $linktype,
									$port['remote_id'] => $linktype,
								)
							);

				}
			}

		}

	//	lm_linkchain::var_dump_html($port);
	}

	function fetch($type = 'png', $command = NULL, $format2 = NULL, &$data2 = NULL) {

		$tmpdata = $data2;
		$ret = $this->gv->fetch($type, $command, $format2, $tmpdata);
		if($data2 !== NULL)
			$data2 = $tmpdata;
		return $ret;
	}

	function image($type = 'png', $command = NULL) {
		$this->gv->image($type, $command);
	}

	function parse() {
		return $this->gv->parse();
	}

	/* should be compatible with getObjectPortsAndLinks from RT database.php */
	function _getObjectPortsAndLinks($object_id, $linktype = 'front', $port_id = NULL, $allports = false) {

		if($linktype == 'front')
			$linktable = 'Link';
		else
			$linktable = 'LinkBackend';

		$qparams = array();

		$query = "SELECT
				'$linktype' as linktype,
				Port.*,
				Port.type AS oif_id,
				PortInnerInterface.iif_name as iif_name,
				POI.oif_name as oif_name,
				Object.id as object_id, Object.name as object_name,
				IFNULL(LinkTable_a.cable,LinkTable_b.cable) as cableid,
				remoteObject.id as remote_object_id, remoteObject.name as remote_object_name,
				remotePort.id as remote_id, remotePort.name as remote_name,
				remotePort.type AS remote_oif_id,
				remotePortInnerInterface.iif_name as remote_iif_name,
				remotePOI.oif_name as remote_oif_name
			FROM Port";

		// JOIN
		$join = "	LEFT JOIN PortInnerInterface on PortInnerInterface.id = Port.iif_id
				LEFT JOIN PortOuterInterface AS POI on POI.id = Port.type
				LEFT JOIN $linktable as LinkTable_a on Port.id = LinkTable_a.porta
				LEFT JOIN $linktable as LinkTable_b on Port.id = LinkTable_b.portb
				LEFT JOIN Object on Object.id = Port.object_id
				LEFT JOIN Port as remotePort on remotePort.id = IFNULL(LinkTable_a.portb, LinkTable_b.porta)
				LEFT JOIN Object as remoteObject on remoteObject.id = remotePort.object_id
				LEFT JOIN PortInnerInterface as remotePortInnerInterface on remotePortInnerInterface.id = remotePort.iif_id
				LEFT JOIN PortOuterInterface as remotePOI on remotePOI.id = remotePort.type
			";

		// WHERE
		if($port_id === NULL)
		{
			$where = " WHERE Object.id = ?";
			$qparams[] = $object_id;

			if(!$allports) {
				$where .= " AND remotePort.id is not NULL";

				if($linktype != 'front') {
					$join .= "
						  LEFT JOIN Link as FrontLink_a on Port.id = FrontLink_a.porta
						  LEFT JOIN Link as FrontLink_b on Port.id = FrontLink_b.portb
						  LEFT JOIN Link as FrontRemoteLink_a on remotePort.id = FrontRemoteLink_a.porta
						  LEFT JOIN Link as FrontRemoteLink_b on remotePort.id = FrontRemoteLink_b.portb
						";
					$where .= " AND ( (FrontLink_a.porta is not NULL or FrontLink_b.portb is not NULL )
						 OR  (FrontRemoteLink_a.porta is not NULL or FrontRemoteLink_b.portb is not NULL) )";
				}
			}
		}
		else
		{
		//	$where = " WHERE Port.id = ? and remotePort.id is not NULL";
			$where = " WHERE Port.id = ?";
			$qparams[] = $port_id;
		}

		// ORDER
		$order = " ORDER by oif_name, Port.Name";

		$query .= $join.$where.$order;

		//echo "$port_id: $query<br><br>";

		$result = usePreparedSelectBlade ($query, $qparams);

		$row = $result->fetchAll(PDO::FETCH_ASSOC);

		$result->closeCursor();

		return $row;
	}

	function _getcolor($type = 'object', $key = 'default', $alpha = 'ff', &$array = NULL , $arraykey = 'color') {

		$object = array(
				'current' => '#ff0000',
				);
		$port = array(
				'current' => '#ffff90',
				'remote' => '#ffffD0',
				'loop' => '#ff6666',
				);

		$cluster = array(
				'current' => '#ff0000',
				'problem' => '#ff3030',
				);

		$edge = array (
				'highlight' => '#ff0000',
				'loop' => '#ff6666',
				);

		$oif_id = array(
				'16' => '#800000', /* AC-in */
				'1322' => '#ff4500', /* AC-out */
				'24' => '#000080', /* 1000base-t */
				);

		$defaultcolor = '#000000'; /* black */
		$default = true;

		if(isset(${$type}[$key]))
		{
			$default = false;
			$color = ${$type}[$key];
		}
		else
			$color = $defaultcolor;


		if($alpha != 'ff' || $default == false)
		{
			$color .= $alpha;

			if($array !== NULL)
				$array[$arraykey] = $color;
			else
				return $color;
		}
		else
			return $defaultcolor;

	} /* _getcolor */

	function dump() {
		var_dump($this->gv);
	}

	function export() {
		var_export($this->gv);
	}

} /* class gvmap */

/* -------------------------------------------------- */

function linkmgmt_updateCableIdAJAX()
{
	$text = strip_tags(genericAssertion ('text', 'string0'));
	$linktype = genericAssertion ('linktype', 'string');
	$id1 = genericAssertion ('id1', 'uint');
	$id2 = genericAssertion ('id2', 'uint');

	$port_info = getPortInfo ($id1);
	fixContext (spotEntity ('object', $port_info['object_id']));
	assertPermission ('object', 'ports', 'editPort');

	if(!permitted(NULL, NULL, 'set_link'))
		return 'Permission denied!';

	if($linktype == 'back')
		linkmgmt_commitUpdatePortLink($id1, $id2, $text, TRUE);
	else
	{
		if (! $port_info['linked'])
			throw new RackTablesError ('Can\'t update cable ID: port is not linked');
		linkmgmt_commitUpdatePortLink($id1, $id2, $text);
	}

	//if ($port_info['reservation_comment'] !== $text)
	//	commitUpdatePortLink ($port_info['id'], $text);
	echo 'OK';
}

/* -------------------------------------------------- */

/* similar to commitUpatePortLink in database.php with backend support */
function linkmgmt_commitUpdatePortLink($port_id1, $port_id2, $cable = NULL, $backend = FALSE) {

	/* TODO check permissions */

	if($backend)
		$table = 'LinkBackend';
	else
		$table = 'Link';

	return usePreparedExecuteBlade
		(
			"UPDATE $table SET cable=\"".(mb_strlen ($cable) ? $cable : NULL).
			"\" WHERE ( porta = ? and portb = ?) or (portb = ? and porta = ?)",
			array (
				$port_id1, $port_id2,
				$port_id1, $port_id2)
		);

} /* linkmgmt_commitUpdatePortLink */

/* -------------------------------------------------- */

function linkmgmt_opunlinkPort() {
	$port_id = $_REQUEST['port_id'];
	$linktype = $_REQUEST['linktype'];

	/* check permissions */
	if(!permitted(NULL, NULL, 'set_link')) {
		exit;
	}

	if($linktype == 'back')
	{
		$table = 'LinkBackend';
		$remote_id = $_REQUEST['remote_id'];

		$retval =  usePreparedExecuteBlade
			(
				"DELETE FROM $table WHERE ( porta = ? and portb = ?) or (portb = ? and porta = ?)",
				array (
					$port_id, $remote_id,
					$port_id, $remote_id)
			);

		if($retval == 0)
			showWarning("Link not found");
		else
			showSuccess("Backend Link deleted");

	}
	else
	{
		// RT function for normal links
		unlinkPort();
	}
} /* opunlinkPort */

/* -------------------------------------------------- */

function linkmgmt_oplinkPort() {

	$linktype = $_REQUEST['linktype'];
	$cable = $_REQUEST['cable'];

	/* check permissions */
	if(!permitted(NULL, NULL, 'set_link')) {
		echo("Permission denied!");
		return;
	}

	if(!isset($_REQUEST['link_list'])) {
		//lm_linkchain::var_dump_html($_REQUEST);
		$porta = $_REQUEST['port'];

		foreach($_REQUEST['remote_ports'] as $portb)
		{
			$link_list[] = "${porta}_${portb}";

			/* with no LM_MULTILINK process first value only */
			if(!LM_MULTILINK)
				break;
		}
	} else
		$link_list = $_REQUEST['link_list'];

	foreach($link_list as $link){

		$ids = preg_split('/[^0-9]/',$link);
		$porta = $ids[0];;
		$portb = $ids[1];

		$ret = linkmgmt_linkPorts($porta, $portb, $linktype, $cable);

		//error_log("$ret - $porta - $portb");
		$port_info = getPortInfo ($porta);
		$remote_port_info = getPortInfo ($portb);
		showSuccess(
                        sprintf
                        (
                                'Port %s %s successfully linked with port %s %s',
                                formatPortLink ($port_info['id'], $port_info['name'], NULL, NULL),
				$linktype,
                                formatPort ($remote_port_info),
				$linktype
                        )
                );
	}

	addJS (<<<END
window.opener.location.reload(true);
window.close();
END
                , TRUE);

	return;
} /* oplinkPort */

/* -------------------------------------------------- */

/*
 * same as in database.php extendend with linktype
 */
function linkmgmt_linkPorts ($porta, $portb, $linktype, $cable = NULL)
{
        if ($porta == $portb)
                throw new InvalidArgException ('porta/portb', $porta, "Ports can't be the same");

	if($linktype == 'back')
	{
		$table = 'LinkBackend';
		$multilink = LM_MULTILINK;
	}
	else
	{
		$table = 'Link';
		$multilink = false;
	}

        global $dbxlink;
        $dbxlink->exec ('LOCK TABLES '.$table.' WRITE');

	if(!$multilink)
	{
		$result = usePreparedSelectBlade
		(
			'SELECT COUNT(*) FROM '.$table.' WHERE porta IN (?,?) OR portb IN (?,?)',
			array ($porta, $portb, $porta, $portb)
		);

	        if ($result->fetchColumn () != 0)
	        {
			$dbxlink->exec ('UNLOCK TABLES');
			return "$linktype Port ${porta} or ${portb} is already linked";
		}
	        $result->closeCursor ();
	}

        if ($porta > $portb)
        {
                $tmp = $porta;
                $porta = $portb;
                $portb = $tmp;
        }
        $ret = FALSE !== usePreparedInsertBlade
        (
                $table,
                array
                (
                        'porta' => $porta,
                        'portb' => $portb,
                        'cable' => mb_strlen ($cable) ? $cable : ''
                )
        );
        $dbxlink->exec ('UNLOCK TABLES');
        $ret = $ret and FALSE !== usePreparedExecuteBlade
        (
                'UPDATE Port SET reservation_comment=NULL WHERE id IN(?, ?)',
                array ($porta, $portb)
        );
        return $ret ? '' : 'query failed';
}

/* -------------------------------------------------- */

/*
 * similar to renderPopupHTML in popup.php
 */
function linkmgmt_opPortLinkDialog() {
//	lm_linkchain::var_dump_html($_REQUEST);
header ('Content-Type: text/html; charset=UTF-8');
?><!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en" style="height: 100%;">
<?php

	$text = '<div style="background-color: #f0f0f0; border: 1px solid #3c78b5; padding: 10px; text-align: center;
 margin: 5px;">';

	if(permitted(NULL,NULL,"set_link"))
		if (isset ($_REQUEST['do_link'])) {
			$text .= getOutputOf ('linkmgmt_oplinkPort');
		}
		else
			if(isset($_REQUEST['byname']))
				$text .= getOutputOf ('linkmgmt_renderPopupPortSelectorbyName');
			else
				$text .= getOutputOf ('linkmgmt_renderPopupPortSelector');
	else
		$text .= "Permission denied!";

        $text .= '</div>';

	echo '<head><title>RackTables pop-up</title>';
        printPageHeaders();
        echo '</head>';
        echo '<body style="height: 100%;">' . $text . '</body>';
?>
</html>
<?php
	exit;
} /* opPortLinkDialog */

/* -------------------------------------------------- */

/*
 * like findSparePorts in popup.php extended with linktype
 *
 * multilink
 *
 */
function linkmgmt_findSparePorts($port_info, $filter, $linktype, $multilink = false, $objectsonly = false, $byname = false, $portcompat = true, $src_object_id = NULL) {


	/*
		$linktable ports that will be returned if not linked in this table
		$linkinfotable display link for info only show backend links if you want front link a port

		front: select ports no front connection and port compat, filter, ...

		back:

	 */

	if($linktype == 'back')
	{
		$linktable = 'LinkBackend';
		$linkinfotable = 'Link';
	}
	else
	{
		$linktable = 'Link';
		$linkinfotable = 'LinkBackend';
	}

	$qparams = array();
	$whereparams = array();

	// all ports with no link
	/* port:object -> linked port:object */
	$query = 'SELECT';
	$join = "";
	$where = " WHERE";
	$group = "";
	$order = " ORDER BY";

	if($objectsonly)
	{
		$query .= " remoteObject.id, CONCAT(IFNULL(remoteObject.name, CONCAT('[',remoteObjectDictionary.dict_value,']')), ' (', count(remoteObject.id), ')') as name";
		$group .= " GROUP by remoteObject.id";
	}
	else
		if($byname)
		{
			if($linktype == 'back')
				$arrow = '=?=>';
			else
				$arrow = '-?->';

			$query .= ' CONCAT(localPort.id, "_", remotePort.id),
				 CONCAT(IFNULL(localObject.name, CONCAT("[",localObjectDictionary.dict_value,"]")), " : ", localPort.Name, " '.$arrow.'", remotePort.name, " : ", IFNULL(remoteObject.name,CONCAT("[",remoteObjectDictionary.dict_value,"]")))';
		}
		else
		{

			if($linktype == 'front')
				$arrow = '==';
			else
				$arrow = '--';

			$query .= " remotePort.id, CONCAT(IFNULL(remoteObject.name, CONCAT('[',remoteObjectDictionary.dict_value,']')), ' : ', remotePort.name,
				IFNULL(CONCAT(' $arrow ', IFNULL(IFNULL(infolnk_a.cable,infolnk_b.cable),''), ' $arrow> ', InfoPort.name, ' : ', IFNULL(InfoObject.name,CONCAT('[',InfoObjectDictionary.dict_value,']'))),'') ) as Text";
		}

	$query .= " FROM Port as remotePort";
	$join .= " LEFT JOIN Object as remoteObject on remotePort.object_id = remoteObject.id";
	$order .= " remoteObject.name";

	/* object type name */
	$join .= " LEFT JOIN Dictionary as remoteObjectDictionary on (remoteObjectDictionary.chapter_id = 1 AND remoteObject.objtype_id = remoteObjectDictionary.dict_key)";

	if($byname)
	{
		/* by name */
		$join .= " JOIN Port as localPort on remotePort.name = localPort.name";
		$where .= " remotePort.object_id <> ? AND localPort.object_id = ?";
		$whereparams[] = $src_object_id;
		$whereparams[] = $src_object_id;

		/* own port not linked */
		$join .= " LEFT JOIN $linktable as localLink_a on localPort.id = localLink_a.porta";
		$where .= " AND localLink_a.porta is NULL";
		$join .= " LEFT JOIN $linktable as localLink_b on localPort.id = localLink_b.portb";
		$where .= " AND localLink_b.portb is NULL";
		$join .= " LEFT JOIN Object as localObject on localObject.id = localPort.object_id";

		/* object type name */
		$join .= " LEFT JOIN Dictionary as localObjectDictionary on (localObject.objtype_id = localObjectDictionary.dict_key  AND localObjectDictionary.chapter_id = 1)";
	}
	else
	{
		/* exclude current port */
		$where .= " remotePort.id <> ?";
		$whereparams[] = $port_info['id'];

		if(! $objectsonly)
			$order .= " ,remotePort.name";

		/* add info to remoteport */
		$join .= " LEFT JOIN $linkinfotable as infolnk_a on remotePort.id = infolnk_a.porta";
		$join .= " LEFT JOIN $linkinfotable as infolnk_b on remotePort.id = infolnk_b.portb";
		$join .= " LEFT JOIN Port as InfoPort on InfoPort.id = IFNULL(infolnk_a.portb, infolnk_b.porta)";
		$join .= " LEFT JOIN Object as InfoObject on InfoObject.id = InfoPort.object_id";

		/* object type name */
		$join .= " LEFT JOIN Dictionary as InfoObjectDictionary on (InfoObject.objtype_id = InfoObjectDictionary.dict_key  AND InfoObjectDictionary.chapter_id = 1)";
	}

	/* only ports which are not linked already */
	$join .= " LEFT JOIN $linktable as lnk_a on remotePort.id = lnk_a.porta";
	$where .= " AND lnk_a.porta is NULL";
	$join .= " LEFT JOIN $linktable as lnk_b on remotePort.id = lnk_b.portb";
	$where .= " AND lnk_b.portb is NULL";

	if($portcompat)
	{
		/* port compat */
		$join .= ' INNER JOIN PortInnerInterface pii ON remotePort.iif_id = pii.id
			INNER JOIN PortOuterInterface poi ON remotePort.type = poi.id';
		// porttype filter (non-strict match)
		$join .= ' INNER JOIN (
			SELECT Port.id FROM Port
			INNER JOIN
			(
				SELECT DISTINCT pic2.iif_id
					FROM PortInterfaceCompat pic2
					INNER JOIN PortCompat pc ON pc.type2 = pic2.oif_id';

                if ($port_info['iif_id'] != 1)
                {
                        $join .= " INNER JOIN PortInterfaceCompat pic ON pic.oif_id = pc.type1 WHERE pic.iif_id = ?";
                        $qparams[] = $port_info['iif_id'];
                }
                else
                {
                        $join .= " WHERE pc.type1 = ?";
                        $qparams[] = $port_info['oif_id'];
                }
                $join .= " AND pic2.iif_id <> 1
			 ) AS sub1 USING (iif_id)
			UNION
			SELECT Port.id
			FROM Port
			INNER JOIN PortCompat ON type1 = type
			WHERE iif_id = 1 and type2 = ?
			) AS sub2 ON sub2.id = remotePort.id";
			$qparams[] = $port_info['oif_id'];
	}


	$qparams = array_merge($qparams, $whereparams);

	 // rack filter
        if (! empty ($filter['racks']))
        {
                $where .= ' AND remotePort.object_id IN (SELECT DISTINCT object_id FROM RackSpace WHERE rack_id IN (' .
                        questionMarks (count ($filter['racks'])) . ')) ';
                $qparams = array_merge ($qparams, $filter['racks']);
        }

	// object_id filter
        if (! empty ($filter['object_id']))
        {
                $where .= ' AND remoteObject.id = ?';
                $qparams[] = $filter['object_id'];
        }
	else
	// objectname filter
        if (! empty ($filter['objects']))
        {
                $where .= ' AND remoteObject.name like ? ';
                $qparams[] = '%' . $filter['objects'] . '%';
        }

        // portname filter
        if (! empty ($filter['ports']))
        {
                $where .= ' AND remotePort.name LIKE ? ';
                $qparams[] = '%' . $filter['ports'] . '%';
        }

	$query .= $join.$where.$group.$order;

	$result = usePreparedSelectBlade ($query, $qparams);

	$row = $result->fetchAll(PDO::FETCH_GROUP|PDO::FETCH_UNIQUE|PDO::FETCH_COLUMN);

	$result->closeCursor();

	/* [id] => displaystring */
	return $row;

} /* findSparePorts */

/* -------------------------------------------------- */

/*
 * like renderPopupPortSelector in popup.php extenden with linktype
 */
function linkmgmt_renderPopupPortSelector()
{
	global $lm_multilink_port_types;

        assertUIntArg ('port');
        $port_id = $_REQUEST['port'];

	$showlinktypeswitch = false;

	if(isset($_GET['linktype']))
		$linktype = $_GET['linktype'];
	else
		$linktype = 'front';

	if($linktype == 'both')
	{

		/*
		 * 	use POST front/back_view to set linktype
		 *	and show linktype switch button
		 */

		$showlinktypeswitch = true;

		if(isset($_POST['front_view']))
			$linktype = 'front';
		else
		if(isset($_POST['back_view']))
			$linktype = 'back';
		else
			$linktype = 'front';
	}

//	lm_linkchain::var_dump_html($_POST);

	$portcompat = true;

	if($linktype == 'back')
	{
		if(isset($_POST['portcompat']))
			$portcompat = $_POST['portcompat'];
	}

	$object_id = $_REQUEST['object_id'];
        $port_info = getPortInfo ($port_id);

	$multilink = LM_MULTILINK && $linktype == 'back' && in_array($port_info['oif_id'], $lm_multilink_port_types);

        if(isset ($_REQUEST['in_rack']))
		$in_rack = $_REQUEST['in_rack'] != 'off';
	else
		$in_rack = true;

//	lm_linkchain::var_dump_html($port_info);
//	lm_linkchain::var_dump_html($_GET);
//	lm_linkchain::var_dump_html($_POST);

        // fill port filter structure
        $filter = array
        (
                'racks' => array(),
                'objects' => '',
                'object_id' => '',
                'ports' => '',
        );

	$remote_object = NULL;
	if(isset($_REQUEST['remote_object']))
	{
		$remote_object = $_REQUEST['remote_object'];

		if($remote_object != 'NULL')
			$filter['object_id'] = $remote_object;
	}

        if (isset ($_REQUEST['filter-obj']))
                $filter['objects'] = $_REQUEST['filter-obj'];
        if (isset ($_REQUEST['filter-port']))
                $filter['ports'] = $_REQUEST['filter-port'];
        if ($in_rack)
        {
                $object = spotEntity ('object', $port_info['object_id']);
                if ($object['rack_id'])
                        $filter['racks'] = getProximateRacks ($object['rack_id'], getConfigVar ('PROXIMITY_RANGE'));
        }

	$objectlist = array('NULL' => '- Show All -');
	$objectlist = $objectlist + linkmgmt_findSparePorts($port_info, $filter, $linktype, $multilink, true, false, $portcompat);

	$spare_ports = linkmgmt_findSparePorts ($port_info, $filter, $linktype, $multilink, false, false, $portcompat);

	$maxsize  = getConfigVar('MAXSELSIZE');
	$objectcount = count($objectlist);

	if($linktype == 'back')
		$notlinktype = 'front';
	else
		$notlinktype = 'back';

        // display search form
        echo 'Link '.$linktype.' of ' . formatPort ($port_info) . ' to...';
        echo '<form method=POST>';
        startPortlet ($linktype.' Port list filter');
       // echo '<input type=hidden name="module" value="popup">';
       // echo '<input type=hidden name="helper" value="portlist">';

        echo '<input type=hidden name="port" value="' . $port_id . '">';
        echo '<table><tr><td valign="top"><table><tr><td>';

	echo '<table align="center"><tr>';

//	echo '<td nowrap="nowrap"><input type="hidden" name="linktype" value="front" /><input type="checkbox" name="linktype" value="back"'.($linktype == 'back' ? ' checked="checked"' : '' ).'>link backend</input></td></tr><tr>';
        echo '<td class="tdleft"><label>Object name:<br><input type=text size=8 name="filter-obj" value="' . htmlspecialchars ($filter['objects'], ENT_QUOTES) . '"></label></td>';
        echo '<td class="tdleft"><label>Port name:<br><input type=text size=6 name="filter-port" value="' . htmlspecialchars ($filter['ports'], ENT_QUOTES) . '"></label></td>';
        echo '<td class="tdleft" valign="bottom"><input type="hidden" name="in_rack" value="off" /><label><input type=checkbox value="1" name="in_rack"'.($in_rack ? ' checked="checked"' : '').' onchange="this.form.submit();">Nearest racks</label></td>';
        echo '</tr></table>';

	echo '</td></tr><tr><td>';
        echo 'Object name (count ports)<br>';
        echo getSelect ($objectlist, array ('name' => 'remote_object',
						'size' => ($objectcount <= $maxsize ? $objectcount : $maxsize)),
						 $remote_object, FALSE);

	echo '</td></tr></table></td>';
        echo '<td valign="top"><table><tr><td><input type=submit value="update objects / ports"></td></tr>';

	if($showlinktypeswitch)
		echo '<tr height=150px><td><input type=submit value="Switch to '.$notlinktype.' view" name="'.$notlinktype.'_view"></tr></td>';

	if($linktype == 'back')
	{
		echo '<input type="hidden" name="portcompat" value="0">';
		echo '<tr height=150px><td><input type=checkbox onchange="this.form.submit();" name="portcompat"'.( $portcompat ? 'checked="checked" ' : '' ).'value="1">Port Compatibility</input></tr></td>';
		echo '<input type="hidden" name="back_view">';
	}

	echo '</table></td>';

        finishPortlet();
        echo '</td><td>';

        // display results
        startPortlet ('Compatible spare '.$linktype.' ports');
	echo "spare $linktype Object:Port -- $notlinktype cableID -->  $notlinktype Port:Object<br>";

	if($multilink)
		echo "Multilink<br>";

        if (empty ($spare_ports))
                echo '(nothing found)';
        else
        {
		$linkcount = count($spare_ports);

		echo "<select id=remote_ports[] tabindex=1 name=remote_ports[] size=".($linkcount <= $maxsize ? $linkcount : $maxsize).($multilink ? " multiple=multiple" : "").">";

		$ret = '';
		foreach ($spare_ports as $key => $value)
		{
			$ret .= "<option value='${key}'>";
			$ret .= stringForOption ($value, 60) . '</option>';
		}

		echo $ret;

		echo "</select>";

                echo "<p>$linktype Cable ID: <input type=text id=cable name=cable>";
                echo "<p><input type='submit' value='Link $linktype' name='do_link'>";
        }
        finishPortlet();
        echo '</td></tr></table>';
        echo '</form>';

} /* linkmgmt_renderPopUpPortSelector */

/* -------------------------------------------------- */

/*
 * similar to renderPopupPortSelector but let you select the destination object
 * and displays possible backend links with ports of the same name
 */
function linkmgmt_renderPopupPortSelectorbyName()
{
	$linktype = $_REQUEST['linktype'];
	$object_id = $_REQUEST['object_id'];

	$object = spotEntity ('object', $object_id);

	$objectlist = linkmgmt_findSparePorts(NULL, NULL, $linktype, false, true, TRUE, false, $object_id);

	$objectname = $object['dname'];

	/* remove self from list */
	unset($objectlist[$object_id]);

	if(isset($_REQUEST['remote_object']))
		$remote_object = $_REQUEST['remote_object'];
	else
	{
		/* choose first object from list */
		$keys = array_keys($objectlist);

		if(isset($keys[0]))
			$remote_object = $keys[0];
		else
			$remote_object = NULL;
	}

	if($remote_object)
	{
		$filter['object_id'] = $remote_object;
		$link_list = linkmgmt_findSparePorts(NULL, $filter, $linktype, false, false, TRUE, false, $object_id);
	}
	else
		$link_list = linkmgmt_findSparePorts(NULL, NULL, $linktype, false, false, TRUE, false, $object_id);

        // display search form
        echo 'Link '.$linktype.' of ' . formatPortLink($object_id, $objectname, NULL, NULL) . ' Ports by Name to...';
        echo '<form method=POST>';

        echo '<table align="center"><tr><td>';
        startPortlet ('Object list');

	$maxsize  = getConfigVar('MAXSELSIZE');
	$objectcount = count($objectlist);

        echo 'Object name (count ports)<br>';
        echo getSelect ($objectlist, array ('name' => 'remote_object',
						'size' => ($objectcount <= $maxsize ? $objectcount : $maxsize)),
						 $remote_object, FALSE);
        echo '</td><td><input type=submit value="show '.$linktype.' ports>"></td>';
        finishPortlet();

        echo '<td>';
        // display results
        startPortlet ('Possible Backend Link List');
	echo "Select links to create:<br>";

	//lm_linkchain::var_dump_html($link_list);
        if (empty ($link_list))
                echo '(nothing found)';
        else
        {
		$linkcount = count($link_list);

		echo "<select id=link_list[] tabindex=1 name=link_list[] size=".($linkcount <= $maxsize ? $linkcount : $maxsize)." multiple=multiple>";

		$ret = '';
		foreach ($link_list as $key => $value)
		{
			$ret .= "<option value='${key}'>";
			$ret .= stringForOption ($value, 60) . '</option>';
		}

		echo $ret;

		echo "</select>";

                echo "<p>$linktype Cable ID: <input type=text id=cable name=cable>";
                echo "<p><input type='submit' value='Link $linktype' name='do_link'>";
        }
        finishPortlet();
        echo '</td></tr></table>';
        echo '</form>';

} /* linkmgmt_renderPopUpPortSelectorByName */

/* ------------------------------------------------ */

function linkmgmt_tabhandler($object_id) {
	global $lm_cache;

	/* TODO  if (permitted (NULL, 'ports', 'set_reserve_comment')) */
	/* TODO Link / unlink permissions  */

	$lm_cache['allowcomment'] = permitted(NULL, NULL, 'set_reserve_comment'); /* RackCode {$op_set_reserve_comment} */
	$lm_cache['allowlink'] = permitted(NULL, NULL, 'set_link'); /* RackCode {$op_set_link} */
	$lm_cache['allowbacklink'] = permitted(NULL, NULL, 'set_backlink'); /* RackCode {$op_set_backlink} */

	//lm_linkchain::var_dump_html($lm_cache);

	if($lm_cache['allowlink'] || $lm_cache['allowcomment'])
		addJS('js/inplace-edit.js');

	/* linkmgmt for current object */
	linkmgmt_renderObjectLinks($object_id);

	/* linkmgmt for every child */
	//$parents = getEntityRelatives ('parents', 'object', $object_id);
	$children = getEntityRelatives ('children', 'object', $object_id); //'entity_id'

	foreach($children as $child) {
		$childobj = spotEntity($child['entity_type'], $child['entity_id']);

		echo '<h1>Links for Child: '.$childobj['name'].'</h1>';
		linkmgmt_renderObjectLinks($child['entity_id']);
		unset($childobj);
	}

	if (isset ($_REQUEST['hl_port_id']))
	{
		assertUIntArg ('hl_port_id');
		$hl_port_id = intval ($_REQUEST['hl_port_id']);
		addJS (<<<ENDJS
$(document).ready(function() {
	var anchor = document.getElementById('$hl_port_id');
	if (anchor)
		anchor.scrollIntoView(false);
});
ENDJS
	, TRUE);
	}

	return;

} /* tabhandler */

/* -------------------------------------------------- */
function linkmgmt_renderObjectLinks($object_id) {

	$object = spotEntity ('object', $object_id);
        $object['attr'] = getAttrValues($object_id);

	/* get ports */
	/* calls getObjectPortsAndLinks */
	amplifyCell ($object);

	//$ports = getObjectPortsAndLinks($object_id);
	$ports = $object['ports'];

	/* reindex array so key starts at 0 */
	$ports = array_values($ports);

	/* URL param handling */
	if(isset($_GET['allports'])) {
		$allports = $_GET['allports'];
	} else
		$allports = FALSE;

	if(isset($_GET['allback'])) {
		$allback = $_GET['allback'];
	} else
		$allback = FALSE;

	if(isset($_GET['firstlast'])) {
		$firstlast = $_GET['firstlast'];
	} else
		$firstlast = FALSE;

	echo '<table><tr>';

	if($allports) {
		unset($_GET['allports']);
		echo '<td width=200><a href="'.makeHref($_GET)
			.'">Hide Ports without link</a></td>';
	}
	else
	{
		$_GET['allports'] = 1;
		echo '<td width=200><a href="'.makeHref($_GET)
			.'">Show All Ports</a></td>';
		unset($_GET['allports']);
	}

	echo '<td width=200><span onclick=window.open("'.makeHrefProcess(array('op' => 'PortLinkDialog','linktype' => 'back','byname' => '1')).
		'","name","height=700,width=800,scrollbars=yes");><a>Link Object Ports by Name</a></span></td>';

	if($allback) {
		unset($_GET['allback']);
		echo '<td width=200><a href="'.makeHref($_GET)
			.'">Collapse Backend Links on same Object</a></td>';
	}
	else
	{
		$_GET['allback'] = 1;
		echo '<td width=200><a href="'.makeHref($_GET)
			.'">Expand Backend Links on same Object</a></td>';
		unset($_GET['allback']);
	}

	/* Graphviz map */
	echo '<td width=100><span onclick=window.open("'.makeHrefProcess(array('op' => 'map','usemap' => 1))
		.'","name","height=800,width=800,scrollbars=yes");><a>Object Map</a></span></td>';

	/* cytoscape map */
	echo '<td width=100><span onclick=window.open("'.makeHrefProcess(array('op' => 'cytoscapemap'))
		.'","name","height=800,width=800,scrollbars=yes");><a>Cytoscape Object Map</a></span></td>';

	/* fristlast */
	if(!$firstlast)
	{
		$_GET['allports'] = 1;
		$_GET['firstlast'] = 1;
		echo '<td width=200><a href="'.makeHref($_GET).'">FirstLast View</a></td>';
		unset($_GET['firstlast']);
	}
	else
	{
		unset($_GET['firstlast']);
		echo '<td width=200><a href="'.makeHref($_GET).'">Default View</a></td>';
		addJS(<<<JSEND
		function toggledetails(elem)
		{
			var h = $('.hidden');
			$('.nothidden').addClass('hidden');
			h.addClass('nothidden');
			h.removeClass('hidden');
			if(elem.innerText == 'Show Details')
				elem.innerText = 'Hide Details';
			else
				elem.innerText = 'Show Details';
		}
JSEND
		, true);
		echo '<td width=200><a onclick="toggledetails(this);">Show Details</a></td>';
	}

	/* Help */
	echo '<td width=200><span onclick=window.open("'.makeHrefProcess(array('op' => 'Help'))
		.'","name","height=400,width=500");><a>Help</a></span></td>';

	if(isset($_REQUEST['hl_port_id']))
		$hl_port_id = $_REQUEST['hl_port_id'];
	else
		$hl_port_id = NULL;

	echo '</tr></table>';


	echo '<br><br><table id=renderobjectlinks0 style="white-space: nowrap">';

	$rowcount = 0;

	if($firstlast)
	{
		echo '<tr><th class=tdleft>Current name</th>';
		echo '<th class=hidden></th>';
		echo '<th class=tdleft>First Object name</th>';
		echo '<th class=tdleft>First Local name</th><th class=tdleft>First Visible label</th>';
		echo '<th class=tdleft>First Interface</th><th class=tdleft>First L2 address</th>';
		echo "<th class='tdleft nothidden'></th>";
		echo "<th class='tdleft nothidden'>Last Object name</th>";
		echo "<th class='tdleft nothidden'>Last name</th><th class='tdleft nothidden'>Last Visible label</th>";
		echo "<th class='tdleft nothidden'>Last Interface</th><th class='tdleft nothidden'>Last L2 address</th>";
		echo '</tr>';
	}

	addCSS('.hidden {display: none;}', TRUE);

	foreach($ports as $key => $port) {

		$lc = new lm_linkchain($port['id']);

		if($allports || $lc->linkcount > 0)
		{
			if($port['id'] == $hl_port_id)
				$rowbgcolor = lm_linkchain::HL_PORT_BGCOLOR;
			else
				$rowbgcolor = ($rowcount % 2 ? lm_linkchain::ALTERNATE_ROW_BGCOLOR : "#ffffff");

			$rowcount++;

			if(!$firstlast)
			{
				echo $lc->getchainlabeltrstart($rowbgcolor).$lc->getchainrow($allback, $rowbgcolor)."</tr>";
				continue;
			}

			$a_class = isEthernetPort ($port) ? 'port-menu' : '';
			echo '<tr style="background: '.($lc->loop ? '#ff9966' : $rowbgcolor ).'"';
			echo "><td class='tdleft' NOWRAP><a name='port-${port['id']}' class='interactive-portname nolink $a_class'>${port['name']}</a></td>";
			echo $lc->printports();
			echo "</tr>";
		}
	}

	echo "</table>";

} /* renderObjectLinks */

/* -------------------------------------------------- */
