# Racktables custom report plugin                                                                                                                                          
This is an additional plugin for RackTables, based on the work of 
Mogilowski Sebastian <sebastian@mogilowski.net>


## Installation

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

----

## FAQ

Q: How to find the right typeid of my devices?

A: Visit 

   Main page -> Configuration -> Dictionary -> Chapter 'ObjectType'
 
and note down the value in the "Key" column.


