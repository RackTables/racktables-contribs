# racktables-reports

This is an additional plugin for RackTables, that creates Custom Reports Tabs right in the Reports page. It's based on the work of  

Sebastian Mogilowski <sebastian@mogilowski.net> (Website and HowTo: http://www.mogilowski.net/projects/racktables )

The original plugin was rewritten to follow the new [plugin standards](https://wiki.racktables.org/index.php/Plugins) for RackTables.

## Other changes for version 0.4.0:
* Unify Server, Switch and Virtual Machine Reports (maintaining only one function for all)
* allow users to customize the path to the CSS and JS files via config variables ('Configuration' => 'User interface')
* always show: Name, MAC(s), IPv4 and IPv6 addresses, comment and contact information
* additionally show:
  * Type, asset no., location, OEM S/N, HW expire date and OS for servers
  * Type, asset no., location, OEM S/N, HW expire date and OS version for switches
  * OS and Hypervisor (container) for virtual machines
* show MAC addresses also for switches (configurable via config variable 'REPORTS_SHOW_MAC_FOR_SWITCHES')
* enhance CSV filename with the short form of the export (server,switches,vm or custom => export_server_$date.csv)
* create 'mailto' links, if the contact is an Email address

## INSTALL

1) Copy the files in the /reports/ folder to your RackTables plugins installation ( _/path/to/racktables/plugins/_ ).

2) Copy the CSS and JS and image files to the corresponding folders:
```
   mkdir -p '/path/to/racktables/wwwroot/{css,js,pix}/report/'
   cp -v 'css/style.css' '/path/to/racktables/wwwroot/css/report/style.css'
   cp -v "js/*" '/path/to/racktables/wwwroot/js/report/'
   cp -v "pix/*" '/path/to/racktables/wwwroot/pix/report/'
```

3) Activate the plugin via the _Configuration_ => _Plugins_ menu.

4) Depending on where you copied the CSS and JS files in step 2, you might want to adjust the configuration variables in _'Configuration'_ => _'User interface'_:
```
   REPORTS_CSS_PATH => defaults to 'css/report' (NO trailing slashes)
   REPORTS_JS_PATH  => defaults to 'js/report'  (NO trailing slashes)
```

5) You might also want to enable/disable the MAC(s) column for your switch report via _'Configuration'_ => _'User interface'_:
```
   REPORTS_SHOW_MAC_FOR_SWITCHES => defaults to 'yes'
```

## USAGE

Login into RackTables and go to "Reports".

Now you find "Custom", "Server", "Switches" and "Virtual machines" in the report menu.

Sort multiple columns simultaneously by holding down the shift key and clicking a second, third or even fourth column header! 

Save your custom report by supplying a name in the "Save:" field and click on the "Ok" button. Restoring your custom report criteria: simply click on the link in the form with the name you provided. 

