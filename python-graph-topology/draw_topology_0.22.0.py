# coding=utf-8
##########################################################
# Name: draw_topo_02.py
# Version: 0.22.0
# Author: Lucas Aimaretto
# Date: 14-jun-2015 
#
# - 0.1: first draft
#        This version will graph any topology based on the tags of the routers.
# - 0.2: Reorder of functions; cleaning of code.
# - 0.3: Implementing of IP/PORT/Speed information. Change on function 
#        fnc_build_query_connections SQL's query
# - 0.4: Included sync-e reference to each port. If not abailable, then N/A is shown.
#        For this, a sync_dict is created.
# - 0.5: Including system IP, sync order, color for not integrated routers.
# - 0.6: If no SFP, no speed can be obtained. Then "No SFP" is shown. If CES, ATM or ASAP, then "No ETH" is shown.
# - 0.7: fnc_build_query_objetos(.) modify to further filter out objects with tags (and no ipvnet)
#		 fnc_build_query_interfaces(.) to bring IPs and interfaces
#        fnc_build_query_connections(.) now only brings connections and no IPs
#        fnc_cross_conn_inter(.) finds out IP for connections               
# - 0.8: Asked whether to graph LAGs or not
#        TBD: consider when link label is something else than "LAG", "Hairpin" or "".
# - 1.1: Distinction between High, Mid and Low Ran
# - 1.2: Reducing font size of link's labels
# - 1.3: Including FO and ARSAT as possible labels for links
# - 1.7: Different colors for MR and HR
# - 1.8: Includes transmission with a list
# - 1.9: Ring topology now available
# - 2.0: Change Name of file to match Claro's format.
# - 2.1: Bug regarding getting the system IP of router: fnc_build_query_attributes(.)
#		 TxType now considered when MIX TX is needed (ie: DWDM+MW)
# - 2.2: Option to graph nodes with the names, only.


#!/usr/bin/python
import MySQLdb
import graphviz as gv
import time
import sys
import functools
import datetime
from operator import itemgetter
from itertools import groupby

########################################################################
# Globals
########################################################################

ipran_tx = ["LAG_Y","HAIRPIN_Y","SDH","RADIO","DWDM","CDWM","FO",""]
version_script="0.22.0"

########################################################################
# Functions
########################################################################

# Function to try if string is number
def fnc_isNumber(text):

	try:
		int(text)
		return 1
	except:
		return 0

# Function to distinguish chains from rings
def fnc_chains_ring(vector):
	topoString=""
	tempList=[]
	len_vector= len(vector)
	i=1
	
	# We can have many topos inside vector
	for topo in vector:
		
		topoString = topo
		charCount = topoString.count("_")
		nameList=topoString.split("_")
		
		print nameList, charCount

		if fnc_isNumber(nameList[0]==0):
			
			if charCount == 1:
				name1 = nameList[0]
				testNumber1 = nameList[1]
				
				# SF903_002
				if fnc_isNumber(testNumber1)==1:
					tempList.append(("Anillo",name1,name1+"_"+testNumber1))
				else:
					print "Wrong ring number: " + testNumber1
					quit()
			
			elif charCount == 2:
				name1 = nameList[0]
				name2 = nameList[0] + "_" + nameList[1]
				testNumber1 = nameList[1]
				testNumber2 = nameList[2]
				
				# SF903_002_ARSAT
				if fnc_isNumber(testNumber1):
					tempList.append(("Anillo_TX",name1,name1+"_"+testNumber1))
				# CF164_CFR17_001
				elif fnc_isNumber(testNumber2):
					tempList.append(("Cadena",name2,name2+"_"+testNumber2))
				else:
					print "Wrong chain number: " + testNumber1 + " or " + testNumber2
					quit()

			elif charCount == 3:
				name1 = nameList[0] + "_" + nameList[1]
				testNumber1 = nameList[2]
				
				# CF164_CFR17_001_MW
				if fnc_isNumber(testNumber1)==1:
					tempList.append(("Cadena_TX",name1,name1+"_"+testNumber1))
				else:
					print "Wrong chain_tx number: " + testNumber1
					quit()

			elif charCount == 0:
				name1 = vector[0]
				tempList.append(("Other",name1,name1))
				
			else:
				print "Wrong topo name."
				quit()
		
	return tempList

