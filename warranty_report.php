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

function scanAttrRelativeDays ($attr_id, $date_format, $not_before_days, $not_after_days)
{
	$attrmap = getAttrMap();
	if ($attrmap[$attr_id]['type'] != 'string')
		throw new InvalidArgException ('attr_id', $attr_id, 'attribute cannot store dates');
	return usePreparedSelectBlade
	(
		'SELECT a.string_value, r.id, r.name, r.asset_no ' .
		'FROM AttributeValue a LEFT JOIN RackObject r ON a.object_id = r.id ' .
		'WHERE attr_id=? and STR_TO_DATE(a.string_value, ?) BETWEEN '.
		'DATE_ADD(curdate(), INTERVAL ? DAY) and DATE_ADD(curdate(), INTERVAL ? DAY)',
		array ($attr_id, $date_format, $not_before_days, $not_after_days)
	);
}

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

    $breakdown = array
    (
        array ('from' => -365, 'to' => 0, 'title' => 'HW warranty has expired'),
        array ('from' => 0, 'to' => 30, 'title' => 'HW warranty expires within 30 days'),
        array ('from' => 30, 'to' => 60, 'title' => 'HW warranty expires within 60 days'),
        array ('from' => 60, 'to' => 90, 'title' => 'HW warranty expires within 90 days'),
    );

    foreach ($breakdown as $section)
    {
        $count = 0;
	$result = scanAttrRelativeDays (22, '%m/%d/%Y', $section['from'], $section['to']);
	if (! count ($result))
		continue;

	startPortlet ($section['title']);
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
