<?php
header('Content-Type: text/plain');
if (isset($_POST['id'])) {
	include('initdb.php');
	$cID = $_POST['id'];
	include('getId.php');
	include('session.php');
	require_once('getRights.php');
	require_once('collabUtils.php');
	$skipOwnerCheck = hasRight('moderator') || hasCollabGrants('mkmcups', $cID, $_POST['collab'], 'edit');
	if (mysql_numrows(mysql_query('SELECT * FROM `mkmcups` WHERE id="'. $cID .'"'. ($skipOwnerCheck ? '':' AND identifiant="'. $identifiants[0] .'" AND identifiant2="'. $identifiants[1] .'" AND identifiant3="'. $identifiants[2] .'" AND identifiant4="'. $identifiants[3] .'"')))) {
		mysql_query('DELETE FROM `mkmcups` WHERE id="'.$cID.'"');
		mysql_query('DELETE FROM `mkmcups_tracks` WHERE mcup="'.$cID.'"');
		if (hasRight('moderator'))
			mysql_query('INSERT INTO `mklogs` VALUES(NULL,NULL, '. $id .', "MCup '. $cID .'")');
	}
	echo 1;
	mysql_close();
}
?>