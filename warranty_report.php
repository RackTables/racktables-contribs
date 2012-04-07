<?php
//
// Warranty by Ernest Shaffer
// Version: 3.0 (merged into RackTables 0.19.12)
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

# Return list of rows for objects, which have the date stored in the given
# attribute belonging to the given range (relative to today's date).
function scanAttrRelativeDays ($attr_id, $date_format, $not_before_days, $not_after_days)
{
	$attrmap = getAttrMap();
	if ($attrmap[$attr_id]['type'] != 'string')
		throw new InvalidArgException ('attr_id', $attr_id, 'attribute cannot store dates');
	$result = usePreparedSelectBlade
	(
		'SELECT string_value, object_id FROM AttributeValue ' .
		'WHERE attr_id=? and STR_TO_DATE(string_value, ?) BETWEEN '.
		'DATE_ADD(curdate(), INTERVAL ? DAY) and DATE_ADD(curdate(), INTERVAL ? DAY)',
		array ($attr_id, $date_format, $not_before_days, $not_after_days)
	);
	return $result->fetchAll (PDO::FETCH_ASSOC);
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
		array ('from' => -365, 'to' => 0, 'class' => 'has_problems_', 'title' => 'HW warranty has expired within last year'),
		array ('from' => 0, 'to' => 30, 'class' => 'row_', 'title' => 'HW warranty expires within 30 days'),
		array ('from' => 30, 'to' => 60, 'class' => 'row_', 'title' => 'HW warranty expires within 60 days'),
		array ('from' => 60, 'to' => 90, 'class' => 'row_', 'title' => 'HW warranty expires within 90 days'),
	);
	foreach ($breakdown as $section)
	{
		$count = 1;
		$order = 'odd';
		$result = scanAttrRelativeDays (22, '%m/%d/%Y', $section['from'], $section['to']);

		startPortlet ($section['title']);
		echo "<table align=center width=60% border=0 cellpadding=5 cellspacing=0 align=center class=cooltable><tr valign=top>";
		echo "<th align=center>Count</th>";
		echo "<th align=center>Name</th>";
		echo "<th align=center>Asset Tag</th>";
		echo "<th align=center>Date Warranty <br> Expires</th></tr>\n";

		if (! count ($result))
			echo '<tr><td colspan=4>(none)</td></tr>';
		else
			foreach ($result as $row)
			{
				$object = spotEntity ('object', $row['object_id']);
				echo '<tr class=' . $section['class'] . $order . ' valign=top>';
				echo "<td>${count}</td>";
				echo '<td>' . mkA ($object['dname'], 'object', $object['id']) . '</td>';
				echo "<td>${object['asset_no']}</td>";
				echo "<td>${row['string_value']}</td>";
				echo "</tr>\n";
				$order = $nextorder[$order];
				$count++;
			}
		echo "</table>\n";
		finishPortlet ();
	}
}

?>
