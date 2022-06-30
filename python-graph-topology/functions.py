########################################################################
# Functions
########################################################################

from operator import itemgetter
from itertools import groupby
import re
import datetime
import networkx as nx
import graphviz as gv
import pandas as pd 
import pyyed
import sys
from pathlib import Path

# This function converts M,G,T capacity into Kbps
# If no unit, return -1
# If problems, return 0
def fnc_aux_convert_speed(capacity, outUnit=None):

	dictUnit = {'K':1E3, 'M':1E6, 'G':1E9, 'T':1E12}

	if capacity == 'vPort':
		unit  = "G"
		value = 20
	else:
		unit  = str(capacity[-1:])
		value = int(capacity[:-1])
	

	if outUnit not in dictUnit.keys() or unit not in dictUnit.keys():
		return 0
	else:
		value = value*dictUnit[unit] / dictUnit[outUnit]

	return value

def get_attribute(key, attribute, global_dict):
  try:
    return global_dict[key][attribute]
  except:
    return "N/A"

def setFinalValue(row):
	
	rtrName 	 = row['name']
	attrName     = row['attrName'].replace(" ","")
	dict_value   = row['dict_value']
	string_value = row['string_value']
	finalValue   = 'N/A'

	if attrName in ['Int_Status','HWtype','Sync_Ref_Order','HWfunction','Int_Type']:
		finalValue = dict_value
	elif attrName in ['Sync_Ref1','Sync_Ref2','Int_LAT_LON']:
		finalValue = string_value
	else:
		finalValue = "N/A"
	row['finalValue'] = finalValue
	
	return row

def fnc_chains_ring(vector):
	topoString = ""
	tempList   = []
	len_vector = len(vector)
	i=1

	regexp = {
	    'Anillo':        re.compile(r'^(\w{5})_(\d{3})$'),
	    'Anillo_TX':     re.compile(r'^(\w{5})_(\d{3})_(\D{1,6})$'),
	    'Cadena':        re.compile(r'^(\w{5}_\w{5})_(\d{3})$'),
	    'Cadena_Radial': re.compile(r'^(\w{5}_\w{5})_(\D{1,6})$'),
	    'Cadena_TX':     re.compile(r'^(\w{5}_\w{5})_(\d{3})_(\D{1,6})$'),
	    'Area':          re.compile(r'^(0.0.\d{1}.\d{1,2})$'),
	    'Region':        re.compile(r'(.*0.0.\d{1}.\d{1,2}.+)'),
		'General':       re.compile(r'(.*)')
	    # ...
	}

	# We can have many topos inside vector
	for topo in vector:

		for key, pattern in regexp.items():
			match = pattern.match(topo)
			if match:
				name   = match.groups()[0]
				tot    = match.group()
				tempList.append( (key, name, tot) )

	return tempList

# Function that builds the filename
def fnc_build_filename(topos_names,routers_names,attrib_names):
	
	prefixTopo = "".join(topos_names)
	prefixRtrs = "".join(routers_names)
	prefixAttr = "".join(attrib_names)

	filename     = prefixTopo + prefixRtrs + prefixAttr

	Path("plugins/topoGen/topos/").mkdir(parents=True, exist_ok=True)

	return "plugins/topoGen/topos/" + filename

# Function that returns whether we have a input_string or router_name
def fnc_input_string_type(vector):

	listTopo   = []
	listRouter = []
	listAttrib = []

	for name in vector:
		if name[:2] in ['r:','R:']:
			listRouter.append(name[2:].upper())
		elif name[:3] in ['av:','AV:']:
			listAttrib.append(name[3:])
		else:
			listTopo.append(name.upper())

	return(listTopo,listRouter,listAttrib)

# Funcion que organiza los puertos de cada nodo
def fnc_port_list(routers):
	router_list=[]
	port_list=[]
	for router in routers:
		#print router[0]
		router_id = router[0][0]
		for port in router:
			port_list.append(port[1])
		router_list.append((router_id,port_list))
		port_list=[]

	return router_list

