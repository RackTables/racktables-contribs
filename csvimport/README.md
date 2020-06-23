# CSVIMPORT
## Description
This is a Racktables plugin which enables loading batches of data into racktables.<br/>
It adds an CSV import page on the configuration menu screen.<br/>
From here you can choose to either import a CSV file, or paste some manual CSV lines in a textbox.<br/>
CSV files can contain multiple types of import data.<br/>
The script currently supports importing of Objects, Racks, VLANs and IP space.<br/>
It also supports linking ports, and assigning rackspace to objects.<br/>
<br/>
The newest version of this plugin can be found at: https://github.com/RackTables/racktables-contribs<br/>
<br/>
## CREDITS
First version by <br/>
Copyright (c) 2014, Erik Ruiter, SURFsara BV, Amsterdam, The Netherlands<br/>
All rights reserved.<br/>
<br/>
Updated in June 2020 by matt32106@github <br/>
- compatibility with Racktables version 0.21 new plugin format<br/>
- integration of the TAG import by (c)2012-2014 Maik Ehinger <github138@t-online.de><br/>
<br/>
## HOW TO USE
Go to the Configuration / ImportCSV menu. <br/>
You can either copy / paste line formatted as below or use a properly formatted csv file.<br/>
Best practice: test a few records at first <br/>
1/ to make sure the format is ok (the list separator format depends on the regional settings of the OS)<br/>
2/ to make sure it updates the values you want before committing to hundreds of changes (there is NO UNDO feature yet)<br/>
3/ if you make big changes, make sure to backup first <br/>
<br/>
### Importing Objects:
 Syntax: OBJECT; Objecttype ; Common name ; Visible label ; Asset tag; portname,portname,etc ; porttype,porttype,etc<br/>
<br/>
 Value 1, OBJECT<br/>
 Value 2, Objectype: Can be one of the predefined types (SERVER, PATCHPANEL, SWITCH, VM), or a numeric value indicating the object type from Racktables<br/>
 Value 3, Common name: Common name string<br/>
 Value 4, Visible label: Visible label string<br/>
 Value 5, Asset tag: Asset tag string<br/>
 Value 6, Port array: This is an optional field where you can create ports for the objects, separated by a comma. When you use this , you also need to add the Port type array<br/>
 An individual port field can be a range of ports. Eg. 'eth[0-9]' creates ten ports ranging from eth0 to eth9.<br/>
 Value 7, Port type array: This is an array which maps to the previous port array. This allows you to specify the interface type of the ports. It takes the form 'a-b'. Where a is the inner interface type, and b the outer interface type. Both a and b are numeric values.<br/>
 New inner / outer interface pair types can be linked using the configuration -> Enabled port types page. When the 'a-' value is ommited, the inner port type defaults to 'hardwire'.<br/>
 Examples:<br/>
<br/>
 OBJECT;SERVER;myServer;www.server.com;SRV001;IPMI,eth[0-2];1-24,3-24<br/>
 Creates a Server object named myServer having 4x 1000-Base-T interfaces, named IPMI (hardwired inner iface, 1gbps), eth0, eth1 and eth2 (gbic inner iface, 1gbps)<br/>
<br/>
 OBJECT;SWITCH;myAccessSwitch1;testswitch;SW0001;ge-0/0/[0-11],fe-0/1/[0-11];24,19<br/>
 Creates a Switch object named myAccessSwitch1 having 12x 1000-Base-T interfaces named ge-0/0/0 to ge-0/0/11. And also 12x 100-Base-TX interfaces named fe-0/1/0 to fe-0/1/11.<br/>
<br/>
<br/>
### Importing Racks
 Syntax: RACK; Location ; Location child ; Row ; Rack; Height<br/>
 Value 1, RACK<br/>
 Value 2, Location ; Specifies the location where the rack is to be placed. This can be the location name string of an existing location. If the location does not exist, it will be created.<br/>
 Value 3, Location child ; Specifies the child location (eg room) where the rack is to be placed. This can be the name of an existing location. If the location does not exist, it will be created.<br/>
 Value 4, Row: Specifies the row where the rack is to be placed. This can be the name of an existing row. If the row does not  exist, it will be created<br/>
 Value 5, Rack: Name of the rack that is to be created.<br/>
 Value 6, Height: Sets the Height of the rack in rackunits. When omitted, the default value is 46 units.<br/>
