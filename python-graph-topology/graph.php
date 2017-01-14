<?php
// =====================================
// Frontend for GraphTopo python script
// =====================================
//	
// Author: Lucas Scola
// e-mail: lucas.scola@hotmail.com.ar
//
// Version: 1.0
//
// =====================================
// Changelog:
//
// *1.0: First implementation.
//

	
//Inserting plugin page into RackTables index
	global $indexlayout;
	global $page;
	global $tab;
	global $tabHandler;
	global $image;
	
	array_push($indexlayout, array('graphtopo'));
	
	$page['graphtopo']['title'] = 'Topology';
	$page['graphtopo']['parent'] = 'index';
	
	$tab['graphtopo']['default'] = 'View';
	registerTabHandler ('graphtopo', 'default', 'renderGraphTopo');
	
	$image['graphtopo']['path'] = 'pix/logo.png';
	$image['graphtopo']['width'] = 218;
	$image['graphtopo']['height'] = 200;
	
	function renderGraphTopo() 
	{
		//variables intialization:
		$topo = $router_mode = $out = $svgName = "";
		$exitCode= -2;
		$scriptOutput = array();
		
		//check if it's a POST method:
		if ($_SERVER["REQUEST_METHOD"] == "POST") {
			//take the arguments from HTML form:
			$topo = $_POST["topo"];
			$router_mode = $_POST["router_mode"];
			$out = $_POST["out"];
		}
		?>
		<div class="settings" style="width: 98%;padding-top:10px;margin: auto;overflow: hidden;">
			<!-- HTML menu formatting -->
			<div class="parameters" style="float:left;clear:both;">
				<form method="post" action="">
				<fieldset>
					<legend> Parameters:</legend>
					Topology: <input type="text" name="topo" value="<?php echo $topo;?>"> <br>
					Router_mode: <select name="router_mode">
						<option <?php if ($router_mode == "0") {?>selected="true" <?php }; ?>value="0">Router as cluster</option>
						<option <?php if ($router_mode == "1") {?>selected="true" <?php }; ?>value="1">Router as node, one-line</option>
						<option <?php if ($router_mode == "2") {?>selected="true" <?php }; ?>value="2">Router as node, two-line</option>
						<option <?php if ($router_mode == "3") {?>selected="true" <?php }; ?>value="3">Router as node, only with name</option>
					</select> <br>
					Output action: <select name="out">
						<option <?php if ($out == "0") {?>selected="true" <?php }; ?>value="0">Default0</option>
						<option <?php if ($out == "1") {?>selected="true" <?php }; ?>value="1">Default1</option>
						<option <?php if ($out == "2") {?>selected="true" <?php }; ?>value="2">Default2</option>
						<option <?php if ($out == "3") {?>selected="true" <?php }; ?>value="3">Default3</option>
						<option <?php if ($out == "4") {?>selected="true" <?php }; ?>value="4">Default4</option>
						<option <?php if ($out == "5") {?>selected="true" <?php }; ?>value="5">Default5</option>
						<option <?php if ($out == "6") {?>selected="true" <?php }; ?>value="6">Default6</option>
						<option <?php if ($out == "7") {?>selected="true" <?php }; ?>value="7">Default7</option>
						</select> <br>
					<input type="submit" value="Execute">
				</fieldset>
				</form>	
			</div>
			
			<div class="info" style="float:right;border: 1px dashed;padding: 5px;">
				<p>Output Action:</p>
				<ul>
					<li>Default0: DOT, Left-to-Right, curved, group-mid-high-ran</li>
					<li>Default1: DOT, Top-to-Bottom, curved, group-mid-high-ran</li>
					<li>Default2: FDP, Top-to-Bottom, curved, no-grouping</li>
					<li>Default3: DOT, Top-to-Bottom, curved, no-grouping</li>
					<li>Default4: CIRCO, Top-to-Bottom, curved, no-grouping</li>
					<li>Default5: CIRCO, Left-to-rigth, curved, no-grouping</li>
					<li>Default6: TWOPI, Left-to-rigth, curved, no-grouping</li>
					<li>Default7: NEATO, Left-to-rigth, curved, no-grouping</li>
				</ul>
			</div>
		</div>
		<div class="result" style="width: 98%;margin: auto;">
			<!-- Results display -->
			<h2>Topology:</h2>
			<?php
					
				if ($_SERVER["REQUEST_METHOD"] == "POST") {
					if (empty($topo)) //Python script needs this parameter in order to work
					{
						echo "<p> Please specify the desired topology.</p>";
					} else 
					{	
						exec("python ../plugins/draw_topo_23_estable.py " . $topo . " " . $router_mode . " " . $out, $scriptOutput, $exitCode); //PHP waits until the called program is done
						
						switch($exitCode)
						{
							case 255:
								echo "<h3>Topology not found!</h3>";
								echo "<img src=\"pix/not.gif\">";
								break;
							case 0:
								echo "<img class=\"topo\" style=\"margin: auto;display: block;max-height: 100%;max-width: 80%;height: auto;\"  src=\"" . $scriptOutput[count($scriptOutput)-1] . "\">";
								break;
						}
					}
						
				}
			?>
		</div>
		<?php
	}
?>
