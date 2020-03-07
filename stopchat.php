<?php
include('session.php');
if ($id && isset($_POST['member']) && isset($_POST['lastID'])) {
	include('initdb.php');
	mysql_query('DELETE FROM `mkconvs` WHERE sender="'. $id .'" AND receiver="'. $_POST['member'] .'"');
	mysql_query('UPDATE `mkconvs` SET writting=NULL WHERE sender="'. $_POST['member'] .'" AND receiver="'. $id .'"');
	mysql_query('UPDATE `mkchats` SET seen=1 WHERE sender="'. $_POST['member'] .'" AND receiver="'. $id .'" AND id<="'. $_POST['lastID'] .'"');
	mysql_close();
	echo 1;
}
?>