# Function that builds the filename
def fnc_build_filename(vector):
	info = fnc_chains_ring(vector)
	len_vector= len(vector)

	if len_vector==1:

		tipoTopo = info[0][0]
		agregador = info[0][1]
		topologia = info[0][2]

		topoName1 = "Topología "
		topoName2 = tipoTopo
		topoName3 = " - MR LR - "
		topoName4 = topologia
		filename = "topo/" + agregador + "/" + topoName1 + topoName2 + topoName3 + topoName4
		
	elif len_vector >1:
		
		tipoTopo = "-".join(list(set([name[0] for name in info])))
		agregador = "-".join(list(set([name[1] for name in info])))
		topologia = "-".join(list(set([name[2] for name in info])))
		
		topoName1 = "Claro Argentina - Topología "
		topoName2 = tipoTopo
		topoName3 = " - MR LR - "
		topoName4 = topologia
		filename = "topo/" + agregador + "/" + topoName1 + topoName2 + topoName3 + topoName4
		
	now = datetime.datetime.now()
	#filename=filename + "_" + version_script + "_" +now.strftime("%Y-%m-%d")+".dot"
	return filename

# Function that builds the SQL query to obtain the object Id based
# on topo_name
def fnc_build_query_objetos(vector):
	query1=""
	query2=""
	len_vector = len(vector)
	i=1
	
	query1=(
		"select "
		"OBJ.name, "
		"TS.entity_id "
		"from "
		"TagStorage as TS join Object as OBJ on (TS.entity_id=OBJ.id) "
		"where entity_realm ='object' and "
	)
	
	for row in vector:
		obj_id=str(row[0])
		if i < len_vector:
			query2 = query2 + "tag_id = " + obj_id + " or "
		else:
			query2 = query2 + "tag_id = " + obj_id
		i=i+1

	query = query1 + query2

	return query
	
# Function that builds the SQL query to obatin the topo_ID
def fnc_build_query_topo_id(vector):
	query1=""
	query2=""
	len_vector = len(vector)
	i=1

	query1=(
		"select id from TagTree where "
	)

	for row in vector:
		obj_id="'" + row + "'"
		if i < len_vector:
			query2 = query2 + "tag = " + obj_id + " or "
		else:
			query2 = query2 + "tag = " + obj_id
		i=i+1

	query = query1 + query2

	return query
	
# Function that removes ports from a router if such port connects
# to a router that does not have a requested tag.
def fnc_remove_routers_wotag(object_vector, connex_vector):

	temp_list=[]
	for o in object_vector:
		temp_list.append(o[0])
	
	final_list=[item for item in connex_vector if item[0] in temp_list and item[5] in temp_list]
			
	return final_list
	
	
# Function that obtains the attributes values per object ID
# It also brings the system IP of the router.
def fnc_build_query_attributes(vector):
	query1=""
	query2=""
	query3=""
	query4=""
	len_vector = len(vector)
	
	query1=(
	"select "
	"ob.name, "
	"concat(ob.name,\"_\",av.string_value) as node, "
	"a.name, "
	"av.string_value, "
	"d.dict_value "
	"from Object as ob "
	"join AttributeValue as av on (ob.id=av.object_id) "
	"join Attribute as a on (av.attr_id=a.id) "
	"left join Dictionary as d on (d.dict_key=av.uint_value) "
	"where (a.name = 'Integrado' or a.name = 'HW type' or a.name = 'HW function' or a.name = 'TxType_CKT_ID' or a.name like '%Ref%') "
	"and ("
	)
	i=1
	for row in vector:
		obj_id=str(row[1])
		if i < len_vector:
			query2 = query2 + "ob.id = " + obj_id + " or "
		else:
			query2 = query2 + "ob.id = " + obj_id 
		i=i+1
		
	query = query1 + query2 + ")"

	query3=(
	"SELECT "
	"ob.name AS obj1, "
	"'TBD1', "
	"concat(ob.name,'_',ip4.name) AS int_name1, "
	"INET_NTOA(ip4.ip) as ip, "
	"'TBD2' "
	"FROM Object AS ob "
	"JOIN IPv4Allocation AS ip4 ON (ip4.object_id=ob.id) "
	"where ip4.name = 'system' "
	"and ("
	)
	i=1
	for row in vector:
		obj_id=str(row[1])
		if i < len_vector:
			query4 = query4 + "ob.id = " + obj_id + " or "
		else:
			query4 = query4 + "ob.id = " + obj_id 
		i=i+1

	query = query + " UNION " + query3 + query4 + ")"

	return query

# Function that builds a SQL query to obtain the IP interfaces
def fnc_build_query_interfaces(vector):
	query1=""
	query2=""
	len_vector = len(vector)
	i=1
	
	query1=(
	"SELECT "
	"ro1.name AS obj1, "
	"ro1.id AS obj1_id, "
	"concat(ro1.name,'_',ip4.name) AS int_name1, "
	"INET_NTOA(ip4.ip) as ip "
	"FROM "
	"Object AS ro1 "
	"JOIN IPv4Allocation AS ip4 ON (ip4.object_id=ro1.id) "
	"WHERE ("
	)
	for row in vector:
		obj_id=str(row[1])
		if i < len_vector:
			query2 = query2 + "ro1.id = " + obj_id + " or "
		else:
			query2 = query2 + "ro1.id = " + obj_id
		i=i+1

	query = query1 + query2 + ")"

	return query
	

