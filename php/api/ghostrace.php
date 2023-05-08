<?php
header('Content-Type: text/plain');
if (isset($_POST['id'])) {
	include('../includes/initdb.php');
	require_once('../includes/utils-tt.php');
	$getTemps = mysql_query('SELECT '.GHOST_MYSQL_FIELDS.' FROM `mkghostdata` WHERE ghost="'. $_POST['id'].'" ORDER BY frame');
	$colon = '';
	echo '[';
	while ($time = mysql_fetch_array($getTemps)) {
		echo $colon;
		print_ghost_frame($time);
		$colon = ',';
	}
	echo ']';
	mysql_close();
}
?>