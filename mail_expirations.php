#!/usr/bin/env php
<?php

/* This script produces a plain-text version of the expirations report and
 * emails it to the specified address unless the report contains no objects.
 * It is intended to be run from command line as daily or weekly cron job.
 *
 * This script requires RackTables version 0.20.11 or newer to work.
 */

$script_mode = TRUE;
require '/usr/local/racktables/wwwroot/inc/init.php';

/*
 * init.php will include any plugins/*.php files, that would be the right place
 * to pre-define any of the constants below like:
 *
 * define ('MAILEXPR_TO', 'User <user@example.com>');
 *
 * This makes it possible to leave this script in its original form and
 * (hopefully) simplify the future upgrades.
 */

defineIfNotDefined ('MAILEXPR_TO', 'Admin <admin@example.com>');
defineIfNotDefined ('MAILEXPR_FROM', 'RackTables <racktables@example.com>');
defineIfNotDefined ('MAILEXPR_SUBJ', 'RackTables expirations report');
defineIfNotDefined ('MAILEXPR_DAYS_AHEAD', 30);

$mail_text = getExpirationsText();
if ($mail_text != '')
	mail (MAILEXPR_TO, MAILEXPR_SUBJ, $mail_text, 'From: ' . MAILEXPR_FROM);
exit (0);

function getExpirationsText()
{
	global $expirations;
	$row_format = "%3s|%-30s|%-15s|%-15s|%s\r\n";
	$ret = '';
	$breakdown = array();
	foreach ($expirations as $attr_id => $sections)
	{
		$tmp = array();
		foreach ($sections as $section)
			if ($section['to'] <= MAILEXPR_DAYS_AHEAD)
				$tmp[] = $section;
		if (count ($tmp))
			$breakdown[$attr_id] = $tmp;
	}
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
