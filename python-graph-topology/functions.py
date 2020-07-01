########################################################################
# Functions
########################################################################

from operator import itemgetter
from itertools import groupby
import re
import datetime
import networkx as nx
import pandas as pd 
import pyyed

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

	if attrName in ['Integrado','HWtype','Sync_Ref_Order','HWfunction','Int_Type']:
		finalValue = dict_value
	elif attrName in ['Sync_Ref1','Sync_Ref2',]:
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
def fnc_build_filename(vector):
	info = fnc_chains_ring(vector)
	len_vector= len(vector)

	#print info

	if len_vector == 1:

		tipoTopo  = info[0][0]
		agregador = info[0][1]
		topologia = info[0][2]

		topoName1 = "Topologia "
		topoName2 = tipoTopo
		topoName3 = " - MR LR - "
		topoName4 = topologia
		filename  = "pix/topo/" + agregador + "/" + topoName1 + topoName2 + topoName3 + topoName4

	elif len_vector >1:

		tipoTopo  = "-".join(list(set([name[0] for name in info])))
		agregador = "-".join(list(set([name[1] for name in info])))
		topologia = "-".join(list(set([name[2] for name in info])))

		topoName1 = "Topologia "
		topoName2 = tipoTopo
		topoName3 = " - MR LR - "
		topoName4 = topologia
		filename  = "pix/topo/" + agregador + "/" + topoName1 + topoName2 + topoName3 + topoName4

	#filename=filename + "_" + version_script + "_" +now.strftime("%Y-%m-%d")+".dot"
	return filename + ".dot"


# Function that returns whether we have a input_string or router_name
def fnc_input_string_type(vector):

	listTopo   = []
	listRouter = []

	for name in vector:
		if name[:2] == 'R:':
			listRouter.append(name[2:])
		else:
			listTopo.append(name)

	return(listTopo,listRouter)



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

	print strSpeed

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
def fnc_add_atributes_to_dic_global(df):
	#                   name HWfunction            HWtype Integrado Sync_Ref1 Sync_Ref2      Sync_Ref_Order
	# 0     C1026_SF270_SARX        NaN    ALU%GPASS%SARX        si     1/2/6     1/3/6  ref1 ref2 external
	# 1     C1406_ND270_SARX        NaN    ALU%GPASS%SARX        si     1/3/7     1/2/7  ref2 ref1 external
	# 2     C1708_ND470_SARX        NaN    ALU%GPASS%SARX        si     1/2/7     1/3/6  ref2 ref1 external
	# 3     C2155_SND70_SARA        NaN    ALU%GPASS%SARA        si     1/1/5     1/1/6  ref1 ref2 external
	# 4     C3110_CTG70_SARM        NaN    ALU%GPASS%SARM       NaN     1/1/2     1/1/1  ref1 ref2 external
	# 5    CF145_TGR70_SAR28        NaN   ALU%GPASS%SAR28        no     1/1/5     1/1/4  ref1 ref2 external
	# 6     CF145_TGR70_SAR8        NaN    ALU%GPASS%SAR8        si       NaN       NaN                 NaN
	# 7     CF181_R2770_SARA        NaN    ALU%GPASS%SARA        si     1/1/5     1/1/6  ref1 ref2 external
	# 8     CF352_GR270_SARX        NaN    ALU%GPASS%SARX        si     1/3/7     1/2/6  ref1 ref2 external
	# 9   CFR17_TOT70_IXR-R6    Mid-Ran  ALU%GPASS%IXR-R6        si     3/1/1     3/2/1  ref1 ref2 bits ptp
	# 10  CFR17_TOT71_IXR-R6    Mid-Ran  ALU%GPASS%IXR-R6        si     3/2/1     3/1/1  ref1 ref2 bits ptp
	# 11   TC3067_HMT70_SARX        NaN    ALU%GPASS%SARX        si     1/2/6     1/3/6  ref1 ref2 external

	dict_global = {}

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

	if 'Integrado' not in df.columns:
		df['Integrado'] = "N/A"

	if "Int_Type" not in df.columns:
		df['Int_Type'] = "N/A"

	#print df

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

		Integrado      = attrib.Integrado
		Sync_Ref1      = attrib.Sync_Ref1
		Sync_Ref2      = attrib.Sync_Ref2
		Sync_Ref_Order = attrib.Sync_Ref_Order
		system         = attrib.ip
		Int_Type       = attrib.Int_Type

		dict_global[router_name] = {}
		dict_global[router_name]['system']         = system
		dict_global[router_name]['HWfunction']     = HWfunction
		dict_global[router_name]['Integrado']      = Integrado
		dict_global[router_name]['Sync_Ref1']      = Sync_Ref1
		dict_global[router_name]['Sync_Ref2']      = Sync_Ref2
		dict_global[router_name]['Sync_Ref_Order'] = Sync_Ref_Order
		dict_global[router_name]['Int_Type']       = Int_Type 
		dict_global[router_name]['color']          = fnc_router_color(HWfunction, Integrado, Int_Type)
		dict_global[router_name]['HWtype']         = HWtype
		dict_global[router_name]['weight']         = fnc_router_weight(HWtype)

		if Sync_Ref1: 
			dict_global[router_name][Sync_Ref1] = 'Sync_Ref1'
		if Sync_Ref2:
			dict_global[router_name][Sync_Ref2] = 'Sync_Ref2'

	return dict_global

