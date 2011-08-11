<?php 
$dbcon = mysql_connect("localhost","user","password");
mysql_query("SET NAMES 'utf8'", $dbcon); 
if(!$dbcon){
  echo "Unable to connect.<br/>";
  return false;
}

function Squery($query){
  //echo $query . "<br/>";
  $result = mysql_query($query);
  if(!$result){
    echo("<p>Error performing query: " . mysql_error() . "</p>");
  }
	return $result;
}
  
function query($query){
  //echo $query . "<br/>";
  $result = mysql_query($query);
  if(!$result){
    echo("<p>Error performing query: " . mysql_error() . "</p>");
  }

  if(mysql_num_rows($result) == 0){
    //  if ($result == ""){
    $result = "niets gevonden";
  }
  return $result;
}
  
function insert($query){
  //echo $query . "<br/>";
  $result = mysql_query($query);
  if(!$result){
    echo("<p>Error performing query: " . mysql_error() . "</p>"); 
  }
}
function getDB(){
  return "racktables";
}
?>
