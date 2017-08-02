<?php
//
// RIPE connector plugin.
// Version 0.1
//
// Written by Vladimir Kushnir
//
// The purpose of this plugin is to load description of you IP ranges from RIPE database
//
// History
// Version 0.1:  Initial release
//
// Requirements:
//
// Installation:
// 1)  Copy script to plugins folder as ripe.php


// Depot Tab for objects.
$tab['ipv4net']['ripe'] = 'Live RIPE';
$tabhandler['ipv4net']['ripe'] = 'ripeTab';
$ophandler['ipv4net']['ripe']['importRipeData'] = 'importRipeData';

function importRipeData() {
	$ripe_db = "http://rest.db.ripe.net/search.xml?source=ripe&query-string=";
	
	// Prepare update	
	assertStringArg('ripe_query');
	assertUIntArg ('net_id');
	assertStringArg('net_name');
	
	//$nbad = $ngood = 0;
	
	$net_id = $_REQUEST['net_id'];	
	$ripe_query = htmlspecialchars_decode($_REQUEST['ripe_query']);
	$ripe_result_str = file_get_contents($ripe_query, false, NULL);
	$ripe_result = simplexml_load_string($ripe_result_str);
	
	$filedir = realpath (dirname (__FILE__) );
	$ripe_xsl = simplexml_load_file($filedir.'/ripe_text.xsl');

    $proc = new XSLTProcessor();
    $proc->importStyleSheet( $ripe_xsl );
	$newName = htmlspecialchars_decode($_REQUEST['net_name']);
	$newComment = trim($proc->transformToXML( $ripe_result ));
	
	// Update
	usePreparedUpdateBlade (
		'IPv4Network',
		array (
			'name' => $newName,
			'comment' => $newComment),
		array (
			'id' => $net_id),
		'AND');

//		$retcode = 51;
//    if (!$nbad)
//		return showFuncMessage (__FUNCTION__, 'OK', array ($ngood));
//	else
//		return showFuncMessage (__FUNCTION__, 'ERR', array ($nbad, $ngood));
}


// Display the ping overview:
function ripeTab($id) {
	$ripe_db = "http://rest.db.ripe.net/search.xml?source=ripe&query-string=";
	assertUIntArg ('id');
	$net = spotEntity ('ipv4net', $id);
	loadIPAddrList ($net);	
	$startip = ip4_bin2int ($net['ip_bin']);
	$endip = ip4_bin2int (ip_last ($net));
	// Get Data from RIPE
	$ripe_inetnum_str = ip4_format (ip4_int2bin ($startip)) . ' - ' . ip4_format (ip4_int2bin ($endip));
	$ripe_query = $ripe_db . ip4_format (ip4_int2bin ($startip)) ;
	$ripe_result_str = file_get_contents($ripe_query, false, NULL);
	$ripe_result = simplexml_load_string($ripe_result_str);
	
	// Check inetnum object	
	$ripe_inetnum_check = "/whois-resources/objects/object[@type='inetnum']/attributes/attribute[@name='inetnum'][@value='$ripe_inetnum_str']";
	$ripe_inetnum = $ripe_result->xpath($ripe_inetnum_check);
	if (empty($ripe_inetnum)) {
		echo "<div class=trerror><center><h1>${net['ip']}/${net['mask']}</h1><h2>${net['name']}</h2></center></div>\n";
	} else {
		$ripe_netname_check = "/whois-resources/objects/object[@type='inetnum']/attributes/attribute[@name='netname']";
		$ripe_netname = $ripe_result->xpath($ripe_netname_check);
		$netname = trim($ripe_netname[0]['value']);
		if (strcmp($netname, $net['name']) != 0) {
			echo "<div class=trwarning><center><h1>${net['ip']}/${net['mask']}</h1><h2>${net['name']}</h2></center></div><div><center>";
		} else {
			echo "<div class=trok><center><h1>${net['ip']}/${net['mask']}</h1><h2>${net['name']}</h2></center></div><div><center>";
		}
		printOpFormIntro ('importRipeData', array ('ripe_query' => $ripe_query, 'net_id' => $id, 'net_name' => $netname));
		echo "<input type=submit value='Import RIPE records in to comments'></center></div>";
		echo "</form>";
	};
	// echo '<em>'.$ripe_query.'</em>';
	echo "<table border=0 width='100%'><tr><td class=pcleft>";
	
	$filedir = realpath (dirname (__FILE__) );
	$ripe_xsl = simplexml_load_file($filedir.'/ripe_html.xsl');
	
	startPortlet ("RIPE Information Datatbase<br>${ripe_inetnum_str}");
    $proc = new XSLTProcessor();
    $proc->importStyleSheet( $ripe_xsl );
    echo '<div class=commentblock>'.trim($proc->transformToXML( $ripe_result )).'</div>';
	finishPortlet();
	echo "</td></tr></table>\n";
}
