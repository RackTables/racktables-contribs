<?php

/*

RackTables warranty extension by Killsystem <killsystem@toolsection.info>

I wrote a local.php that creates a tab for most of the major server vendors
that allows to check the warranty information.
Thanks to Troy Rose (troyjrose@gmail.com) for the renderIframeTabForEntity
functions. I modified it a little bit to better serve my needs.

The local.php has a few requirements:
* IBM and some HP server
* You have to create a extra field with the name "Productnumber".
Without that HP cannot resolve warranty information for systems with a 10
char long sn.
IBM warranty check doesn't work without it.
* NetApp systems
* You will get a login screen for the now portal. There is no workaround
* I use the barcode field for the serial number. I you use another field
you have to change this var within the links. (I try to use the hardware sn
field in the future but until then...)

It also adds a tab for the HP System Management Homepage.

It works with
* HP storage
* HP server
* HP libraries
* IBM server
* IBM storage
* NetApp storage
* NetApp VTL

I hope it helps some of you folks. If some knows how to check other
hardware vendor please let me know.

*/

//HP System Management Homepage
$tab['object']['HPSysMan'] = 'HP System Management Homepage';
$trigger['object']['HPSysMan'] = 'localtrigger_HPServer';
$tabhandler['object']['HPSysMan'] = 'localfunc_HPSysMan';
//HP warranty
$tab['object']['HPWarranty'] = 'HP warrantycheck';
$trigger['object']['HPWarranty'] = 'localtrigger_HPWarranty';
$tabhandler['object']['HPWarranty'] = 'localfunc_HPWarranty';
//IBM warranty
$tab['object']['IBMWarranty'] = 'IBM warrantycheck';
$trigger['object']['IBMWarranty'] = 'localtrigger_IBMWarranty';
$tabhandler['object']['IBMWarranty'] = 'localfunc_IBMWarranty';
//NetApp warranty
$tab['object']['NetAppWarranty'] = 'NetApp warrantycheck';
$trigger['object']['NetAppWarranty'] = 'localtrigger_NetAppWarranty';
$tabhandler['object']['NetAppWarranty'] = 'localfunc_NetAppWarranty';
//Dell warranty
$tab['object']['DellWarranty'] = 'Dell warrantycheck';
$trigger['object']['DellWarranty'] = 'localtrigger_DellWarranty';
$tabhandler['object']['DellWarranty'] = 'localfunc_DellWarranty';

//Functions and triggers
function localfunc_HPSysMan()
{
	$object = spotEntity ('object', getBypassValue());
	$alloclist = getObjectIPv4Allocations ($object['id']);
	if (count ($alloclist))
	{
		foreach ($alloclist as $dottedquad => $alloc)
		{
			if ($alloc[addrinfo][allocs][0][type]=="regular") {
				renderIframeTabForEntity("HP System Management Homepage", "https://".$alloc[addrinfo][ip].":2381");
				}
		}
	}
}

function localtrigger_HPServer()
{
	$object = spotEntity ('object', getBypassValue());
	$record = getAttrValues ($object['id'], TRUE);
	if ($object['objtype_id'] == 4 && strstr($record[2][value],"HP"))
		return 1;
	else
	{
		return '';
	}
}

function localfunc_HPWarranty()
{
	$object = spotEntity ('object', getBypassValue());
	foreach (getAttrValues ($object['id'], TRUE) as $record)
		if (strlen ($record['value']) && $record['name'] == "Productnumber")
			$hppn = $record['value'];
	if ($object['barcode'])
	renderIframeTabForEntity("HP warranty", "http://h20000.www2.hp.com/bizsupport/TechSupport/WarrantyResults.jsp?nickname=&sn=".$object['barcode']."&country=DE&lang=de&cc=de&pn=".$hppn."&find=Display+Warranty+Information+%C2%BB&");
}

function localtrigger_HPWarranty()
{
	$object = spotEntity ('object', getBypassValue());
	$record = getAttrValues ($object['id'], TRUE);
	if (($object['objtype_id'] == 4 || $object['objtype_id'] == 5 || $object['objtype_id'] == 6)&& strstr($record[2][value],"HP"))
		return 1;
	else
	{
		return '';
	}
}

function localfunc_IBMWarranty()
{
	$object = spotEntity ('object', getBypassValue());
	switch ($object['objtype_id']) {
		case 4:
		$ibmbrandid = 5000008; //System x
		break;
		case 5:
		$ibmbrandid = 5345868; //System Storage
		break;
	}
	foreach (getAttrValues ($object['id'], TRUE) as $record)
		if (strlen ($record['value']) && $record['name'] == "Productnumber")
			$ibmtype = ereg_replace("-.*","",$record['value']);
	if ($ibmtype)
		renderIframeTabForEntity("IBM warranty", "http://www-947.ibm.com/systems/support/supportsite.wss/warranty?type=".$ibmtype."&serial=".$object['barcode']."&action=warranty&brandind=".$ibmbrandid."&Submit=Submit");
}

function localtrigger_IBMWarranty()
{
	$object = spotEntity ('object', getBypassValue());
	$record = getAttrValues ($object['id'], TRUE);
	if (($object['objtype_id'] == 4 || $object['objtype_id'] == 5) && strstr($record[2][value],"IBM"))
		return 1;
	else
	{
		return '';
	}
}


function localfunc_NetAppWarranty()
{
	$object = spotEntity ('object', getBypassValue());
	renderIframeTabForEntity("NetApp warranty", "http://now.netapp.com/eservice/serviceSystemSearch.do?searchType=NA_WQS_PRODUCT&value=".$object['barcode']."&button.findbynumber=Go!&execQuery=Y&moduleName=SERVICE&sessionInfo=false");
}

function localtrigger_NetAppWarranty()
{
	$object = spotEntity ('object', getBypassValue());
	$record = getAttrValues ($object['id'], TRUE);
	if (($object['objtype_id'] == 5 || $object['objtype_id'] == 6) && strstr($record[2][value],"NetApp"))
		return 1;
	else
	{
		return '';
	}
}

function localfunc_DellWarranty()
{
	$object = spotEntity ('object', getBypassValue());
	renderIframeTabForEntity("Dell warranty", "http://support.dell.com/support/topics/global.aspx/support/my_systems_info/details?c=us&l=en&s=gen&ServiceTag=".$object['barcode']);
}

function localtrigger_DellWarranty()
{
	$object = spotEntity ('object', getBypassValue());
	$record = getAttrValues ($object['id'], TRUE);
	if (($object['objtype_id'] == 4) && strstr($record[2][value],"Dell"))
		return 1;
	else
	{
		return '';
	}
}

// Main function to suck in iframes used by others
// Written by Troy Rose (troyjrose@gmail.com)

function renderIframeTabForEntity ($title, $link)
{
        // Main layout starts.
	echo "<style type=\"text/css\">\n";
	echo "\n
	#iframe_wrap {\n
		position:absolute;\n
		top: 190px;\n
		left: 0;\n
		right: 0;\n
		bottom: 5px;\n
		align: center;
		margin:0;\n
		padding:0;\n
		}\n
	</style>\n";
	startPortlet ("<a href=\"{$link}\" target=_new>{$title}</a>");
	finishPortlet();
	echo "<div id=\"iframe_wrap\" align=\"center\">";
	echo "<iframe border=0 src=\"$link\" width=%99 height=%99 halign=center style='width: expression(document.documentElement.clientWidth); height: expression(document.documentElement.clientHeight-210)'>";
	echo "</div>";

}
