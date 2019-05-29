racktables-stuff
================

# About

A small plugin for Racktables, written by Philipp Grau <phgrau@zedat.fu-berlin.de>
<br>*N.B.:* Only tested by the author on on versions 0.20.7, 0.20.8

20190528_124247 - Michael Tiernan <Michael.Tiernan+RTe@gMail.com> - Seems to fail under RT 0.20.14.
<br>Possibly programmed for older version of interface.

# History

| Version | Author | Description
|---|---|---|
| 0.6.2.1 | Michael Tiernan<br>Michael.Tiernan+RTe@gMail.com - 2019-05-28 | Took another presumptuous leap to modify the README.md in an effort to clarify.
| 0.6.2 | phgrau | Enable tag coloring and base on new renderRacks. Split Name, Rack & Zero-U into separate rows to improve alignment.
| 0.6.1 | phgrau | Wrap every X racks
| 0.6 | phgrau | Do not call deprecated functions.
| 0.5 | phgrau | Rearrange files for simpler installation.
| 0.4 | phgrau | Display rack row from left to right, no wrapping
| 0.3 | phgrau | Add patch for interface for rotated labels in layout style V (for bladecenters)
| 0.2 | phgrau | Removed call of markupObjectProblems(), function was removed in 0.20.5
| 0.1 | phgrau | Initial release (Tested on 0.20.4)
               
# Purpose

The purpose of this plugin is to render a web page with all racks of a row with clickable hostnames and display the first tag.
Currently five racks will be grouped in a row.

## Preview:
![Preview hosted on github](https://raw.githubusercontent.com/RackTables/racktables-contribs/master/full_row_view/full_row_view.png)

# Installation

Copy the extention file `full_row_view.php` to your RackTables plugin directory.

~~~sh
UrRTPath="/<yourRTpath>/plugins/"

cp -ir plugins/full_row_view.php /${UrRTPath}/plugins/
~~~

You can also apply a patch to the source file `/${UrRTPath}/wwwroot/inc/interface.php` to rotate labels in vertical boxes to accomodate blade systems and stuff.
<br>
_N.B.:_ This modification does not work with every browser, be warned.

# Use/Operation

Open an individual rack or row in your RackTables display and there should be a new tab "Full Row View"

* Copy the contents of the plugins/ directory to the RackTables plugins/ directory.
* you can apply a patch to wwwroot/inc/interface.php to rotate labels in vertical boxes (Blades and stuff)

Modified Version rotates the label for vertical layouts, does not work 
with every browser, be warned

* open a rack row in the racktables web frontend, there should be a tab "Full Row View", enjoy.

Feedback is welcome!
