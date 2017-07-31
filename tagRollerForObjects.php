<?php

$tab['depot']['tagroller'] = 'Tag roller';
$tabhandler['depot']['tagroller'] = 'renderTagRollerForObjects';
$ophandler['depot']['tagroller']['rollObjectTags'] = 'rollObjectTags';

function rollObjectTags () // see rollTags() in ophandlers.php
{
	setFuncMessages (__FUNCTION__, array ('OK' => 67, 'ERR' => 149));
	if (genericAssertion ('sum', 'string0') != genericAssertion ('realsum', 'uint'))
	{
		showFuncMessage (__FUNCTION__, 'ERR');
		return;
	}
	// Even if the user requested an empty tag list, don't bail out, but process existing
	// tag chains with "zero" extra. This will make sure, that the stuff processed will
	// have its chains refined to "normal" form.
	$extratags = isset ($_REQUEST['taglist']) ? $_REQUEST['taglist'] : array();
	$n_ok = 0;
	// Minimizing the extra chain early, so that tag rebuilder doesn't have to
	// filter out the same tag again and again. It will have own noise to cancel.
	$extrachain = getExplicitTagsOnly (buildTagChainFromIds ($extratags));

	$cellfilter = getCellFilter();
	$objects = applyCellFilter ('object', $cellfilter);
	foreach ($objects as $obj)
	{
		if (rebuildTagChainForEntity ('object', $obj['id'], $extrachain))
			$n_ok++;
	}
	showFuncMessage (__FUNCTION__, 'OK', array ($n_ok));
}

function renderTagRollerForObjects ()
{
	$a = rand (1, 20);
	$b = rand (1, 20);
	$sum = $a + $b;
	echo "<p>";
	printOpFormIntro ('rollObjectTags', array ('realsum' => $sum));
	echo "<table border=1 align=center>";
	echo "<tr><td colspan=2>This special tool allows assigning tags to objects as selected below.<br>";
	echo "The tag(s) selected below will be appended to already assigned tag(s) of each particular entity.</td></tr>";
	echo "<tr><th>Tags</th><td>";
	printTagsPicker ();
	echo "</td></tr>";
	echo "<tr><th>Control question: the sum of ${a} and ${b}</th><td><input type=text name=sum></td></tr>";
	echo "<tr><td colspan=2 align=center><input type=submit value='Go!'></td></tr>";
	echo "</table></form>";
	echo "<p>";
	renderDepot();
}

?>
