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
	global $indexlayout;
	global $page;
	global $tab;
	global $tabHandler;
	global $image;

	array_push($indexlayout, array('graphtopo'));
	addCSS('css/gstyle.css');
	addJS('js/jquery-image.js');
	/* addCSS('css/jquery.ui.resizable.css');
	addJS('js/jquery.ui.core.js');
	addJS('js/jquery.ui.widget.js');
	addJS('js/jquery.ui.mouse.js');
	addJS('js/jquery.ui.resizable.js');
	 */

	$page['graphtopo']['title']  = 'Topology';
	$page['graphtopo']['parent'] = 'index';

	$tab['graphtopo']['default'] = 'View';
	registerTabHandler ('graphtopo', 'default', 'renderGraphTopo');

	$image['graphtopo']['path']   = 'pix/logo.png';
	$image['graphtopo']['width']  = 218;
	$image['graphtopo']['height'] = 200;

	function renderGraphTopo()
	{
		//variables intialization:
		$topo = $router_mode = $format = "";
		$algo = $rankdir = $lines = $aggr = $lag = "";
		$nodesep = $ranksep = "";
		$svgName = "";
		$exitCode= -2;
		$scriptOutput = array();

		//check if it's a POST method:
		if ($_SERVER["REQUEST_METHOD"] == "POST") {
			//take the arguments from HTML form:
			$topo 			= $_POST["topo"];
			$router_mode 	= $_POST["router_mode"];
			$format 		= $_POST["format"];
			$algo 			= $_POST["algo"];
			$rankdir 		= $_POST["rankdir"];
			$lines 			= $_POST["lines"];
			$aggr 			= $_POST["aggr"];
			$lag 			= $_POST["lag"];
			$nodesep		= $_POST["nodesep"];
			$ranksep		= $_POST["ranksep"];
		}
		?>
		<!-- Page help -->
		<!--
		<div class="help">
			<div id="help-button">
				<h2 id='help-msg'>Help</h2>
			</div>
			<div id="help-pane">
				<img id="help-img" src="pix/legend_topo.png">
				<!-- <div id="help-img"></div> -->
			</div>
		</div>
		-->

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

							<div class="gdiv">
							<label class="ginput" for="router_mode">Router mode:</label>
							<select class="ginput" name="router_mode">
								<option <?php if ($router_mode == "0") {?>selected="true" <?php }; ?>value="0">Router as cluster</option>
								<option <?php if ($router_mode == "1" || $router_mode == "") {?>selected="true" <?php }; ?>value="1">Router as node, one-line</option>
								<option <?php if ($router_mode == "2") {?>selected="true" <?php }; ?>value="2">Router as node, two-line</option>
								<option <?php if ($router_mode == "3") {?>selected="true" <?php }; ?>value="3">Router as node, only with name</option>
							</select>
							</div>

							<div class="gdiv">
							<label class="ginput" for="format">Format:</label>
							<select class="ginput" name="format">
								<option <?php if ($format == "0") {?>selected="true" <?php }; ?>value="0">PNG</option>
								<option <?php if ($format == "1" || $format == "") {?>selected="true" <?php }; ?>value="1">SVG</option>
							</select>
							</div>

							<div class="gdiv">
							<label class="ginput" for="algo">Algorithm:</label>
							<select class="ginput" name="algo">
								<option <?php if ($algo == "0") {?>selected="true" <?php }; ?>value="0">DOT</option>
								<option <?php if ($algo == "1") {?>selected="true" <?php }; ?>value="1">FDP</option>
								<option <?php if ($algo == "2") {?>selected="true" <?php }; ?>value="2">CIRCO</option>
								<option <?php if ($algo == "3" || $algo == "") {?>selected="true" <?php }; ?>value="3">TWOPI</option>
								<option <?php if ($algo == "4") {?>selected="true" <?php }; ?>value="4">NEATO</option>
							</select>
							</div>

							<div class="gdiv">
							<label class="ginput" for="rankdir">Orientation:</label>
							<select class="ginput" name="rankdir">
								<option <?php if ($rankdir == "0" || $rankdir == "") {?>selected="true" <?php }; ?>value="0">Left to Right</option>
								<option <?php if ($rankdir == "1") {?>selected="true" <?php }; ?>value="1">Top to Bottom</option>
							</select>
							</div>

							<div class="gdiv">
							<label class="ginput" for="lines">Lines:</label>
							<select class="ginput" name="lines">
								<option <?php if ($lines == "0") {?>selected="true" <?php }; ?>value="0">Straight</option>
								<option <?php if ($lines == "1") {?>selected="true" <?php }; ?>value="1">Curve</option>
								<option <?php if ($lines == "2") {?>selected="true" <?php }; ?>value="2">Square</option>
								<option <?php if ($lines == "3" || $lines == "") {?>selected="true" <?php }; ?>value="3">Polyline</option>
							</select>
							</div>

							<div class="gdiv">
							<label class="ginput" for="aggr">Grouping:</label>
							<select class="ginput" name="aggr">
								<option <?php if ($aggr == "0" || $aggr == "") {?>selected="true" <?php }; ?>value="0">No</option>
								<option <?php if ($aggr == "1") {?>selected="true" <?php }; ?>value="1">Yes</option>
							</select>
							</div>

							<div class="gdiv">
							<label class="ginput" for="lag">LAG:</label>
							<select class="ginput" name="lag">
								<option <?php if ($lag == "0" || $lag == "") {?>selected="true" <?php }; ?>value="0">No</option>
								<option <?php if ($lag == "1") {?>selected="true" <?php }; ?>value="1">Yes</option>
							</select>
							</div>

							<div class="gdiv">
							<label class="ginput" for="nodesep">NodeSep:</label>
							<input type = "number" max = "10.0" min = "0.0" value = <?php if ($nodesep != "") { echo $nodesep; } else { ?>"1.0"<?php }; ?> step = "0.2" name = "nodesep" />
							</div>

							<div class="gdiv">
							<label class="ginput" for="ranksep">RankSep:</label>
							<input type = "number" max = "10.0" min = "0.0" value = <?php if ($ranksep != "") { echo $ranksep; } else { ?>"1.0"<?php }; ?> step = "0.2" name = "ranksep" />
							</div>

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
						echo "<p> Please specify the desired topology.</p>";
					} else
					{
						$topo = preg_replace('/\s+/', '', $topo);
						exec("plugins/draw_topo_26_estable.bin ".$topo." ".$router_mode." ".$format." ".$algo." ".$rankdir." ".$lines." ".$aggr." ".$lag." ".$nodesep." ".$ranksep , $scriptOutput, $exitCode); //PHP waits until the called program is done
						switch($exitCode)
						{
							case 255:
								echo "<h3 class=\"not\">Topology not found!</h3>";
								echo "<img class=\"not\" src=\"pix/not.gif\">";
								break;
							case 0:
								
								{echo "<a href=\"" . $scriptOutput[count($scriptOutput)-1] . ".graphml" . "\" download>Download GRAPHML</a>";}

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
