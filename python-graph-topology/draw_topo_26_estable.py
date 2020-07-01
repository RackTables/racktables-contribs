# coding=utf-8
##########################################################
# Name: draw_topo_02.py
# Version: 0.2
# Author: Lucas Aimaretto
# Date: 14-jun-2015
#
# - 0.1:	first draft
#			This version will graph any topology based on the tags of the routers.
# - 0.2:	Reorder of functions; cleaning of code.
# - 0.3:	Implementing of IP/PORT/Speed information. Change on function
#			fnc_build_query_connections SQL's query
# - 0.4:	Included sync-e reference to each port. If not abailable, then N/A is shown.
#			For this, a sync_dict is created.
# - 0.5:	Including system IP, sync order, color for not integrated routers.
# - 0.6:	If no SFP, no speed can be obtained. Then "No SFP" is shown. If CES, ATM or ASAP, then "No ETH" is shown.
# - 0.7:	fnc_build_query_objetos(.) modify to further filter out objects with tags (and no ipvnet)
#			fnc_build_query_interfaces(.) to bring IPs and interfaces
#			fnc_build_query_connections(.) now only brings connections and no IPs
#			fnc_cross_conn_inter(.) finds out IP for connections
# - 0.8:	Asked whether to graph LAGs or not
#			TBD: consider when link label is something else than "LAG", "Hairpin" or "".
# - 1.1:	Distinction between High, Mid and Low Ran
# - 1.2:	Reducing font size of link's labels
# - 1.3:	Including FO and ARSAT as possible labels for links
# - 1.7:	Different colors for MR and HR
# - 1.8:	Includes transmission with a list
# - 1.9:	Ring topology now available
# - 2.0:	Change Name of file to match Claro's format.
# - 2.1:	Bug regarding getting the system IP of router: fnc_build_query_attributes(.)
#			TxType now considered when MIX TX is needed (ie: DWDM+MW)
# - 2.2:	Option to graph nodes with the names, only.
# - 2.3:	implementing argv to pass parameters in line
#			change \n to &#92;n in port an router when mode 1,2,3
# - 2.4:	full custimoization via argv[]
#			Different color depending con Ref_Order

#!/usr/bin/python
import MySQLdb
import graphviz as gv
import pydot
import time
import sys
import functools
import datetime
import pandas as pd 
import networkx as nx
from sql_definitions import *
from functions import *

#  python -m nuitka draw_topo_26_estable.py --nofollow-import-to=MySQLdb --nofollow-import-to=graphviz --nofollow-import-to=pydot --nofollow-import-to=time --nofollow-import-to=sys --nofollow-import-to=functools --nofollow-import-to=datetime --nofollow-import-to=pandas --nofollow-import-to=networkx --nofollow-import-to=operator --nofollow-import-to=itertools --nofollow-import-to=re --nofollow-import-to=matplotlib --follow-imports
#
pd.set_option('display.max_rows', 500)
pd.set_option('display.max_columns', 500)
pd.set_option('display.width', 1000)

import matplotlib
matplotlib.use('Agg')
import matplotlib.pyplot as plt

graph_lag=graph_hp=graph_cpam='n'
router_mode='3'
topos_names=['co008_016']

########################################################################
# Globals
########################################################################

ipran_tx = ["LAG_Y","HAIRPIN_Y","SDH","RADIO","DWDM","CDWM","FO",""]
version_script="21"

########################################################################
# Program
########################################################################

db = MySQLdb.connect(host="127.0.0.1", port=3306, user="viewRT", passwd="viewRT", db="racktables")

posProg 	= 0
posTopo 	= 1
posMode		= 2
posFormat	= 3
posAlgo		= 4
posDirect	= 5
posLine		= 6
posAggreg	= 7
posLag		= 8
posNodeSep  = 9
posRankSep  = 10
#posHairPin	= 9
#posCPAM	= 10

# Check for inline parameters
if len(sys.argv)<9:
	print "Not enough paramteres. Quitting..."
	sys.exit(-1)

# Getting topo and routers name
input_string = sys.argv[posTopo]
input_string = input_string.upper()
input_string = input_string.split(",")

# We distinguis if we have topos and/or routers
topos_names,routers_names = fnc_input_string_type(input_string)
#print "topos: ",len(topos_names),"; routers: ",len(routers_names)

