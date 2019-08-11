<?php
// Custom Racktables Report v.0.4.1
// List a type of objects in a table and allow to export them via CSV

// 2016-02-04 - Mogilowski Sebastian <sebastian@mogilowski.net>
// 2018-09-10 - Lars Vogdt <lars@linux-schulserver.de>


require_once "reportExtensionLib.php";
require_once "custom-report.php";

function plugin_reports_info ()
{               
        return array
        (       
                'name' => 'reports',
                'longname' => 'Custom Reports', 
                'version' => '0.4.1',
                'home_url' => 'https://github.com/lrupp/racktables-reports'
        );          
}  

function plugin_reports_init ()
{
	global $page, $tab;
	$tab['reports']['custom'] = 'Custom';
	$tab['reports']['server'] = 'Server';
	$tab['reports']['switches'] = 'Switches';
	$tab['reports']['vm'] = 'Virtual Machines';

	$tabhandler['reports']['custom'] = 'renderCustomReport';
	$tabhandler['reports']['server'] = 'renderServerReport';
	$tabhandler['reports']['switches'] = 'renderSwitchReport';
	$tabhandler['reports']['vm'] = 'renderVMReport';

	registerTabHandler('reports', 'custom', 'renderCustomReport');
        registerTabHandler('reports', 'server', 'renderServerReport');
	registerTabHandler('reports', 'switches', 'renderSwitchReport');
	registerTabHandler('reports', 'vm', 'renderVMReport');
}

function plugin_reports_install ()
{
	addConfigVar('REPORTS_CSS_PATH', 'css/report', 'string', 'yes', 'no', 'no', 'Path to the CSS files of the Custom Reports plugin');
	addConfigVar('REPORTS_JS_PATH', 'js/report', 'string', 'yes', 'no', 'no', 'Path to the Javascript files of the Custom Reports plugin');
	addConfigVar('REPORTS_SHOW_MAC_FOR_SWITCHES', 'yes', 'string', 'no', 'no', 'yes', 'Show MAC addresses in Custom Switch Report' );
	return TRUE;
}

function plugin_reports_uninstall ()
{
	deleteConfigVar('REPORTS_CSS_PATH');
	deleteConfigVar('REPORTS_JS_PATH');
	deleteConfigVar('REPORTS_SHOW_MAC_FOR_SWITCHES');
	return TRUE;
}

function plugin_reports_upgrade ()
{
        return TRUE;
}

function renderServerReport()
{
	$filter='{$typeid_4}'; # typeid_4 = Server
	renderReport($filter);
}

function renderSwitchReport()
{
	$filter='{$typeid_8}'; # typeid_8 = Switches
	renderReport($filter);
}

function renderVMReport()
{
	$filter='{$typeid_1504}'; # typeid_1504 = Virtual machines
	renderReport($filter);
}


