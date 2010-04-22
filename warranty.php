<?php
//Warranty by Ernest Shaffer
$tab['reports']['mytab3'] = 'Warranty Expires';
$tabhandler['reports']['mytab3'] = 'getMyServers3';

function getMyServers3 ()
{
    global $nextorder;
    $query_array;
    $query_array["0"] = "SELECT a.string_value, r.id, r.name, r.barcode, r.asset_no FROM AttributeValue a Left JOIN RackObject r ON a.object_id = r.id where a.attr_id=22 and STR_TO_DATE(a.string_value, '%m/%d/%Y') <= curdate()";

    $query_array["30"] = "SELECT a.string_value, r.id, r.name, r.barcode, r.asset_no FROM AttributeValue a Left JOIN RackObject r ON a.object_id = r.id where a.attr_id=22 and STR_TO_DATE(a.string_value, '%m/%d/%Y') BETWEEN curdate() and DATE_ADD(curdate(), INTERVAL 30 DAY)";

    $query_array["60"] = "SELECT a.string_value, r.id, r.name, r.barcode, r.asset_no FROM AttributeValue a Left JOIN RackObject r ON a.object_id = r.id where a.attr_id=22 and STR_TO_DATE(a.string_value, '%m/%d/%Y') BETWEEN DATE_ADD(curdate(), INTERVAL 30 DAY) and DATE_ADD(curdate(), INTERVAL 60 DAY)";

    $query_array["90"] = "SELECT a.string_value, r.id, r.name, r.barcode, r.asset_no FROM AttributeValue a Left JOIN RackObject r ON a.object_id = r.id where a.attr_id=22 and STR_TO_DATE(a.string_value, '%m/%d/%Y') BETWEEN DATE_ADD(curdate(), INTERVAL 60 DAY) and DATE_ADD(curdate(), INTERVAL 90 DAY)";

    $title["0"] = "Warranty Has Expired";
    $title["30"] = "Warranty expires within 30 Days";
    $title["60"] = "Warranty expires within 60 Days";
    $title["90"] = "Warranty expires within 90 Days";

    foreach( $query_array as $days => $query) {
        $count = 0;
        $result = NULL;
        $result = useSelectBlade ($query, __FUNCTION__);

        echo "<style type='text/css'>\n";
        echo "tr.has_problems_even {\n";
        echo "background-color: #ffa0a0;\n";
        echo "}\n";
        echo "tr.has_problems_odd {\n";
        echo "background-color: #ffd0d0;\n";
        echo "}\n";
        echo "</style>\n";

        echo " <center> <h3> $title[$days] </h3><br>";
        echo "<table align=center border=0 cellpadding=5 cellspacing=0 align=center class=cooltable><tr valign=top>";

        echo "<th align=center>Count</th>";
        echo "<th align=center>Name</th>";
        echo "<th align=center>Barcode</th>";
        echo "<th align=center>Assett Tag</th>";
        echo "<th align=center>Date Warranty <br> Expires</th>";

        $order = 'odd';
        while ($row = $result->fetch (PDO::FETCH_ASSOC))
        {
            if ($days == 0) {
              echo "<tr class=has_problems_${order} valign=top>";
            } else {
              echo "<tr class=row_${order} valign=top>";
            }
            printf("<td>%s</td>", $count += 1);
            echo "<td><a href='".makeHref(array('page'=>'object', 'object_id'=>$row['id']))."'>${row['name']}</a></td>";
            printf("<td>%s</td>", $row['barcode']);
            printf("<td>%s</td>", $row['asset_no']);
            printf("<td>%s</td>", $row['string_value']);
            echo "</tr>\n";
            $order = $nextorder[$order];
        }
        echo "</table>\n";
    }

}
?>
