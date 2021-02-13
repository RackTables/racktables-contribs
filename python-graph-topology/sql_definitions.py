# Function that builds the SQL query to obtain the object Id based on name
def fnc_build_query_objetos_name(vector):
	query1 = ""
	query2 = ""
	len_vector = len(vector)
	i = 1

	query1 = (
		"select name,id from Object where "
		"name = '"
	)

	for row in vector:
		if i < len_vector:
			query2 = query2 + row + "' or name like '"
		else:
			query2 = query2 + row + "'"
		i = i + 1

	query = query1 + query2 + ";"

	return query

# Function that builds the SQL query to obtain the object Id based
# on input_string
def fnc_build_query_objetos(vector):
	query1=""
	query2=""
	len_vector = len(vector)
	i=1

	query1=(
		"select "
		"OBJ.name as name, "
		"TS.entity_id as id "
		"from "
		"TagStorage as TS join Object as OBJ on (TS.entity_id=OBJ.id) "
		"where entity_realm ='object' and ("
	)

	for row in vector:
		obj_id=str(row[0])
		if i < len_vector:
			query2 = query2 + "tag_id = " + obj_id + " or "
		else:
			query2 = query2 + "tag_id = " + obj_id + ");"
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

# Function that obtains the attributes values per object ID
# It also brings the system IP of the router.
def fnc_build_query_attributes(df):

	query1     = ""
	query2     = ""
	len_vector = len(df)

	query1=(
	"select "
	"ob.name, "
	"a.name as attrName, "
	"av.string_value, "
	"d.dict_value "

	"from Object as ob "
	"join AttributeValue as av on (ob.id=av.object_id) "
	"join Attribute as a on (av.attr_id=a.id) "
	"left join Dictionary as d on (d.dict_key=av.uint_value) "
	"where (a.name = 'Int_Status' or a.name = 'Int_Type' or a.name = 'HW type' or a.name = 'HW function' or a.name = 'Int_LAT_LON' or a.name = 'TxType_CKT_ID' or a.name like '%Ref%') "
	"and ("
	)
	i=1
	for row in df.itertuples():
		obj_id = str(row.id)
		if i < len_vector:
			query2 = query2 + "ob.id = " + obj_id + " or "
		else:
			query2 = query2 + "ob.id = " + obj_id
		i=i+1

	query = query1 + query2 + ")"

	return query

# Function that builds a SQL query to obtain the IP interfaces
def fnc_build_query_interfaces(df):
	query1=""
	query2=""
	len_vector = len(df)
	i=1

	query1=(
	"SELECT "
	"ro1.name AS name, "
	"INET_NTOA(ip4.ip) as ip, "
	"ip4.name as intName "

	"FROM "
	"Object AS ro1 "
	"JOIN IPv4Allocation AS ip4 ON (ip4.object_id=ro1.id) "
	"WHERE ("
	)
	for row in df.itertuples():
		obj_id=str(row.id)
		if i < len_vector:
			query2 = query2 + "ro1.id = " + obj_id + " or "
		else:
			query2 = query2 + "ro1.id = " + obj_id
		i=i+1

	query = query1 + query2 + ")"

	return query

# Function that builds a SQL query to obtain the connections
# among routers.
def fnc_build_query_connections(df):

	query1=""
	query2=""
	len_vector = len(df)
	i=1

	query1=(
		"SELECT "
		"ro1.name AS name1, "
		"p1.name AS port1, "
		"Link.cable, "
		"p2.name AS port2, "
		"ro2.name AS name2, "
		"d1.dict_value AS obj1type, "
		"d2.dict_value AS obj2type, "
		"poia.oif_name as port1Speed, "
		"poib.oif_name as port2Speed, "
		"p1.label as intName1, "
		"p2.label as intName2 "
		"FROM Object AS ro1 "
		"JOIN Port AS p1 ON(ro1.id=p1.object_id) "
		"JOIN Link ON(p1.id=Link.porta) "
		"JOIN Port AS p2 ON(Link.portb=p2.id) "
		"JOIN Object AS ro2 ON(p2.object_id=ro2.id) "
		"JOIN PortOuterInterface AS poia ON(poia.id=p1.type) "
		"JOIN PortOuterInterface AS poib ON(poib.id=p2.type) "
		"LEFT JOIN Dictionary AS d1 ON(ro1.objtype_id=d1.dict_key) "
		"LEFT JOIN Dictionary AS d2 ON(ro2.objtype_id=d2.dict_key) "
		"WHERE ("
	)
	for row in df.itertuples():
		obj_id=str(row.id)
		if i < len_vector:
			query2 = query2 + "ro1.id = " + obj_id + " or ro2.id = " + obj_id + " or "
		else:
			query2 = query2 + "ro1.id = " + obj_id + " or ro2.id = " + obj_id
		i=i+1

	query = query1 + query2 + ")"

	return query