#!/usr/bin/env python3

import MySQLdb
import sys
import yaml

import pandas as pd
import networkx as nx
from sql_definitions import *
from functions import *


SETTINGS = yaml.safe_load(open('plugins/topoGen/settings.yml'))
#SETTINGS = yaml.safe_load(open('settings.yml'))

########################################################################
# Program
########################################################################

db = MySQLdb.connect(
					    host   = SETTINGS['mysql']['host'],
					    port   = SETTINGS['mysql']['port'],
					    user   = SETTINGS['mysql']['username'],
					    passwd = SETTINGS['mysql']['password'],
					    db     = SETTINGS['mysql']['database'],
					)

posProg 	= 0
posTopo 	= 1
posMode		= 2
router_mode = sys.argv[posMode]

# Getting topo and routers name
input_string = sys.argv[posTopo]
input_string = input_string.split(",")

# We distinguis if we have topos and/or routers
topos_names,routers_names,attrib_names = fnc_input_string_type(input_string)

#print(topos_names,routers_names,attrib_names)

# In this list we'll save the object_id's
object_list            = []
object_list_topos      = []
object_list_routers    = []
object_list_attrib     = []
df_object_list_topos   = pd.DataFrame()
df_object_list_routers = pd.DataFrame()
df_object_list_attrib  = pd.DataFrame()

#==================================================================
#==================================================================
# query10 obtains the IDs of routers that have tag
# The result is the following.
if len(topos_names) > 0:
	#print(topos_names)
	# query0 obtains the Id of the tag_name.
	query0 = fnc_build_query_topo_id(topos_names)
	#print(query0)
	cur    = db.cursor()
	cur.execute(query0)
	topo_id = list(cur.fetchall())
	if len(topo_id) > 0:
		query10 = fnc_build_query_objetos(topo_id)
		df_object_list_topos = pd.read_sql(query10,db)
	else:
		sys.exit(-1)

#==================================================================
#==================================================================
# query11 obtains the IDs of routers that have tag 'routers_names'
# The result is the following.
if len(routers_names) > 0:
	query11 = fnc_build_query_objetos_name(routers_names)
	df_object_list_routers = pd.read_sql(query11,db)

#==================================================================
#==================================================================
# query12 obtains the IDs of routers that have attribute
# The result is the following.
if len(attrib_names) > 0:
	query12 = fnc_build_query_attrib_name(attrib_names)
	df_object_list_attrib = pd.read_sql(query12,db)

# We add together topos and routers
df_object_list = pd.concat([df_object_list_topos,df_object_list_routers,df_object_list_attrib]).drop_duplicates()

#==================================================================
#==================================================================
# query25 obtains the interfaces and IP addresses.
query25=fnc_build_query_interfaces(df_object_list)
df_object_interfaces = pd.read_sql(query25,db)

# We grab routers with their system or loopback interfaces.
df_system = df_object_interfaces[df_object_interfaces.intName.isin(SETTINGS['loopInterfaces'])]
df_system = df_system[['name','ip']]
df_system = pd.merge(df_object_list,df_system,on=['name'],how='left')

#==================================================================
#==================================================================
# query20 brings the connection among objects.
# This query also brings connections to routers that do not have the
# the requested tag.
query20 = fnc_build_query_connections(df_object_list)
df_object_connections = pd.read_sql(query20,db)

dfConn = pd.merge(df_system,df_object_connections,left_on=['name'],right_on=['name1'])
dfConn = pd.merge(df_system,dfConn,               left_on=['name'],right_on=['name2'])
dfConn = dfConn[["name1","port1","cable","port2","name2","obj1type","obj2type","port1Speed","port2Speed","intName1","intName2"]]

# We merge both connections and interfaces so we stay with information of routers with proper tag/attribute
# This means that some routers alone, will be left outside
dfConnFinal = pd.merge(dfConn,     df_object_interfaces, left_on=['name1','intName1'], right_on=['name','intName'], how='left')
dfConnFinal = pd.merge(dfConnFinal,df_object_interfaces, left_on=['name2','intName2'], right_on=['name','intName'], how='left')
dfConnFinal = dfConnFinal.fillna("N/A")
dfConnFinal = dfConnFinal[["name1","port1","cable","port2","name2","obj1type","obj2type","port1Speed","port2Speed","intName1","intName2","ip_x","ip_y"]]
dfConnFinal = dfConnFinal.drop_duplicates()

dfConnFinal['topo'] = '-'.join(topos_names)
dfConnFinal['cap']  = dfConnFinal.apply(lambda x: fnc_port_speed(x.port1Speed, SETTINGS), axis=1)

#==================================================================
#==================================================================
# query15 obtains the attributes of the routers whose Ids are in 'object_list'
# It also brings de system IP of the router.
query15 = fnc_build_query_attributes(df_object_list)

df_attr_list = pd.read_sql(query15, db)

if len(df_attr_list) > 0:
	df_attr_list = df_attr_list.apply(setFinalValue, axis=1)
	df_attr_list = df_attr_list.drop(['string_value','dict_value'], axis=1)
	df_attr_list = df_attr_list.pivot(index='name',columns='attrName',values='finalValue').reset_index()
	df_attr_list.columns = [x.replace(" ","") for x in df_attr_list.columns]
	df_attr_list = pd.merge(df_system, df_attr_list, on=['name'], how='left')
	df_attr_list = df_attr_list.fillna("N/A")

#==================================================================
#==================================================================
# BUild global dictionaries
global_dict = fnc_add_atributes_to_dic_global(SETTINGS, df_system, df_attr_list)

#==================================================================
# The graph is created.
#==================================================================

## Obtain the filename for the topology
filename = fnc_build_filename(topos_names,routers_names,attrib_names)

# Gegin the plot
if router_mode in ['4']:

	fnc_build_graphml(df_system, dfConnFinal, global_dict, router_mode, filename)
	sys.exit(4)

elif router_mode in ['5']:

	fnc_build_graphnx(df_system, dfConnFinal, global_dict, router_mode, filename)
	sys.exit(5)

elif router_mode in ['6','7']:

	fnc_build_osm(global_dict, dfConnFinal, router_mode, filename)
	sys.exit(int(router_mode))

elif router_mode in ['8']:

	fnc_build_excel(df_system, dfConnFinal, global_dict, router_mode, filename)
	sys.exit(8)
