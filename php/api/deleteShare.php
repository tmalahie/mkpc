<?php
header('Content-Type: text/plain');
if (isset($_POST['ids']) && preg_match('#^\d+(,\d+)*$#', $_POST['ids'])) {
	include('../includes/session.php');
	include('../includes/initdb.php');
	require_once('../includes/getRights.php');
	require_once('../includes/utils-logs.php');
	$persoIds = $_POST['ids'];
	if (hasRight('moderator')) {
		$characters = array();
		$getChars = mysql_query('SELECT id,name,author,sprites FROM `mkchars` WHERE id IN ('. $persoIds .')');
		while ($character = mysql_fetch_array($getChars))
			$characters[] = snapshotRow($character);
		mysql_query('UPDATE `mkpersosel` SET perso2=-1 WHERE perso2 IN ('. $persoIds .')');
		mysql_query('UPDATE `mkpersosel` SET perso1=perso2,perso2=-1 WHERE perso1 IN ('. $persoIds .')');
		mysql_query('DELETE FROM `mkchisto` WHERE id IN ('. $persoIds .')');
		mysql_query('UPDATE `mkchars` SET author=NULL WHERE id IN ('. $persoIds .')');
		insertLog($id, 'SPerso '. $persoIds, array(
			'type' => 'characters',
			'characters' => $characters
		));
	}
	mysql_close();
	echo 1;
}
?>