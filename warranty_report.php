<?php
//
// Warranty by Ernest Shaffer
// Version: 2.1
//
// Displays objects with pending or expired HW warranty expiration dates
// Groups them into 4 groups:
//   HW warranty has expired
//   HW warranty expires within 30 days
//   HW warranty expires within 60 days
//   HW warranty expires within 90 days
//

$tab['reports']['warranty'] = 'HW Warranty Expires';
$tabhandler['reports']['warranty'] = 'hwExpireReport';

function hwExpireReport ()
{
    global $nextorder;
    addCSS ('
tr.has_problems_even {
background-color: #ffa0a0;
}
tr.has_problems_odd {
background-color: #ffd0d0;
}
', TRUE);
    $query_array;
    $query_array["0"] = "SELECT a.string_value, r.id, r.name, r.asset_no FROM AttributeValue a Left JOIN RackObject r ON a.object_id = r.id where attr_id=22 and STR_TO_DATE(a.string_value, '%m/%d/%Y') <= curdate()";

    $query_array["30"] = "SELECT a.string_value, r.id, r.name, r.asset_no FROM AttributeValue a Left JOIN RackObject r ON a.object_id = r.id where attr_id=22 and STR_TO_DATE(a.string_value, '%m/%d/%Y') BETWEEN curdate() and DATE_ADD(curdate(), INTERVAL 30 DAY)";

    $query_array["60"] = "SELECT a.string_value, r.id, r.name, r.asset_no FROM AttributeValue a Left JOIN RackObject r ON a.object_id = r.id where attr_id=22 and STR_TO_DATE(a.string_value, '%m/%d/%Y') BETWEEN DATE_ADD(curdate(), INTERVAL 30 DAY) and DATE_ADD(curdate(), INTERVAL 60 DAY)";

    $query_array["90"] = "SELECT a.string_value, r.id, r.name, r.asset_no FROM AttributeValue a Left JOIN RackObject r ON a.object_id = r.id where attr_id=22 and STR_TO_DATE(a.string_value, '%m/%d/%Y') BETWEEN DATE_ADD(curdate(), INTERVAL 60 DAY) and DATE_ADD(curdate(), INTERVAL 90 DAY)";

    $title["0"] = "HW warranty has expired";
    $title["30"] = "HW warranty expires within 30 days";
    $title["60"] = "HW warranty expires within 60 days";
    $title["90"] = "HW warranty expires within 90 days";

    foreach( $query_array as $days => $query) {
        $count = 0;
        $result = NULL;
        $result = usePreparedSelectBlade ($query);

	startPortlet ($title[$days]);
        echo "<table align=center width=60% border=0 cellpadding=5 cellspacing=0 align=center class=cooltable><tr valign=top>";

        echo "<th align=center>Count</th>";
        echo "<th align=center>Name</th>";
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
            printf("<td>%s</td>", $row['asset_no']);
            printf("<td>%s</td>", $row['string_value']);
            echo "</tr>\n";
            $order = $nextorder[$order];
        }
        echo "</table>\n";
	finishPortlet ();
    }

}
?>