# Function that returns de speed of the port
def fnc_port_speed(port_string):

	speed = port_string.split('B')[0]
	
	speedDict = {
		"100"  :"100Mb",
		"1000" :"1G",
		"10G"  :"10G",
		"100G" :"100G",

		"SFP-CES" :"n/ETH",
		"SFP-ATM" :"n/ETH",
		"SFP-ASAP":"n/ETH",
		
		"empty SFP-1000":"n/SFP",
		"virtual port"  :"vPort",		
	}

	return speedDict.get(speed,"N/A")

# Function to compare speeds for egressRate in vPorts
def fnc_speed_compare(strSpeed):

	#print strSpeed

	speed = strSpeed.split(":")[0]
	unit  = speed[-1:]
	value = float(speed[:-1])
	if unit == "G":
		return value
	elif unit == "M":
		return float(value/1000)

# Function that returns the SFP type
def fnc_port_sfp(port_string):

	if port_string == "virtual port":
		return "LAG"
	elif port_string == "spoke-sdp":
		return "VLL"
	elif port_string == "SFP-CES" or port_string == "SFP-ATM" or port_string == "SFP-ASAP":
		return port_string.split('-')[1]
	else:
		sfp_type = port_string.split('-')
		sfp_type = sfp_type[1]
		return sfp_type

