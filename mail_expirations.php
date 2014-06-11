#!/usr/bin/env php
<?php

/* This script produces a plain-text version of the expirations report and
 * emails it to the specified address unless the report contains no objects.
 * It is intended to be run from command line as daily or weekly cron job.
 *
 * Tested to work with RackTables 0.20.8.
 */

$mail_to = 'Admin <admin@example.com>';
$mail_from = 'RackTables <racktables@example.com>';
$mail_subject = 'RackTables expirations report';


$script_mode = TRUE;
require '/usr/local/racktables/wwwroot/inc/init.php';

$mail_text = getExpirationsText();
if ($mail_text != '')
	mail ($mail_to, $mail_subject, $mail_text, "From: ${mail_from}");
exit (0);

function getExpirationsText()
{
	$row_format = "%3s|%-30s|%-15s|%-15s|%s\r\n";
	$ret = '';
	$breakdown = array();
	$breakdown[21] = array
	(
		array ('from' => -365, 'to' => 0, 'title' => 'has expired within last year'),
		array ('from' => 0, 'to' => 30, 'title' => 'expires within 30 days'),
//		array ('from' => 30, 'to' => 60, 'title' => 'expires within 60 days'),
//		array ('from' => 60, 'to' => 90, 'title' => 'expires within 90 days'),
	);
	$breakdown[22] = $breakdown[21];
	$breakdown[24] = $breakdown[21];
	$attrmap = getAttrMap();
	foreach ($breakdown as $attr_id => $sections)
	{
		$ret .= $attrmap[$attr_id]['name'] . "\r\n";
		$ret .= "===========================================\r\n";
		foreach ($sections as $section)
		{
			$count = 1;
			$result = scanAttrRelativeDays ($attr_id, $section['from'], $section['to']);
			if (! count ($result))
				continue;

			$ret .= $section['title'] . "\r\n";
			$ret .= "-----------------------------------------------------------------------------------\r\n";
			$ret .= sprintf ($row_format, '#', 'Name', 'Asset Tag', 'OEM S/N 1', 'Date Warranty Expires');
			$ret .= "-----------------------------------------------------------------------------------\r\n";
			foreach ($result as $row)
			{
				$object = spotEntity ('object', $row['object_id']);
				$attributes = getAttrValues ($object['id']);
				$ret .= sprintf
				(
					$row_format,
					$count,
					$object['dname'],
					$object['asset_no'],
					array_key_exists (1, $attributes) ? $attributes[1]['a_value'] : '',
					datetimestrFromTimestamp ($row['uint_value'])
				);
				$count++;
			}
			$ret .= "-----------------------------------------------------------------------------------\r\n";
		}
		$ret .= "\r\n";
	}
	return $ret;
}

?>
