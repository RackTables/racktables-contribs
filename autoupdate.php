<?php

$dblink = mysql_connect('localhost', 'rackuser', 'rackpw');
if (!$dblink) {
        die('Could not connect: ' . mysql_error());
}

mysql_select_db("racktables",$dblink);

//Object Information Posted
if(isset($_POST['name']))
	$post_object['name']=$_POST['name'];			//char(255)
if(isset($_POST['label']))
	$post_object['label']=$_POST['label'];			//char(255)
if(isset($_POST['barcode']))
	$post_object['barcode']=$_POST['barcode'];		//char(16)
if(isset($_POST['asset_no']))
	$post_object['asset_no']=$_POST['asset_no'];		//char(64)
if(isset($_POST['has_problems']))
	$post_object['has_problems']=$_POST['has_problems'];	//enum('yes','no')
if(isset($_POST['comment']))
	$post_object['comment']=$_POST['comment'];		//text

//Attribute Map
$attribute_id['serial']=1;
$attribute_id['contact_person']=14;
$attribute_id['serial_2']=20;
$attribute_id['ram_mb']=17;
$attribute_id['cpu_mhz']=18;
$attribute_id['warranty']=22;
$attribute_id['po_number']=10001;
$attribute_id['mac']=10011;
$attribute_id['serial_baseboard']=10016;
$attribute_id['bios_release_date']=10018;
$attribute_id['bios_version']=10017;
$attribute_id['processor_manufacturer']=10022;
$attribute_id['processor_name']=10024;


$unique_name="serial_baseboard";


//Set Unique Attribute Values or exit
$attribute_unique_id=$attribute_id[$unique_name];
if(isset($_POST['name']) && isset($_POST[$unique_name])) {
	$unique_value=$_POST[$unique_name];
	$name=$_POST['name'];
}
else
	exit("no unique identifier (mac) and/or name");

//Set Authenticate Values or exit
if(isset($_POST['username']) && isset($_POST['password'])) {
	$username=$_POST['username'];
	$password=$_POST['password'];
}
else
	exit("no username and/or password");

//Object Information in Database

$object_data[]=NULL;
$object_id=NULL;
$object_name=NULL;
$master_object_id=NULL;
$master_object_name=NULL;

$object_asset=NULL;

function authenticate() {

	global $username;
	global $password;

	$passwordhash=hash('sha1',"$password");
	$result=mysql_query("select user_password_hash from UserAccount where user_name=\"$username\"");
	$row=mysql_fetch_row($result);
	if ($row[0]==$passwordhash)
		return 1;
	else
		return 0;
}

function lookup() {

	global $object_data;			//object values from database.

	global $name; 					//$_POST['name']
	
	global $object_id;				//$object_id is set after a successful object lookup
	global $object_name;			//used to lookup master object
	global $master_object_id;		//$master_object_id is set after a successfull master object lookup (ie, C6100 chassis)
	global $master_object_name;		//used for master object update where the name ties the object to the master object (ie, r5288 is tied to the master object name "r5285 r5286 r5287 r5288")
	global $unique_value;
	global $attribute_unique_id;

// select RackObject.id,RackObject.name from RackObject,AttributeValue where RackObject.id=AttributeValue.object_id AND AttributeValue.attr_id=10011 AND AttributeValue.string_value="00:26:6C:F2:56:D4"
// select RackObject.id,RackObject.name from RackObject where RackObject.name="r5288";

//if $unique_value posted
//	lookup object by unique
//		if id returned
//			if posted name exists in another object
//				exit
//			ok to update
//		else lookup by name
//			if id returned
//				lookup unique by id
//					if unique NULL
//						ok to update
//              else
//					unique different, cannot update
//		if object found
//			lookup master object


	if (isset($unique_value)) {
		$result_by_unique=mysql_query("select RackObject.id,RackObject.name,RackObject.label,RackObject.barcode,RackObject.objtype_id,RackObject.asset_no,RackObject.has_problems,RackObject.comment from RackObject,AttributeValue where RackObject.id=AttributeValue.object_id AND AttributeValue.attr_id=$attribute_unique_id AND AttributeValue.string_value=\"$unique_value\"");
		$row_by_unique=mysql_fetch_assoc($result_by_unique); //***copied to  $object_data[] global***
		if (isset($row_by_unique['id'])) {												//if object found with unique id
			$result_by_name=mysql_query("select id from RackObject where name=\"$name\"");		//lookup object by posted name
			$row_by_name=mysql_fetch_assoc($result_by_name);
			if (isset($row_by_name['id']) && $row_by_name['id']!=$row_by_unique['id'])		//if there is another record with that name
				exit("duplicate record with that name");										//exit
			$object_id=$row_by_unique['id'];
			$object_name=$row_by_unique['name'];
			$object_data=$row_by_unique;		//make a copy of the object associative array for update() to use later
			echo "found id,name by mac lookup: $row_by_unique[id] | $row_by_unique[name]\n";
		} else {
			$result_by_name=mysql_query("select RackObject.id,RackObject.name,RackObject.label,RackObject.barcode,RackObject.objtype_id,RackObject.asset_no,RackObject.has_problems,RackObject.comment from RackObject where RackObject.name=\"$name\"");
			$row_by_name=mysql_fetch_assoc($result_by_name);
			if (isset($row_by_name['id'])) {
				echo  "found id,name by name lookup: $row_by_name[id] | $row_by_name[name]\n";
				$result_by_unique=mysql_query("select RackObject.id,RackObject.name,AttributeValue.string_value from RackObject,AttributeValue where AttributeValue.attr_id=$attribute_unique_id AND RackObject.id=AttributeValue.object_id And RackObject.name=\"$name\"");
				$row_by_unique=mysql_fetch_row($result_by_unique);
				if (!isset($row_by_unique[0])) {		//ok to update if unique identifier is missing from AttributeValue. ***no unique identifier in AttributeValue will return an empty set.***
					$object_id=$row_by_name['id'];
					$object_name=$row_by_name['name'];
					$object_data=$row_by_name;		//make a copy of the object associative array for update() to use later
					echo "mac NULL. ok to update\n";
				} else {
					echo "cannot update. different mac\n";
				}
			}
		}	
	}
//Look for Master Object id and name
	if (isset($object_id)) {
		$result_like_name=mysql_query("select id, name from RackObject where name like \"$object_name %\" OR name like \"% $object_name %\" OR name like \"% $object_name\"");
		$row_like_name=mysql_fetch_assoc($result_like_name);
		$master_object_id=$row_like_name['id'];
		$master_object_name=$row_like_name['name'];
		echo "found master object_id: $master_object_id";

	}
}

