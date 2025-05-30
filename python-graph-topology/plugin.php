<?php

define ('TOPOGEN_BIN', '/var/www/.venv_topoGen/bin/python3 plugins/topoGen/topoGen.py');

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
	global $image;

	array_push($indexlayout, array('graphtopo'));

	$page['graphtopo']['title']  = 'Topology';
	$page['graphtopo']['parent'] = 'index';

	$tab['graphtopo']['default'] = 'View';
	registerTabHandler ('graphtopo', 'default', 'renderGraphTopo');

	//$image['graphtopo']['srctype'] = 'dataurl';
	//$image['graphtopo']['data']    = 'image/png;base64,' . base64_encode(file_get_contents(dirname (__FILE__) . '/logo.png'));
	$image['graphtopo']['path']    = 'pix/logo.png';
	$image['graphtopo']['width']   = 218;
	$image['graphtopo']['height']  = 200;
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
	$topo = $router_mode = NULL;

	//check if it's a POST method:
	if ($_SERVER["REQUEST_METHOD"] == "POST") {
		//take the arguments from HTML form:
		$topo        = assertStringArg ('topo', TRUE);
		$router_mode = assertUIntArg ('router_mode', TRUE);
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
			'label' => 'Topology mode',
			'options' => array
			(
				4 => 'yEd',
				5 => 'nx',
				6 => 'MapName',
				7 => 'MapLinks',
				8 => 'Excel',
			),
			'selected' => $router_mode ?? 5,
		),

	);
	addCSS('topoGen/css/gstyle.css');
	addJS('topoGen/js/jquery-image.js');
	?>

	<div class="settings">

		<!-- HTML menu formatting -->
		<div class="parameters">
			<form method="post" action="">
			<fieldset class="fields">
				<legend>Parameters:</legend>
				<div class="gdiv">
				Topology name: <br> <input type="text" name="topo" value="<?php echo $topo;?>">
				</div>

				<div class="gdiv">
					<?php
						foreach ($selects as $name => $each)
						{
							echo "<div class=gdiv>\n";
							echo "<label class=ginput for=${name}>${each['label']}:</label>\n";
							printSelect($each['options'], array ('name' => $name, 'class' => 'ginput'), $each['selected']);
							echo "</div>\n";
						}
					?>
				</div>

				<div class="gdiv">
				<input type="submit" value="Execute">
				</div>				

			</fieldset>
			</form>
		</div>

	</div>
	<div class="result">
		<!-- Results display -->
		<?php

			if ($_SERVER["REQUEST_METHOD"] == "POST") {
				if (empty($topo)) //Python script needs this parameter in order to work
					showError('Please specify the desired topology.');
				else
				{
					$argv = array
					(
						//dirname(__FILE__) . '/' . TOPOGEN_BIN,
						//escapeshellarg(preg_replace('/\s+/', '', $topo)),
						TOPOGEN_BIN,
						$topo,
						$router_mode
					);
					exec (implode(' ', $argv), $scriptOutput, $exitCode); //PHP waits until the called program is done		
					switch ($exitCode)
					{
						case 1:
							showError('Failed to run ' . TOPOGEN_BIN . ' (missing Python modules?)');
							break;
						case 127:
							showError('Failed to run ' . TOPOGEN_BIN . ' (file not found)');
							break;
						default:
							echo "<h2>Topology:</h2>\n";
							$last_line = array_pop($scriptOutput);
					}
					switch($exitCode)
					{
						case 255:
							showError('Topology not found!');
							{echo "<img class=\"not\" src=\"pix/not.gif\">";}
							break;
						case 4:
							echo "<a href='${last_line}' download>Download GRAPHML</a>";
							break;
						case 5:
							echo file_get_contents ($last_line);
							break;
						case 6:
						case 7:
							echo "<iframe src='${last_line}' width=1150 height=550></iframe>";
							break;
						case 8:
							echo "<a href='${last_line}' download>Download Excel</a>";
							break;
						case 0:
							break;
					}
				} // if $topo is not empty
			} // if POST
		?>
	</div>
	<?php
}

?>
