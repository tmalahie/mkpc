<?php
if (isset($_POST['options'])) $options = $_POST['options'];
include('initdb.php');
if (isset($options)) $_POST['options'] = $options;
include('session.php');
if ($id) {
	do {
		$key = rand();
		if (!$key)
			continue;
		$q = mysql_query('INSERT IGNORE INTO `mkprivgame` SET id="'.$key.'",player="'.$id.'"');
	} while (!mysql_affected_rows());
	include('updateGameOptions.php');
	echo $key;
}
else
	echo -1;
mysql_close();
?>