# Function that creates a list which holds each router and all its ports.
# Input: object_connections
# [('Router1', '1/1/1'), ('Router1', '1/1/2')]
# Output
# ('Router1/1/1', {'label': '1/1/1'})
# The output of this function is used as input to graphviz
def fnc_node_list(df, global_dict, router_mode, graph_hp, graph_lag, graph_cpam):

	temp_list=[]

	subset = df[['name1','port1','name2','port2','cable','port1Speed','port2Speed','ip_x','ip_y']]

	for row in subset.itertuples():

		cableID = row.cable
		if not cableID: cableID = ""

		if ("LAG" in cableID.upper() and graph_lag=="n") or ("HAIRPIN" in cableID.upper() and graph_hp=="n") or ("CPAM" in cableID.upper() and graph_cpam=="n"):
			pass 
		else:

			routerA    = row.name1
			portA      = row.port1
			portAspeed = fnc_port_speed(row.port1Speed)
			portAtype  = fnc_port_sfp(row.port1Speed)
			ipA        = row.ip_x
			if not ipA:	ipA="N/A"
			nodeA      = routerA + "_" + portA
			ref1a      = get_attribute(routerA,portA,global_dict)

			routerB    = row.name2
			portB      = row.port2
			portBspeed = fnc_port_speed(row.port2Speed)
			portBtype  = fnc_port_sfp(row.port2Speed)
			ipB        = row.ip_y
			if not ipB: ipB="N/A"
			nodeB      = routerB + "_" + portB
			ref1b      = get_attribute(routerB,portB,global_dict)

			if router_mode == "0":
				labelA="<"+ portA + "<BR />" + portAtype + "-" +  portAspeed + "<BR />" + ipA + "<BR />" + ref1a +">"
				labelB="<"+ portB + "<BR />" + portBtype + "-" +  portBspeed + "<BR />" + ipB + "<BR />" + ref1b +">"
			elif router_mode in ["1","2"]:
				labelA= portA + "&#92;n" + portAtype + "-" + portAspeed + "&#92;n" + ipA + "&#92;n" + ref1a
				labelB= portB + "&#92;n" + portBtype + "-" + portBspeed + "&#92;n" + ipB + "&#92;n" + ref1b
			elif router_mode in ["3","4"]:
				labelA=""
				labelB=""

			temp_list.append((routerA,nodeA,{'label':labelA}))
			temp_list.append((routerB,nodeB,{'label':labelB}))

	# List will be ordered and sorted always by the first field
	# which is the router name
	if router_mode in ['0','1','2','3']:
		lista_sorted  = sorted(temp_list, key=itemgetter(0))
		lista_grouped = groupby(lista_sorted, key=itemgetter(0))

		a = []
		for i,rou in enumerate(lista_grouped):
			a.append(list(rou[1]))

		return a

	elif router_mode in ['4']:
		return temp_list

