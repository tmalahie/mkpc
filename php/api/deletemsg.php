<?php
header('Content-Type: text/plain');
include('../includes/session.php');
if ($id) {
	if (isset($_POST['msg'])) {
		include('../includes/initdb.php');
		require_once('../includes/getRights.php');
		if (hasRight('moderator')) {
			mysql_query('DELETE FROM minichat WHERE id="'. $_POST['msg'] .'"');
			mysql_query('INSERT INTO `mklogs` VALUES(NULL,NULL, '. $id .', "Chat '. $_POST['msg'] .'")');
		}
		mysql_close();
	}
}
echo 1;
?>