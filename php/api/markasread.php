<?php
header('Content-Type: text/plain');
include('../includes/session.php');
if ($id && isset($_POST['answer'])) {
	include('../includes/initdb.php');
	if ($msg = mysql_fetch_array(mysql_query('SELECT * FROM `mkchats` WHERE id="'. $_POST['answer'] .'" AND receiver="'. $id .'"')))
		mysql_query('UPDATE `mkchats` SET seen=1 WHERE sender="'. $msg['sender'] .'" AND receiver="'. $id .'" AND id<="'. $msg['id'] .'"');
	echo 1;
	mysql_close();
}
?>