# Intro
Zabbix plugin for RackTables offers a limited functional of Zabbix monitoring system for objects of RackTables environment.
Plugin uses open-source PHP class [library](https://github.com/confirm/PhpZabbixApi) to communicate with the Zabbix™ JSON-RPC API.  
Please feel free to observe demo screenshots in [Wiki](https://github.com/skilsara/zabbix-plugin-for-racktables/wiki) tab.

Author: Kirill Skilsara k.a.skilsara@protonmail.ch

# How to install plugin
1) Download a files "Zabbix.php" and "zabbix.config.php" from this repository to your server which runs RackTables.

2) Download a latest release of Zabbix PHP API [library](https://github.com/confirm/PhpZabbixApi/releases/latest) to the same server.

3) Unpack all archives, there are four files which you will be needed summarily:
- Zabbix.php
- zabbix.config.php
- ZabbixApiAbstract.class.php
- ZabbixApi.class.php

4) Place these files into "inc" directory inside root directory of RackTables.

5) Inside "inc" directory edit file "init.php"; in the head of this one you will see a bunch of "require_once" php files, so after last of them add this:

`require_once 'Zabbix.php';`

save changes, close the file.

6) Now we need specify parameters for accessing Zabbix API. Open file "zabbix.config.php" inside "inc" directory and find "Configuration section":

`###########################################################################`                        
`########################## Configuration section ##########################`                            
`###########################################################################`                            

`### Main Parameters ###`

`$zabbix_user = 'admin'; // User for access to Zabbix frontend. User must have read permissions at least.`
`$zabbix_password = 'password'; // Password for selected user.`                                                
`$zabbix_url = 'http://example.org'; // You must specify only your domain name or IP address with http(s) prefix! Example: http://example.org`

`### End of Main Parameters ###`

`### Zabbix triggers and graphs parametres ###`

`$general_info_enabled = 'yes'; // Display general info such as status of agent. Default value is 'yes'`
`$triggers_enabled = 'yes'; // Display a list of triggers. Default value is 'yes'`                             
`$graphs_enabled = 'no'; // Default value is 'no'. This is experimental feature.`                                    
`$graphs_period = 86400; // Graph period in seconds, default value is 86400 = 1 day.`

`### End of Zabbix triggers and graphs parametres ###`

You must specify username, password and URL for your Zabbix server inside brackets. This user must have read permissions at least. I would recommend to create special user which will have only read permissions and only to host groups that you allow.

Now if you will look at RackTables frontend - there is a new tab inside each object, called "Zabbix".

# Plugin usage
**WARNING!**

- Names of objects in RackTables environment and names of objects in Zabbix environment must be the **SAME**. At this moment plugin doesn't include any mechanism of syncing data or something like that.
- Tab with Zabbix info is a static page. If you need most recent data - refresh the page.

**General info**                                                                                                                                                                                                                      
Shows a global status of host (monitored\not monitored by zabbix) and statuses of agents (Zabbix Agent, JMX Agent, etc). Enabled by default.       

**Triggers**                                                                                                                      
Shows a total number of triggers linked to host, numbers of triggers in "OK" and "PROBLEM" statuses and detailed description of each trigger. Enabled by default.        

**Graphs**                                                                                                                      
Experimental feature. Gets **ALL** graphs linked to host (you must be authorized in Zabbix frontend to see them in RackTables). It's a quite heavy request, so make sure do you really need it. Disabled by default. Default time range for graphs - 1 day (you can change it).
