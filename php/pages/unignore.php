<?php
header('Content-Type: text/plain');
include('session.php');
if ($id && isset($_POST['member'])) {
	include('initdb.php');
	mysql_query('DELETE FROM `mkignores` WHERE ignorer="'. $id .'" AND ignored="'. $_POST['member'] .'"');
	echo 1;
	mysql_close();
}
?>