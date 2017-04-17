<?php
// load ZabbixApi
require_once 'ZabbixApi.class.php';
require_once 'zabbix.config.php';
use ZabbixApi\ZabbixApi;
$tab['object']['Zabbix'] = 'Zabbix';
$tabhandler['object']['Zabbix'] = 'ZabbixTabHandler';

function ApplyCSSforZabbixPage() {
        echo '
                <style type="text/css">
                .table-triggers {width: 100%;}
                .header-description, .header-severity, .header-change, .header-status {background-color: #999797; margin: 10px 1px 1px 1px; width: 75%; padding: 10px 0; text-align: center;}
                .header-severity {width: 7%;}
                .header-change {width: 10%;}
                .information, .warning, .average, .high, .critical, .disaster {padding: 10px 0; margin: 1px; width: 7%; text-align: center;}
                .information {background-color: #d6f6ff;}
                .warning {background-color: #fff6a5;}
                .average {background-color: #ffb689;}
                .high {background-color: #ff9999;}
                .disaster {background-color: #ff3838;}
                .na {background-color: #ff3838;}
                .trigger_description, .last_change, .status_ok, .status_problem {padding: 10px 0; margin: 1px; text-align: center;}
                .trigger_description {background-color: #dbdbdb; width: 75%;}
                .last_change {background-color: #c4c2c2; width: 10%}
                .status_problem {background-color: #f83838; width: 7%;}
                .status_ok {background-color: #33ff00; width: 7%;}
                .portlet .ok {color: green;}
                .portlet .problem {color: red;}
                .portlet .unknown {color: orange;}
                </style>
             ';
}
function ZabbixTabHandler()
{

global $zabbix_url;
global $zabbix_user;
global $zabbix_password;
global $general_info_enabled;
global $triggers_enabled;
global $graphs_enabled;
global $graphs_period;

### API Settings ###
try
{
// connect to Zabbix API
    $api = new ZabbixApi(''.$zabbix_url.'/zabbix/api_jsonrpc.php', $zabbix_user, $zabbix_password);
}
catch(Exception $e)
{
// Exception in ZabbixApi catched
    echo $e->getMessage();
}
### End of API Settings ###
###########################################################################
########################## End of config section ##########################
###########################################################################
$attributes = spotEntity ('object', $_REQUEST['object_id']);
// Checking if host with selected name really exist
$hosts = $api->hostGet(array('search' => array('name' => $attributes['name'])));
if (empty($hosts)) {
        echo '<div style="margin: 10px;">Seems host doesn\'t exist in Zabbix or possibly there is object name mismatch between Zabbix and RackTables, check it.</div>';
}
// if host exist go to next section
else {
ApplyCSSforZabbixPage();

if ($general_info_enabled == 'yes') {
        $general_info = $api->hostGet(array('search' => array('name' => $attributes['name']),'output' => 'extend'));
        echo '<div class="portlet">';
        echo '<h2>General Info</h2>';
        if ($general_info[0]->status == 0) {
                echo '<h4>Host is <span class="ok">MONITORED</span> by Zabbix</h4>';
        }
        else {
                echo '<h4>Host is <span class="problem">NOT MONITORED</span> by Zabbix</h4>';
        }
        if ($general_info[0]->available == 1) {
        echo '<h4>Host is <span class="ok">MONITORED</span> by Zabbix Agent</h4>';
        }
        elseif ($general_info[0]->available == 2) {
                echo '<h4>Host is <span class="problem">NOT MONITORED</span> by Zabbix Agent</h4>';
        }
        else {
                echo '<h4>Zabbix Agent status on host is <span class="unknown">UNKNOWN</span></h4>';
        }
        if ($general_info[0]->ipmi_available == 1) {
        echo '<h4>Host is <span class="ok">MONITORED</span> by IPMI Agent</h4>';
        }
        elseif ($general_info[0]->ipmi_available == 2) {
                echo '<h4>Host is <span class="problem">NOT MONITORED</span> by IPMI Agent</h4>';
        }
        else {
                echo '<h4>IPMI Agent status on host is <span class="unknown">UNKNOWN</span></h4>';
        }
        if ($general_info[0]->jmx_available == 1) {
        echo '<h4>Host is <span class="ok">MONITORED</span> by JMX Agent</h4>';
        }
        elseif ($general_info[0]->jmx_available == 2) {
                echo '<h4>Host is <span class="problem">NOT MONITORED</span> by JMX Agent</h4>';
        }
        else {
                echo '<h4>JMX Agent status on host is <span class="unknown">UNKNOWN</span></h4>';
        }
        if ($general_info[0]->snmp_available == 1) {
                echo '<h4>Host is <span class="ok">AVAILABLE</span> by SNMP</h4>';
        }
        elseif ($general_info[0]->snmp_available == 2) {
                echo '<h4>Host is <span class="problem">NOT AVAILABLE</span> by SNMP</h4>';
        }
        else {
                echo '<h4>SNMP status on host is <span class="unknown">UNKNOWN</span></h4>';
        }
        echo '</div>';
}
if ($triggers_enabled == 'yes') {
$triggers = $api->hostGet(array(
        'search' => array('name' => $attributes['name']),
        'selectTriggers' => array('priority','description','value','lastchange')
));
echo '<div class="portlet">';
echo '<h2>Triggers</h2>';
echo '<h4>Triggers total: '.count($triggers[0]->triggers).'</h4>';
$i = 0; $j = 0;
foreach ($triggers[0]->triggers as $element) {
        if ($element->value == 1) {$i++;} else {$j++;}
}
echo '<h4>Triggers in <span class="problem">PROBLEM</span> status: '.$i.'</h4>';
echo '<h4>Triggers in <span class="ok">OK</span> status: '.$j.'</h4>';
echo '<table class="table-triggers">';
echo '<tr>';
echo '<th class="header-severity">Severity</th>';
echo '<th class="header-description">Description</th>';
echo '<th class="header-change">Last Change</th>';
echo '<th class="header-status">Status</th>';
echo '</tr>';
foreach ($triggers[0]->triggers as $element) {
                echo '<tr>';
                if ($element->priority == 1) {
                        echo '<td class="information">Information</td>';
                }
                elseif ($element->priority == 2) {
                        echo '<td class="warning">Warning</td>';
                }
                elseif ($element->priority == 3) {
                        echo '<td class="average">Average</td>';
                }
                elseif ($element->priority == 4) {
                        echo '<td class="high">High</td>';
                }
                elseif ($element->priority == 5) {
                        echo '<td class="disaster">Disaster</td>';
                }
                else {
                        echo '<td class="na">N/A</td>';
                }
                echo '<td class="trigger_description">'.$element->description.'</td>';
                echo '<td class="last_change">'.date('d/m/Y h:i',$element->lastchange).'</td>';
                if ($element->value == 1) {
                echo '<td class="status_problem">PROBLEM</td>';
                }
                else {
                        echo '<td class="status_ok">OK</td>';
                }
                echo '</tr>';
        }
echo '</table>';
echo '</div>';
}
if ($graphs_enabled == 'yes') {
$graphs = $api->hostGet(array(
        'search' => array('name' => $attributes['name']),
        'selectGraphs' => array('graphids')
));
echo '<div class="portlet">';
echo '<h2>Graphs</h2>';
foreach ($graphs[0]->graphs as $graph) {
        echo '<img src="'.$zabbix_url.'/zabbix/chart2.php?graphid='.$graph->graphid.'&period='.$graphs_period.'">';
        echo '<br><br>';
}
echo '</div>';
                }
        }
}
?>