# Function that builds a SQL query to obtain the connections
# among routers.
def fnc_build_query_connections(vector):
	query1=""
	query2=""
	len_vector = len(vector)
	i=1
	
	query1=(
		"SELECT "
		"ro1.name AS obj1, "
		"ro1.id as obj1_id, "
		"concat(ro1.name,'_',p1.label) AS int_name1, "
		"p1.name AS port1, "
		"Link.cable, "
		"p2.name AS port2, "
		"concat(ro2.name,'_',p2.label) AS int_name2, "
		"ro2.name AS obj2, "
		"ro2.id as obj2_id, "
		"d.dict_value AS obj1type, "
		"poia.oif_name, "
		"poib.oif_name "
		"FROM Object AS ro1 "
		"JOIN Port AS p1 ON(ro1.id=p1.object_id) "
		"JOIN Link ON(p1.id=Link.porta) "
		"JOIN Port AS p2 ON(Link.portb=p2.id) "
		"JOIN Object AS ro2 ON(p2.object_id=ro2.id) "
		"JOIN PortOuterInterface AS poia ON(poia.id=p1.type) "
		"JOIN PortOuterInterface AS poib ON(poib.id=p2.type) "
		"LEFT JOIN Dictionary AS d ON(ro1.objtype_id=d.dict_key) "
		"WHERE ("
	)
	for row in vector:
		obj_id=str(row[1])
		if i < len_vector:
			query2 = query2 + "ro1.id = " + obj_id + " or ro2.id = " + obj_id + " or "
		else:
			query2 = query2 + "ro1.id = " + obj_id + " or ro2.id = " + obj_id
		i=i+1

	query = query1 + query2 + ")"

	return query
	

# Function that organizes each router's ports
def fnc_port_list(routers):
	router_list=[]
	port_list=[]
	for router in routers:
		print router[0]
		router_id = router[0][0]
		for port in router:
			port_list.append(port[1])
		router_list.append((router_id,port_list))
		port_list=[]

	return router_list
	
# Function that returns de speed of the port
def fnc_port_speed(port_string):
	
	speed=port_string.split('B')
	speed=speed[0]
	
	if speed == "100":
		return "-100Mb"
	elif speed == "1000":
		return "-1Gb"
	elif speed == "10G":
		return "-10Gb"
	elif speed == "empty SFP-1000":
		return "-n/SFP"
	elif speed == "virtual port":
		return ""
	elif speed == "SFP-CES" or speed == "SFP-ATM" or speed == "SFP-ASAP":
		return "-n/ETH"
	else:
		return "-N/A"

# Function that returns the SFP type
def fnc_port_sfp(port_string):

	if port_string == "virtual port":
		return "LAG"
	elif port_string == "SFP-CES" or port_string == "SFP-ATM" or port_string == "SFP-ASAP":
		return port_string.split('-')[1]
	else:	
		sfp_type = port_string.split('-')
		sfp_type = sfp_type[1]
		return sfp_type

# Function that creates a dictionary for sync references
# The asignment is done on a port basis
def fnc_add_atributes_to_dic_ref(attributes):
	
	dict_ref={}
	
	for attrib in attributes:
		node_id=attrib[1]
		node_ref=attrib[2]
		if node_ref:
			dict_ref[node_id]=node_ref
		else:
			dict_ref[node_id]="N/A"
			
	return dict_ref

# Function that creates a dictionary with global attributes
def fnc_add_atributes_to_dic_global(attributes):

	dict_global={}
	
	for attrib in attributes:
		
		if "ARSAT" in attrib[0]:
			print attrib
		
		router_name = attrib[0]
		attrib_name = attrib[2]
		system_ip = attrib[3]
		attrib_value = attrib[4]
		ckt_id = attrib[3]

		if "HW function" in attrib_name:
			hw_function=attrib_value
			dict_key=router_name + "_function"
			dict_global[dict_key]=hw_function
		if "Integrado" in attrib_name:
			dict_key=router_name + "_Integrado"
			dict_global[dict_key]=attrib_value
		if "Ref_Order" in attrib_name:
			dict_key=router_name + "_Ref_Order"
			dict_global[dict_key]=attrib_value
		if "TxType_CKT_ID" in attrib_name:
			dict_key=router_name + "_ckt_id"
			dict_global[dict_key]=ckt_id
		if "system" in attrib_name:
			dict_key=router_name + "_system"
			dict_global[dict_key]=system_ip
			
	return dict_global
			
		