# In this list wwe'll save the object_id's
object_list            = []
object_list_topos      = []
object_list_routers    = []
df_object_list_topos   = pd.DataFrame()
df_object_list_routers = pd.DataFrame()

#==================================================================
#==================================================================
# query10 obtains the IDs of routers that have tag 'input_string'
# The result is the following.
if len(topos_names) > 0:

	# query0 obtains the Id of the tag_name.
	query0 = fnc_build_query_topo_id(topos_names)
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


# We add together topos and routers
#object_list    = object_list_topos + object_list_routers
df_object_list = pd.concat([df_object_list_topos,df_object_list_routers])


#==================================================================
#==================================================================
# query25 obtains the interfaces and IP addresses.
query25=fnc_build_query_interfaces(df_object_list)
df_object_interfaces = pd.read_sql(query25,db)

# We grab routers with their system or loopback interfaces.
df_system = df_object_interfaces[(df_object_interfaces.intName == 'system') | (df_object_interfaces.intName == 'System') | (df_object_interfaces.intName == 'loopback')]
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

# We merge both connections and interfaces so we stay with informatino of routers with proper tag
dfConnFinal = pd.merge(dfConn,     df_object_interfaces, left_on=['name1','intName1'], right_on=['name','intName'], how='left')
dfConnFinal = pd.merge(dfConnFinal,df_object_interfaces, left_on=['name2','intName2'], right_on=['name','intName'], how='left')
dfConnFinal = dfConnFinal.fillna("N/A")
dfConnFinal = dfConnFinal[["name1","port1","cable","port2","name2","obj1type","obj2type","port1Speed","port2Speed","intName1","intName2","ip_x","ip_y"]]
dfConnFinal = dfConnFinal.drop_duplicates()

#==================================================================
#==================================================================
# query15 obtains the attributes of the routers whose Ids are in 'object_list'
# It also brings de system IP of the router.
query15 = fnc_build_query_attributes(df_object_list)
df_attr_list = pd.read_sql(query15, db)
df_attr_list = df_attr_list.apply(setFinalValue, axis=1)
df_attr_list = df_attr_list.drop(['string_value','dict_value'], axis=1)
df_attr_list = df_attr_list.pivot(index='name',columns='attrName',values='finalValue').reset_index()
df_attr_list.columns = [x.replace(" ","") for x in df_attr_list.columns]
df_attr_list = pd.merge(df_system, df_attr_list, on=['name'], how='left')
df_attr_list = df_attr_list.fillna("N/A")

#==================================================================
#==================================================================
# BUild global dictionaries
global_dict = fnc_add_atributes_to_dic_global(df_attr_list)


#==================================================================
# The graph is created.
#==================================================================

# Format Dictionaries
mode_dict 		= {"0":"cluster", 	"1":"one-line",	"2":"two-lines", "3":"only-names", "4":"networkx"}
format_dict 	= {"0":"png", 		"1":"svg"}
algo_dict 		= {"0":"dot", 		"1":"fdp",		"2":"circo",	"3":"twopi",	"4":"neato"}
rankdir_dict 	= {"0":"LR",		"1":"TB"}
lines_dict 		= {"0":"line",		"1":"true",		"2":"ortho",	"3":"polyline"}
aggr_dict 		= {"0":"n",			"1":"y"}
lag_dict		= {"0":"n",			"1":"y"}
port_dict 		= {}

router_mode 		= sys.argv[posMode]
output_format 		= sys.argv[posFormat]
output_algo			= sys.argv[posAlgo]
output_direction	= sys.argv[posDirect]
output_line			= sys.argv[posLine]
aggregator			= sys.argv[posAggreg]
graph_lag			= lag_dict[sys.argv[posLag]]
nodesep				= sys.argv[posNodeSep]
ranksep				= sys.argv[posRankSep]
graph_hp			= "n"
graph_cpam			= "n"

#==================================================================
#==================================================================
# At this instance of the run, the list object_connections[] only has
# connections to routers that do have the requested tag.
# The function fnc_edge_list(.) reorders that information
# so it will be easier to feed graphviz.
edges   = fnc_edge_list(dfConnFinal, graph_lag, graph_hp, graph_cpam, router_mode)
routers = fnc_node_list(dfConnFinal, global_dict, router_mode, graph_hp, graph_lag, graph_cpam)

