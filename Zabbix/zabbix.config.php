<?php

###########################################################################
########################## Configuration section ##########################
###########################################################################

### Main Parameters ###

$zabbix_user = 'admin'; // User for access to Zabbix frontend. User must have read permissions at least.
$zabbix_password = 'password'; // Password for selected user.
$zabbix_url = 'http://example.org'; // You must specify only your domain name or IP address with http(s) prefix! Example: http://example.org

### End of Main Parameters ###

### Zabbix triggers and graphs parametres ###

$general_info_enabled = 'yes'; // Display general info such as status of agent. Default value is 'yes'
$triggers_enabled = 'yes'; // Display a list of triggers. Default value is 'yes'
$graphs_enabled = 'no'; // Default value is 'no'. This is experimental feature.
$graphs_period = 86400; // Graph period in seconds, default value is 86400 = 1 day.

### End of Zabbix triggers and graphs parametres ###

?>