# Function that creates a list which holds each router and all its ports.
# Input: object_connections
# [('Router1', '1/1/1'), ('Router1', '1/1/2')]
# Output
# ('Router1/1/1', {'label': '1/1/1'})
# The output of this function is used as input to graphviz
def fnc_node_list(routers,sync_dict, router_mode, graph_hp, graph_lag, graph_cpam):
	
	temp_list=[]
	
	for row in routers:
		

		cableID=row[3]
		if not cableID: cableID=""

		if ("LAG" in cableID.upper() and graph_lag=="n") or ("HAIRPIN" in cableID.upper() and graph_hp=="n") or ("CPAM" in cableID.upper() and graph_cpam=="n"):
			a=1
		else:
			
			routerA=row[0]
			portA=row[2]
			portAspeed=fnc_port_speed(row[10])
			portAtype=fnc_port_sfp(row[10])
			ipA=row[8]
			if not ipA:	ipA="N/A"
			nodeA=routerA + "_" + portA
			ref1a=sync_dict.get(nodeA,"N/A")
			
			routerB=row[5]
			portB=row[4]
			portBspeed=fnc_port_speed(row[11])
			portBtype=fnc_port_sfp(row[11])
			ipB=row[9]
			if not ipB: ipB="N/A"
			nodeB=routerB + "_" + portB
			ref1b=sync_dict.get(nodeB,"N/A")
			
			if router_mode==0:
				labelA="<"+ portA + "<BR />" + portAtype + portAspeed + "<BR />" + ipA + "<BR />" + ref1a +">"
				labelB="<"+ portB + "<BR />" + portBtype + portBspeed + "<BR />" + ipB + "<BR />" + ref1b +">"
			elif router_mode==1 or router_mode==2:
				labelA= portA + "\n" + portAtype + portAspeed + "\n" + ipA + "\n" + ref1a
				labelB= portB + "\n" + portBtype + portBspeed + "\n" + ipB + "\n" + ref1b
			elif router_mode==3:
				labelA=""
				labelB=""

			temp_list.append((routerA,nodeA,{'label':labelA}))
			temp_list.append((routerB,nodeB,{'label':labelB}))
	
	# List will be ordered and sorted always by the first field
	# which is the router name
	lista_sorted=sorted(temp_list, key=itemgetter(0))
	lista_grouped = groupby(lista_sorted, key=itemgetter(0))
		
	a = []
	for i,rou in enumerate(lista_grouped):
		a.append(list(rou[1]))
	
	return a

# Function that crosses the connections and interfaces to come out
# with a proper list
#
# Output:
#('Router1', 105L, '1/1/7', None, '2/2/10', 'Router2', 418L, 'Router', '10.2.34.113', '10.2.34.114', '1000Base-T', '1000Base-T')
#
def fnc_cross_conn_inter(connections,interfaces):
	connection_list=[]
	
	for conn in connections:
		
		name1=conn[0]
		id1=conn[1]
		p1=conn[3]
		cable=conn[4]
		p2=conn[5]
		name2=conn[7]
		id2=conn[8]
		router_type=conn[9]
		ip1=""
		ip2=""
		p1t=conn[10]
		p2t=conn[11]
		
		int1=conn[2]
		int2=conn[6]
		
		for interface in interfaces:
			int_name=interface[2]
			ip=interface[3]
			if int1==int_name:
				ip1=ip
				break
			else:
				ip1="N/A"
				
		for interface in interfaces:
			int_name=interface[2]
			ip=interface[3]
			if int2==int_name:
				ip2=ip
				break
			else:
				ip2="N/A"
				
		connection_list.append((name1,id1,p1,cable,p2,name2,id2,router_type,ip1,ip2,p1t,p2t,int1,int2))
		
	#fnc_print_list(connection_list)
		
	return connection_list
			

# Function that builds a list which holds the connections among routers.
# The output of this function is used as input to graphviz
def fnc_edge_list(vector, graph_lag, graph_hp, graph_cpam, router_mode):
	edge_list=[]
	for row in vector:
		routerA=row[0]
		portA=row[2]
		routerB=row[5]
		portB=row[4]
		cableID=row[3]
		nodeA=routerA + "_" + portA
		nodeB=routerB + "_" + portB
		
		if not cableID: cableID=""
		
		#print nodeA, cableID, nodeB
		
		if ("LAG" in cableID.upper() and graph_lag=="n") or ("HAIRPIN" in cableID.upper() and graph_hp=="n") or ("CPAM" in cableID.upper() and graph_cpam=="n"):
			a=1
		else:
			edge_list.append(((nodeA,nodeB),{'label':cableID}))
	
	return edge_list

# Verifies that an object exists
def fnc_check_for_topo(objList):
	if len(objList) == 0:
		print "The requested topology does not exist"
		return 0
	else:
		return 1

# Function to print lists
def fnc_print_list(vector):
	for vec in vector:
		print vec
		
# Function to obtain topology name
def fnc_build_topo_name(vector):
	topo_name=""
	len_vector= len(vector)
	i=1
	for tn in vector:
		if i < len_vector:
			topo_name=topo_name+tn+"-"
		else:
			topo_name=topo_name+tn
		i=i+1
		
	return topo_name
	
