<?php
/*

Copyright 2011 Dennis Plöger

This is a RackTables extension to display a schematic cabling plan of all
objects in the system. The current revision consists of:
1. PHP file to use with the well-known "local.php" file
2. PNG file to copy into "pix" directory of RackTables instance
3. patch file to modify index.php

This extension requires an installed PEAR Image-GraphViz module.

*/

require_once 'Image/GraphViz.php';

if (isset($indexlayout)) {
    array_push($indexlayout[2], 'cablingplan');
}

$page['cablingplan']['title'] = 'Cabling plan';
$page['cablingplan']['parent'] = 'index';
$tab['cablingplan']['default'] = 'Cabling plan (PNG)';
$tab['cablingplan']['defaultsvg'] = 'Cabling plan (SVG)';
$tabhandler['cablingplan']['default'] = 'showCablingPlan';
$tabhandler['cablingplan']['defaultsvg'] = 'showCablingPlanSvg';

$image['cablingplan']['path'] = 'pix/cablingplan.png';
$image['cablingplan']['width'] = 218;
$image['cablingplan']['height'] = 200;

function showCablingPlan ()
{
    // Show cabling plan image
    echo "<img hspace='10' vspace='10' src='?module=rendercablingplan&format=png' />\n";
}

function showCablingPlanSvg ()
{
    // Show cabling plan image
    echo "<img hspace='10' vspace='10' src='?module=rendercablingplan&format=svg' />\n";
}

function renderCablingPlan ()
{
    // Build cabling plan

    // Select edges
    $sql = "SELECT oa.id AS source, ob.id AS target, CONCAT(pa.name, _utf8' <> ', pb.name) AS label, 0 AS weight " .
    "FROM ((Link l JOIN Port pa ON l.porta = pa.id) JOIN RackObject oa ON pa.object_id = oa.id " .
    "JOIN Port pb ON l.portb = pb.id JOIN RackObject ob ON pb.object_id = ob.id)";

    $result = usePreparedSelectBlade($sql); 
    $edges = array();
    while ($row = $result->fetch (PDO::FETCH_ASSOC)) {
        $found = false;
        foreach ($edges as $key => $edge) {
            if (($edge['source'] == $row['source']) && ($edge['target'] == $row['target'])) {
                // Edge already exists ("Parallel"). Alter label and add weight
                $edges[$key]['label'] .= "\n" . $row['label'];
                $edges[$key]['weight']++;
                $found = true;
            }
        }

        if (!$found) {
            $edges[] = $row;
        }
    }

    // Select nodes
    $sql = "SELECT DISTINCT o.id AS id, o.name AS label, '' AS url " .
    "FROM Port p JOIN RackObject o ON p.object_id = o.id " .
    "WHERE (p.id IN (SELECT Link.porta AS porta FROM Link) OR p.id IN " .
    "(SELECT Link.portb AS portb FROM Link))";
    $result = usePreparedSelectBlade($sql); 
    $nodes = $result->fetchAll (PDO::FETCH_ASSOC);

    $graph = new Image_GraphViz(
        true, 
        array(
        ), 
        'Cabling Plan', 
        false, 
        false
    );

    foreach ($nodes as $node) {
        $graph->addNode(
            $node['id'],
            array(
                'label' => $node['label'],
                'shape' => 'box3d'
            )
        );
    }

    foreach ($edges as $edge) {
        $graph->addEdge(
            array($edge['source'] => $edge['target']),
            array(
                'label' => $edge['label'],
                'weight' => floatval($edge['weight']),
                'fontsize' => 8.0,
                'arrowhead' => 'dot',
                'arrowtail' => 'dot',
                'arrowsize' => 0.5
            )
        );
    }

    if (in_array($_REQUEST['format'], array('svg', 'png'))) {
        $graph->image($_REQUEST['format']);
    }
}
