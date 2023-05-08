<?php
header('Content-Type: text/plain');
if (isset($_GET['topic']) && isset($_GET['category'])) {
	include('../includes/session.php');
	require_once('../includes/getRights.php');
	include('../includes/initdb.php');
	if (hasRight('moderator')) {
		mysql_query('UPDATE `mktopics` SET category="'. $_GET['category'] .'" WHERE id="'. $_GET['topic'] .'"');
		echo 1;
	}
	mysql_close();
}
?>