# Function that returns the color of the router depending on its
# situation
def fnc_router_color(router_function,router_int):
	# Color depending on function
	if router_function == "High-Ran":
		return 'lightblue4'
	elif router_function == "Mid-Ran":
		return 'lightpink4'
	elif "si" in router_int:
		return 'lightblue'
	elif "TX" in router_function:
		return 'yellow'
	# Color depending on integration
	elif router_int=="no":
		return 'orange'
	elif router_int=="out-of-service" or router_int=="desinsertado":
		return 'red'
	else:
		return 'grey'

# Function that returns metadata of the router
def fnc_router_metadata(global_dict,router_name,what, router_function, router_ckt_id, router_mode):
	
	router_sync_order=global_dict.get(router_name+"_Ref_Order","N/A")
	router_ip=global_dict.get(router_name+"_system","N/A")
	
	if "TX" in router_function:
		
		if what=="labelHtml":
		
			router_label=(
				"<"+
				"<font point-size=\"10\">"+router_ckt_id+"</font>"+"<BR />"
				+">"
				)
			return router_label
		
		elif what=="labelText":
			
			router_label = router_ckt_id
			return router_label
	
	else:
	
		if what=="labelHtml":
			
			if router_mode==3:
				router_label=(
					"<"+
					"<font point-size=\"10\">"+router_name+"</font>"+"<BR />"+
					"<font point-size=\"9\">" +router_ip+"</font>"+"<BR />"
					+">"
					)
				return router_label
				
			else:
				router_label=(
					"<"+
					"<font point-size=\"10\">"+router_name+"</font>"+"<BR />"+
					"<font point-size=\"9\">" +router_ip+"</font>"+"<BR />"+
					"<font point-size=\"9\">" +router_sync_order+"</font>"
					+">"
					)
				return router_label
			
		elif what=="labelText":
			
			if router_mode==3:
				router_label = router_name + "\n" + router_ip
				return router_label
			else:
				router_label = router_name + "\n" + router_ip + "\n" + router_sync_order
				return router_label
			


# Function that returns port_string when router as a node
def fnc_port_string(router_label, port_string, color, router_mode, router_function):
	
	port_string=port_string[1:]
	temp_port = port_string.split("|")
	
	if "TX" in router_function:
		
		if router_function == "TX_MIX":
		
			temp_string=""
			
			for port in port_string.split("|"):
				temp_string = temp_string + port.split("\n")[0] + "|"
				
			port_string = temp_string[:-1]
			
			temp_string = "{" + port_string + "}"
			temp_string = " [" + color + ",label=\"" + temp_string + "\"]"
			return temp_string
			
		elif router_function == "TX_ARSAT":
			
			print router_label
			
			temp_string = "{" + router_label + "}"
			temp_string = " [" + color + ",label=\"" + temp_string + "\"]"
			return temp_string
	
	else:
		
		if router_mode==1:
			temp_string = "{" + router_label + "|" + port_string + "}"
		elif router_mode==2:
			temp_string = "{" + router_label + "|" + "{" + port_string + "}" + "}"
		elif router_mode==3:
			temp_string = router_label

		temp_string = " [" + color + ",label=\"" + temp_string + "\"]"
		return temp_string
		

########################################################################
# Program
########################################################################

db = MySQLdb.connect(host="10.10.61.10", 	# your host, usually localhost
               	     port=3306,
                     user="root", 			# your username
                     passwd="mysqlroot", 	# your password
                     db="racktables") 		# name of the data base

topo_name = raw_input(
	"\nInput the tag name.\n"
	"If you wish to graph more than one topology, separate those with a comma (,) with no space.\n"
	"Example: topo1,topo2: ")
if not topo_name:
	print "None has been input.\n"
	quit()
else:
	topo_name = topo_name.upper()
	topo_name = topo_name.split(",")
	
#==================================================================
#==================================================================
# query0 obtains the Id of the tag_name.

query0 = fnc_build_query_topo_id(topo_name)

cur = db.cursor()
cur.execute(query0)
topo_id = list(cur.fetchall())
if fnc_check_for_topo(topo_id) == 0: quit()

#==================================================================
#==================================================================
# query10 obtains the IDs of routers that have tag 'topo_name'
# The result is the following.

#(objeto_name, object_id)

query10 = fnc_build_query_objetos(topo_id)

cur = db.cursor()
cur.execute(query10)
object_list = list(cur.fetchall())

#==================================================================
#==================================================================
# query15 obtains the attributes of the routers that have tag 
# 'topo_name'.
# It also brings de system IP of the router.
# The result is the following.

#('Router1', 'Router1_1/1/1', 'Sync_Ref1', '1/1/1', None, '10.2.21.168')
#('Router1', 'Router1_1/1/2', 'Sync_Ref2', '1/1/2', None, '10.2.21.168')
#('Router1', None, 'Sync_Ref_Order', None, 'ref1 ref2 external', '10.2.21.168')
#('Router1', None, 'Integrado', None, 'si', '10.2.21.168')