function update_object($object_id) {
	global $username;
	global $object_data;
	global $post_object;
	global $attribute_id;		//Attribute id map
	$result_attributes_stored=mysql_query("select attr_id,string_value,uint_value,float_value from AttributeValue where object_id=$object_id");

	while($row=mysql_fetch_row($result_attributes_stored)) {
	if (isset($row[1]))
		$attributes_stored["$row[0]"]=$row[1];			//build index of stored attribute values, merging types string, uint, and float
	elseif (isset($row[2]))
		$attributes_stored["$row[0]"]=$row[2];
	elseif (isset($row[3]))
		$attributes_stored["$row[0]"]=$row[3];
	}

//+---------+-------------------+------------+-------------+
//| attr_id | string_value      | uint_value | float_value |
//+---------+-------------------+------------+-------------+
//|       1 | 125PNM1           |       NULL |        NULL | 
//|       2 | NULL              |          0 |        NULL | 
//|       4 | NULL              |          0 |        NULL | 
//|      14 | Mark Brice        |       NULL |        NULL | 
//|      17 | NULL              |      98994 |        NULL | 
//|      18 | NULL              |       2930 |        NULL | 
//|      22 | 2/2/13            |       NULL |        NULL | 
//|   10011 | 00:26:6C:F2:56:D4 |       NULL |        NULL | 
//+---------+-------------------+------------+-------------+


	$result_attribute_type=mysql_query("select Attribute.id,Attribute.type from Attribute,AttributeMap where AttributeMap.objtype_id=4 AND AttributeMap.attr_id=Attribute.id AND (Attribute.type=\"string\" OR Attribute.type=\"uint\")");
	while($row=mysql_fetch_row($result_attribute_type))
		$attribute_type["$row[0]"]=$row[1];					//build *attribute type* lookup map. used when we update AttributeValue
	//+-------+--------+
	//| id    | type   |
	//+-------+--------+
	//|     1 | string | 
	//|    14 | string | 
	//|    17 | uint   | 
	//|    18 | uint   | 
	//|    20 | string | 
	//|    21 | string | 
	//|    22 | string | 
	//|    24 | string | 
	//|    25 | string | 
	//| 10001 | string | 
	//| 10011 | string | 
	//+-------+--------+

//Update RackObject Attributes
//
//	for each possible attribute
//		if attribute was posted
//			if attribute exists in database
//				if posted attribute is different from attribute in database
//					if attribute type == string
//						update string_value of AttributeValue
//					elseif attribute type == uint
//						update uint_value of AttributeValue
//					elseif attribute type == float
//						update float_value of AttributeValue
//			else
//				if posted attribute is different from attribute in database
//					if attribute type == string
//						insert string_value of AttributeValue
//					elseif attribute type == uint
//						insert uint_value of AttributeValue
//					elseif attribute type == float
//						insert float_value of AttributeValue
//
//
//
	foreach($attribute_id as $key => $value) {						// go through each possible attribute. example: $key:warranty, $value:22
		if (isset($_POST[$key])) {										// if there is a post value for the attribute
			$posted_value=$_POST[$key];
			$attr_id=$value;													//set the attribute id for lookup
			if (isset($attributes_stored[$value])) {	// if the valid attribute exists in AttributeValue ie 22 : 12/12/12
				if ($_POST[$key]!=$attributes_stored[$value]) {					// if the POSTED attribute is not equal to the value stored, update.
					echo "value submitted ($_POST[$key]) differs from value stored ($attributes_stored[$value])\n";
					//UPDATE ************ mysql update
					if ($attribute_type[$attr_id] == "string")
						mysql_query("update AttributeValue set string_value=\"$posted_value\" where object_id=$object_id and attr_id=\"$attr_id\"");
					else if ($attribute_type[$attr_id] == "uint")
						mysql_query("update AttributeValue set uint_value=$posted_value where object_id=$object_id and attr_id=\"$attr_id\"");
					else if ($attribute_type[$attr_id] == "float")
						mysql_query("update AttributeValue set float_value=$posted_value where object_id=$object_id and attr_id=\"$attr_id\"");
					
				}
			}
			else {													// valid attribute is not stored, we'll need to add a row to AttributeValue
				echo "value submitted ($_POST[$key]) differs from value stored (NULL)\n";
				//INSERT************ mysql insert
				if ($attribute_type[$attr_id] == "string")
						mysql_query("insert into AttributeValue (object_id,attr_id,string_value) values($object_id,$attr_id,\"$posted_value\")");
				else if ($attribute_type[$attr_id] == "uint")
						mysql_query("insert into AttributeValue (object_id,attr_id,uint_value) values($object_id,$attr_id,$posted_value)");
				else if ($attribute_type[$attr_id] == "float")
						mysql_query("insert into AttributeValue (object_id,attr_id,float_value) values($object_id,$attr_id,$posted_value)");
			}
		}
	}
	
//Update RackObject and RackObjectHistory

//for each $object_data
//	if $insert_labels string does not exist
//		add first label to $insert_labels string 
//		add first values to $insert_values string
//	elseif database value exists && post value exists
//		add labels to $insert_labels string
//		if post value exists
//			if database value differs from post value
//				add post value to $insert_values string
//			else
//				add database value to $insert_values string
//		else
//			add database value to $insert_values string
//	if $update_values string does not exist && post value exists
//		if post value differs from database value
//			add first value to $update_values
//	elseif posted value exists
//		if posted value differs from database value
//			add posted value to $update_values string
//
//RackObject update: update RackObject set label="testing1" where id=1164
//RackObjectHistory insert: insert into RackObjectHistory (id,name,label,objtype_id,has_problems,comment,user_name) values(1164,"r5288","testing1","4","no","","autoupdate")


	foreach($object_data as $key => $value) {
		if (!isset($insert_labels)) {
			$insert_labels=$key;		//first run will see object id, which is never posted
			$insert_values=$value;
		}
		elseif (isset($value) || isset($post_object[$key])) {
			$insert_labels=$insert_labels.",".$key;
			if (isset($post_object[$key])) {
				if ($value!=$post_object[$key])
					$insert_values=$insert_values.",\"$post_object[$key]\"";
				else
					$insert_values=$insert_values.",\"$value\"";
			}
			else
				$insert_values=$insert_values.",\"$value\"";
		}
		if (!isset($update_values) && isset($post_object[$key])) {
			if ($post_object[$key]!=$value)
				$update_values="$key=\"$post_object[$key]\"";
		}
		elseif (isset($post_object[$key])) {
			if ($post_object[$key]!=$value)
				$update_values=$update_values.", $key=\"$post_object[$key]\"";
		}
	}
	if (isset($update_values)) {
		$RackObject_statement="update RackObject set ".$update_values." where id=$object_id";
		$RackObjectHistory_statement="insert into RackObjectHistory ($insert_labels,user_name) values($insert_values,\"$username\")\n";
		mysql_query("$RackObject_statement");
		mysql_query("$RackObjectHistory_statement");

echo "RackObject statement: $RackObject_statement\n";
echo "RackObjectHistory statement: $RackObjectHistory_statement\n";
	}
}

function update_master_object($master_object_id) {
	global $master_object_name;
	global $object_name;
	global $name;
	if ($name!=$object_name) {
		$master_object_name_new=preg_replace("/$object_name/",$name,$master_object_name);
		//UPDATE ************ mysql update
		$master_object_update_query="update RackObject set name=\"".$master_object_name_new."\" where id=".$master_object_id;
		mysql_query("$master_object_update_query");
	}
}


lookup();

if (authenticate()==0)
	exit("incorrect username and/or password");

if ($object_id)	//if lookup() set $object_id, there was a successfull object lookup
	update_object($object_id);

if ($master_object_id)	//if lookup() set $master_object_id, there was a successfull master object lookup
	update_master_object($master_object_id);

mysql_close();

?>