<br/>
 Examples:<br/>
<br/>
 RACK;Datacenter AMS01; Room 0.08; R01; AA-1<br/>
 Creates a rack named AA-1 in Room 0.08 of location Datacenter AMS01, with a height of 46 units.<br/>
<br/>
### Assigning Rackspace
 Syntax: RACKASSIGNMENT; Object name ;Rack; units ; fib<br/>
 Value 1, RACKASSIGNMENT<br/>
 Value 2, Object name: Name of the Racktables object<br/>
 Value 3, Rack; NAme of the rack where the object is to be placed.<br/>
 Value 4, units: List of units to be assigned to the object. The unit numbers are separated by a comma. 0 for Zero-U.<br/>
 Value 5, fib: List of Front / Interior / Back indication. This list maps directly to the previous unit list.<br/>
<br/>
 Examples:<br/>
<br/>
 RACKASSIGNMENT;myServer;AA-1;32,33,34,35;fi,fi,fi,fi<br/>
 Mounts the myServer object in Rack AA-1 on rackunits 32-35, using front and interior part of the rack units.<br/>
<br/>
### Linking ports
 Syntax:CABLELINK; Objectname A; Portname A; Objectname B; Portname B; Cable ID<br/>
 Value 1, CABLELINK<br/>
 Value 2, Objectname A: Specifies the name of the object on the A-side of the link<br/>
 Value 3, Portname A: Specifies the name of the port on the A-side of the link<br/>
 Value 4, Objectname B: Specifies the name of the object on the B-side of the link<br/>
 Value 5, Portname B: Specifies the name of the port on the B-side of the link<br/>
 Value 6, Cable ID: Specifies the Cable ID. It can be numeric or string.<br/>
<br/>
 Examples:<br/>
 CABLELINK;myServer;eth1;myAccessSwitch1;ge-0/0/1;0080123<br/>
 Connects the eth1 port of myServer to the ge-0/0/1 port of myAccessSwitch1, using cable ID 0080123<br/>
<br/>
### Importing VLANs
 Syntax: VLAN; VLAN domain; VLAN name; VLAN ID ; Propagation; Attached IP<br/>
 Value 1, VLAN<br/>
 Value 2, VLAN domain: Specifies the name of the VLAN domain where the VLAN is to be added. If the domain does not exist, it will be created.<br/>
 Value 3, VLAN name: Specifies the name of the to be created VLAN.<br/>
 Value 4, Propagation: Sets the Racktables propagation feature for the VLAN, options are ondemand or compulsory. When ommitted the value defaults to compulsory.<br/>
 Value 5, Attached IP: This is an optional list of existing IPv4/IPv6 networks which can be assigned to the VLAN. The ranges should not have netmasks, and each range is separated by a comma.<br/>
<br/>
 Examples:<br/>
 VLAN;Private;Netops;1020;compulsory;10.1.3.0,2001:610:1020::0<br/>
 Creates VLAN 1020, named Netops having the IPv4 range 10.1.3.0 and the IPv6 range 2001:610:1020::0 attached.<br/>
<br/>
### Importing IP space
 Syntax: IP; Prefix; Name; is_connected; VLAN domain; VLAN ID<br/>
 Value 1, IP<br/>
 Value 2, Prefix: Specifies the IPv4 / IPv6 prefix of the network, including netmask.<br/>
 Value 3, Name: Specifies the name of the network which is to be added.<br/>
 Value 4, is_connected: Specifies if broadcast and network address in the subnet need to be reserved. Can be TRUE or FALSE. When omitted, the default is FALSE<br/>
 Value 5, VLAN domain: This is an optional value which can be used to set the VLAN domain of the network. You have to specifiy the name of the VLAN domain.<br/>
 Value 6, VLAN ID: This is an optional numeric value setting the VLAN ID of the network. It is to be used in conjunction with the previous VLAN domain value.<br/>
<br/>
 Examples:<br/>
 IP;10.1.3.0/24;Netops network;TRUE;SURFsara;1020<br/>
 Creates the IP network 10.1.3.0/24 called 'Netops network' and attaches it to VLAN 1020 in the SURFsara VLAN domain.<br/>
