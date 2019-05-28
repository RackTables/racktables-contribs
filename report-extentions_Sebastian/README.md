Racktables Extensions v0.3.3
=====

# Author:
The original author for this extention is Mogilowski Sebastian <sebastian@mogilowski.net>

# History
| Date | Author | Description
|--- |--- |---
| 2019-05-28 | Michael Tiernan<br>Michael.Tiernan+RTe@gMail.com | Took a presumptuous leap and renamed the dir to make it clearer to others what's in the (previously 'extentions') directory.
| 2019-05-23 | Michael Tiernan<br>Michael.Tiernan+RTe@gMail.com | Created markdown version of doc.|
| 2016-02-04 | Mogilowski Sebastian<br>sebastian@mogilowski.net | Creation |

# Website and HowTo
http://www.mogilowski.net/projects/racktables

# Installation

To enable all additional reports, just move (as root) all the contents of the "plugins" folder into the racktables "plugins" folder, for example:

~~~sh
UrRTPath="/<yourRTpath>/plugins/"

cp -ir plugins/* /${UrRTPath}/plugins/
~~~

also you'll need to make sure of your permissions.

~~~sh
find /${UrRTPath}/plugins/ -type d -perm -0100 \( ! -perm -0010 -o ! -perm -0001 \) -print0 | xargs -0 -r chmod a+rx
find /${UrRTPath}/plugins/ -type f -perm -0400 \( ! -perm -0040 -o ! -perm -0004 \) -print0 | xargs -0 -r chmod a+r
~~~

on Red Hat systems with `selinux` enabled, you'll also need to do this:

~~~sh
chcon -R -t httpd_sys_content_t /${UrRTPath}/plugins/ 
~~~

# Usage

Login into racktables and go to "Reports".

Now you find "Server", "Virtual machines", "Switches" and "Custom" in the report menu.

To disable individual reports from this "Reports" page it is enough to remove one or more corresponding files from the `plugins` directory:

| Report | Plugin file
|---|---
| Custom | custom-report.php
| Server | server-report.php
| Switches | switch-report.php
| Virtual Machines | vm-report.php

[Question outstanding for MCT, where are the custom reports stored? I have not investigated but it should be documented.]

## Usage Feature/Note

Sort multiple columns simultaneously by holding down the shift key and clicking a second, third or even fourth column header! 
