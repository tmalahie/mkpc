<?php
global $alreadyEscaped;
if (!isset($alreadyEscaped)) {
	$alreadyEscaped = true;
	function mres($s) {
		if (is_array($s))
			return $s;
		if (!isset($dbh))
			return addslashes($s);
		return mysql_real_escape_string($s);
	}
	foreach ($_GET as $k => $get)
		$_GET[$k] = mres($_GET[$k]);
	foreach ($_POST as $k => $get)
		$_POST[$k] = mres($_POST[$k]);
}
?>