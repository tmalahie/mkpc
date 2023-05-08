<?php
header('Content-Type: text/plain');
if (isset($_POST['options'])) $options = $_POST['options'];
include('../includes/initdb.php');
if (isset($options)) $_POST['options'] = $options;
include('../includes/session.php');
if ($id) {
	do {
		$key = rand();
		if (!$key)
			continue;
		$q = mysql_query('INSERT IGNORE INTO `mkprivgame` SET id="'.$key.'",player="'.$id.'"');
	} while (!mysql_affected_rows());
	include('../includes/updateGameOptions.php');
	echo $key;
}
else
	echo -1;
mysql_close();
?>