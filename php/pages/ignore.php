<?php
header('Content-Type: text/plain');
include('../includes/session.php');
if ($id && isset($_POST['member'])) {
	include('../includes/initdb.php');
	if (mysql_numrows(mysql_query('SELECT * FROM `mkjoueurs` WHERE id="'. $_POST['member'] .'"'))) {
		mysql_query('INSERT INTO `mkignores` VALUES ("'. $id .'","'. $_POST['member'] .'")');
		mysql_query('DELETE FROM `mkconvs` WHERE sender="'. $id .'" AND receiver="'. $_POST['member'] .'"');
		mysql_query('UPDATE `mkchats` SET seen=1 WHERE sender="'. $_POST['member'] .'" AND receiver="'. $id .'"');
	}
	echo 1;
	mysql_close();
}
?>