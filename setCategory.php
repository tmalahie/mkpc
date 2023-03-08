<?php
header('Content-Type: text/plain');
if (isset($_GET['topic']) && isset($_GET['category'])) {
	include('session.php');
	require_once('getRights.php');
	include('initdb.php');
	if (hasRight('moderator')) {
		mysql_query('UPDATE `mktopics` SET category="'. $_GET['category'] .'" WHERE id="'. $_GET['topic'] .'"');
		echo 1;
	}
	mysql_close();
}
?>