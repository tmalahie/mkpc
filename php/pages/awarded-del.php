<?php
include('../includes/session.php');
if (!$id) {
	echo "Vous n'&ecirc;tes pas connect&eacute;";
	exit;
}
include('../includes/language.php');
include('../includes/initdb.php');
if (!$id) {
	echo "Vous n'&ecirc;tes pas connect&eacute;";
	mysql_close();
	exit;
}
require_once('../includes/getRights.php');
if (!hasRight('organizer')) {
	echo "Vous n'&ecirc;tes pas animateur";
	mysql_close();
	exit;
}
if (!isset($_GET['user']) && !isset($_GET['award'])) {
	mysql_close();
	exit;
}
$award = mysql_fetch_array(mysql_query('DELETE FROM mkawarded WHERE user="'. $_GET['user'] .'" AND award="'. $_GET['award'] .'"'));
mysql_query('INSERT INTO `mklogs` VALUES(NULL,NULL, '. $id .', "SAwarded '. $_GET['user'] .' '. $_GET['award'] .'")');
header('location: awards.php?awarded-deleted');
?>