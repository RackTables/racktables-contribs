<?php
// =====================================
// Frontend for GraphTopo python script
// =====================================
//
// Author: Lucas Scola
// e-mail: ucas.scola@hotmail.com.ar
//
// Version: 1.0
//
// =====================================
// Changelog:
// *1.2: Legend added
// *1.1: -Goodbye inline style.
//			 -New inputs implementation.
// *1.0: First implementation.
//


//Inserting plugin page into RackTables index

function plugin_topoGen_info()
{
	return array
	(
		'name' => 'topoGen',
		'longname' => 'Topology Generator',
		'version' => '2.0',
		'home_url' => 'https://github.com/RackTables/racktables-contribs/'
	);
}

function plugin_topoGen_init()
{
	global $indexlayout;
	global $page;
	global $tab;
	global $tabHandler;
	global $image;

	array_push($indexlayout, array('graphtopo'));
	addCSS('topoGen/css/gstyle.css');
	addJS('topoGen/js/jquery-image.js');

	$page['graphtopo']['title']  = 'Topology';
	$page['graphtopo']['parent'] = 'index';

	$tab['graphtopo']['default'] = 'View';
	registerTabHandler ('graphtopo', 'default', 'renderGraphTopo');

	$image['graphtopo']['path']   = 'pix/logo.png';
	$image['graphtopo']['width']  = 218;
	$image['graphtopo']['height'] = 200;
}

function plugin_topoGen_install()
{
	return TRUE;
}

function plugin_topoGen_uninstall()
{
	return TRUE;
}

function plugin_topoGen_upgrade()
{
	return TRUE;
}	

function renderGraphTopo()
{
	//variables intialization:
	$topo = $router_mode = $format = NULL;
	$algo = $rankdir = $lines = $aggr = $lag = NULL;
	//$nodesep = $ranksep = "";
	$svgName = "";
	$exitCode= -2;
	$scriptOutput = array();

	//check if it's a POST method:
	if ($_SERVER["REQUEST_METHOD"] == "POST") {
		//take the arguments from HTML form:
		$topo        = assertStringArg ('topo', TRUE);
		$router_mode = assertUIntArg ('router_mode', TRUE);
		$format      = 0;
		$algo        = 0;
		$rankdir     = 0;
		$lines       = 0;
		$aggr        = 0;
		$lag         = 0;
	}
	$yesno_options = array
	(
		0 => 'No',
		1 => 'Yes',
	);
	$selects = array
	(
		'router_mode' => array
		(
			'label' => 'Router mode',
			'options' => array
			(
#				0 => 'gvCluster',
#				1 => 'gvOne-line',
#				2 => 'gvTwo-line',
#				3 => 'gvOnly-name',
				4 => 'yEd',
				5 => 'nx',
				6 => 'MapName',
				7 => 'MapLinks',
				8 => 'Excel',
			),
			'selected' => $router_mode ?? 5,
		),
/*
		'algo' => array
		(
			'label' => 'Algorithm',
			'options' => array
			(
				0 => 'gvDOT',
				1 => 'gvFDP',
				2 => 'gvCIRCO',
				3 => 'gvTWOPI',
				4 => 'gvNEATO',
			),
			'selected' => $algo ?? 3,
		),
		'format' => array
		(
			'label' => 'Format',
			'options' => array
			(
				0 => 'PNG',
				1 => 'SVG',
			),
			'selected' => $format ?? 1,
		),
		'rankdir' => array
		(
			'label' => 'Orientation',
			'options' => array
			(
				0 => 'Left to Right',
				1 => 'Top to Bottom',
			),
			'selected' => $rankdir ?? 1,
		),
		'lines' => array
		(
			'label' => 'Lines',
			'options' => array
			(
				0 => 'Straight',
				1 => 'Curve',
				2 => 'Square',
				3 => 'Polyline',
			),
			'selected' => $lines ?? 3,
		),
		'aggr' => array
		(
			'label' => 'Grouping',
			'options' => $yesno_options,
			'selected' => $aggr ?? 1,
		),
		'lag' => array
		(
			'label' => 'LAG',
			'options' => $yesno_options,
			'selected' => $lag ?? 1,
		),
*/
	);
	?>

	<div class="settings">
				<!-- HTML menu formatting -->
				<div class="parameters">
					<form method="post" action="">
					<fieldset class="fields">
						<legend>Parameters:</legend>
						<div class="gdiv">
						Topology: <input type="text" name="topo" value="<?php echo $topo;?>">
						</div>
						<div class="gdiv">
						<input type="submit" value="Execute">
						</div>
					</fieldset>
					<fieldset class="fields">

						<!-- Fields for customizing output -->
						<legend>Advanced:</legend>
<?php
	foreach ($selects as $name => $each)
	{
		echo "<div class=gdiv>\n";
		echo "<label class=ginput for=${name}>${each['label']}:</label>\n";
		printSelect($each['options'], array ('name' => $name, 'class' => 'ginput'), $each['selected']);
		echo "</div>\n";
	}
?>
					</fieldset>
					</form>
				</div>
	</div>
	<div class="result">
		<!-- Results display -->
		<h2>Topology:</h2>
		<?php

			if ($_SERVER["REQUEST_METHOD"] == "POST") {
				if (empty($topo)) //Python script needs this parameter in order to work
				{
					{echo "<h3 class=\"warn\">Please, specify the desired topology.</h3>";}

				} else
				{
					$topo = preg_replace('/\s+/', '', $topo);
					exec("python3 plugins/topoGen/topoGen.py ".$topo." ".$router_mode." ".$format." ".$algo." ".$rankdir." ".$lines." ".$aggr." ".$lag, $scriptOutput, $exitCode); //PHP waits until the called program is done

					switch($exitCode)
					{
						case 255:
							{echo "<h3 class=\"not\">Topology not found!</h3>";}
							{echo "<img class=\"not\" src=\"pix/not.gif\">";}
							break;

						case 4:

							{echo "<a href=\"" . $scriptOutput[count($scriptOutput)-1] . "\" download>Download GRAPHML</a>";}
							break;

						case 5:

							include($scriptOutput[count($scriptOutput)-1]);
							
							break;

						case 6:
						
							{echo "<iframe src=\"" . $scriptOutput[count($scriptOutput)-1] . "\" width=\"1150\" height=\"550\"></iframe>";}
						
							break;

						case 7:

							{echo "<iframe src=\"" . $scriptOutput[count($scriptOutput)-1] . "\" width=\"1150\" height=\"550\"></iframe>";}

							break;

						case 8:

							echo "<a href=\"" . $scriptOutput[count($scriptOutput)-1] . "\" download>Download Excel</a>";
							break;

						case 0:
							
							if ($format == "0")
							{echo "<img class=\"topo\" src=\"" . $scriptOutput[count($scriptOutput)-1] . ".png\">";}
							if ($format == "1")
							{echo "<img class=\"topo\" src=\"" . $scriptOutput[count($scriptOutput)-1] . ".svg\">";}

							break;
					}
				}

			}
		?>
	</div>
	<?php
}

?>
