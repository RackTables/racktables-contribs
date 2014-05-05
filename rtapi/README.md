rtapi
=====

Racktables API

Python module for accessing and manipulating racktables objects.

Dependency
------------------

* ipaddr module - https://code.google.com/p/ipaddr-py/

This module must be installed along with rtapi.

Installation
----------------
Simply copy rtapi and ipaddr into your projects folder or in place from where your project importing python modules.


Class manual
--------------------
    NAME
       rtapi

    FILE
        /home/wire/Projects/rtapi/__init__.py
    
    DESCRIPTION
        PyRacktables
        Simple python Class for manipulation with objects in racktables database. 
    
        For proper function, some methods need ipaddr module (https://pypi.python.org/pypi/ipaddr)

    PACKAGE CONTENTS


    CLASSES
        RTObject
    
    class RTObject
     |  Ractables object. Require database object as argument.
     |  
     |  Methods defined here:
     |  
     |  AddObject(self, name, server_type_id, asset_no, label)
     |      Add new object to racktables
     |  
     |  AssignChassisSlot(chassis_name, slot_number, server_name)
     |      Assign server objects to server chassis
     |  
     |  CleanIPAddresses(self, object_id, ip_addresses, device)
     |      Clean unused ip from object. ip addresses is list of IP addresses configured on device (device) on host (object_id)
     |  
     |  CleanIPv6Addresses(self, object_id, ip_addresses, device)
     |      Clean unused ipv6 from object. ip_addresses mus be list of active IP addresses on device (device) on host (object_id)
     |  
     |  CleanVirtuals(self, object_id, virtual_servers)
     |      Clean dead virtuals from hypervisor. virtual_servers is list of active virtual servers on hypervisor (object_id)
     |  
     |  GetAllServerChassisId(self)
     |      Get list of all server chassis IDs
     |  
     |  GetAttributeId(self, searchstring)
     |      Search racktables database and get attribud id based on search string as argument
     |  
     |  GetDictionaryId(self, searchstring)
     |      Search racktables dictionary using searchstring and return id of dictionary element
     |  
     |  GetInterfaceId(self, object_id, interface)
     |      Find id of specified interface
     |  
     |  GetInterfaceName(self, object_id, interface_id)
     |      Find name of specified interface. Required object_id and interface_id argument
     |  
     |  GetObjectComment(self, object_id)
     |      Get object comment
     |  
     |  GetObjectId(self, name)
     |      Translate Object name to object id
     |  
     |  GetObjectLabel(self, object_id)
     |      Get object label
     |  
     |  GetObjectName(self, object_id)
     |      Translate Object ID to Object Name
     |  
     |  InsertAttribute(self, object_id, object_tid, attr_id, string_value, uint_value, name)
     |      Add or Update object attribute. 
     |      Require 6 arguments: object_id, object_tid, attr_id, string_value, uint_value, name
     |  
     |  InsertLog(object_id, message)
     |      Attach log message to specific object
     |  
     |  InterfaceAddIpv4IP(self, object_id, device, ip)
     |      Add/Update IPv4 IP on interface
     |  
     |  InterfaceAddIpv6IP(self, object_id, device, ip)
     |      Add/Update IPv6 IP on interface
     |  
     |  LinkNetworkInterface(self, object_id, interface, switch_name, interface_switch)
     |      Link two devices togetger
     |  
     |  LinkVirtualHypervisor(self, object_id, virtual_id)
     |      Assign virtual server to correct hypervisor
     |  
     |  ListObjects(self)
     |      List all objects
     |  
     |  ObjectExistName(self, name)
     |      Check if object exist in database based on name
     |  
     |  ObjectExistST(self, service_tag)
     |      Check if object exist in database based on asset_no
     |  
     |  ObjectExistSTName(self, name, asset_no)
     |      Check if object exist in database based on name
     |  
     |  UpdateNetworkInterface(self, object_id, interface)
     |      Add network interfece to object if not exist
     |  
     |  UpdateObjectComment(self, object_id, comment)
     |      Update comment on object
     |  
     |  UpdateObjectLabel(self, object_id, label)
     |      Update label on object
     |  
     |  UpdateObjectName(self, object_id, name)
     |      Update name on object
     |  
     |  __init__(self, dbobject)
     |      Initialize Object
     |  
     |  db_fetch_lastid(self)
     |      SQL function which return ID of last inserted row.
     |  
     |  db_insert(self, sql)
     |      SQL insert/update function. Require sql query as parameter
     |  
     |  db_query_all(self, sql)
     |      SQL query function, return all rows. Require sql query as parameter
     |  
     |  db_query_one(self, sql)
     |      SQL query function, return one row. Require sql query as parameter

    DATA
        __all__ = ['RTObject']
        __author__ = 'Robert Vojcik (robert@vojcik.net)'
        __copyright__ = 'OpenSource'
        __license__ = 'GPLv2'
        __version__ = '0.0.1'

    VERSION
        0.0.1

    AUTHOR
        Robert Vojcik (robert@vojcik.net)

Example
-------


    import ipaddr
    import MySQLdb
    import rtapi

    # Create connection to database
    try:
        # Create connection to database
        db = MySQLdb.connect(host='hostname',port=3306, passwd='mypass',db='racktables',user='racktables)
    except MySQLdb.Error ,e:
        print "Error %d: %s" % (e.args[0],e.args[1])
        sys.exit(1)

    # Initialize rtapi with database connection
    rt = rtapi.RTObject(db)

    # List all objects from database
    print rt.ListObjects()


