<?php
if (isset($_POST['id'])) {
	include('initdb.php');
	require_once('utils-tt.php');
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