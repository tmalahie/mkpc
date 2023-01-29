<?php
include('session.php');
if ($id && isset($_POST['member']) && isset($_POST['state'])) {
	include('initdb.php');
	mysql_query('UPDATE `mkconvs` SET reduced="'. $_POST['state'] .'" WHERE sender="'. $id .'" AND receiver="'. $_POST['member'] .'"');
	mysql_close();
	echo 1;
}
?>