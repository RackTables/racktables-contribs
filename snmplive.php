<?php

/********************************************
 *
 * RackTables 0.20.x snmp live extension
 *
 *	displays SNMP port status
 *
 *
 * needs PHP >= 5.4.0
 *	saved SNMP settings ( see snmpgeneric.php extension )
 *	also RT port names and SNMP port names must be the same ( should work fine with snmpgeneric.php created ports )
 *
 * (c)2015 Maik Ehinger <m.ehinger@ltur.de>
 */

/****
 * INSTALL
 * 	just place file in plugins directory
 *
 */

/**
 * The newest version of this plugin can be found at:
 *
 * https://github.com/github138/myRT-contribs/tree/develop-0.20.x
 *
 */

/* TODOs
 *
 *  - code cleanup
 */

/* RackTables Debug Mode */
//$debug_mode=1;

$tab['object']['snmplive'] = 'SNMP Live';
$tabhandler['object']['snmplive'] = 'snmplive_tabhandler';
$trigger['object']['snmplive'] = 'snmplive_tabtrigger';

$ophandler['object']['snmplive']['ajax'] = 'snmplive_opajax';

function snmplive_tabtrigger() {
	// display tab only on IPv4 Objects
	return considerConfiguredConstraint (spotEntity ('object', getBypassValue()), 'IPV4OBJ_LISTSRC') ? 'std' : '';
} /* snmplive_tabtrigger */