# Function that creates a dictionary with global attributes
def fnc_add_atributes_to_dic_global(df_systems, df):
	#                   name HWfunction            HWtype Integrado Sync_Ref1 Sync_Ref2      Sync_Ref_Order
	# 0               router    NaN    ALU%GPASS%SARX        si     1/2/6     1/3/6  ref1 ref2 external
	# 1               router    NaN    ALU%GPASS%SARX        si     1/3/7     1/2/7  ref2 ref1 external
	# 2               router    NaN    ALU%GPASS%SARX        si     1/2/7     1/3/6  ref2 ref1 external
	# 3               router    NaN    ALU%GPASS%SARA        si     1/1/5     1/1/6  ref1 ref2 external
	# 4               router    NaN    ALU%GPASS%SARM       NaN     1/1/2     1/1/1  ref1 ref2 external
	# 5               router    NaN   ALU%GPASS%SAR28        no     1/1/5     1/1/4  ref1 ref2 external
	# 6               router    NaN    ALU%GPASS%SAR8        si       NaN       NaN                 NaN
	# 7               router    NaN    ALU%GPASS%SARA        si     1/1/5     1/1/6  ref1 ref2 external
	# 8               router    NaN    ALU%GPASS%SARX        si     1/3/7     1/2/6  ref1 ref2 external
	# 9               router  Mid-Ran  ALU%GPASS%IXR-R6        si     3/1/1     3/2/1  ref1 ref2 bits ptp
	# 10              router  Mid-Ran  ALU%GPASS%IXR-R6        si     3/2/1     3/1/1  ref1 ref2 bits ptp
	# 11              router    NaN    ALU%GPASS%SARX        si     1/2/6     1/3/6  ref1 ref2 external

	dict_global = {}

	# Default dictionary for all items ...
	for system in df_systems.itertuples():
		router_name = system.name
		dict_global[router_name] = {}
		dict_global[router_name]['system']         = "N/A" 
		dict_global[router_name]['HWfunction']     = "N/A"
		dict_global[router_name]['Int_Status']     = "N/A"
		dict_global[router_name]['Sync_Ref1']      = "N/A"
		dict_global[router_name]['Sync_Ref2']      = "N/A"
		dict_global[router_name]['Sync_Ref_Order'] = "N/A"
		dict_global[router_name]['Int_Type']       = "N/A"
		dict_global[router_name]['color']          = {'yEd':'#c0c0c0','graphviz':'grey'}
		dict_global[router_name]['HWtype']         = "N/A"
		dict_global[router_name]['weight']         = "N/A"
		dict_global[router_name]['lat']            = "0"
		dict_global[router_name]['lon']            = "0"		

	# We verify if columns exist in DF ... not sure if this is needed.
	if 'HWfunction' not in df.columns:
		df['HWfunction'] = "N/A"

	if 'HWtype' not in df.columns:
		df['HWtype'] = "N/A"

	if 'Sync_Ref1' not in df.columns:
		df['Sync_Ref1'] = "N/A"

	if 'Sync_Ref2' not in df.columns:
		df['Sync_Ref2'] = "N/A"

	if 'Sync_Ref_Order' not in df.columns:
		df['Sync_Ref_Order'] = "N/A"

	if 'Int_Status' not in df.columns:
		df['Int_Status'] = "N/A"

	if "Int_Type" not in df.columns:
		df['Int_Type'] = "N/A"

	if "Int_LAT_LON" not in df.columns:
		df['Int_LAT_LON'] = "0:0"

	for attrib in df.itertuples():

		router_name    = attrib.name
		HWfunction     = attrib.HWfunction
		HWtype         = attrib.HWtype

		if HWtype != "N/A":
			try:
				HWtype = attrib.HWtype.split("%")[2]
			except:
				try:
					HWtype = router_name.split("_")[2]
				except:
					HWtype = "N/A"

		Int_Status     = attrib.Int_Status
		Sync_Ref1      = attrib.Sync_Ref1
		Sync_Ref2      = attrib.Sync_Ref2
		Sync_Ref_Order = attrib.Sync_Ref_Order
		system         = attrib.ip
		Int_Type       = attrib.Int_Type

		Int_LAT_LON    = attrib.Int_LAT_LON

		if Int_LAT_LON != "N/A":
			Int_LAT_LON = Int_LAT_LON.replace(" ","")
		else:
			Int_LAT_LON = "0:0"

		#dict_global[router_name] = {}
		dict_global[router_name]['system']         = system
		dict_global[router_name]['HWfunction']     = HWfunction
		dict_global[router_name]['Int_Status']     = Int_Status
		dict_global[router_name]['Sync_Ref1']      = Sync_Ref1
		dict_global[router_name]['Sync_Ref2']      = Sync_Ref2
		dict_global[router_name]['Sync_Ref_Order'] = Sync_Ref_Order
		dict_global[router_name]['Int_Type']       = Int_Type 
		dict_global[router_name]['color']          = fnc_router_color(HWfunction, Int_Status, Int_Type)
		dict_global[router_name]['HWtype']         = HWtype
		dict_global[router_name]['weight']         = fnc_router_weight(HWtype)
		dict_global[router_name]['lat']            = Int_LAT_LON.split(":")[0]
		dict_global[router_name]['lon']            = Int_LAT_LON.split(":")[1]

		if Sync_Ref1: 
			dict_global[router_name][Sync_Ref1] = 'Sync_Ref1'
		if Sync_Ref2:
			dict_global[router_name][Sync_Ref2] = 'Sync_Ref2'

	return dict_global

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

def fnc_router_weight(HWtype):

	if HWtype in ['SR12','SR12e','SR7','SRa8','SRa4']:
		return 30
	elif HWtype in ['IXR-R6']:
		return 15
	else:
		return 1

