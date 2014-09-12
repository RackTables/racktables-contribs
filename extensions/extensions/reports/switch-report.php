<?php
// Custom Racktables Report v.0.3
// List all virtual machines

// 2012-10-23 - Mogilowski Sebastian <sebastian@mogilowski.net>

$tabhandler['reports']['switches'] = 'renderSwitchReport'; // register a report rendering function
$tab['reports']['switches'] = 'Switches';                  // The title of the report tab

require_once "reportExtensionLib.php";

function renderSwitchReport()
{
    $aResult = array();
    $iTotal = 0;
    $sFilter = '{$typeid_8}'; # typeid_8 = Switches

    foreach (scanRealmByText ('object', $sFilter) as $Result)
    {
        $aResult[$Result['id']] = array();
        $aResult[$Result['id']]['sName'] = $Result['name'];

        // Create active links in comment
        $aResult[$Result['id']]['sComment'] = makeLinksInText($Result['comment']);

        // Load additional attributes:
        $attributes = getAttrValues ($Result['id']);
        $aResult[$Result['id']]['sContact'] = '';
        if ( isset( $attributes['14']['a_value'] ) )
            $aResult[$Result['id']]['sContact'] = $attributes['14']['a_value'];

        $aResult[$Result['id']]['HWtype'] = '';
        if ( isset( $attributes['2']['a_value'] ) )
            $aResult[$Result['id']]['HWtype'] = $attributes['2']['a_value'];

        $aResult[$Result['id']]['OEMSN'] = '';
        if ( isset( $attributes['1']['a_value'] ) )
            $aResult[$Result['id']]['OEMSN'] = $attributes['1']['a_value'];

        $aResult[$Result['id']]['HWExpDate'] = '';
        if ( isset( $attributes['22']['value'] ) )
            $aResult[$Result['id']]['HWExpDate'] = date("Y-m-d",$attributes['22']['value']);

        $aResult[$Result['id']]['sOSVersion'] = '';
        if ( isset( $attributes['5']['a_value'] ) )
            $aResult[$Result['id']]['sOSVersion'] = $attributes['5']['a_value'];

        $aResult[$Result['id']]['sSlotNumber'] = 'unknown';
        if ( isset( $attributes['28']['a_value'] ) && ( $attributes['28']['a_value'] != '' ) )
         $aResult[$Result['id']]['sSlotNumber'] = $attributes['28']['a_value'];

        // Location
        $aResult[$Result['id']]['sLocation'] = getLocation($Result);

        $iTotal++;
    }

    if ( isset($_GET['csv']) ) {

        header('Content-type: text/csv');
        header('Content-Disposition: attachment; filename=export_'.date("Ymdhis").'.csv');
        header('Pragma: no-cache');
        header('Expires: 0');

        $outstream = fopen("php://output", "w");

        $aCSVRow = array('Name','Comment','Contact','Type','OEM','HW Expire Date','OS Version','Location');

        fputcsv( $outstream, $aCSVRow );

        foreach ($aResult as $id => $aRow) {
            $aCSVRow = array();

            $aCSVRow[0] = $aRow['sName'];
            $aCSVRow[1] = str_replace('&quot;',"'",$aRow['sComment']);
            $aCSVRow[2] = $aRow['sContact'];
            $aCSVRow[3] = $aRow['HWtype'];
            $aCSVRow[4] = $aRow['OEMSN'];
            $aCSVRow[5] = $aRow['HWExpDate'];
            $aCSVRow[6] = $aRow['sOSVersion'];
            $aCSVRow[7] = preg_replace('/<a[^>]*>(.*)<\/a>/iU', '$1', $aRow['sLocation']);

           fputcsv( $outstream, $aCSVRow );
        }

        fclose($outstream);

        exit(0); # Exit normally after send CSV to browser

    }

    // Load stylesheet and jquery scripts
    echo '<link rel="stylesheet" href="extensions/jquery/themes/racktables/style.css" type="text/css"/>';
    echo '<script type="text/javascript" src="extensions/jquery/jquery-latest.js"></script>';
    echo '<script type="text/javascript" src="extensions/jquery/jquery.tablesorter.js"></script>';
    echo '<script type="text/javascript" src="extensions/jquery/picnet.table.filter.min.js"></script>';

    // Display the stat array
    echo "<h2>Switch report ($iTotal)</h2><ul>";

    echo '<a href="index.php?page=reports&tab=switches&csv">CSV Export</a>';

    echo '<table id="reportTable" class="tablesorter">
            <thead>
              <tr>
                <th>Name</th>
                <th>Comment</th>
                <th>Contact</th>
                <th>Type</th>
                <th>OEM S/N</th>
                <th>HW Expire Date</th>
                <th>OS Version</th>
                <th>Location</th>
               </tr>
             </thead>
           <tbody>';

    foreach ($aResult as $id => $aRow)
    {
        echo '<tr>
                <td><a href="'. makeHref ( array( 'page' => 'object', 'object_id' => $id) )  .'">'.$aRow['sName'].'</a></td>
                <td>'.$aRow['sComment'].'</td>
                <td>'.$aRow['sContact'].'</td>
                <td>'.$aRow['HWtype'].'</td>
                <td>'.$aRow['OEMSN'].'</td>
                <td>'.$aRow['HWExpDate'].'</td>
                <td>'.$aRow['sOSVersion'].'</td>
                <td>'.$aRow['sLocation'].'</td>
              </tr>';
    }

    echo '  </tbody>
          </table>';

    echo '<script type="text/javascript">
            $(document).ready(function()
              {
                $.tablesorter.defaults.widgets = ["zebra"];
                $("#reportTable").tablesorter(
                    { headers: {
                    }, sortList: [[0,0]] }
                );
                $("#reportTable").tableFilter();
              }
            );
          </script>';
}
