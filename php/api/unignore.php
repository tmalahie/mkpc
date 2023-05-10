<?php
header('Content-Type: text/plain');
include('../includes/session.php');
if ($id && isset($_POST['member'])) {
	include('../includes/initdb.php');
	mysql_query('DELETE FROM `mkignores` WHERE ignorer="'. $id .'" AND ignored="'. $_POST['member'] .'"');
	echo 1;
	mysql_close();
}
?>