query15=fnc_build_query_attributes(object_list)
cur=db.cursor()
cur.execute(query15)
attr_list = list(cur.fetchall())
sync_dict = fnc_add_atributes_to_dic_ref(attr_list)
global_dict = fnc_add_atributes_to_dic_global(attr_list)

#==================================================================
#==================================================================
# query20 brings the connection among objects.
# This query also brings connections to routers that do not have the
# the requested tag.
# The result is the following.

#(Router1, port1, cableID, port1, Router2)

#('Router1', 172L, 'Router1_RTR1toRTR2_1_1G', '1/6/8', None, '2/2/14', 'Router2_RTR2toRTR1_1_1G', 'Router2', 418L, 'Router', '1000Base-T', '1000Base-T')

query20=fnc_build_query_connections(object_list)
cur = db.cursor()
cur.execute(query20)
object_connections = list(cur.fetchall())

#==================================================================
#==================================================================
# query25 brings the connection among objects.
# This query obtains the interfaces and IP addresses.

# (Router, ID, Interface_name, IP)

query25=fnc_build_query_interfaces(object_list)
cur = db.cursor()
cur.execute(query25)
object_interfaces = list(cur.fetchall())

#==================================================================
#==================================================================
# The function fnc_cross_conn_inter(.) crosses connections with
# interfaces to come out with the following information

#(Router1, port1, cableID, port1, Router2)
#('Router1', 105L, '1/1/7', None, '2/2/10', 'Router2', 418L, 'Router', '10.2.34.113', '10.2.34.114', '1000Base-T', '1000Base-T')

object_connections=fnc_cross_conn_inter(object_connections,object_interfaces)

#==================================================================
#==================================================================
# The function fnc_remove_routers_wotag(.) filters out the data
# obtained with query20 in such a way that only the connections
# to routers with the requested tag will remain.
# The result is the following.

#(Router1, port1, cableID, port1, Router2)
#('Router1', 105L, '1/1/7', None, '2/2/10', 'Router2', 418L, 'Router', '10.2.34.113', '10.2.34.114', '1000Base-T', '1000Base-T')

object_connections=fnc_remove_routers_wotag(object_list,object_connections)

#==================================================================
# The graph is created.
#==================================================================

# Format Dictionaries
algo_dict = {"0":"dot", "1":"fdp", "2":"circo", "3":"twopi", "4":"neato"}
format_dict = {"0":"png", "1":"svg"}
lines_dict = {"0":"line", "1":"true","2":"ortho", "3":"polyline"}
rankdir_dict = {"0":"LR","1":"TB"}
port_dict = {}

router_mode = raw_input(
	"\nPlease select router_mode:"
	"\n0 - Router as cluster"
	"\n1 - Router as node, one-line"
	"\n2 - Router as node, two-line"
	"\n3 - Router as node, only with name"
	"\n\nOption:"
)
router_mode = int(router_mode)

output_selection = raw_input(
	"\nPlease choose output action:"
	"\n0 - Default0 (no-LAG, no-HairPin, no-CPAM, SVG, DOT, Left-to-Right, curved, group-mid-high-ran)"
	"\n1 - Default1 (no-LAG, no-HairPin, no-CPAM, SVG, DOT, Top-to-Bottom, curved, group-mid-high-ran)"
	"\n2 - Default2 (no-LAG, no-HairPin, no-CPAM, SVG, FDP, Top-to-Bottom, curved, no-grouping)"
	"\n3 - Default3 (no-LAG, no-HairPin, no-CPAM, SVG, DOT, Top-to-Bottom, curved, no-grouping)"
	"\n4 - Default4 (no-LAG, no-HairPin, no-CPAM, SVG, CIRCO, Top-to-Bottom, curved, no-grouping)"
	"\n5 - Default5 (no-LAG, no-HairPin, no-CPAM, SVG, CIRCO, Left-to-Right, curved, no-grouping)"
	"\n6 - Default6 (no-LAG, no-HairPin, no-CPAM, SVG, TWOPI, Left-to-Right, curved, no-grouping)"
	"\nc - Custom"
	"\nOption:"
)

if output_selection=="c":
		
	graph_lag = raw_input("\nDo you want to graph LAGs? [y|n]: ")
	graph_hp = raw_input("Do you want to graph Hairpin? [y|n]: ")
	graph_cpam = raw_input("Do you want to graph CPAM? [y|n]: ")

	output_format = raw_input(
		"\nPlease choose output format:"
		"\n0 - PNG"
		"\n1 - SVG"
		"\nOption:"
	)

	output_algo = raw_input(
		"\nPlease choose output algorithm:"
		"\n0 - DOT"
		"\n1 - FDP"
		"\n2 - CIRCO"
		"\n3 - TWOPI"
		"\n4 - NEATO"
		"\nOption:"
	)

	output_direction = raw_input(
		"\nPlease choose order direction:"
		"\n0 - Left-to-Rigth"
		"\n1 - Top-to-Bottom"
		"\nOption:"
	)

	output_line = raw_input(
		"\nPlease choose connector type:"
		"\n0 - straight"
		"\n1 - curved"
		"\n2 - right-angled"
		"\n3 - polyline"
		"\nOption:"
	)

	aggregator = raw_input(
		"\nDo you want to group routers in [low|mid|high] ran devices [y/n]: "
	)