# Function that returns the color of the router depending on its
# situation
def fnc_router_color(HWfunction, Int_Status, Int_Type):

	# Color depending on function
	functionDict = {
		'High-Ran':		{'yEd':'#3399ff','graphviz':'lightblue4'},
		'High-Ran-ABR':	{'yEd':'#3399ff','graphviz':'lightblue4'},
		'Mid-Ran':		{'yEd':'#ff99cc','graphviz':'lightpink4'},
		'AggEthernet':	{'yEd':'#33ff33','graphviz':'limegreen'},
		'FronteraPE':	{'yEd':'#cccc00','graphviz':'darkgoldenrod'},
		'TX':			{'yEd':'#ffff00','graphviz':'yellow'},
		'CSR':			{'yEd':'#99ccff','graphviz':'lightblue3'},
	}

	intStatusDict = {
		'no':{'yEd':'#ffcc00','graphviz':'orange'},
	}

	intTypeDict = {
		'swap':				{'yEd':'#66ccff','graphviz':'lightblue2'},
		'cambioArea':		{'yEd':'#808000','graphviz':'khaki'},
		'integracion':		{'yEd':'#99ccff','graphviz':'lightblue3'},
		'cambioTopologia':	{'yEd':'#ccffff','graphviz':'lightblue1'},
		'reinsercion':		{'yEd':'#ff6600','graphviz':'red'},
	}

	refOrderDict = {
		'ref1 ref2':{'yEd':'#99ccff','graphviz':'lightblue3'},
		'ref2 ref1':{'yEd':'#ccffff','graphviz':'lightblue1'}
	}

	# Default Color if nothing is matched ...
	color = {'yEd':'#c0c0c0','graphviz':'grey'}

	####

	if Int_Status == 'no':
		color = intStatusDict.get(Int_Status,color)

	elif Int_Status == 'si':

		if HWfunction != "N/A":
			color = functionDict.get(HWfunction,color)
		else:
			if Int_Type != "N/A":
				color = intTypeDict.get(Int_Type,color)
	
	return color


# Function to process label of edges
def fnc_edge_format(labelText, what, portSpeed):
	
	# LAG3:1G:DWDM:10172
	# 10G:-1:DWDM:10265
	# 296M:-1:MW
	# 3G:-1:2

	stringLen = len(labelText.split(":"))
	portSpeed = portSpeed[0]

	if portSpeed[:3] != "LAG":

		if  stringLen == 4:

			egressRate  = labelText.split(":")[0]
			metOspef	= labelText.split(":")[1]
			TxType		= labelText.split(":")[2]
			txTypeCkt	= labelText.split(":")[3]

		elif stringLen == 3:

			egressRate  = labelText.split(":")[0]
			metOspef	= labelText.split(":")[1]
			TxType		= labelText.split(":")[2]
			txTypeCkt	= "NA"

		elif stringLen == 2:

			egressRate  = labelText.split(":")[0]
			metOspef	= labelText.split(":")[1]
			TxType		= "FO"
			txTypeCkt	= "NA"

		elif stringLen == 1:

			egressRate  = portSpeed
			metOspef	= "-1"
			TxType		= "FO"
			txTypeCkt	= "NA"

		else:

			egressRate  = "1G"
			metOspef	= "-1"
			TxType		= "FO"
			txTypeCkt	= "NA"

	colorDict = {
		"CWDM" :"red",
		"DWDM" :"blue",
		"FO"   :"black",
		"SDH"  :"green",
		"MW"   :"pink",
		"ARSAT":"navy",
		"DATCO":"purple",
	}

	widthDict = {
		0.1     :"0.5",
		1       :"1.0",
		10      :"2.5",
		50      :"5.0",
		100     :"10.0",
	}

	#print("labelText: ", labelText, "what: ", what, "portSpede: ", portSpeed, "ER: ", egressRate)

	egressRate = int(fnc_aux_convert_speed(egressRate,'G'))
	egressRate = min([x for x in widthDict.keys() if egressRate <= x])

	if what == "color":
		return colorDict.get(TxType,'yellow')
	elif what == "width":
		return widthDict.get(egressRate, '50.0')

# Function that returns metadata of the router
def fnc_router_metadata(global_dict, router_name, what, router_function, router_ckt_id, router_mode):

	router_sync_order  = get_attribute(router_name, "Sync_Ref_Order", global_dict)
	router_ip          = get_attribute(router_name, "system", global_dict)

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

			if router_mode == "3":
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

			if router_mode=="3":
				router_label = router_name + "&#92;n" + router_ip
				return router_label
			else:
				router_label = router_name + "&#92;n" + router_ip + "&#92;n" + router_sync_order
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

			#print router_label

			temp_string = "{" + router_label + "}"
			temp_string = " [" + color + ",label=\"" + temp_string + "\"]"
			return temp_string

	else:

		if router_mode=="1":
			temp_string = "{" + router_label + "|" + port_string + "}"
		elif router_mode=="2":
			temp_string = "{" + router_label + "|" + "{" + port_string + "}" + "}"
		elif router_mode=="3":
			temp_string = router_label

		temp_string = " [" + color + ",label=\"" + temp_string + "\"]"
		return temp_string

