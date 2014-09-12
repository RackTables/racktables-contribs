<?php
// Custom Racktables Report v.0.3.1
// List all virtual machines

// 2013-08-28 - Mogilowski Sebastian <sebastian@mogilowski.net>

$tabhandler['reports']['vm'] = 'renderVMReport'; // register a report rendering function
$tab['reports']['vm'] = 'Virtual Machines';      // The title of the report tab

require_once "reportExtensionLib.php";

function renderVMReport()
{
    $aResult = array();
    $iTotal = 0;
    $sFilter = '{$typeid_1504}'; # typeid_1504 = Virtual machines

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

        $aResult[$Result['id']]['OEMSN'] = '';
        if ( isset( $attributes['1']['a_value'] ) )
            $aResult[$Result['id']]['OEMSN'] = $attributes['1']['a_value'];

        $aResult[$Result['id']]['sOS'] = '';
        if ( isset( $attributes['4']['a_value'] ) )
            $aResult[$Result['id']]['sOS'] = $attributes['4']['a_value'];

        // IP Informations
        $aResult[$Result['id']]['ipV4List'] = getObjectIPv4AllocationList($Result['id']);
        $aResult[$Result['id']]['ipV6List'] = getObjectIPv6AllocationList($Result['id']);

        // Port (MAC) Informations
        $aResult[$Result['id']]['ports'] = getObjectPortsAndLinks($Result['id']);

        // Container
        $aResult[$Result['id']]['container'] = getObjectContainerList($Result['id']);

        $iTotal++;
    }

    if ( isset($_GET['csv']) ) {

        header('Content-type: text/csv');
        header('Content-Disposition: attachment; filename=export_'.date("Ymdhis").'.csv');
        header('Pragma: no-cache');
        header('Expires: 0');

        $outstream = fopen("php://output", "w");

        $aCSVRow = array('Name','MAC','IP(s)','Comment','Contact','OS','Hypervisor');

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
            $aCSVRow[5] = $aRow['sOS'];
            
            $aCSVRow[6] = '';
            foreach ( $aRow['container'] as $key => $aDetails ) {
            	$aCSVRow[6] .= trim($aDetails['container_name']).' ';
            }
            $aCSVRow[6] = trim($aCSVRow[6]);

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
    echo "<h2>Virtual machines report ($iTotal)</h2><ul>";

    echo '<a href="index.php?page=reports&tab=vm&csv">CSV Export</a>';

    echo '<table id="reportTable" class="tablesorter">
            <thead>
              <tr>
                <th>Name</th>
                <th>MAC</th>
                <th>IP(s)</th>
                <th>Comment</th>
                <th>Contact</th>
                <th>OS</th>
                <th>Hypervisor</th>
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

        echo '  </td>'.
             '  <td>';

        foreach ( $aRow['ipV4List'] as $key => $aDetails ) {
            if ( function_exists('ip4_format') )
        	    $key = ip4_format($key);
            if ( trim($key) != '')
                echo $key . '<br/>';
        }

        foreach ( $aRow['ipV6List'] as $key => $aDetails ) {
            if ( function_exists('ip6_format') )
                $key = ip6_format($key);
            else
              $key = new IPv6Address($key);
            if ( trim($key) != '')
                echo $key . '<br/>';
        }

        echo '  </td>
                <td>'.$aRow['sComment'].'</td>
                <td>'.$aRow['sContact'].'</td>
                <td>'.$aRow['sOS'].'</td>
                <td>';

        foreach ( $aRow['container'] as $key => $aDetails )
        	echo '<a href="'. makeHref ( array( 'page' => 'object', 'object_id' => $key) )  .'">'.$aDetails['container_name'].'</a><br/>';
                
        echo   '</td>
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
                      2: { sorter: "ipAddress" }
                    }, sortList: [[0,0]] }
                );
                $("#reportTable").tableFilter();
              }
            );
          </script>';
}
