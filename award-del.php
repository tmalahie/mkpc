<?php
include('session.php');
if (!$id) {
	echo "Vous n'&ecirc;tes pas connect&eacute;";
	exit;
}
include('language.php');
include('initdb.php');
if (!$id) {
	echo "Vous n'&ecirc;tes pas connect&eacute;";
	mysql_close();
	exit;
}
require_once('getRights.php');
if (!hasRight('organizer')) {
	echo "Vous n'&ecirc;tes pas animateur";
	mysql_close();
	exit;
}
if (!isset($_GET['id'])) {
	mysql_close();
	exit;
}
$award = mysql_fetch_array(mysql_query('DELETE FROM mkawards WHERE id="'. $_GET['id'] .'"'));
mysql_query('INSERT INTO `mklogs` VALUES(NULL,NULL, '. $id .', "SAward '. $_GET['id'] .'")');
mysql_close();
header('location: awards.php?award-deleted');
?>