def fnc_build_graphml(df_system, dfConnFinal, global_dict, router_mode, filename):

	if router_mode in ['1','2','3','4']:

		G = pyyed.Graph()

		for router in df_system.itertuples():
			router_name   	= router.name
			router_function = get_attribute(router_name,"HWfunction",global_dict)
			router_int      = get_attribute(router_name,"Int_Status",global_dict)
			router_ckt_id   = get_attribute(router_name,"ckt_id",global_dict)
			router_color    = get_attribute(router_name,"color",global_dict)['yEd']
			router_label    = fnc_router_metadata(global_dict, router_name, 'labelHtml', router_function, router_ckt_id, router_mode)
			router_system   = get_attribute(router_name,"system",global_dict)

			G.add_node(router_name, label=router_name + "\n" + router_system, shape_fill=router_color)

		for link in dfConnFinal.itertuples():
			edgeLabel = link.port1 + "--" + link.port2
			if link.cable != "N/A":
				G.add_edge(link.name1, link.name2, arrowhead='none',arrowfoot='none', label= link.cable + "\n" + edgeLabel)
			else:
				G.add_edge(link.name1, link.name2, arrowhead='none',arrowfoot='none', label= edgeLabel)

		print(filename + '.graphml')
		G.write_graph(filename + '.graphml')

def fnc_build_graphnx(df_system, dfConnFinal, global_dict, router_mode, filename):

	from pyvis.network import Network

	phyList = ['High-Ran','High-Ran-ABR','FronteraPE','Mid-Ran','AggEthernet','FronteraSDH','BBIP']

	#plt.figure(figsize=(15,15))

	#G = nx.Graph()
	G = Network("800px", "1300px", heading="")

	#G.set_edge_smooth('dynamic')

	G.set_options("""
		var options = {
			"nodes": {
				"size":13,
			  	"font": {
			    	"size": 11
			  	}
			},
			"edges": {
				"arrowStrikethrough": false,
				"color": {
				"inherit": false
				},
				"font": {
				"size": 10,
				"align": "top"
				},
				"smooth": false
			},
			"manipulation": {
				"enabled": true,
				"initiallyActive": true
			},
			"physics": {
				"barnesHut": {
					"centralGravity": 0.2,
					"springLength": 100,
					"springConstant": 0.01,
					"damping": 0.7,
					"avoidOverlap": 1
				},
			"maxVelocity": 5,
			"minVelocity": 0.47,
			"solver": "barnesHut"
			}
		}
		""")

	for i in df_system.itertuples():
		router_name   = i[1]
		id            = i[2]
		ip_system     = i[3]

		if global_dict[router_name]['HWfunction'] in phyList:
			physics = False
		else:
			physics = True

		G.add_node(router_name,
					label   = str(router_name) + "\n" + str(ip_system),
					chassis = global_dict[router_name]['HWtype'],
					ip      = str(ip_system),
					color   = get_attribute(router_name,"color",global_dict)['yEd'],
					physics = physics,
					)

	for i in dfConnFinal.itertuples():

		router_name_A = i[1]
		port_name_A   = i[2]
		ip_A          = i[12]
		ip_system_A   = global_dict[router_name_A]['system'] 

		router_name_B = i[5]
		port_name_B   = i[4]
		ip_B          = i[13]
		ip_system_B   = global_dict[router_name_B]['system']

		cable_id      = i[3]

		if global_dict[router_name_A]['HWfunction'] in phyList and global_dict[router_name_B]['HWfunction'] in phyList:
			physics = False
		else:
			physics = True

		edgeLabel = port_name_A + "--" + port_name_B

		if cable_id in ["N/A",None]:
			G.add_edge(source=router_name_A, to=router_name_B, label=edgeLabel, physics=physics)
		else:
			G.add_edge(source=router_name_A, to=router_name_B, label=cable_id + "\n" + edgeLabel, physics=physics)


	#G.barnes_hut(overlap=1)
	#G.force_atlas_2based(overlap=1)
	#G.show_buttons()
	#G.save_graph("plugins/topoGen/topo.html")

	print(filename + "_nx.html")
	G.save_graph(filename + "_nx.html")

