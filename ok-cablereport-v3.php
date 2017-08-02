<?php

///////////////////////////////////////////////////////////
// Opin Kerfi: CableReport
// Version: 3.2
//
// Description:
//   Racktables reports plugin for listing of all linked cables in Racktables.
//   Uses jQuery and DataTables for easy sorting and searching.
//   Tested on Racktables version 0.20.5 and 0.20.7.
//
// Author: Ingimar Robertsson <ingimar@ok.is>
//
// Installation:
//  Copy ok-cablereport-v31.php into the Racktables plugins folder.
//
// Version History:
//   3.2 - Changed fetchPortList call
//   3.1 - Replaced SQL query with Racktables API calls and added links to devices and ports
//   3.0 - Major cleanup of version 2.0 and republished to racktables-contrib
//   2.0 - First version using jQuery/DataTables
//   1.0 - Initial version, static table
///////////////////////////////////////////////////////////

// Variables:
$tabname = 'Cable Report';
$tableheader = 'Cable Report for Racktables';
$displaylinks = 1;      // 1 = Display HTML links for devices and ports

///////////////////////////////////////////////////////////
$tabhandler['reports']['cablereport'] = 'CableReport'; // register a report rendering function
$tab['reports']['cablereport'] = $tabname; // title of the report tab

function CableReport()
{
        global $tableheader , $displaylinks;

        // Remote jQuery and DataTables files:
        echo '<link rel="stylesheet" type="text/css" href="https://cdn.datatables.net/1.10.0/css/jquery.dataTables.css">';
        echo '<script type="text/javascript" charset="utf8" src="https://code.jquery.com/jquery-1.10.2.min.js"></script>';
        echo '<script type="text/javascript" charset="utf8" src="https://cdn.datatables.net/1.10.0/js/jquery.dataTables.js"></script>';

        // Local jQuery and DataTables files from DataTables-1.10.0 distribution zip:
        //echo '<link rel="stylesheet" type="text/css" href="/rt/extensions/cablereportv3/DataTables-1.10.0/media/css/jquery.dataTables.css">';
        //echo '<script type="text/javascript" charset="utf8" src="/rt/extensions/cablereportv3/DataTables-1.10.0/media/js/jquery.js"></script>';
        //echo '<script type="text/javascript" charset="utf8" src="/rt/extensions/cablereportv3/DataTables-1.10.0/media/js/jquery.dataTables.js"></script>';

        echo '<script>
                $(document).ready(function() {
                    $("#cablereport").dataTable({
                        "bPaginate": "true",
                        "bLengthChange": "false",
                        "sPaginationType": "full_numbers",
                        "aaSorting": [[ 0, "desc" ]],
                        "iDisplayLength": 20,
                        "stateSave": false,
                        "oLanguage": {
                                "sLengthMenu": \'Display <select>\'+
                                           \'<option value="10">10</option>\'+
                                           \'<option value="20">20</option>\'+
                                           \'<option value="30">30</option>\'+
                                           \'<option value="40">40</option>\'+
                                           \'<option value="50">50</option>\'+
                                           \'<option value="-1">All</option>\'+
                                           \'</select> records\'
                        }
                    });
                });
                </script>';

        echo "\n";
        echo '<div class=portlet>';
        echo '<h2>' . $tableheader . '</h2>';
        echo "\n";
        echo '<table id="cablereport" class="display">';
        echo "\n";
        echo '<thead><tr>';
        echo '<th>Cable ID</th>';
        echo '<th>Device 1</th>';
        echo '<th>Port 1</th>';
        echo '<th>Type 1</th>';
        echo '<th>Device 2</th>';
        echo '<th>Port 2</th>';
        echo '<th>Type 2</th>';
        echo '</tr></thead>';
        echo "\n";
        echo '<tbody>';
        echo "\n";

        $allports = fetchPortList('IF(la.porta, pa.id, pb.id) IS NOT NULL');
        $cid = 0;
        foreach ( $allports as $port ) {
                $allporttypes[$port['id']] = $port['oif_name'];

                if ( $port['linked'] != 1 ) {
                        continue;
                }

                if ( $done[$port['id']] == 1 ) {
                        continue;
                } else {
                        $cid++;
                        $cabletable[$cid]['cableid'] = $port['cableid'];
                        if ( $displaylinks == 1 ) {
                                $cabletable[$cid]['device1'] = formatPortLink($port['object_id'],$port['object_name'],NULL,NULL);
                                $cabletable[$cid]['port1']   = formatPortLink($port['object_id'],NULL,$port['id'],$port['name']);
                        } else {
                                $cabletable[$cid]['device1'] = $port['object_name'];
                                $cabletable[$cid]['port1']   = $port['name'];
                        }
                        $cabletable[$cid]['port1id'] = $port['id'];
                        $cabletable[$cid]['type1']   = $port['oif_name'];
                        if ( $displaylinks == 1 ) {
                                $cabletable[$cid]['device2'] = formatPortLink($port['remote_object_id'],$port['remote_object_name'],NULL,NULL);
                                $cabletable[$cid]['port2']   = formatPortLink($port['remote_object_id'],NULL,$port['remote_id'],$port['remote_name']);
                        } else {
                                $cabletable[$cid]['device2'] = $port['remote_object_name'];
                                $cabletable[$cid]['port2']   = $port['remote_name'];
                        }
                        $cabletable[$cid]['port2id'] = $port['remote_id'];
                        $cabletable[$cid]['type2']   = ''; # missing from fetchPortList() add later from $allporttypes being created;
                        $done[$port['remote_id']] = 1;
                }
        }

        foreach ( $cabletable as $cable ) {
                echo '<tr>';
                echo '<td>';
                echo $cable['cableid'];
                echo '</td><td>';
                echo $cable['device1'];
                echo '</td><td>';
                echo $cable['port1'];
                echo '</td><td>';
                echo $cable['type1'];
                echo '</td><td>';
                echo $cable['device2'];
                echo '</td><td>';
                echo $cable['port2'];
                echo '</td><td>';
                echo $allporttypes[$cable['port2id']];
                echo '</td>';
                echo '</tr>';
                echo "\n";
        }

        echo '</tbody></table><br/><br/>';
        echo 'ok-cablereport version 3.2';
        echo '</div>';
}
