<?php
header('Content-Type: text/plain');
if (isset($_POST['id_msg']) && isset($_POST['message'])) {
	include('../includes/session.php');
	include('../includes/escape_all.php');
	if ($id) {
		if ($_POST['message']) {
			include('../includes/initdb.php');
			if ($getMsg = mysql_fetch_array(mysql_query('SELECT auteur FROM `mkcomments` WHERE id="'. $_POST['id_msg'] .'"'))) {
				require_once('../includes/getRights.php');
				if (($getMsg['auteur'] == $id) || hasRight('moderator')) {
					mysql_query('UPDATE `mkcomments` SET message="'. $_POST['message'] .'" WHERE id="'. $_POST['id_msg'] .'"');
					if ($getMsg['auteur'] != $id)
						mysql_query('INSERT INTO `mklogs` VALUES(NULL,NULL, '. $id .', "EComment '. $_POST['id_msg'] .'")');
					echo 1;
				}
			}
			mysql_close();
		}
	}
}
?>