#===================================================================
#===================================================================
#print "router-mode ", router_mode, "format ", format_dict[output_format], "algo ", algo_dict[output_algo], sys.argv[posNodeSep], sys.argv[posRankSep]
# Gegin the plot
if router_mode  in ['0','1','2','3']:

	g0 = gv.Graph(format=format_dict[output_format], engine=algo_dict[output_algo])

	topo = '\"'+fnc_build_topo_name(input_string)+'\"'

	g0.body.append('label='  +topo)
	g0.body.append('rankdir='+rankdir_dict[output_direction])
	g0.body.append('splines='+lines_dict[output_line])

	g0.node_attr['style']     = 'filled'
	g0.node_attr['fixedsize'] = 'false'
	g0.node_attr['fontsize']  = '9'

	if router_mode == '0':

		g0.node_attr['shape'] = 'box'
		labelType="labelHtml"

	elif router_mode in ['1','2','3']:

		g0.body.append('overlap=false')
		g0.body.append('nodesep='+nodesep)
		g0.body.append('ranksep='+ranksep)

		g0.node_attr['shape']   = 'Mrecord'
		g0.node_attr['overlap'] = 'false'

		labelType="labelText"

	i = 1

	for router in routers:

		# Variables
		router_name     = router[0][0]
		router_function = get_attribute(router_name,"HWfunction",global_dict)
		router_int      = get_attribute(router_name,"Integrado",global_dict)
		router_ckt_id   = get_attribute(router_name,"ckt_id",global_dict)
		router_color    = get_attribute(router_name,"color",global_dict)['graphviz']
		router_label    = fnc_router_metadata(global_dict,router_name, labelType, router_function, router_ckt_id, router_mode)

		if router_mode == '0':

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
				node_id = port[1]
				if ":" in node_id: 
					node_id = node_id.replace(":","#")
				port_id = port[2]['label']
				c.node(node_id,label=port_id)

			g0.subgraph(c)

		elif router_mode in ['1','2','3']:

			# Parametrization
			struct_name = "struct"+str(i)

			# Color depending on function in network
			color = 'fillcolor='+router_color

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

			g0.body.append(struct_name+node_string)			

		i=i+1

	for e in edges:

		if router_mode == '0':

			edgeA = e[0][0]
			if ":" in edgeA:
				edgeA = edgeA.replace(":","#")
			edgeB = e[0][1]
			if ":" in edgeB:
				edgeB = edgeB.replace(":","#")
			edgeLabel = e[1]['label']
			edgeSpeed = e[2]

		elif router_mode in ['1','2','3']:

			tempA = e[0][0]
			if ":" in tempA:
				tempA=tempA.replace(":","#")
			tempA = port_dict[tempA].split("_")

			if router_mode =="3":
				edgeA = "struct"+tempA[0]
			else:
				edgeA = "struct"+tempA[0]+":f"+tempA[1]

			tempB = e[0][1]
			if ":" in tempB:
				tempB=tempB.replace(":","#")
			tempB = port_dict[tempB].split("_")

			if router_mode == "3":
				edgeB = "struct"+tempB[0]
			else:
				edgeB = "struct"+tempB[0]+":f"+tempB[1]

			edgeLabel=e[1]['label']
			edgeSpeed=e[2]

		g0.edge_attr['fontsize']='9'
		g0.edge(edgeA,edgeB,label=edgeLabel, color=fnc_edge_format(edgeLabel, "color", edgeSpeed), penwidth=fnc_edge_format(edgeLabel,"width",edgeSpeed))


	if len(topos_names) > 0:
		filename=fnc_build_filename(topos_names)
	elif len(routers_names) > 0:
		filename=fnc_build_filename("ROUTERS")

	#print filename + "." + format_dict[output_format]
	print filename
	g0.render(filename)

	# We now build the graphml
	if router_mode in ['1','2','3']:
		edges   = fnc_edge_list(dfConnFinal, graph_lag, graph_hp, graph_cpam, '4')
		routers = fnc_node_list(dfConnFinal, global_dict, '4', graph_hp, graph_lag, graph_cpam)

	fnc_build_graphml(routers,edges,global_dict,router_mode,filename)