function snmplive_tabhandler($object_id)
{

	addCSS(<<<ENDCSS
        .ifoperstatus-default { background-color:#ddd; }
        .ifoperstatus-1, .ifoperstatus-up { background-color:#00ff00; }
        .ifoperstatus-2, .ifoperstatus-down { background-color:#ff0000; }
        .ifoperstatus-3, .ifoperstatus-testing { background-color:#ffff66; }
        .ifoperstatus-4, .ifoperstatus-unknown { background-color:#ffffff; }
        .ifoperstatus-5, .ifoperstatus-dormant { background-color:#90bcf5; }
        .ifoperstatus-6, .ifoperstatus-notPresent { }
        .ifoperstatus-7, .ifoperstatus-lowerLayerDown { }

        .port-groups { border-spacing:1px;display:table; }
        .port-group { display:table-cell;border:3px solid #000;background-color:#c0c0c0; }

        .port-column { display:table-cell;position:relative; }

        .port { position:relative;width:42px;height:100px;border:2px solid #000;overflow:hidden; }
        .port-pos-1 { margin-bottom:1px; }
        .port-pos-2 { }
        .port-pos-0 { margin-top:1px; }

        .port-header { position:absolute }
        .port-header-pos-1 { top:0px; }
        .port-header-pos-0 { bottom:0px; }

        .port-status { position:absolute;min-width:42px;text-align:center;font-size:10pt; }

        .port-status-pos-1 { top:35px; }
        .port-status-pos-0 { bottom:35px; }

        .port-info { position:absolute;width:90%;background-color:#ddd;overflow:hidden; }
        .port-info-pos-1 { top: 80px; }
        .port-info-pos-0 { bottom: 80px;}

        .port-name {  font-size:10pt;margin:0px auto;width:40px;text-align:center; }
        .port-number { font-size:8pt;color:#eee; }

        .port-detail { position:fixed;z-index:1000;top:0px;right:0px;border:3px solid #000;background-color:#fff }
        .port-detail-links { background-color:#ccc }
        .hidden { visibility:hidden; }
        .info-footer { }

ENDCSS
, TRUE);

	echo "<div id=\"info\"></div>";

	if(isset($_GET['debug']))
		$debug = $_GET['debug'];
	else
		$debug = 0;

	$object = spotEntity('object', $object_id);
	amplifyCell($object);

	if(isset($_GET['modules']))
		$modules = $_GET['modules'];
	else
		$modules = false;

	if($modules)
		unset($_GET['modules']);
	else
		$_GET['modules'] = 1;

	echo "<a href=".makeHref($_GET).">".($modules ? "Hide" : "Show" )." Modules</a>";

	pl_layout_default($object, 0, false, $modules);

	addJS(<<<ENDJS
       function togglevisibility(elem, hide)
        {
                if(hide)
                        elem.css('visibility', 'hidden');
                else
                        elem.css('visibility', 'visible');
                //a.show();
                //a.hide();
        }

       function setdetail(elem, hide)
        {
                var a = $( "#port" + elem.id + "-detail");

                togglevisibility(a, hide);
        }

         function setports( data, textStatus, jqHXR ) {
                if(data.debug)
                        $( "#info" ).html($( "#info" ).html() + "DEBUG: " + data.name + ": " + data.debug);

                for(var index in data.ports)
                {
                        setport(data, data.ports[index]);
                }
         }

        function setportstatus( obj, port , id , detail)
        {

                tagidsuffix = "";

                if(detail)
                        tagidsuffix = tagidsuffix + "-detail";

                if(!detail)
                {
                        $( "#port" + id + "-status" + tagidsuffix ).html("<table class=\"ifoperstatus-" + port.snmpinfos.
operstatus + "\"><tr><td>"
                                        +  port.snmpinfos.operstatus + "<br>" + port.snmpinfos.speed
					+  ( port.snmpinfos.vlan ? "<br>" + port.snmpinfos.vlan : "" )
                                        + "</td></tr></table>");
                        return;
                }

                $( "#port" + id + "-status" + tagidsuffix ).html(port.snmpinfos.alias
                                        + "<table class=\"ifoperstatus-" + port.snmpinfos.operstatus + "\"><tr><td>"
                                        + (port.snmpinfos.ipv4 ? port.snmpinfos.ipv4 : "")
					+ "<br>" + port.snmpinfos.operstatus
					+ ( port.snmpinfos.vlan ? "<br>" + port.snmpinfos.vlan_name + " (" + port.snmpinfos.vlan + ")" : "" )
                                        + "</td></tr></table>");
        }

        function setport( obj, port ) {

                if(port.debug)
                        $( "#info" ).html($( "#info" ).html() + port.name + " " + port.debug);

                if(port.snmpinfos)
                {
                        setportstatus(obj, port, port.id, false);
                        setportstatus(obj, port, port.id, true);
                }

        }

        function ajaxerror(jqHXR, textStatus, qXHR, errorThrown)
        {
                $( "#info" ).html($( "#info" ).html() + "<br>" + textStatus + " " + qXHR + " " + errorThrown);
        }

	$.ajax({
		dataType: "json",
		url: "index.php",
		data: {
			page: "object",
			tab: "snmplive",
			module: "redirect",
			op: "ajax",
			json: "get",
			object_id: "$object_id",
			debug: $debug
		},
		error: ajaxerror,
		success: setports
	});

ENDJS
, TRUE);

} /* snmplive_tabhandler */

/* -------------------------------------------------- */

function snmplive_opajax()
{

	ob_start();
	$object_id = $_REQUEST['object_id'];
	$object = spotEntity('object', $object_id);

	$object['ports'] = getObjectPortsAndLinks ($object_id);

	if(isset($_GET['debug']))
		$debug = $_GET['debug'];
	else
		$debug = 0;

	$object['iftable'] = sl_getsnmp($object, $debug);

	if($object['iftable'])
		foreach($object['ports'] as $key => &$port)
		{
			// snmpinfos
			$port['snmpinfos'] = sl_getportsnmp($object, $port, $debug);

			if(!$port['snmpinfos'])
				unset($object['ports'][$key]);
		}

	/* not needed anymore */
	unset($object['iftable']);

	/* set debug output */
	if(ob_get_length())
		$object['debug'] = ob_get_contents();

	ob_end_clean();

	echo json_encode($object);
	exit;

} /* snmpgeneric_opcreate */

/* -------------------------------------------------- */

function pl_layout_default(&$object, $groupports = 8, $bottomstart = false, $modules = false, $portrows = 2)
{
	$i = 0;
	$portcolumn = "";
	$linkcount = 0;

	$lastmodule = null;
	$nomodul = array();

	echo "<div class=\"port-groups\">";
	foreach($object['ports'] as $key => $port)
	{

		$object['portnames'][$port['name']] = $port;

		$port_id = $port['id'];
		$port_name = $port['name'];

		$pname = $port_name;
		$module = "";
		$pport = "";
		// split name in name, module, port
		if(preg_match('/^([a-zA-Z]+)?(?:[\W]?([\d]+)?[\W])?([\d]+)?$/', $port_name, $match))
			if(count($match) == 4)
				list($tmp,$pname,$module,$pport) = $match;

		if($port['linked'])
			$linkcount++;

		if($module == "")
		{
			$nomodul[] = pl_layout_port($port, count($nomodul) + 1, 1);
			continue;
		}

		if($modules)
		{
			// port modules
			if($module != $lastmodule)
			{
				if(($i % $portrows) != 0)
					echo "$portcolumn</div>"; // port-column

				if($groupports)
					if(($i % $groupports) != 0)
						echo "</div>"; // port-group

				echo "</div>"; // port-groups

				$i = 0;
				$portcolumn = "";
				echo "Modul: $module";
				echo "<br><div class=\"port-groups\">";
			}

			$lastmodule = $module;
		}

		$i++;

		if($groupports)
			if(($i % $groupports) == 1)
				echo "<div class=\"port-group\">";

		if($portrows == 2)
		{
			// print each row different
			if(($i % $portrows) == 1)
				$pos = ($bottomstart ? 0 : 1); // 0 = bottom; 1 = top
			else
				$pos = ($bottomstart ? 1 : 0); // 0 = bottom; 1 = top
		}
		else
			$pos = ($bottomstart ? 0 : 1); // 0 = bottom; 1 = top

		$portdiv = pl_layout_port($port, $i, $pos);

		if(!$bottomstart)
			$portcolumn = "$portcolumn$portdiv";
		else
			$portcolumn = "$portdiv$portcolumn";

		if(($i % $portrows) == 0)
		{
			echo "<div class=\"port-column\">";
			echo "$portcolumn</div>";
			$portcolumn = "";
		}

		if($groupports)
			if(($i % $groupports) == 0)
				echo "</div>";
	}

	if(($i % $portrows) != 0)
	{
		$fillcount = $portrows - ($i % $portrows);

		$fill = "";
		for($f=0;$f<$fillcount;$f++)
			$fill .= "<div class=\"port\"></div>";

		if(!$bottomstart)
			$portcolumn .= $fill;
		else
			$portcolumn = "$fill$portcolumn";

		echo "<div class=\"port-column\">";
		echo "$portcolumn</div>"; // port-column
	}

	if($groupports)
		if(($i % $groupports) != 0)
			echo "</div>"; // port-group

	echo "</div>"; // port-groups

	/* Port without modul */
	if($nomodul)
	{
		echo "Other Ports:<br><div id=\"nomodule\" class=\"port-groups\">";
		foreach($nomodul as $portdiv)
			echo "<div class=\"port-column\">$portdiv</div>";
		echo "</div>";
	}

	return $linkcount;

} // layout_default

function pl_layout_port($port, $number, $pos)
{

	$port_id = $port['id'];
	$port_name = $port['name'];

	$title = "Name: $port_name - No: $number - ID: $port_id";

	$portdiv = "<div id=\"$port_id\" class=\"port port-pos-$pos\" onmouseover=\"setdetail(this,false);\" onmouseout=\"setdetail(this,true);\">";
	$portheader = "<div class=\"port-header port-header-pos-$pos\">";
	$portlabel = "<div class=\"port-number\">$number</div>";
	$portname = "<div class=\"port-name\">$port_name</div>";

	$details = "<table><tr><td>No.: $number (ID: ".$port['id'].")<br>".$port['object_name']."<br>".$port['name']."<br>"
		.$port['label']."<br>".$port['reservation_comment']
		."<div id=\"port${port_id}-status-detail\">No Status</div></td>";

	if($port['linked'])
		$details .= "<td>Remote:<br>".$port['cableid']."<br>".$port['remote_object_name']."<br>".$port['remote_name']."<div id=\"port${port_id}-status-detail-remote\">No Remote Status</div></td>";

	$details .= "</tr></table>";


	$portdetail = "<div id=\"port${port_id}-detail\" class=\"port-detail hidden\" onclick=\"togglevisibility(this,true);\">$details</div>";

	$portstatus = "<div id=\"port${port_id}-status\" class=\"port-status port-status-pos-$pos\" title=\"$title\">-</div>";

	if($pos) {
		$portheader .= "$portlabel$portname</div>";
		$portdiv .= "$portheader$portstatus<div class=\"port-info port-info-pos-$pos\"></div></div>$portdetail";
	}
	else
	{
		$portheader .= "$portname$portlabel</div>";
		$portdiv .= "<div class=\"port-info port-info-pos-$pos\"></div>$portstatus$portheader</div>$portdetail";
	}

	return $portdiv;
}
/* ------------------------------------------------------- */

function sl_getportsnmp(&$object, $port, $debug = false)
{
	$ipv4 = $object['SNMP']['IP'];

	$port_name = $port['name'];

	// SNMP up / down
	if(!isset($object['iftable'][$port_name]))
		return false;

	$ifoperstatus = $object['iftable'][$port_name]['status'];

	$ifspeed = $object['iftable'][$port_name]['speed'];

	$ifalias = $object['iftable'][$port_name]['alias'];

	$vlan="";
	$vlan_name="";

	if(isset($object['iftable'][$port_name]['vlan']))
	{
		$vlan = $object['iftable'][$port_name]['vlan'];
		$vlan_name = $object['iftable'][$port_name]['vlan_name'];
	}

	return array(
		'ipv4' => $ipv4,
		'operstatus' => $ifoperstatus,
		'alias' => $ifalias,
		'speed' => $ifspeed,
		'name' => $port_name,
		'vlan' => $vlan,
		'vlan_name' => $vlan_name,
	);

} // sl_getportsnmp

function sl_getsnmp(&$object, $debug = false)
{
	$object_id = $object['id'];
	$object_name = $object['name'];

	$breed = detectDeviceBreed ($object_id);

	if(isset($object['SNMP']))
	{
		if($debug)
			echo "INFO: No SNMP Object \"$object_name\" ID: $object_id<br>";
		return null;
	}

	if(!considerConfiguredConstraint($object, 'IPV4OBJ_LISTSRC'))
	{
		if($debug)
			echo "INFO: No IPv4 Object \"$object_name\" ID: $object_id<br>";

		return False;
	}

	/* get object saved SNMP settings */
	$snmpconfig = explode(':', strtok($object['comment'],"\n\r"));

	if($snmpconfig[0] != "SNMP")
	{

		if($debug)
			echo "INFO: No saved SNMP Settings for \"$object_name\" ID: $object_id<br>";

		return False;
	}

	/* set objects SNMP ip address */
	$ipv4 = $snmpconfig[1];

	if(0)
		var_dump_html($snmpconfig);

	if(!$ipv4)
	{
		echo "ERROR: no ip for \"$object_name!!\"<br>";

		return False;
	}

	$object['SNMP']['IP'] = $ipv4;

	if(count($snmpconfig) < 4 )
	{
		echo "SNMP Error: Missing Setting for $object_name ($ipv4)";

		return False;
	}

	/* SNMP prerequisites successfull */

	$s = new sl_ifxsnmp($snmpconfig[2], $ipv4, $snmpconfig[3], $snmpconfig, $breed);

	if(!$s->error)
	{

		/* get snmp data */
		$iftable = $s->getiftable();

		if($debug && $s->error)
			echo $s->getError();

		if($iftable)
			return $iftable;
		else
		{

			echo "SNMP Error: ".$s->getError()." for $object_name ($ipv4)<br>";
			return False;
		}

	}
	else
	{
		echo "SNMP Config Error: ".$s->error." for \"$object_name\"<br>";
		return False;
	}

	return null;

} // sl_getsnmp
/* ------------------ */
class sl_ifxsnmp extends SNMP
{

	public $error = false;

	private $devicebreed = null;

	function __construct($version, $hostname, $community, $security = null, $breed = null)
	{

		$this->devicebreed = $breed;

		switch($version)
		{
			case "1":
			case "v1":
					$version = parent::VERSION_1;
					break;
			case "2":
			case "2c":
			case "v2c":
					$version = parent::VERSION_2c;
					break;
			case "3":
			case "v3":
					$version = parent::VERSION_3;
					break;
		}

		parent::__construct($version, $hostname, $community);

		if($version == SNMP::VERSION_3)
		{
			if($security !== null && count($security) == 9)
			{
				$auth_passphrase = base64_decode($security[6]);
				$priv_passphrase = base64_decode($security[8]);

				if(!$this->setsecurity($security[4], $security[5], $auth_passphrase, $security[7], $priv_passphrase))
				{

					$this->error = "Security Error for v3 ($hostname)";
					return;
				}

			}
			else
			{
				$this->error = "Missing security settings for v3 ($hostname)";
				return;
			}
		}

		$this->quick_print = 1;
		$this->oid_output_format = SNMP_OID_OUTPUT_NUMERIC;
	}

	function getiftable()
	{
		$oid_ifindex = '.1.3.6.1.2.1.2.2.1.1'; // iftable
		$oid_ifoperstatus = '.1.3.6.1.2.1.2.2.1.8'; //iftable
		$oid_ifspeed = '.1.3.6.1.2.1.2.2.1.5'; //iftable
		$oid_ifdescr = '.1.3.6.1.2.1.2.2.1.2'; //iftable
		$oid_ifhighspeed = '.1.3.6.1.2.1.31.1.1.1.15'; //ifXtable
		$oid_ifname = '.1.3.6.1.2.1.31.1.1.1.1'; //ifXtable
		$oid_ifalias = '.1.3.6.1.2.1.31.1.1.1.18'; //ifXtable

		$ifindex = $this->walk($oid_ifindex); // iftable

		if($ifindex === FALSE)
		{
			return FALSE;
			exit;
		}

		$ifname = $this->walk($oid_ifname, TRUE); //ifXtable

		if($ifname == false)
			$ifname = $this->walk($oid_ifdescr, TRUE); //ifXtable

		$ifalias = $this->walk($oid_ifalias, TRUE); //ifXtable

		$ifspeed = $this->walk($oid_ifspeed, TRUE); //iftable
		$ifhighspeed = $this->walk($oid_ifhighspeed, TRUE); //ifXtable

		$this->enum_print = false;
		$ifoperstatus = $this->walk($oid_ifoperstatus, TRUE); //iftable

		$retval = array();
		foreach($ifindex as $index)
		{
			$ifname[$index] = shortenIfName ($ifname[$index], $this->devicebreed);

			$retval[$ifname[$index]]['ifindex'] = $index;

			$retval[$ifname[$index]]['status'] = $ifoperstatus[$index];

			$retval[$ifname[$index]]['alias'] = $ifalias[$index];

			$highspeed = $ifhighspeed[$index];
			if($highspeed)
				$speed = $highspeed;
			else
				$speed = $ifspeed[$index];

			if($speed >= 1000000) // 1Mbit
				$speed /= 1000000;

			$speed = ($speed >= 1000 ? ($speed / 1000)."Gb" : $speed."Mb" );

			$retval[$ifname[$index]]['speed'] = "$speed";

		}

		$this->get8021qvlan($retval);

		if(0)
			sl_var_dump_html($retval);

		return $retval;
	}

	/* append vlan to each port in retval */
	function get8021qvlan(&$retval)
	{
		//$oid_dot1dBasePort =		'.1.3.6.1.2.1.17.1.4.1.1';
		$oid_dot1dBasePortIfIndex =	'.1.3.6.1.2.1.17.1.4.1.2'; // dot1 index -> if index
		$oid_dot1qPvid =		'.1.3.6.1.2.1.17.7.1.4.5.1.1';
		$oid_dot1qVlanStaticName =	'.1.3.6.1.2.1.17.7.1.4.3.1.1';

		// @ supprress warning
		$dot1dbaseportifindex = @$this->walk($oid_dot1dBasePortIfIndex, TRUE);

		if($dot1dbaseportifindex === false)
		{
			$this->error = true;
			return;
		}

		$dot1qpvid = $this->walk($oid_dot1qPvid, TRUE);
		$dot1qvlanstaticname = $this->walk($oid_dot1qVlanStaticName, TRUE);

		$ifindexdot1dbaseport = array_flip($dot1dbaseportifindex);

		$ret = array();
		foreach($retval as $ifname => &$port)
		{
			if(!isset($ifindexdot1dbaseport[$port['ifindex']]))
				continue;

			$dot1index = $ifindexdot1dbaseport[$port['ifindex']];
			$vlan = $dot1qpvid[$dot1index];
			$retval[$ifname]['vlan'] = $vlan;
			$retval[$ifname]['vlan_name'] = $dot1qvlanstaticname[$vlan];
		}

	}
} // sl_ifxsnmp

/* ------------------------------------------------------- */
/* for debugging */
function sl_var_dump_html(&$var, $text = '') {

	echo "<pre>------------------Start Var Dump - $text -----------------------\n";
	var_dump($var);
	echo "\n---------------------END Var Dump - $text -----------------------</pre>";
}
?>
