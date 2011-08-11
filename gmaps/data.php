<?php
header('Content-Type: text/xml');
/*header("<?xml version=\"1.0\" encoding=\"utf-8\"?>");*/

require_once("./DB.php");
$db = getDB();

?>
<markers>

<?php
  $sql = "SELECT *, RackRow.id as RackRow_id FROM " . $db . ".maps, " . $db . ".RackRow WHERE RackRow.id = 	maps.RackRow_id";
  $res = query($sql);
  if ($res != "niets gevonden"){
    
    while ($row = mysql_fetch_array($res)){
      
      echo "<marker lat=\"";
        echo $row['lat'];
        echo "\" lng=\"";
        echo $row['lng'];
        echo "\" label=\"";
        echo $row['name'];
      echo "\">";
        echo "<infowindow><![CDATA[";
          echo "<h2>" . $row['name'] . "</h2><b>Racks</b><br />";
          $sql1 = "SELECT * FROM " . $db . ".Rack WHERE Rack.row_id = " . $row["RackRow_id"] ;
          $res1 = query($sql1);
          if ($res1 != "niets gevonden"){
            while ($row1 = mysql_fetch_array($res1)){
              echo "<a href=../index.php?page=rack&rack_id=" . $row1["id"] . " TARGET=\"_blank\">" . $row1["name"] . "</a><br />";
            }
          }
          
          echo "<br /><a href=../index.php?page=row&row_id=" . $row['RackRow_id'] . " TARGET=\"_blank\">Racks</a>";
          echo $row['html'];
        echo "]]></infowindow>";
      echo "</marker>";
    }
  }
  
  
  $sql = "select ".
         "DISTINCT m.lat as lat1, ". 
         "m.lng as lng1, ".
         "mm.lat as lat2, ".
         "mm.lng as lng2 ".
         "from  ".
         $db . ".RackObject as ro, ".
         $db . ".RackObject as rro, ".
         $db . ".RackSpace as s, ".
         $db . ".RackSpace as ss, ".
         $db . ".Rack as r, ".
         $db . ".Rack as rr, ".
         $db . ".maps as m, ".
         $db . ".maps as mm, ".
         $db . ".Link k inner join  ".
         $db . ".Port g on k.porta=g.id inner join ". 
         $db . ".Port gg on k.portb=gg.id ".
         "WHERE  ".
         "g.object_id = ro.id &&  ".
         "gg.object_id = rro.id && ".
         "s.object_id = g.object_id && ".
         "ss.object_id = gg.object_id && ".
         "s.rack_id = r.id && ".
         "ss.rack_id = rr.id && ".
         "m.RackRow_id = r.row_id && ".
         "mm.RackRow_id = rr.row_id;";
  
  $res = query($sql);
  if ($res != "niets gevonden"){
    
    while ($row = mysql_fetch_array($res)){
      if ($row['lat1'] != $row['lat2'] && $row['lng1'] != $row['lng2']){
        echo "<line colour=\"#FF0000\" width=\"4\" html=\"1\">";
          echo "<point lat=\"" . $row['lat1'] . "\"";
            echo " lng=\"" . $row['lng1'] . "\"/>";
          echo "<point lat=\"" . $row['lat2'] . "\"";
            echo " lng=\"" . $row['lng2'] . "\"/>";
        echo "</line>";
      }
    }
  }
?>
</markers>