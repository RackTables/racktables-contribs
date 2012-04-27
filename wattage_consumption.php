<?php

//-----------------------------------------------------------------------------------
// Wattage Consumption - written by curtisb
//$tab['reports']['watts_per_row'] = 'Energy Per Row';
$tab['reports']['watts_per_row'] = 'Enviromental Totals';
$tabhandler['reports']['watts_per_row'] = 'getWattsPerRow';

function getWattsPerRow ()
{

        // assertions
        // find the needed attributes

        global $nextorder;

        // Was this function called with a specific row_id?
        if (isset ($_REQUEST['row_id']))
        {
            assertStringArg ('row_id');
            $row_toshow = $_REQUEST['row_id'];
        }
        else
        {
            $row_toshow = '50032';
        }

        //from renderRackspace(), interface.php:151
        $found_racks = array();
        $rows = array();
        $cellfilter = getCellFilter();
        $rackCount = 0;
        $order = 'odd';
        // get rackspace information
        foreach (getRackRows() as $row_id => $row_name) {
                $rackList = filterCellList (listCells ('rack', $row_id), $cellfilter['expression']);
                $found_racks = array_merge($found_racks, $rackList);
                $rows[] = array(
                        'row_id' => $row_id,
                        'row_name' => $row_name,
                        'racks' => $rackList
                );
                $rackCount += count($rackList);
        }

        // Main layout starts.
        echo "<table border=0 class=objectview cellspacing=0 cellpadding=0>";

        // Left portlet with list of rows.
        echo "<tr><td class=pcleft>";
        startPortlet ('Rack Rows (' . count ($rows) . ')');
        echo "<table border=0 cellspacing=0 cellpadding=3 width='100%'>\n";
        foreach ($rows as $row)
        {
            $row_id = $row['row_id'];
            $row_name = $row['row_name'];
            $rackList = $row['racks'];

            echo "<tr class=row_${order}><td width='20%'></td><td class=tdleft>";
            if (!count ($rackList))
            {
                echo "${row_name} (empty row)";
            }
            else
            {
                echo "<a href='" . makeHref(array('page'=>'reports', 'tab'=>'watts_per_row', 'row_id'=>$row_id)) . "'>${row_name}</a>";
            }
            echo "<td><tr>\n";
            $order = $nextorder[$order];
 
        }            
            
        echo "</td></tr>\n";
        echo "</table><br>\n";
        finishPortlet();

        echo "</td><td class=pcright>";

        // Right Portlet: Draw the racks in the selected row
        $rowInfo = getRackRowInfo ($row_toshow);
        $cellfilter = getCellFilter();
        $rackList = filterCellList (listCells ('rack', $row_toshow), $cellfilter['expression']);

        global $nextorder;
        $rackwidth = getRackImageWidth() * getConfigVar ('ROW_SCALE');
        // Maximum number of racks per row is proportionally less, but at least 1.
        $maxPerRow = max (floor (getConfigVar ('RACKS_PER_ROW') / getConfigVar ('ROW_SCALE')), 1);
        $rackListIdx = 0;
        $rowTotalWattage = 0;
        $order = 'odd';
        startPortlet ('Racks within '. $rowInfo['name'] . ' (' . count($rackList) . ')' );
        echo "<table border=0 cellspacing=5 align='center'><tr>";
        foreach ($rackList as $rack)
        {
                $rackTotalWattage = 0;
                // see renderRack(), interface.php:311
                $rackData = spotEntity ('rack', $rack['id']);
                amplifyCell ($rackData);  
                $objectChildren = getEntityRelatives ('children', 'object', $objectData['id']);
                foreach ($rackData['mountedObjects'] as $object)
                {
                    $objectData = spotEntity ('object', $object);
                    amplifyCell ($objectData);  
                    foreach (getAttrValues ($objectData['id']) as $record)
                    {
                         if ($record['name'] == 'Wattage consuption')
                         { 
                             $rackTotalWattage += $record['value'];
                         }                  
                    }
                }
                if ($rackListIdx % $maxPerRow == 0)
                {
                        if ($rackListIdx > 0)
                                echo '</tr>';
                        echo '<tr>';
                }
                echo "<td align=center class=row_${order}><a href='".makeHref(array('page'=>'rack', 'rack_id'=>$rack['id']))."'>";
                echo "<img border=0 width=${rackwidth} height=" . (getRackImageHeight ($rack['height']) * getConfigVar ('ROW_SCALE'));
                echo " title='${rack['height']} units'";
                echo "src='?module=image&img=minirack&rack_id=${rack['id']}'>";
                echo "<br>${rack['name']} ($rackTotalWattage)</a></td>";
                $order = $nextorder[$order];
                $rackListIdx++;
                $rowTotalWattage += $rackTotalWattage;
        }

        echo "</tr><tr><td align=center colspan=";
        print (count($rackList));
        echo "><br><b>The row total for attribute Wattage consuption is:  $rowTotalWattage</b></td>\n";

        echo "</tr></table>\n";
        finishPortlet();
        echo "</td></tr></table>";
}

?>
