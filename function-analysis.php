#!/usr/bin/php
<?php

# This script outputs a list of functions that are used in local.php
# file, but don't belong to it. It requires a working php-pecl-parsekit
# package installation.

function get_all_function_calls ($tree)
{
	$self = __FUNCTION__;
	$ret = array();
	if (array_key_exists ('opcodes', $tree))
		foreach ($tree['opcodes'] as $item)
			switch ($item['opcode'])
			{
			case 59: // ZEND_INIT_FCALL_BY_NAME
				$ret[] = $item['op2']['constant'];
				break;
			case 60: // ZEND_DO_FCALL
				$ret[] = $item['op1']['constant'];
				break;
			default:
				break;
			}
	if (array_key_exists ('function_table', $tree))
		foreach ($tree['function_table'] as $item)
			$ret = array_merge ($ret, $self ($item));
	return $ret;
}

function get_locally_declared_functions ($tree)
{
	$self = __FUNCTION__;
	$ret = array();
	if (array_key_exists ('function_table', $tree))
		foreach ($tree['function_table'] as $funcname => $item)
			$ret = array_merge ($ret, array ($funcname), $self ($item));
	return $ret;
}

function get_foreign_function_calls ($tree)
{
	$ret = array();
	$localnames = get_locally_declared_functions ($tree);
	foreach (array_unique (get_all_function_calls ($tree)) as $func_call)
		if (! in_array ($func_call, $localnames) and ! function_exists ($func_call))
			$ret[] = $func_call;
	return $ret;
}

$tree = parsekit_compile_file ('local.php');
print_r (get_foreign_function_calls ($tree));

?>
