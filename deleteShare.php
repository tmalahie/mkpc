<?php
if (isset($_POST['id'])) {
	include('session.php');
	include('initdb.php');
	require_once('getRights.php');
	$persoId = $_POST['id'];
	if (hasRight('moderator')) {
		mysql_query('UPDATE `mkpersosel` SET perso2=-1 WHERE perso2="'. $persoId .'"');
		mysql_query('UPDATE `mkpersosel` SET perso1=perso2,perso2=-1 WHERE perso1="'. $persoId .'"');
		mysql_query('DELETE FROM `mkchisto` WHERE id="'. $persoId .'"');
		mysql_query('UPDATE `mkchars` SET author=NULL WHERE id="'. $persoId .'"');
		mysql_query('INSERT INTO `mklogs` VALUES(NULL, '. $id .', "SPerso '. $persoId .'")');
	}
	mysql_close();
	echo 1;
}
?>