elif output_selection=="0":
	#(no-LAG, no-HairPin, PNG, DOT, Left-to-Right, curved, group-mid-high-ran)
	
	output_format="1"
	output_algo="0"
	output_direction="0"
	output_line="1"
	aggregator="y"
	graph_lag="n"
	graph_hp="n"
	graph_cpam="n"
	
elif output_selection=="1":
	#(no-LAG, no-HairPin, PNG, DOT, Top-to-Bottom, curved, group-mid-high-ran)
	
	output_format="1"
	output_algo="0"
	output_direction="1"
	output_line="1"
	aggregator="y"
	graph_lag="n"
	graph_hp="n"
	graph_cpam="n"
	
elif output_selection=="2":
	#(no-LAG, no-HairPin, PNG, FDP, Top-to-Bottom, curved, no-grouping)
	
	output_format="1"
	output_algo="1"
	output_direction="1"
	output_line="1"
	aggregator="n"
	graph_lag="n"
	graph_hp="n"
	graph_cpam="n"
	
elif output_selection=="3":
	#(no-LAG, no-HairPin, PNG, DOT, Top-to-Bottom, curved, no-grouping)
	
	output_format="1"
	output_algo="0"
	output_direction="1"
	output_line="1"
	aggregator="n"
	graph_lag="n"
	graph_hp="n"
	graph_cpam="n"
	
elif output_selection=="4":
	#(no-LAG, no-HairPin, PNG, CIRCO, Top-to-Bottom, curved, no-grouping)
	
	output_format="1"
	output_algo="2"
	output_direction="1"
	output_line="1"
	aggregator="n"
	graph_lag="n"
	graph_hp="n"
	graph_cpam="n"
	
elif output_selection=="5":
	#(no-LAG, no-HairPin, PNG, CIRCO, Left-to-rigth, curved, no-grouping)
	
	output_format="1"
	output_algo="2"
	output_direction="0"
	output_line="1"
	aggregator="n"
	graph_lag="n"
	graph_hp="n"
	graph_cpam="n"
	
elif output_selection=="6":
	#(no-LAG, no-HairPin, PNG, TWOPI, Left-to-rigth, curved, no-grouping)
	
	output_format="1"
	output_algo="3"
	output_direction="0"
	output_line="1"
	aggregator="n"
	graph_lag="n"
	graph_hp="n"
	graph_cpam="n"

#==================================================================
#==================================================================
# At this instance of the run, the list object_connections[] only has
# connections to routers that do have the requested tag.
# The function fnc_edge_list(.) reorders that information
# so it will be easier to feed graphviz.

#((Router1_port1, Router2_port1), {label: cableID})

edges=fnc_edge_list(object_connections, graph_lag, graph_hp, graph_cpam, router_mode)


#===================================================================
#===================================================================
# The list edges[] holds the connections among routers that do have
# the requested tag.
# With this information we filter contruct a new list grouping ports
# per router.

#(Router1, Router1_portX, {'label':""}),(Router2, Router2_portY, {'label':""})
#('Router1', 'Router1_1/2/7', {'label': '1/2/7\nT-1Gb\n10.2.61.146\nSync_Ref1'}), ('Router2', 'Router2_1/2/8', {'label': '1/2/8\nT-1Gb\n10.2.61.149\nSync_Ref2'})

routers=fnc_node_list(object_connections,sync_dict, router_mode, graph_hp, graph_lag, graph_cpam)

#===================================================================
#===================================================================
# Begin the plot

g0 = gv.Graph(format=format_dict[output_format], engine=algo_dict[output_algo])

topo = '\"'+fnc_build_topo_name(topo_name)+'\"'

g0.body.append('label='+topo)
g0.body.append('rankdir='+rankdir_dict[output_direction])
#g0.body.append('rank=min')
g0.body.append('splines='+lines_dict[output_line])
g0.node_attr['style']='filled'
g0.node_attr['fixedsize']='false'
g0.node_attr['fontsize']='9'

if router_mode==0:
	g0.node_attr['shape']='box'
	labelType="labelHtml"
elif router_mode==1 or router_mode==2 or router_mode==3:
	g0.body.append('overlap=false')
	g0.node_attr['shape']='Mrecord'
	g0.node_attr['overlap']='false'
	labelType="labelText"

