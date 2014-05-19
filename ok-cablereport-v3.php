<?php

///////////////////////////////////////////////////////////
// Opin Kerfi: CableReport
// Version: 3.0
//
// Description:
//   Racktables reports plugin for listing of all linked cables in Racktables.
//   Uses jQuery and DataTables for easy sorting and searching.
//   Tested on Racktables version 0.20.5.
//
// Author: Ingimar Robertsson <ingimar@ok.is>
//
// Installation:
//  Copy ok-cablereport-v3.php into the Racktables plugins folder.
//
// Version History:
//   3.0 - Major cleanup of version 2.0 and republished to racktables-contrib
//   2.0 - First version using jQuery/DataTables
//   1.0 - Initial version, static table
///////////////////////////////////////////////////////////

// Variables:
$tabname = 'Cable Report';
$tableheader = 'Cable Report for Racktables';

///////////////////////////////////////////////////////////
$tabhandler['reports']['cablereportv3'] = 'CableReportV3'; // register a report rendering function
$tab['reports']['cablereportv3'] = $tabname; // title of the report tab

function CableReportV3()
{
        global $tableheader;
        $query = 'SELECT L.cable AS cableid, O.name AS dev1, P.name AS port1, D.dict_value as type1, ' .
                '  O2.name AS dev2, P2.name as port2, D2.dict_value as type2 ' .
                'FROM Link as L ' .
                'LEFT JOIN Port as P on P.id = L.porta ' .
                'LEFT JOIN Port as P2 on P2.id = L.portb ' .
                'LEFT JOIN Object as O on O.id = P.object_id ' .
                'LEFT JOIN Object as O2 on O2.id = P2.object_id ' .
                'LEFT JOIN Dictionary as D on D.dict_key = P.type ' .
                'LEFT JOIN Dictionary as D2 on D2.dict_key = P2.type ';

        $result = usePreparedSelectBlade ($query);

        // Local jQuery and DataTables files from DataTables-1.10.0 distribution zip:
        echo '<link rel="stylesheet" type="text/css" href="/rt/extensions/cablereportv3/DataTables-1.10.0/media/css/jquery.dataTables.css">';
        echo '<script type="text/javascript" charset="utf8" src="/rt/extensions/cablereportv3/DataTables-1.10.0/media/js/jquery.js"></script>';
        echo '<script type="text/javascript" charset="utf8" src="/rt/extensions/cablereportv3/DataTables-1.10.0/media/js/jquery.dataTables.js"></script>';
        // Remote jQuery and DataTables files:
//      echo '<link rel="stylesheet" type="text/css" href="https://cdn.datatables.net/1.10.0/css/jquery.dataTables.css">';
//      echo '<script type="text/javascript" charset="utf8" src="https://code.jquery.com/jquery-1.10.2.min.js"></script>';
//      echo '<script type="text/javascript" charset="utf8" src="https://cdn.datatables.net/1.10.0/js/jquery.dataTables.js"></script>';

        echo '<script>
                $(document).ready(function() {
                    $("#cablereport").dataTable({
                        "bPaginate": "false",
                        "bLengthChange": "false",
                        "sPaginationType": "full_numbers",
                        "aaSorting": [[ 0, "desc" ]],
                        "iDisplayLength": 99999,
                    });
                });
                </script>';


        echo '<div class=portlet>';
        echo '<h2>' . $tableheader . '</h2>';
        echo '<table id="cablereport" class="display">';
        echo '<thead><tr>';
        echo '<th>Cable ID</th>';
        echo '<th>Device 1</th>';
        echo '<th>Port 1</th>';
        echo '<th>Type 1</th>';
        echo '<th>Device 2</th>';
        echo '<th>Port 2</th>';
        echo '<th>Type 2</th>';
        echo '</tr></thead><tbody>';

        foreach ($result as $row)
        {
                echo '<tr>';
                echo '<td><b>' . $row['cableid'] . '</b></td>';
                echo '<td><b>' . $row['dev1'] . '</b></td>';
                echo '<td><b>' . $row['port1'] . '</b></td>';
                echo '<td>' . $row['type1'] . '</td>';
                echo '<td><b>' . $row['dev2'] . '</b></td>';
                echo '<td><b>' . $row['port2'] . '</b></td>';
                echo '<td>' . $row['type2'] . '</td>';
                echo "</tr>\n";
        }

        echo '</tbody></table><br/><br/>';
        echo '</div>';
}
?>