<br/>
### Importing Object IP interfaces
 Syntax: OBJECTIP; Objectname; OS Interface name; IP address; Type<br/>
 Value 1, OBJECTIP<br/>
 Value 2, Objectname: Specifies the name of the object<br/>
 Value 3, OS Interface name: Specifies the name of the interface to be added<br/>
 Value 4, IP address: Specifies the ip address of the interface to b e added (IPv4 or Ipv6) no subnet mask required<br/>
 Value 5, Type: Chooses the type of interface to be added. Can be: regular, virtual, shared, router, point2point. The default type is: router<br/>
<br/>
 Examples:<br/>
<br/>
 OBJECTIP;myRouter;eth0;10.1.3.1;regular<br/>
 Creates an IP interface name eth0, with address 10.1.3.1 and type 'regular', which is added to the myRouter object.<br/>
<br/>
### Setting Object Attributes:
  Syntax: OBJECTATTRIBUTE;Objectname;attribute id;attribute value<br/>
  Value 1, OBJECTATTRIBUTE<br/>
  Value 2, Objectname: Specifies the name of the object<br/>
  Value 3, attribute id: Specifies the numeric ID of the attribute (can be looked up in Attribute table), also some general attributes are supported, in this case use: NAME / LABEL / ASSETTAG / HASPROBLEMS (yes|no) / COMMENT<br/>
  Value 4, attribute value; Specificies the value to be set for the attribute<br/>
<br/>
  Examples:<br/>
  OBJECTATTRIBUTE;myRouter;3;mgmt.myrouter.com<br/>
  Sets the FQDN (3)  for the myRouter object.<br/>
<br/>
  OBJECTATTRIBUTE;myRouter;COMMENT;This is a comment<br/>
  Sets the comment field for the myRouter object.<br/>
<br/>
### Creating Container Link:
  Syntax: CONTAINERLINK;Parent Object Name;Child Object Name<br/>
  Value 1, CONTAINERLINK<br/>
  Value 2, Parent Object Name : Specify the name of the Parent Object (eg. Hypervisor Server)<br/>
  Value 3, Child Object Name : Specify the name of the Child Object (eg. VM)<br/>
<br/>
  Examples:<br/>
  CONTAINERLINK;ESX_Host1;VM_1<br/>
  Adds VM_1 as a member of the Container ESX_Host1<br/>
<br/>
### Adding Tags to objects, networks, racks
  Syntax: TAG;Realm;Name;Tag Names<br/>
  Value 1, TAG<br/>
  Value 2, Realm: `object` or `rack` or `ipv4net` (**all lower case!**)<br/>
  Value 3, Name : Specify the name of the object/rack/network to add the tag to(eg. Server1 )<br/>
  Value 4, Tag Names : Specify the name of the Tags (eg. VM) separated by commas<br/>
<br/>
  Examples:<br/>
  TAG;object;Server1;Tag1,Tag2<br/>
  Adds the tag called Tag1 and Tag2 to server object called Server1<br/>
  TAG;ipv4net;192.168.1.0;Tag1,Tag2<br/>
  Adds the tag called Tag1 and Tag2 to ipv4net object called 192.168.1.0<br/>
  TAG;ipv4net;10.10.10.0/26;Tag1,Tag2<br/>
  Adds the tag called Tag1 and Tag2 to ipv4net network 10.10.10.0/26<br/>
<br/>
  Old Syntax: OBJECTTAG; Object Name;Tag Name  (new TAG syntax above is prefered but this one still works)<br/>
  Value 1, OBJECTTAG<br/>
  Value 2, Object Name : Specify the name of the Object to add the tag to(eg. Server)<br/>
  Value 3, Tag Name : Specify the name of the Tag (eg. VM)<br/>
<br/>
  Examples:<br/>
  OBJECTTAG;Server1;Tag1<br/>
  Adds the tag called Tag1 to server object called Server1<br/>
<br/>
### Update an IP network
  Syntax: UPDATEIP;IP Address; Name; Reserved;Comment<br/>
  Value 1: UPDATEIP<br/>
  Value 2: IP Address<br/>
  Value 3: Name<br/>
  Value 4: Reseverd: yes or no<br/>
  Value 5: Comment<br/>
<br/>
  Examples:<br/>
  UPDATEIP;192.168.1.2;Test Address;no;Testing only<br/>
  Updates IP 192.168.1.2 with Name Test, Reserved no and Comment "Testing only"<br/>
