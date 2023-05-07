<?php
header('Content-Type: text/plain');
include('session.php');
if ($id) {
	if (isset($_POST['key'])) {
		if (isset($_POST['options'])) $options = $_POST['options'];
		include('initdb.php');
		if (isset($options)) $_POST['options'] = $options;
		$key = $_POST['key'];
		if ($privateLink = mysql_fetch_array(mysql_query('SELECT player FROM `mkprivgame` WHERE id="'. $key .'"'))) {
			if ($privateLink['player'] == $id)
				include('updateGameOptions.php');
		}
		mysql_close();
		echo 1;
	}
}
?>