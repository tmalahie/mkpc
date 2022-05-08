<?php
if (isset($_GET['id']) && isset($_GET['type'])) {
	include('escape_all.php');
	$id = intval($_GET['id']);
	$type = $_GET['type'];
	require_once('generateTrackIcon.php');
	header('content-type: image/png');
	include('initdb.php');
	$filepath = generateTrackIcon($id,$type);
	mysql_close();
	echo @file_get_contents($filepath);
}
?>