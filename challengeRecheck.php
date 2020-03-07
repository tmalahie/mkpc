<?php
if (isset($_POST['challenge'])) {
	include('initdb.php');
	include('session.php');
	require_once('getRights.php');
	if (hasRight('clvalidator')) {
		$challengeId = $_POST['challenge'];
		mysql_query('DELETE FROM mkclrecheck WHERE id="'. $challengeId .'"');
		mysql_query('INSERT INTO `mklogs` VALUES(NULL, '. $id .', "CChallenge '. $challengeId .'")');
	}
	mysql_close();
	echo 1;
}
?>