# Function that builds a list which holds the connections among routers.
# The output of this function is used as input to graphviz
def fnc_edge_list(df, graph_lag, graph_hp, graph_cpam, router_mode):

	subset = df[['name1','port1','name2','port2','cable','port1Speed','port2Speed','intName1','intName2']]

	edge_list=[]

	for row in subset.itertuples():
		
		routerA    = row.name1
		portA      = row.port1
		routerB    = row.name2
		portB      = row.port2
		cableID    = row.cable
		portAspeed = fnc_port_speed(row.port1Speed)
		portBspeed = fnc_port_speed(row.port2Speed)
		intName1   = row.intName1
		intName2   = row.intName2

		nodeA = routerA + "_" + portA
		nodeB = routerB + "_" + portB

		if not cableID or cableID == "N/A": cableID = ""

		if ("LAG" in cableID.upper() and graph_lag=="n") or ("HAIRPIN" in cableID.upper() and graph_hp=="n") or ("CPAM" in cableID.upper() and graph_cpam=="n"):
			pass
		else:
			if router_mode in ['0','1','2','3']:
				edge_list.append( ( (nodeA,nodeB),{'label':cableID},(portAspeed,portBspeed),(intName1,intName2) ) )
			elif router_mode in ['4']:
				edge_list.append( (routerA,routerB,cableID) )

	return edge_list

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
def fnc_router_color(HWfunction, intStatus, Int_Type):

	# Color depending on function
	functionDict = {
		'High-Ran':		{'yEd':'#3399ff','graphviz':'lightblue4'},
		'High-Ran-ABR':	{'yEd':'#3399ff','graphviz':'lightblue4'},
		'Mid-Ran':		{'yEd':'#ff99cc','graphviz':'lightpink4'},
		'AggEthernet':	{'yEd':'#33ff33','graphviz':'limegreen'},
		'FronteraPE':	{'yEd':'#cccc00','graphviz':'darkgoldenrod'},
		'TX':			{'yEd':'#ffff00','graphviz':'yellow'},
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

	color = {'yEd':'#c0c0c0','graphviz':'grey'}

	####

	if intStatus == 'no':
		color = intStatusDict.get(intStatus,color)

	elif intStatus == 'si':

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


def fnc_build_graphml(routers,edges,global_dict,router_mode,filename):

	if router_mode in ['1','2','3','4']:

		G = pyyed.Graph()

		nodes = pd.DataFrame(routers)

		nodes.columns=['router_name','port','label']
		nodes = nodes[['router_name']]
		nodes = nodes.drop_duplicates()

		links = pd.DataFrame(edges)
		links.columns=['routerA','routerB','cableID']		

		for router in nodes.itertuples():
			router_name   	= router.router_name
			router_function = get_attribute(router_name,"HWfunction",global_dict)
			router_int      = get_attribute(router_name,"Integrado",global_dict)
			router_ckt_id   = get_attribute(router_name,"ckt_id",global_dict)
			router_color    = get_attribute(router_name,"color",global_dict)['yEd']
			router_label    = fnc_router_metadata(global_dict, router_name, 'labelHtml', router_function, router_ckt_id, router_mode)
			router_system   = get_attribute(router_name,"system",global_dict)

			G.add_node(router_name,label=router_name + "\n" + router_system, shape_fill=router_color)

		for link in links.itertuples():
			edgeLabel = link.cableID
			G.add_edge(link.routerA,link.routerB, arrowhead='none',arrowfoot='none',label=edgeLabel)

		G.write_graph(filename+'.graphml')			


	elif router_mode in ['0']:

		G = pyyed.Graph()
		
		for router in routers:

			# Variables
			router_name     = router[0][0]
			router_function = get_attribute(router_name,"HWfunction",global_dict)
			router_int      = get_attribute(router_name,"Integrado",global_dict)
			router_ckt_id   = get_attribute(router_name,"ckt_id",global_dict)
			router_color    = get_attribute(router_name,"color",global_dict)['yEd']
			router_label    = fnc_router_metadata(global_dict, router_name, 'labelHtml', router_function, router_ckt_id, router_mode)
			router_system   = get_attribute(router_name,"system",global_dict)

			grupo = G.add_group(router_name,label=router_name + "\n" + router_system, fill=router_color)

			for port in router:
				node_id = port[1]
				port_id = port[2]['label']
				port_id = port_id.replace('<BR />','\n')
				port_id = port_id.replace('<','')
				port_id = port_id.replace('>','')
				grupo.add_node(node_id, label=port_id)

		for e in edges:

			edgeA     = e[0][0]
			edgeB     = e[0][1]
			edgeLabel = e[1]['label']

			G.add_edge(edgeA,edgeB, arrowhead='none',arrowfoot='none',label=edgeLabel)

		G.write_graph(filename+'.graphml')