function renderReport($sFilter)
{
  $aResult = array();
  $iTotal = 0;

  foreach (scanRealmByText ('object', $sFilter) as $Result)
  {

    $aResult[$Result['id']] = array();
    $aResult[$Result['id']]['sName'] = $Result['name'];

    // Asset Number
    $aResult[$Result['id']]['sAsset'] = $Result['asset_no'];

    // Load additional attributes:
    $attributes = getAttrValues ($Result['id']);

    // Contact information
    $aResult[$Result['id']]['sContact'] = '';
    if ( isset( $attributes['14']['a_value'] ) )
        $aResult[$Result['id']]['sContact'] = $attributes['14']['a_value'];

    // Create active links in comment
    $aResult[$Result['id']]['sComment'] = $Result['comment'];

    // Hardware Type
    $aResult[$Result['id']]['HWtype'] = '';
    if ( isset( $attributes['2']['a_value'] ) )
        $aResult[$Result['id']]['HWtype'] = $attributes['2']['a_value'];

    // OEM S/N
    $aResult[$Result['id']]['OEMSN'] = '';
    if ( isset( $attributes['1']['a_value'] ) )
        $aResult[$Result['id']]['OEMSN'] = $attributes['1']['a_value'];

    // HW Expire Date
    $aResult[$Result['id']]['HWExpDate'] = '';
    if ( isset( $attributes['22']['value'] ) )
        $aResult[$Result['id']]['HWExpDate'] = date("Y-m-d",$attributes['22']['value']);

    // Operating System (OS)
    $aResult[$Result['id']]['sOS'] = '';
    if ( isset( $attributes['4']['a_value'] ) )
        $aResult[$Result['id']]['sOS'] = $attributes['4']['a_value'];

    // OS Version (for Switches)
    $aResult[$Result['id']]['sOSVersion'] = '';
    if ( isset( $attributes['5']['a_value'] ) )
        $aResult[$Result['id']]['sOSVersion'] = $attributes['5']['a_value'];


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

    // Container
    $aResult[$Result['id']]['container'] = getObjectContainerList($Result['id']);

    $iTotal++;
  }

  // define standard fields for all filters
  $aCSVRow = array('Name','MAC(s)','IP(s)','Comment','Contact');
  $title = 'Unnamed report';
  // add specific fields depending on filter value
  switch($sFilter)
  {
      case '{$typeid_4}': $title = 'Server report';
                          $report_type = 'server';
                          array_push($aCSVRow, 'Type','Asset No.','Location','OEM S/N','HW Expire Date','OS');
                          break;
      case '{$typeid_8}': $title = 'Switch report';
                          $report_type = 'switches';
                          array_push($aCSVRow, 'Type','Asset No.','Location','OEM S/N','HW Expire Date','OS Version');
                          break;
      case '{$typeid_1504}': $title = 'Virtual machines report';
                             $report_type = 'vm';
                             array_push($aCSVRow, 'OS','Hypervisor');
                             break;
      default: echo '<h2>Unknown/No valid filter definition found</h2>';
               break;
  }

  if ( isset($_GET['csv']) ) {

     header('Content-type: text/csv');
     header('Content-Disposition: attachment; filename=export_'.$report_type.'_'.date("Ymdhis").'.csv');
     header('Pragma: no-cache');
     header('Expires: 0');

     $outstream = fopen("php://output", "w");

     fputcsv( $outstream, $aCSVRow );

     foreach ($aResult as $id => $aRow) 
     {
         //           0      1      2        3         4        5        6            7       8            9        10
         // Server: 'Name','MAC','IP(s)','Comment','Contact', 'Type','Asset No.','Location','OEM','HW Expire Date','OS'
         // Switch: 'Name','MAC','IP(s)','Comment','Contact', 'Type','Asset No.','Location','OEM','HW Expire Date','OS Version'
         // VM    : 'Name','MAC','IP(s)','Comment','Contact', 'OS',  'Hypervisor'

         $aCSVRow = array();
         // Name
         $aCSVRow[0] = $aRow['sName'];

         // MAC
         $aCSVRow[1] = '';
         foreach ( $aRow['ports'] as $portNumber => $aPortDetails ) {
          if (trim($aPortDetails['l2address']) != '')
           $aCSVRow[1] .= $aPortDetails['l2address'] . ' ';
         }
         $aCSVRow[1] = trim($aCSVRow[1]);

         // IP(s)
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

         // Comment
         $aCSVRow[3] = str_replace('&quot;',"'",$aRow['sComment']);

         // Contact
         $aCSVRow[4] = $aRow['sContact'];

         switch($sFilter)
         {
             case '{$typeid_4}':
             case '{$typeid_8}':
                                 // Type
                                 $aCSVRow[5] = $aRow['HWtype'];
                                 // Asset No
                                 $aCSVRow[6] = $aRow['sAsset'];
                                 // Location
                                 $aCSVRow[7] = preg_replace('/<a[^>]*>(.*)<\/a>/iU', '$1', $aRow['sLocation']);
                                 // OEM S/N
                                 $aCSVRow[8] = $aRow['OEMSN'];
                                 // HW Expire Date
                                 $aCSVRow[9] = $aRow['HWExpDate'];
                                 break;
             case '{$typeid_1504}':
                                 // OS
                                 $aCSVRow[5] = $aRow['sOS'];
                                 // Container
                                 $aCSVRow[6] = '';
                                 foreach ( $aRow['container'] as $key => $aDetails ) {
                                     $aCSVRow[6] .= trim($aDetails['container_name']).' ';
                                 }
                                 break;
         }
         switch($sFilter)
         {
             case '{$typeid_4}': // OS
                                 $aCSVRow[10] = $aRow['sOS'];
                                 break;
             case '{$typeid_8}': // OS Version
                                 $aCSVRow[10] = $aRow['sOSVersion'];
                                 break;
         }

         fputcsv( $outstream, $aCSVRow );
     }

     fclose($outstream);

     exit(0); # Exit normally after send CSV to browser

  }

  // Load stylesheet and jquery sicripts
  $css_path=getConfigVar('REPORTS_CSS_PATH');
  $js_path=getConfigVar('REPORTS_JS_PATH');
  addCSS ("$css_path/style.css");
  addJS ("$js_path/jquery-latest.js");
  addJS ("$js_path/jquery.tablesorter.js");
  addJS ("$js_path/picnet.table.filter.min.js");

  // Display the stat array
  echo "\n<h2>$title (".$iTotal.")</h2><ul>";
  echo "<a href='index.php?page=reports&tab=$report_type&csv'>CSV Export</a>\n";
  echo "<table id=\"reportTable\" class=\"tablesorter\">\n  <thead>\n    <tr>\n";
  foreach ($aCSVRow  as $row)
  {
    echo "      <th>$row</th>\n";
  }
  echo "    </tr>\n  </thead>\n<tbody>\n";

  foreach ($aResult as $id => $aRow)
  {
      //           0      1      2        3         4        5        6            7       8            9        10
      // Server: 'Name','MAC','IP(s)','Comment','Contact', 'Type','Asset No.','Location','OEM','HW Expire Date','OS'
      // Switch: 'Name','MAC','IP(s)','Comment','Contact', 'Type','Asset No.','Location','OEM','HW Expire Date','OS Version'
      // VM    : 'Name','MAC','IP(s)','Comment','Contact', 'OS',  'Hypervisor'
      //
      // Name
      echo "<tr>\n  <td><a href=\"". makeHref ( array( 'page' => 'object', 'object_id' => $id) )  ."\">".$aRow['sName']."</a></td>\n  <td>";
      // MAC
      if (getConfigVar ('REPORTS_SHOW_MAC_FOR_SWITCHES') == 'yes')
      {
        foreach ( $aRow['ports'] as $portNumber => $aPortDetails ) {
          if (trim($aPortDetails['l2address']) != '')
              echo $aPortDetails['l2address'] . "<br/>\n";
        }
      }
      echo "  </td>\n  <td>";

      // IP(s)
      foreach ( $aRow['ipV4List'] as $key => $aDetails ) {
          if ( function_exists('ip4_format') )
              $key = ip4_format($key);
          if ( trim($key) != '')
              echo $key . "<br/>\n";
      }

      foreach ( $aRow['ipV6List'] as $key => $aDetails ) {
          if ( function_exists('ip6_format') )
              $key = ip6_format($key);
          if ( trim($key) != '')
              echo $key . "<br/>\n";
      }

      // Comment & Contact
      echo "  </td>\n  <td>".makeLinksInText($aRow['sComment'])."  </td>\n";
      echo '  <td>'.makeLinksInText($aRow['sContact'])."  </td>\n";

      switch($sFilter)
      {
             case '{$typeid_4}':
             case '{$typeid_8}':
                                 // Type
                                 echo '  <td>'.$aRow['HWtype']."</td>\n";
                                 // Asset No
                                 echo '  <td>'.$aRow['sAsset']."</td>\n";
                                 // Location
                                 echo '  <td>'.$aRow['sLocation']."</td>\n";
                                 // OEM S/N
                                 echo '  <td>'.$aRow['OEMSN']."</td>\n";
                                 // HW Expire Date
                                 echo '  <td>'.$aRow['HWExpDate']."</td>\n";
                                 break;
             case '{$typeid_1504}':
                                 // OS
                                 echo '  <td>'.$aRow['sOS']."</td>\n";
                                 // Container
                                 echo '  <td>';
                                 foreach ( $aRow['container'] as $key => $aDetails ) {
                                     echo trim($aDetails['container_name'])."<br/>\n";
                                 }
                                 echo "  </td>\n";
                                 break;
      }
      switch($sFilter)
      {
             case '{$typeid_4}': // OS
                                 echo '  <td>'.$aRow['sOS']."</td>\n";
                                 break;
             case '{$typeid_8}': // OS Version
                                 echo '  <td>'.$aRow['sOSVersion']."</td>\n";
                                 break;
      }
      echo "  </tr>\n";
  }

  echo "  </tbody>\n</table>\n";

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

?>