if aggregator == "y":
	g10 = gv.Graph('cluster_hr')
	g20 = gv.Graph('cluster_mr')
	g25 = gv.Graph('cluster_tx')
	g30 = gv.Graph('cluster_lr', engine=algo_dict[output_algo])
	
	g10.body.append('rankdir=TB')
	g10.body.append('label=\"HR\"')
	
	g20.body.append('rankdir=TB')
	g20.body.append('label=\"MR\"')
	
	g30.body.append('rankdir=TB')
	g30.body.append('label=\"LR\"')

if router_mode==0:
	i = 1
	for router in routers:
		# Variables
		router_name = router[0][0]
		router_function=global_dict.get(router_name+"_function","N/A")
		router_int=global_dict.get(router_name+"_Integrado","N/A")
		router_ckt_id=global_dict.get(router_name+"_ckt_id","N/A")
		router_label=fnc_router_metadata(global_dict,router_name,labelType, router_function, router_ckt_id, router_mode)
		router_color=fnc_router_color(router_function,router_int)
		
		print router_name, router_function
		
		# Parametrization
		cluster_name = "cluster"+str(i)
		c = gv.Graph(cluster_name)
		c.body.append('label='+router_label)
		c.body.append('shape=box')
		c.body.append('style=filled')
		
		# Color depending on function in network
		c.body.append('fillcolor='+router_color)
		c.node_attr.update(style='filled')
		
		# Ports workout
		for port in router:
			
			node_id=port[1]
			if ":" in node_id: node_id=node_id.replace(":","#")
			port_id=port[2]['label']
			c.node(node_id,label=port_id)
		
		# Asignación al cluster Low o High Run
		if aggregator == "y":
			if router_function == "High-Ran":
				g10.subgraph(c)
			elif router_function == "Mid-Ran":
				g20.subgraph(c)
			else:
				g30.subgraph(c)
				
			g0.subgraph(g10)
			g0.subgraph(g20)
			g0.subgraph(g30)
			
		# No grouping
		else:
			g0.subgraph(c)
			
		i=i+1

	for e in edges:
		edgeA=e[0][0]
		if ":" in edgeA: edgeA=edgeA.replace(":","#")
		edgeB=e[0][1]
		if ":" in edgeB: edgeB=edgeB.replace(":","#")
		edgeLabel=e[1]['label']
		g0.edge_attr['fontsize']='9'
		g0.edge(edgeA,edgeB,label=edgeLabel)
		
elif router_mode==1 or router_mode==2 or router_mode==3:
	i = 1
	for router in routers:
		# Variables
		router_name = router[0][0]
		router_function=global_dict.get(router_name+"_function","N/A")
		router_int=global_dict.get(router_name+"_Integrado","N/A")
		router_ckt_id=global_dict.get(router_name+"_ckt_id","N/A")
		router_label=fnc_router_metadata(global_dict,router_name,labelType, router_function, router_ckt_id, router_mode)
		router_color=fnc_router_color(router_function,router_int)
		
		# Parametrization
		struct_name = "struct"+str(i)

		# Color depending on function in network
		color= 'fillcolor='+router_color
		
		# Ports workout
		p=1
		port_string=""
		for port in router:

			node_id=port[1]
			if ":" in node_id: node_id=node_id.replace(":","#")
			port_id=port[2]['label']
			
			port_string=port_string+"|<f"+str(p)+">"+port_id
			dict_key = str(i)+"_"+str(p)
			port_dict[node_id]=dict_key
			
			p=p+1

		node_string = fnc_port_string(router_label, port_string, color, router_mode, router_function)

		# Asignación al cluster Low o High Run
		if aggregator == "y":
			if router_function == "High-Ran": g10.body.append(struct_name+node_string)
			elif router_function == "Mid-Ran": g20.body.append(struct_name+node_string)
			else: g30.body.append(struct_name+node_string)
			g0.subgraph(g10)
			g0.subgraph(g20)
			g0.subgraph(g30)

		# No grouping
		else:
			g0.body.append(struct_name+node_string)

		i=i+1
	
	for e in edges:
		tempA=e[0][0]
		if ":" in tempA: tempA=tempA.replace(":","#")
		tempA=port_dict[tempA].split("_")
		if router_mode==3:
			edgeA="struct"+tempA[0]
		else:
			edgeA="struct"+tempA[0]+":f"+tempA[1]

		tempB=e[0][1]
		if ":" in tempB: tempB=tempB.replace(":","#")
		tempB=port_dict[tempB].split("_")
		if router_mode==3:
			edgeB="struct"+tempB[0]
		else:
			edgeB="struct"+tempB[0]+":f"+tempB[1]
		
		edgeLabel=e[1]['label']
		g0.edge_attr['fontsize']='9'
		g0.edge(edgeA,edgeB,label=edgeLabel)

filename=fnc_build_filename(topo_name)
print filename
g0.render(filename)
