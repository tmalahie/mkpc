<?php
header('Content-Type: text/plain');
if (isset($_POST['id'])) {
	include('../includes/initdb.php');
	require_once('../includes/utils-tt.php');
	print_ghost_frames($_POST['id']);
	mysql_close();
}
?>