def fnc_build_osm(global_dict, dfConnFinal, router_mode, filename):

	import json
	import geopy.distance
	import folium

	dfConn       = dfConnFinal[['name1','name2']]
	df_attribute = pd.DataFrame(global_dict).T.reset_index()

	dfConn = pd.merge(dfConn,df_attribute,left_on='name1', right_on='index')[['name1','name2','lat','lon']]
	dfConn.rename(columns={'lat':'lat1','lon':'lon1'}, inplace=True)
	dfConn = pd.merge(dfConn,df_attribute,left_on='name2', right_on='index')[['name1','name2','lat1','lon1','lat','lon']]
	dfConn.rename(columns={'lat':'lat2','lon':'lon2'}, inplace=True)
	dfConn = dfConn[['lat1','lon1','lat2','lon2']]
	dfConn['d'] = dfConn.apply(lambda x: geopy.distance.distance( (x.lat1, x.lon1), (x.lat2, x.lon2) ).km , axis=1).round(2)

	dictNodes = { x:{'name':x, 'system':global_dict[x]['system'], 'lat':global_dict[x]['lat'], 'lon':global_dict[x]['lon'] } for x in global_dict.keys() }
	dictLinks = dfConn.to_dict('index')

	### Rendering Map
	firstNode = list(dictNodes.keys())[0]
	m = folium.Map(location=[ dictNodes[firstNode]['lat'], dictNodes[firstNode]['lon']] )

	for key in dictNodes.keys():
		folium.Marker( 
	 		[dictNodes[key]['lat'],dictNodes[key]['lon']], 
	 		popup=folium.Popup("<b>" +  dictNodes[key]['name'] + "</b>\n" + dictNodes[key]['system'],sticky=True)
	 	).add_to(m)

	if router_mode == '7':

		for key in dictLinks.keys(): 
			folium.vector_layers.PolyLine( 
				[ 
					( float(dictLinks[key]['lat1']), float(dictLinks[key]['lon1']) ), 
					( float(dictLinks[key]['lat2']), float(dictLinks[key]['lon2']) ) 
				],
				popup=folium.Popup(str(dictLinks[key]['d']) + 'km', sticky=True),
			).add_to(m)

	print(filename  + '_map.html')
	m.save(filename + '_map.html')

def fnc_build_excel(df_system, dfConnFinal, global_dict, router_mode, filename):

	from pandas import ExcelWriter

	def save_xls(list_dfs, xls_path):
		with ExcelWriter(xls_path) as writer:
			for df, name in list_dfs:
				df.to_excel(writer, name)
			writer.save()

	dfSummary1 = dfConnFinal[['name1','obj1type','intName1','ip_x','port1','port1Speed','cable']]
	dfSummary1 = dfSummary1.rename(columns={'name1':'name','obj1type':'objType','intName1':'intName','ip_x':'ip','port1':'port','port1Speed':'portSpeed'})
	dfSummary2 = dfConnFinal[['name2','obj2type','intName2','ip_y','port2','port2Speed','cable']]
	dfSummary2 = dfSummary2.rename(columns={'name2':'name','obj2type':'objType','intName2':'intName','ip_y':'ip','port2':'port','port2Speed':'portSpeed'})

	dfSumm = pd.concat([dfSummary1,dfSummary2])
	dfSumm = dfSumm.sort_values(by=['name','port'])

	list_dfs = [(dfConnFinal,'Connections'),(dfSumm,'Summary'),(df_system,'system')]

	print(filename + '.xlsx')
	save_xls(list_dfs, filename + '.xlsx')