#!/usr/bin/python
#
#   RTAPI
#   Racktables API is simple python module providing some methods 
#   for monipulation with racktables objects.
#
#   This utility is released under GPL v2
#   
#   Server Audit utility for Racktables Datacenter management project.
#   Copyright (C) 2012  Robert Vojcik (robert@vojcik.net)
#   
#   This program is free software; you can redistribute it and/or
#   modify it under the terms of the GNU General Public License
#   as published by the Free Software Foundation; either version 2
#   of the License, or (at your option) any later version.
#   
#   This program is distributed in the hope that it will be useful,
#   but WITHOUT ANY WARRANTY; without even the implied warranty of
#   MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
#   GNU General Public License for more details.
#   
#   You should have received a copy of the GNU General Public License
#   along with this program; if not, write to the Free Software
#   Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston, MA  02110-1301, USA.
#

'''PyRacktables
Simple python Class for manipulation with objects in racktables database. 

For proper function, some methods need ipaddr module (https://pypi.python.org/pypi/ipaddr)
'''
__author__ = "Robert Vojcik (robert@vojcik.net)"
__version__ = "0.1.2"
__copyright__ = "OpenSource"
__license__ = "GPLv2"

__all__ = ["RTObject"]


import re
import ipaddr


class RTObject:
    '''Ractables object. Require database object as argument. '''

    # Init method
    def __init__(self, dbobject):
        '''Initialize Object'''
        # Open configuration file
        self.db = dbobject
        self.dbresult = self.db.cursor()

    # DATABASE methods
    def db_query_one(self, sql):
        '''SQL query function, return one row. Require sql query as parameter'''
        self.dbresult.execute(sql)
        return self.dbresult.fetchone()

    def db_query_all(self, sql):
        '''SQL query function, return all rows. Require sql query as parameter'''
        self.dbresult.execute(sql)
        return self.dbresult.fetchall()
    
    def db_insert(self, sql):
        '''SQL insert/update function. Require sql query as parameter'''
        self.dbresult.execute(sql)
        self.db.commit()

    def db_fetch_lastid(self):
        '''SQL function which return ID of last inserted row.'''
        return self.dbresult.lastrowid
    
    def ListObjects(self):
        '''List all objects'''
        sql = 'SELECT name FROM Object'
        return "Found " + str(len(self.db_query_all(sql))) +" objects in database" 

    # Object methotds
    def ObjectExistST(self,service_tag):
        '''Check if object exist in database based on asset_no'''
        sql = 'SELECT name FROM Object WHERE asset_no = \''+service_tag+'\''
        if self.db_query_one(sql) == None:
            return False
        else:
            return True
    
    def ObjectExistName(self,name):
        '''Check if object exist in database based on name'''
        sql = 'select id from Object where name = \''+name+'\''
        if self.db_query_one(sql) == None:
            return False
        else:
            return True

    def ObjectExistSTName(self,name,asset_no):
        '''Check if object exist in database based on name'''
        sql = "SELECT id FROM Object WHERE name = '%s' AND asset_no = '%s'" % (name,asset_no)
        if self.db_query_one(sql) == None:
            return False
        else:
            return True

    def AddObject(self,name,server_type_id,asset_no,label):
        '''Add new object to racktables'''
        sql = "INSERT INTO Object (name,objtype_id,asset_no,label) VALUES ('%s',%d,'%s','%s')" % (name,server_type_id,asset_no,label)
        self.db_insert(sql)

    def UpdateObjectLabel(self,object_id,label):
        '''Update label on object'''
        sql = "UPDATE Object SET label = '%s' where id = %d" % (label, object_id)
        self.db_insert(sql)
    
    def UpdateObjectComment(self,object_id,comment):
        '''Update comment on object'''
        sql = "UPDATE Object SET comment = '%s' where id = %d" % (comment, object_id)
        self.db_insert(sql)

    def UpdateObjectName(self,object_id,name):
        '''Update name on object'''
        sql = "UPDATE Object SET name = '%s' where id = %d" % (name, object_id)
        self.db_insert(sql)

    def GetObjectName(self,object_id):
        '''Translate Object ID to Object Name'''
        #Get interface id
        sql = "SELECT name FROM Object WHERE id = %d" % (object_id)
        result = self.db_query_one(sql)
        if result != None:
            object_name = result[0]
        else:
            object_name = None

        return object_name
    
    def GetObjectLabel(self,object_id):
        '''Get object label'''
        #Get interface id
        sql = "SELECT label FROM Object WHERE id = %d" % (object_id)
        result = self.db_query_one(sql)
        if result != None:
            object_label = result[0]
        else:
            object_label = None

        return object_label

    def GetObjectComment(self,object_id):
        '''Get object comment'''
        #Get interface id
        sql = "SELECT comment FROM Object WHERE id = %d" % (object_id)
        result = self.db_query_one(sql)
        if result != None:
            object_comment = result[0]
        else:
            object_comment = None

        return object_comment

    def GetObjectId(self,name):
        '''Translate Object name to object id'''
        #Get interface id
        sql = "SELECT id FROM Object WHERE name = '%s'" % (name)
        result = self.db_query_one(sql)
        if result != None:
            object_id = result[0]
        else:
            object_id = None

        return object_id

    # Logging
    def InsertLog(self,object_id,message):
        '''Attach log message to specific object'''
        sql = "INSERT INTO ObjectLog (object_id,user,date,content) VALUES (%d,'script',now(),'%s')" % (object_id, message)
        self.db_insert(sql)

    # Attrubute methods
    def InsertAttribute(self,object_id,object_tid,attr_id,string_value,uint_value,name):
        '''Add or Update object attribute. 
        Require 6 arguments: object_id, object_tid, attr_id, string_value, uint_value, name'''
    
        # Check if attribute exist
        sql = "SELECT string_value,uint_value FROM AttributeValue WHERE object_id = %d AND object_tid = %d AND attr_id = %d" % (object_id, object_tid, attr_id)
        result = self.db_query_one(sql)

        if result != None:
            # Check if attribute value is same and determine attribute type
            old_string_value = result[0]
            old_uint_value = result[1]
            same_flag = "no"
            attribute_type = "None"

            if old_string_value != None:
                attribute_type = "string"
                old_value = old_string_value
                if old_string_value == string_value:
                    same_flag = "yes"
            elif old_uint_value != None:
                attribute_type = "uint"
                old_value = old_uint_value
                if old_uint_value == uint_value:
                    same_flag = "yes"

            # If exist, update value
            new_value = ''
            if same_flag == "no":
                if attribute_type == "string":
                    sql = "UPDATE AttributeValue SET string_value = '%s' WHERE object_id = %d AND attr_id = %d AND object_tid = %d" % (string_value, object_id, attr_id, object_tid)
                    new_value = string_value
                if attribute_type == "uint":
                    sql = "UPDATE AttributeValue SET uint_value = %d WHERE object_id = %d AND attr_id = %d AND object_tid = %d" % (uint_value, object_id, attr_id, object_tid)
                    new_value = uint_value

                self.db_insert(sql)

        else:
            # Attribute not exist, insert new
            if string_value == "NULL":
                sql = "INSERT INTO AttributeValue (object_id,object_tid,attr_id,uint_value) VALUES (%d,%d,%d,%d)" % (object_id,object_tid,attr_id,uint_value)
            else:
                sql = "INSERT INTO AttributeValue (object_id,object_tid,attr_id,string_value) VALUES (%d,%d,%d,'%s')" % (object_id,object_tid,attr_id,string_value)
            self.db_insert(sql)

    def GetAttributeId(self,searchstring):
        '''Search racktables database and get attribud id based on search string as argument'''
        sql = "SELECT id FROM Attribute WHERE name LIKE '%"+searchstring+"%'"
  
        result = self.db_query_one(sql)

        if result != None:
            getted_id = result[0]
        else:
            getted_id = None

        return getted_id

    # Interfaces methods
    def GetInterfaceName(self,object_id,interface_id):
        '''Find name of specified interface. Required object_id and interface_id argument'''
        #Get interface id
        sql = "SELECT name FROM Port WHERE object_id = %d AND name = %d" % (object_id, interface_id)
        result = self.db_query_one(sql)
        if result != None:
            port_name = result[0]
        else:
            port_name = None

        return port_name

    def GetInterfaceId(self,object_id,interface):
        '''Find id of specified interface'''
        #Get interface id
        sql = "SELECT id,name FROM Port WHERE object_id = %d AND name = '%s'" % (object_id, interface)
        result = self.db_query_one(sql)
        if result != None:
            port_id = result[0]
        else:
            port_id = None

        return port_id

    def UpdateNetworkInterface(self,object_id,interface):
        '''Add network interfece to object if not exist'''

        sql = "SELECT id,name FROM Port WHERE object_id = %d AND name = '%s'" % (object_id, interface)

        result = self.db_query_one(sql)
        if result == None:
        
            sql = "INSERT INTO Port (object_id,name,iif_id,type) VALUES (%d,'%s',1,24)" % (object_id,interface)
            self.db_insert(sql)
            port_id = self.db_fetch_lastid()

        else:
            port_id = result[0]


        return port_id

    def LinkNetworkInterface(self,object_id,interface,switch_name,interface_switch):
        '''Link two devices togetger'''
        #Get interface id
        port_id = self.GetInterfaceId(object_id,interface)
        if port_id != None:
            #Get switch object ID
            switch_object_id = self.GetObjectId(switch_name)
            if switch_object_id != None:
                switch_port_id = self.GetInterfaceId(switch_object_id,interface_switch)
                if switch_port_id != None:
                    if switch_port_id > port_id:
                        select_object = 'portb'
                    else:
                        select_object = 'porta'
                    sql = "SELECT %s FROM Link WHERE porta = %d OR portb = %d" % (select_object, port_id, port_id)
                    result = self.db_query_one(sql)
                    if result == None:
                        #Insert new connection
                        sql = "INSERT INTO Link (porta,portb) VALUES (%d,%d)" % (port_id, switch_port_id)
                        self.db_insert(sql)
                        resolution = True
                    else:
                        #Update old connection
                        old_switch_port_id = result[0]
                        if old_switch_port_id != switch_port_id:
                            sql = "UPDATE Link set portb = %d, porta = %d WHERE porta = %d OR portb = %d" % (switch_port_id,port_id, port_id, port_id)
                            self.db_insert(sql)
                            sql = "SELECT Port.name as port_name, Object.name as obj_name FROM Port INNER JOIN Object ON Port.object_id = Object.id WHERE Port.id = %d" % old_switch_port_id
                            result = self.db_query_one(sql)
                            old_switch_port, old_device_link = result

                            text = "Changed link from %s -> %s" % (old_device_link,old_switch_port)
                            self.InsertLog(object_id,text)
                            resolution = True
                        resolution = None

                else:
                    resolution = None
            else:
                resolution = None

        else:
            resolution = None

        return resolution

    def InterfaceAddIpv4IP(self,object_id,device,ip):
        '''Add/Update IPv4 IP on interface'''

        sql = "SELECT INET_NTOA(ip) from IPv4Allocation WHERE object_id = %d AND name = '%s'" % (object_id,device)
        result = self.db_query_all(sql)

        if result != None:
            old_ips = result
        
        is_there = "no"
            
        for old_ip in old_ips:
            if old_ip[0] == ip:
                is_there = "yes"

        if is_there == "no":
            sql = "INSERT INTO IPv4Allocation (object_id,ip,name) VALUES (%d,INET_ATON('%s'),'%s')" % (object_id,ip,device)
            self.db_insert(sql)
            text = "Added IP %s on %s" % (ip,device)
            self.InsertLog(object_id,text)

    def InterfaceAddIpv6IP(self,object_id,device,ip):
        '''Add/Update IPv6 IP on interface'''
        #Create address object using ipaddr 
        addr6 = ipaddr.IPAddress(ip)
        #Create IPv6 format for Mysql
        ip6 = "".join(str(x) for x in addr6.exploded.split(':'))

        sql = "SELECT HEX(ip) FROM IPv6Allocation WHERE object_id = %d AND name = '%s'" % (object_id, device)
        result = self.db_query_all(sql)
        
        if result != None:
            old_ips = result

        is_there = "no"

        for old_ip in old_ips:
            if old_ip[0] != ip6:
                is_there = "yes"

        if is_there == "no":
            sql = "INSERT INTO IPv6Allocation (object_id,ip,name) VALUES (%d,UNHEX('%s'),'%s')" % (object_id,ip6,device)
            self.db_insert(sql)
            text = "Added IPv6 IP %s on %s" % (ip,device)
            self.InsertLog(object_id,text)


    
    def GetDictionaryId(self,searchstring):
        '''Search racktables dictionary using searchstring and return id of dictionary element'''
        sql = "SELECT dict_key FROM Dictionary WHERE dict_value LIKE '%"+searchstring+"%'"

        result = self.db_query_one(sql)
        if result != None:
            getted_id = result[0]
        else:
            getted_id = None

        return getted_id

    def CleanVirtuals(self,object_id,virtual_servers):
        '''Clean dead virtuals from hypervisor. virtual_servers is list of active virtual servers on hypervisor (object_id)'''

        sql = "SELECT child_entity_id FROM EntityLink WHERE parent_entity_id = %d" % object_id

        result = self.db_query_all(sql)

        if result != None:
            old_virtuals_ids = result
            delete_virtual_id = []
            new_virtuals_ids = []
            # Translate names into ids
            for new_virt in virtual_servers:
                new_id = self.GetObjectId(new_virt)
                if new_id != None:
                    new_virtuals_ids.append(new_id)

            for old_id in old_virtuals_ids:
                try:
                    test = new_virtuals_ids.index(old_id[0])
                except ValueError:
                    delete_virtual_id.append(old_id[0]) 
        if len(delete_virtual_id) != 0:
            for virt_id in delete_virtual_id:

                sql = "DELETE FROM EntityLink WHERE parent_entity_id = %d AND child_entity_id = %d" % (object_id,virt_id)
                self.db_insert(sql)
                virt_name = self.GetObjectName(virt_id)
                logstring = "Removed virtual %s" % virt_name
                self.InsertLog(object_id,logstring)

    def CleanIPAddresses(self,object_id,ip_addresses,device):
        '''Clean unused ip from object. ip addresses is list of IP addresses configured on device (device) on host (object_id)'''

        sql = "SELECT INET_NTOA(ip) FROM IPv4Allocation WHERE object_id = %d AND name = '%s'" % (object_id, device)
        
        result = self.db_query_all(sql)

        if result != None:
            old_ips = result
            delete_ips = []

            for old_ip in old_ips:
                try:
                    test = ip_addresses.index(old_ip[0])
                except ValueError:
                    delete_ips.append(old_ip[0]) 
        if len(delete_ips) != 0:
            for ip in delete_ips:
                sql = "DELETE FROM IPv4Allocation WHERE ip = INET_ATON('%s') AND object_id = %d AND name = '%s'" % (ip,object_id,device)
                self.db_insert(sql)
                logstring = "Removed IP %s from %s" % (ip,device)
                self.InsertLog(object_id,logstring)

    def CleanIPv6Addresses(self,object_id,ip_addresses,device):
        '''Clean unused ipv6 from object. ip_addresses mus be list of active IP addresses on device (device) on host (object_id)'''

        sql = "SELECT HEX(ip) FROM IPv6Allocation WHERE object_id = %d AND name = '%s'" % (object_id,device)
        result = self.db_query_all(sql)

        if result != None:
            old_ips = result
            delete_ips = []
            new_ip6_ips = []

            #We must prepare ipv6 addresses into same format for compare
            for new_ip in ip_addresses:
                converted = ipaddr.IPAddress(new_ip).exploded.lower()
                new_ip6_ips.append(converted)


            for old_ip_hex in old_ips:
                try:
                    #First we must construct IP from HEX
                    tmp = re.sub("(.{4})","\\1:", old_ip_hex[0], re.DOTALL)
                    #Remove last : and lower string
                    old_ip = tmp[:len(tmp)-1].lower()

                    test = new_ip6_ips.index(old_ip)

                except ValueError:
                    delete_ips.append(old_ip)

        if len(delete_ips) != 0:
            for ip in delete_ips:
                db_ip6_format = "".join(str(x) for x in ip.split(':')) 
                sql = "DELETE FROM IPv6Allocation WHERE ip = UNHEX('%s') AND object_id = %d AND name = '%s'" % (db_ip6_format,object_id,device)
                self.db_insert(sql)
                logstring = "Removed IP %s from %s" % (ip,device)
                self.InsertLog(object_id,logstring)

    def LinkVirtualHypervisor(self,object_id,virtual_id):
        '''Assign virtual server to correct hypervisor'''
        sql = "SELECT child_entity_id FROM EntityLink WHERE parent_entity_id = %d AND child_entity_id = %d" % (object_id,virtual_id)
        result = self.db_query_one(sql)

        if result == None:
            sql = "INSERT INTO EntityLink (parent_entity_type, parent_entity_id, child_entity_type, child_entity_id) VALUES ('object',%d,'object',%d)" % (object_id, virtual_id)
            self.db_insert(sql)
            text = "Linked virtual %s with hypervisor" % self.GetObjectName(virtual_id)
            self.InsertLog(object_id,text)

    def AssignChassisSlot(self,chassis_name,slot_number,server_name):
        '''Assign server objects to server chassis'''
        chassis_id = self.GetObjectId(chassis_name)
        server_id = self.GetObjectId(server_name)
        slot_attribute_id = self.GetAttributeId("Slot number")

        # Assign slot number to server
        sql = "INSERT INTO AttributeValue (object_id,object_tid,attr_id,string_value) VALUES ( %d, 4, %d, '%s')" % ( server_id, slot_attribute_id, slot_number)
        try:
            self.db_insert(sql)
        except:
            pass

        # Assign server to chassis
        # Check if it's connected
        sql = "SELECT parent_entity_id FROM EntityLink WHERE child_entity_type = 'object' AND child_entity_id = %d" % (server_id)
        result = self.db_query_one(sql)

        if result != None:
        # Object is connected to someone
            if result[0] != chassis_id:
            # Connected to differend chassis/chassis
                sql = "UPDATE EntityLink SET parent_entity_id = %d WHERE child_entity_id = %d AND child_entity_type = 'object' AND parent_entity_id = %d" % (chassis_id, server_id, result[0])
                self.db_insert(sql)

                old_object_name = self.GetObjectName(result[0])
                self.InsertLog(old_object_name, "Unlinked server %s" % (server_name))
                self.InsertLog(server_id, "Unlinked from Blade Chassis %s" % (old_object_name))
                self.InsertLog(chassis_id, "Linked with server %s" % (server_name))
                self.InsertLog(server_id, "Linked with Blade Chassis %s" % (chassis_name))
        
        else:
        # Object is not connected
            sql = "INSERT INTO EntityLink (parent_entity_type, parent_entity_id, child_entity_type, child_entity_id) VALUES ('object', %d, 'object', %d)" % (chassis_id, server_id)
            self.db_insert(sql)
            self.InsertLog(chassis_id, "Linked with server %s" % (server_name))
            self.InsertLog(server_id, "Linked with Blade Chassis %s" % (chassis_name))
            
            

    def GetAllServerChassisId(self):
        '''Get list of all server chassis IDs'''
        sql = "SELECT object_id FROM AttributeValue WHERE attr_id = 2 AND uint_value = 994"
        return self.db_query_all(sql)
