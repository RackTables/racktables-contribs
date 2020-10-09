<?php
// Custom Racktables Report v.0.4.0
// List a type of objects in a table and allow to export them via CSV

// 2016-02-04 - Mogilowski Sebastian <sebastian@mogilowski.net>
// 2018-09-10 - Lars Vogdt <lars@linux-schulserver.de>


function renderCustomReport()
{
    # Get object list
    $phys_typelist = readChapter (CHAP_OBJTYPE, 'o');
    $attibutes     = getAttrMap();
    $aTagList      = getTagList();
    $report_type   = 'custom';

    if ( ( $_SERVER['REQUEST_METHOD'] == 'POST' ) && ( isset( $_POST['csv'] ) ) ) {

        header('Content-type: text/csv');
        header('Content-Disposition: attachment; filename=export_'.$report_type.'_'.date("Ymdhis").'.csv');
        header('Pragma: no-cache');
        header('Expires: 0');

        $outstream = fopen("php://output", "w");

        $aResult = getResult($_POST); // Get Result
        $_POST['name'] = validateColums($_POST); // Fix empty colums

        $csvDelimiter = (isset( $_POST['csvDelimiter'] )) ? $_POST['csvDelimiter'] : ',';

        /* Create Header */
        $aCSVRow = array();

        if ( isset( $_POST['sName'] ) && $_POST['sName'] )
            array_push($aCSVRow, "Name");

        if ( isset( $_POST['label'] ) )
            array_push($aCSVRow, "Label");

        if ( isset( $_POST['type'] ) )
        	array_push($aCSVRow, "Type");

        if ( isset( $_POST['asset_no'] ) )
            array_push($aCSVRow, "Asset Tag");

        if ( isset( $_POST['has_problems'] ) )
            array_push($aCSVRow, "Has Problems");

        if ( isset( $_POST['comment'] ) )
            array_push($aCSVRow, "Comment");

        if ( isset( $_POST['runs8021Q'] ) )
            array_push($aCSVRow, "Runs 8021Q");

        if ( isset( $_POST['location'] ) )
            array_push($aCSVRow, "Location");

        if ( isset( $_POST['MACs'] ) )
            array_push($aCSVRow, "MACs");

        if ( isset( $_POST['IPs'] ) )
            array_push($aCSVRow, "IPs");

        if ( isset( $_POST['attributeIDs'] ) ) {
            foreach ( $_POST['attributeIDs'] as $attributeID )
                array_push($aCSVRow, $attibutes[$attributeID]['name']);
        }

        if ( isset( $_POST['Tags'] ) )
            array_push($aCSVRow, "Tags");

        if ( isset( $_POST['Ports'] ) )
            array_push($aCSVRow, "Ports");

        if ( isset( $_POST['Containers'] ) )
       	    array_push($aCSVRow, "Containers");

        if ( isset( $_POST['Childs'] ) )
            array_push($aCSVRow, "Child objects");

        fputcsv( $outstream, $aCSVRow, $csvDelimiter );

        /* Create data rows */
        foreach ( $aResult as $Result ) {
            $aCSVRow = array();

            if ( isset( $_POST['sName'] ) )
                array_push($aCSVRow, $Result['name']);

            if ( isset( $_POST['label'] ) )
                array_push($aCSVRow, $Result['label']);

            if ( isset( $_POST['type'] ) )
            	array_push($aCSVRow, $phys_typelist[$Result['objtype_id']]);

            if ( isset( $_POST['asset_no'] ) )
                array_push($aCSVRow, $Result['asset_no']);

            if ( isset( $_POST['has_problems'] ) )
                array_push($aCSVRow, $Result['has_problems']);

            if ( isset( $_POST['comment'] ) )
                array_push($aCSVRow, str_replace('&quot;',"'",$Result['comment']));

            if ( isset( $_POST['runs8021Q'] ) )
                array_push($aCSVRow, $Result['runs8021Q']);

            if ( isset( $_POST['location'] ) )
                array_push($aCSVRow, preg_replace('/<a[^>]*>(.*)<\/a>/iU', '$1', getLocation($Result)));

            if ( isset( $_POST['MACs'] ) ) {
                $sTemp = '';
                foreach ( getObjectPortsAndLinks($Result['id']) as $portNumber => $aPortDetails ) {
                    if ( trim($aPortDetails['l2address']) != '')
                        $sTemp .= $aPortDetails['l2address'].' ';
                }
                array_push($aCSVRow, $sTemp);
            }

            if ( isset( $_POST['IPs'] ) ) {
                $sTemp = '';
                foreach ( getObjectIPv4AllocationList($Result['id']) as $key => $aDetails ) {
                    if ( function_exists('ip4_format') )
                        $key = ip4_format($key);
                    if ( trim($key) != '')
                        $sTemp .= $key.' ';

                }
                foreach ( getObjectIPv6AllocationList($Result['id']) as $key => $aDetails ) {
                    if ( function_exists('ip6_format') )
                        $key = ip6_format($key);
                    else
                      $key = new IPv6Address($key);
                    if ( trim($key) != '')
                        $sTemp .= $key.' ';
                }
                array_push($aCSVRow, $sTemp);
            }

            if ( isset( $_POST['attributeIDs'] ) ) {
                $attributes = getAttrValues ($Result['id']);
                foreach ( $_POST['attributeIDs'] as $attributeID ) {
                    if ( isset( $attributes[$attributeID]['a_value'] ) )
                        array_push($aCSVRow,$attributes[$attributeID]['a_value']);
                    elseif ( ($attributes[$attributeID]['value'] != '') && ( $attributes[$attributeID]['type'] == 'date' )   )
                        array_push($aCSVRow,date("Y-m-d",$attributes[$attributeID]['value']));
                    else
                     array_push($aCSVRow,'');
                }
            }

            if ( isset( $_POST['Tags'] ) ) {
                $sTemp = '';
                foreach ( $Result['tags'] as $aTag ) {
                    $sTemp .= $aTag['tag'].' ';
                }
                if ( count($Result['itags']) > 0 ) {
                    $sTemp .=  '(';
                    foreach ( $Result['itags'] as $aTag ) {
                        $sTemp .= $aTag['tag'].' ';
                    }
                    $sTemp .=  ')';
                }
                array_push($aCSVRow, $sTemp);
            }

            if ( isset( $_POST['Ports'] ) ) {
                $sTemp = '';

                foreach ( $Result['portsLinks'] as $port ) {
                    $sTemp .= $port['name'].': '.$port['remote_object_name'];
                    if ( trim($port['cableid']) != '')
                        $sTemp .= ' Cable ID: '.$port['cableid'];
                    $sTemp .= ' ';
                }
                $sTemp = trim($sTemp);

                array_push($aCSVRow, $sTemp);
            }

            if ( isset( $_POST['Containers'] ) ) {
            	$sTemp = '';

            	foreach ( getObjectContainerList($Result['id']) as $key => $aDetails ) {
            	    $sTemp .= trim($aDetails['container_name']).' ';
            	}
            	$sTemp = trim($sTemp);

            	array_push($aCSVRow, $sTemp);
            }

            if ( isset( $_POST['Childs'] ) ) {
            	$sTemp = '';

            	foreach ( getObjectChildObjectList($Result['id']) as $key => $aDetails ) {
            	    $sTemp .= trim($aDetails['object_name']).' ';
            	}
            	$sTemp = trim($sTemp);

            	array_push($aCSVRow, $sTemp);
            }

            fputcsv( $outstream, $aCSVRow, $csvDelimiter );
        }

        fclose($outstream);

        exit(0); # Exit normally after send CSV to browser

    }

    echo '<h2> Custom report</h2><ul>';

    // Load stylesheet and jquery scripts
    $css_path=getConfigVar('REPORTS_CSS_PATH');
    $js_path=getConfigVar('REPORTS_JS_PATH');
    addCSS ("$css_path/style.css");
    addJS ("$js_path/saveFormValues.js");
    addJS ("$js_path/jquery-latest.js");
    addJS ("$js_path/jquery.tablesorter.js");
    addJS ("$js_path/picnet.table.filter.min.js");

    if ( $_SERVER['REQUEST_METHOD'] == 'POST' )
        echo '<a href="#" class="show_hide">Show/hide search form</a><br/><br/>';

    echo '<div class="searchForm">';

    echo '<form method="post" name="searchForm">';

    echo '<table class="searchTable">
            <tr>
              <th>Object Type</th>
              <th>Common Values</th>
              <th>Attributes</th>
              <th>Tags</th>
              <th>Misc</th>
            </tr>
            <tr>';

    echo '<td valign="top">
             <table class="searchTable">';
    $i=0;
    foreach ( $phys_typelist as $objectTypeID => $sName ) {
        if( $i % 2 )
          echo '<tr class="odd">';
        else
         echo '<tr>';
        echo '  <td>
                   <input type="checkbox" name="objectIDs[]" value="'.$objectTypeID.'"';
                    if (isset($_POST['objectIDs']) && in_array($objectTypeID, $_POST['objectIDs']) )
                      echo ' checked="checked"';
        echo '     > '.$sName.'
                 </td>
               </tr>';
        $i++;
    }
    echo '  </table>
           </td>';

    echo '<td valign="top">
           <table class="searchTable">
             <tr><td><input type="checkbox" name="sName" value="1" ';if (isset($_POST['sName'])) echo ' checked="checked"'; echo '> Name</td></tr>
             <tr class="odd"><td><input type="checkbox" name="label" value="1" ';if (isset($_POST['label'])) echo ' checked="checked"'; echo '> Label</td></tr>
             <tr><td><input type="checkbox" name="type" value="1" ';if (isset($_POST['type'])) echo ' checked="checked"'; echo '> Type</td></tr>
             <tr class="odd"><td><input type="checkbox" name="asset_no" value="1" ';if (isset($_POST['asset_no'])) echo ' checked="checked"'; echo '> Asset Tag</td></tr>
             <tr><td><input type="checkbox" name="location" value="1" ';if (isset($_POST['location'])) echo ' checked="checked"'; echo '> Location</td></tr>
             <tr class="odd"><td><input type="checkbox" name="has_problems" value="1" ';if (isset($_POST['has_problems'])) echo ' checked="checked"'; echo '> Has Problems</td></tr>
             <tr><td><input type="checkbox" name="comment" value="1" ';if (isset($_POST['comment'])) echo ' checked="checked"'; echo '> Comment</td></tr>
             <tr class="odd"><td><input type="checkbox" name="runs8021Q" value="1" ';if (isset($_POST['runs8021Q'])) echo ' checked="checked"'; echo '> Runs 8021Q</td></tr>
             <tr><td><input type="checkbox" name="MACs" value="1" ';if (isset($_POST['MACs'])) echo ' checked="checked"'; echo '> MACs</td></tr>
             <tr class="odd"><td><input type="checkbox" name="IPs" value="1" ';if (isset($_POST['IPs'])) echo ' checked="checked"'; echo '> IPs</td></tr>
             <tr><td><input type="checkbox" name="Tags" value="1" ';if (isset($_POST['Tags'])) echo ' checked="checked"'; echo '> Tags</td></tr>
             <tr class="odd"><td><input type="checkbox" name="Ports" value="1" ';if (isset($_POST['Ports'])) echo ' checked="checked"'; echo '> Ports</td></tr>
             <tr><td><input type="checkbox" name="Containers" value="1" ';if (isset($_POST['Containers'])) echo ' checked="checked"'; echo '> Containers</td></tr>
             <tr class="odd"><td><input type="checkbox" name="Childs" value="1" ';if (isset($_POST['Childs'])) echo ' checked="checked"'; echo '> Child objects</td></tr>
           </table>
         </td>';

    echo '<td valign="top">
             <table class="searchTable">';
    $i=0;
    foreach ( $attibutes as $attributeID => $aRow ) {
      if( $i % 2 )
          echo '<tr class="odd">';
        else
         echo '<tr>';
      echo ' <td>
                <input type="checkbox" name="attributeIDs[]" value="'.$attributeID.'"';
                 if (isset($_POST['attributeIDs']) && in_array($attributeID, $_POST['attributeIDs']) )
                 echo ' checked="checked"';
      echo '> '.$aRow['name'].'
              </td>
             </tr>';
      $i++;
    }
    echo '  </table>
           </td>';

    echo '<td valign="top">
            <table class="searchTable">';

    $i = 0;
    foreach ( $aTagList as $aTag ) {
        echo '<tr '.($i%2 ? 'class="odd"' : '').'>
                <td>
                  <input type="checkbox" name="tag['.$aTag['id'].']" value="1" '.( isset($_POST['tag'][$aTag['id']]) ? 'checked="checked" ' : '').'> '.
                  $aTag['tag'].'
                </td>
              </tr>';
        $i++;
    }
    if ( count($aTagList) < 1 )
     echo '<tr><td><i>No Tags available</i></td></tr>';

    echo '  </table>
          </td>';

    echo '<td valign="top">
            <table class="searchTable">
              <tr class="odd"><td><input type="checkbox" name="csv" value="1"> CSV Export</td></tr>
              <tr><td><input type="text" name="csvDelimiter" value="," size="1"> CSV Delimiter</td></tr>
              <tr class="odd"><td>Name Filter: <i>(Regular Expression)</i></td></tr>
              <tr><td><input type="text" name="name_preg" value="'; if (isset($_POST['name_preg'])) echo $_POST['name_preg']; echo '" style="height: 11pt;"></td></tr>
              <tr class="odd"><td>Asset Tag Filter: <i>(Regular Expression)</i></td></tr>
              <tr><td><input type="text" name="tag_preg" value="'; if (isset($_POST['tag_preg'])) echo $_POST['tag_preg']; echo '" style="height: 11pt;"></td></tr>
              <tr class="odd"><td>Comment Filter: <i>(Regular Expression)</i></td></tr>
              <tr><td><input type="text" name="comment_preg" value="'; if (isset($_POST['comment_preg'])) echo $_POST['comment_preg']; echo '" style="height: 11pt;"></td></tr>
              <tr class="odd"><td>&nbsp;</td></tr>
              <tr>
                <td>
                  Save:
                  <input id="nameQuery" type="text" name="nameQuery" value="" style="height: 11pt; width:150px"/> <input type="button" value=" Ok " onclick="saveQuery();">
              	</td>
              </tr>
              <tr class="odd">
                <td>
                  Load:<br/>
                   <span id="loadButtons"></span>
                   <script type="text/javascript">
                     loadButtons();
                   </script>
                </td>
              </tr>
              <tr><td>&nbsp;</td></tr>
              <tr><td align="right"><input type="submit" value=" Search "></td></tr>
            </table>
          </td>
        </tr>
      </table>';

    echo '</form>';

    echo '</div>';

    if ( $_SERVER['REQUEST_METHOD'] == 'POST' ) {

     $aResult = getResult($_POST); // Get Result
     $_POST['sName'] = validateColums($_POST); // Fix empty colums

     if ( count($aResult) > 0) {

        echo '<table  id="customTable" class="tablesorter">
               <thead>
                 <tr>';

        if ( isset( $_POST['sName'] )  && $_POST['sName'] )
            echo '<th>Name</th>';

        if ( isset( $_POST['label'] ) )
            echo '<th>Label</th>';

        if ( isset( $_POST['type'] ) )
        	echo '<th>Type</th>';

        if ( isset( $_POST['asset_no'] ) )
            echo '<th>Asset Tag</th>';

        if ( isset( $_POST['has_problems'] ) )
            echo '<th>Has Problems</th>';

        if ( isset( $_POST['comment'] ) )
            echo '<th>Comment</th>';

        if ( isset( $_POST['runs8021Q'] ) )
            echo '<th>Runs 8021Q</th>';

        if ( isset( $_POST['location'] ) )
            echo '<th>Location</th>';

        if ( isset( $_POST['MACs'] ) )
            echo '<th>MAC(s)</th>';

        if ( isset( $_POST['IPs'] ) )
            echo '<th>IP(s)</th>';

        if ( isset( $_POST['attributeIDs'] ) ) {
            foreach ( $_POST['attributeIDs'] as $attributeID )
                echo '<th>'.$attibutes[$attributeID]['name'].'</th>';
        }

        if ( isset( $_POST['Tags'] ) ) {
            echo '<th>Tags</th>';
        }

        if ( isset ($_POST['Ports']) ) {
            echo '<th>Ports</th>';
        }

        if ( isset( $_POST['Containers'] ) ) {
        	echo '<th>Containers</th>';
        }

        if ( isset( $_POST['Childs'] ) ) {
        	echo '<th>Child objects</th>';
        }

        echo '  </tr>
              </thead>
              <tbody>';

        foreach ( $aResult as $Result ) {

            echo '<tr>';

            if ( isset( $_POST['sName'] ) ) {
                echo '<td>
                        <span class="object_'.str_replace('$','',$Result['atags'][1]['tag']).'">';
                if ( isset( $Result['name'] ) )
                    echo '<a href="'. makeHref ( array( 'page' => 'object', 'object_id' => $Result['id']) )  .'">'.$Result['name'].'</a>';
                else
                 echo '&nbsp;';
                echo '  </span>
                       </td>';
            }

            if ( isset( $_POST['label'] ) ) {
                echo '<td>';
                if ( isset( $Result['label'] ) )
                  echo $Result['label'];
                else
                 echo '&nbsp;';
                echo '</td>';
            }

            if ( isset( $_POST['type'] ) ) {
            	echo '<td>';
            	if ( isset( $Result['objtype_id'] ) )
            		echo $phys_typelist[$Result['objtype_id']];
            	else
            		echo '&nbsp;';
            	echo '</td>';
            }

            if ( isset( $_POST['asset_no'] ) ) {
               echo '<td>';
               if ( isset( $Result['asset_no'] ) )
                 echo $Result['asset_no'];
               else
                echo '&nbsp;';
               echo '</td>';
            }

            if ( isset( $_POST['has_problems'] ) ) {
               echo '<td>';
               if ( isset( $Result['has_problems'] ) )
                 echo $Result['has_problems'];
               else
                echo '&nbsp;';
               echo '</td>';
            }

            if ( isset( $_POST['comment'] ) ) {
               echo '<td>';
               if ( isset( $Result['comment'] ) )
                 echo makeLinksInText($Result['comment']);
               else
                echo '&nbsp;';
               echo '</td>';
            }

            if ( isset( $_POST['runs8021Q'] ) ) {
               echo '<td>';
               if ( isset( $Result['runs8021Q'] ) )
                 echo $Result['runs8021Q'];
               else
                echo '&nbsp;';
               echo '</td>';
            }

            if ( isset( $_POST['location'] ) ) {
                echo '<td>';
                echo getLocation($Result);
                echo '</td>';
            }

            if ( isset( $_POST['MACs'] ) ) {
                echo '<td>';
                foreach ( getObjectPortsAndLinks($Result['id']) as $portNumber => $aPortDetails ) {
                    if ( trim($aPortDetails['l2address']) != '')
                       echo $aPortDetails['l2address'].'<br/>';
                }
                echo '</td>';
            }

            if ( isset( $_POST['IPs'] ) ) {
                echo '<td>';
                foreach ( getObjectIPv4AllocationList($Result['id']) as $key => $aDetails ) {
                    if ( function_exists('ip4_format') )
                	    $key = ip4_format($key);
                    if ( trim($key) != '')
                        echo $key . '<br/>';
                }
                foreach ( getObjectIPv6AllocationList($Result['id']) as $key => $aDetails ) {
                    if ( function_exists('ip6_format') )
                        $key = ip6_format($key);
                    else
                     $key = new IPv6Address($key);
                    if ( trim($key) != '')
                        echo $key . '<br/>';
                }
                echo '</td>';
            }

            if ( isset( $_POST['attributeIDs'] ) ) {
             $attributes = getAttrValues ($Result['id']);
                foreach ( $_POST['attributeIDs'] as $attributeID ) {
                    echo '<td>';
                    if ( isset( $attributes[$attributeID]['a_value'] ) && ($attributes[$attributeID]['a_value'] != '') )
                        echo $attributes[$attributeID]['a_value'];
                    elseif ( ($attributes[$attributeID]['value'] != '') && ( $attributes[$attributeID]['type'] == 'date' )   )
                        echo date("Y-m-d",$attributes[$attributeID]['value']);
                    else
                     echo '&nbsp;';

                }
            }

            if ( isset( $_POST['Tags'] ) ) {
                echo '<td>';
                foreach ( $Result['tags'] as $aTag )
                    echo '<a href="'. makeHref ( array( 'page' => 'depot', 'tab' => 'default', 'andor' => 'and', 'cft[]' => $aTag['id']) ) .'">'.$aTag['tag'].'</a> ';

                if ( count($Result['itags']) > 0 ) {
                    echo '(';
                    foreach ( $Result['itags'] as $aTag )
                        echo '<a href="'. makeHref ( array( 'page' => 'depot', 'tab' => 'default', 'andor' => 'and', 'cft[]' => $aTag['id']) ) .'">'.$aTag['tag'].'</a> ';

                    echo ')';
                }
                echo '</td>';
            }

            if ( isset ($_POST['Ports']) ) {
                echo '<td>';

                foreach ( $Result['portsLinks'] as $port ) {
                   echo $port['name'].': ';
                   if ( $port['remote_object_name'] != 'unknown' )
                       echo formatPortLink ($port['remote_object_id'], $port['remote_object_name'], $port['remote_id'], NULL);
                   else
                    echo $port['remote_object_name'];
                   if ( trim($port['cableid']) != '')
                       echo ' Cable ID: '.$port['cableid'];
                   echo '<br/>';
                }

                echo '</td>';
            }

            if ( isset ($_POST['Containers']) ) {
            	echo '<td>';

            	foreach ( getObjectContainerList($Result['id']) as $key => $aDetails ) {
            	    echo '<a href="'. makeHref ( array( 'page' => 'object', 'object_id' => $key) )  .'">'.$aDetails['container_name'].'</a><br/>';
            	}

            	echo '</td>';
            }

            if ( isset ($_POST['Childs']) ) {
            	echo '<td>';

            	foreach ( getObjectChildObjectList($Result['id']) as $key => $aDetails ) {
            	    echo '<a href="'. makeHref ( array( 'page' => 'object', 'object_id' => $key) )  .'">'.$aDetails['object_name'].'</a><br/>';
            	}

            	echo '</td>';
            }

            echo '</tr>';

        }

        echo '  </tbody>
              </table>
              <script type="text/javascript">$(".searchForm").hide();</script>';
     }
     else {
        echo '<br/><br/><div align="center" style="font-size:10pt;"><i>No items found !!!</i></div><br/>';
     }

     echo '<script type="text/javascript">
               $(document).ready(function()
                 {
                   $.tablesorter.defaults.widgets = ["zebra"];
                   $("#customTable").tablesorter(
                     { headers: {
                     }, sortList: [[0,0]] }
                   );
                   $("#customTable").tableFilter();

                   $(".show_hide").show();

                   $(".show_hide").click(function(){
                     $(".searchForm").slideToggle(\'slow\');
                   });

                 }
                 );
            </script>';
    }

}

/**
 * getResult Function
 *
 * Call Racktables API to get Objects and filter the result if required
 *
 * @param array $post
 * @return array Result
 */
function getResult ( $post ) {

	#Get available objects
	$phys_typelist = readChapter (CHAP_OBJTYPE, 'o');

	$rackObjectTypeID     = array_search('Rack', $phys_typelist);
	$rowObjectTypeID      = array_search('Row', $phys_typelist);
	$locationObjectTypeID = array_search('Location', $phys_typelist);

	$rackRealm     = false;
	$rowRealm      = false;
	$locationRealm = false;

    $sFilter = '';
    if ( isset ($post['objectIDs']) ) {
        foreach ( $post['objectIDs'] as $sFilterValue ) {
            $sFilter.='{$typeid_'.$sFilterValue.'} or ';

            if (($rackObjectTypeID) && ($sFilterValue == $rackObjectTypeID))
            	$rackRealm = true;
            if (($rowObjectTypeID) && ($sFilterValue == $rowObjectTypeID))
            		$rowRealm = true;
            if (($locationObjectTypeID) && ($sFilterValue == $locationObjectTypeID))
            	$locationRealm = true;

        }
        $sFilter=substr($sFilter, 0, -4);
        $sFilter = '('.$sFilter.')';
    }

    $aResult = scanRealmByText ( 'object', $sFilter );

    # Get other realms than objects if user selected them
    if ($rackRealm)
    	$aResult = array_merge($aResult, scanRealmByText ( 'rack') );
    if ($rowRealm)
    	$aResult = array_merge($aResult, scanRealmByText ( 'row') );
    if ($locationRealm)
    	$aResult = array_merge($aResult, scanRealmByText ( 'location') );

    // Add tags
    $aTemp = array();
    foreach ( $aResult as $Result) {
        $Result['tags']  = loadEntityTags( 'object', $Result['id'] );
        $Result['itags'] = getImplicitTags( $Result['tags'] );

        array_push($aTemp, $Result);
    }
    $aResult = $aTemp;

    // Search / Filter by name
    if ( isset ($post['name_preg']) && ($post['name_preg'] != '') ) {
        $aTemp = array();
        foreach ( $aResult as $Result ) {
             if ( preg_match ( '/'.$post['name_preg'].'/' , $Result['name']) )
                array_push($aTemp, $Result);
        }
        $aResult = $aTemp;
    }

    // Search / Filter by tag
    if ( isset ($post['tag_preg']) && ($post['tag_preg'] != '') ) {
        $aTemp = array();
        foreach ( $aResult as $Result ) {
            if ( preg_match ( '/'.$post['tag_preg'].'/' , $Result['asset_no']) )
                array_push($aTemp, $Result);
        }
        $aResult = $aTemp;
    }

    // Search / Filter by comment
    if ( isset ($post['comment_preg']) && ($post['comment_preg'] != '') ) {
        $aTemp = array();
        foreach ( $aResult as $Result ) {
            if ( preg_match ( '/'.$post['comment_preg'].'/' , $Result['comment']) )
                array_push($aTemp, $Result);
        }
        $aResult = $aTemp;
    }

    // Tags
    if ( (isset ($post['tag'])) && ( count($post['tag']) >0 ) ) {
        $aTemp = array();
        $aSearchTags = array_keys($post['tag']);

        foreach ( $aResult as $Result ) {

            foreach ( $Result['tags'] as $aTag ) {
               if ( in_array($aTag['id'], $aSearchTags) )
                   array_push($aTemp, $Result);
            }

            foreach ( $Result['itags'] as $aTag ) {
                if ( in_array($aTag['id'], $aSearchTags) )
                    array_push($aTemp, $Result);
            }

        }

        $aResult = $aTemp;
    }

    // Ports - Load port data if necessary
    if ( isset ($post['Ports']) ) {
        $aTemp = array();

        foreach ( $aResult as $Result ) {

            $Result['portsLinks'] = getObjectPortsAndLinks ($Result['id']);

            foreach ( $Result['portsLinks'] as $i => $port) {

                $Result['portsLinks'][$i]['remote_object_name'] = 'unknown';
                if (!is_null( $port['remote_object_id'] )){
                    $remote_object = spotEntity ('object', intval($port['remote_object_id']));
                    $Result['portsLinks'][$i]['remote_object_name'] = $remote_object['name'];
                }
            }

            array_push($aTemp, $Result);
        }

       $aResult = $aTemp;
    }

    return $aResult;
}

/**
 * validateColums Function
 *
 * If user doesn't select any column to display this function preselect the name column
 * to display the results
 *
 * @param array $_POST
 * @return bool display
 */
function validateColums($POST) {
    if (isset( $POST['sName'] ) )
        return true;

    if ( (!isset($POST['label'])) &&
         (!isset($POST['asset_no'])) &&
         (!isset($POST['has_problems'])) &&
         (!isset($POST['comment'])) &&
         (!isset($POST['runs8021Q'])) &&
         (!isset($POST['location'])) &&
         (!isset($POST['MACs'])) &&
         (!isset($POST['label'])) &&
      (!isset($POST['attributeIDs'])) ) {
     return true;
    }

}
