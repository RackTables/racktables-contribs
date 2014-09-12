<?php
// Custom Racktables Report v.0.3
// List all server

// 2012-10-23 - Mogilowski Sebastian <sebastian@mogilowski.net>

$tabhandler['reports']['server'] = 'renderServerReport'; // register a report rendering function
$tab['reports']['server'] = 'Server';                    // The title of the report tab

require_once "reportExtensionLib.php";

function renderServerReport()
{
  $aResult = array();
  $iTotal = 0;
  $sFilter = '{$typeid_4}'; # typeid_4 = Server

  foreach (scanRealmByText ('object', $sFilter) as $Result)
  {

    $aResult[$Result['id']] = array();
    $aResult[$Result['id']]['sName'] = $Result['name'];

    // Create active links in comment
    $aResult[$Result['id']]['sComment'] = $Result['comment'];

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

    $aResult[$Result['id']]['sOS'] = '';
    if ( isset( $attributes['4']['a_value'] ) )
        $aResult[$Result['id']]['sOS'] = $attributes['4']['a_value'];

    $aResult[$Result['id']]['sSlotNumber'] = 'unknown';
    if ( isset( $attributes['28']['a_value'] ) && ( $attributes['28']['a_value'] != '' ) )
        $aResult[$Result['id']]['sSlotNumber'] = $attributes['28']['a_value'];

    // Location
    $aResult[$Result['id']]['sLocation'] = getLocation($Result);

    // IP Informations
    $aResult[$Result['id']]['ipV4List'] = getObjectIPv4AllocationList($Result['id']);
    $aResult[$Result['id']]['ipV6List'] = getObjectIPv6AllocationList($Result['id']);

    // Port (MAC) Informations
    $aResult[$Result['id']]['ports'] = getObjectPortsAndLinks($Result['id']);

    $iTotal++;
  }

  if ( isset($_GET['csv']) ) {

     header('Content-type: text/csv');
     header('Content-Disposition: attachment; filename=export_'.date("Ymdhis").'.csv');
     header('Pragma: no-cache');
     header('Expires: 0');

     $outstream = fopen("php://output", "w");

     $aCSVRow = array('Name','MAC','IP(s)','Comment','Contact','Type','OEM','HW Expire Date','OS','Location');

     fputcsv( $outstream, $aCSVRow );

     foreach ($aResult as $id => $aRow) {
         $aCSVRow = array();
         $aCSVRow[0] = $aRow['sName'];

         $aCSVRow[1] = '';
         foreach ( $aRow['ports'] as $portNumber => $aPortDetails ) {
          if (trim($aPortDetails['l2address']) != '')
           $aCSVRow[1] .= $aPortDetails['l2address'] . ' ';
         }
         $aCSVRow[1] = trim($aCSVRow[1]);

         $aCSVRow[2] = '';
         foreach ( $aRow['ipV4List'] as $key => $aDetails ) {
          if ( function_exists('ip4_format') )
           $key = ip4_format($key);
          if ( trim($key) != '')
           $aCSVRow[2] .= $key . ' ';
         }
         foreach ( $aRow['ipV6List'] as $key => $aDetails ) {
          if ( function_exists('ip6_format') )
           $key = ip6_format($key);
          if ( trim($key) != '')
           $aCSVRow[2] .= $key . ' ';
         }
         $aCSVRow[2] = trim($aCSVRow[2]);

         $aCSVRow[3] = str_replace('&quot;',"'",$aRow['sComment']);
         $aCSVRow[4] = $aRow['sContact'];
         $aCSVRow[5] = $aRow['HWtype'];
         $aCSVRow[6] = $aRow['OEMSN'];
         $aCSVRow[7] = $aRow['HWExpDate'];
         $aCSVRow[8] = $aRow['sOS'];
         $aCSVRow[9] = preg_replace('/<a[^>]*>(.*)<\/a>/iU', '$1', $aRow['sLocation']);

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
  echo '<h2>Server report ('.$iTotal.')</h2><ul>';

  echo '<a href="index.php?page=reports&tab=server&csv">CSV Export</a>';

  echo '<table id="reportTable" class="tablesorter">
          <thead>
            <tr>
              <th>Name</th>
              <th>MAC</th>
              <th>IP(s)</th>
              <th>Comment</th>
              <th>Contact</th>
              <th>Type</th>
              <th>OEM S/N</th>
              <th>HW Expire Date</th>
              <th>OS</th>
              <th>Location</th>
            </tr>
          </thead>
        <tbody>';

  foreach ($aResult as $id => $aRow)
  {
      echo '<tr>
              <td><a href="'. makeHref ( array( 'page' => 'object', 'object_id' => $id) )  .'">'.$aRow['sName'].'</a></td>
              <td>';

      foreach ( $aRow['ports'] as $portNumber => $aPortDetails ) {
          if (trim($aPortDetails['l2address']) != '')
              echo $aPortDetails['l2address'] . '<br/>';
      }

      echo '  </td>
              <td>';

      foreach ( $aRow['ipV4List'] as $key => $aDetails ) {
          if ( function_exists('ip4_format') )
      	      $key = ip4_format($key);
          if ( trim($key) != '')
              echo $key . '<br/>';
      }

      foreach ( $aRow['ipV6List'] as $key => $aDetails ) {
          if ( function_exists('ip6_format') )
              $key = ip6_format($key);
          if ( trim($key) != '')
              echo $key . '<br/>';
      }

      echo '</td>
            <td>'.makeLinksInText($aRow['sComment']).'</td>
            <td>'.$aRow['sContact'].'</td>
            <td>'.$aRow['HWtype'].'</td>
            <td>'.$aRow['OEMSN'].'</td>
            <td>'.$aRow['HWExpDate'].'</td>
            <td>'.$aRow['sOS'].'</td>
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
                2: { sorter: "ipAddress" },
              }, sortList: [[0,0]] }
            )
            $("#reportTable").tableFilter();
          });
       </script>';
}
