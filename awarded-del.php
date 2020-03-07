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
if (!isset($_GET['user']) && !isset($_GET['award'])) {
	mysql_close();
	exit;
}
$award = mysql_fetch_array(mysql_query('DELETE FROM mkawarded WHERE user="'. $_GET['user'] .'" AND award="'. $_GET['award'] .'"'));
mysql_query('INSERT INTO `mklogs` VALUES(NULL, '. $id .', "SAwarded '. $_GET['user'] .' '. $_GET['award'] .'")');
header('location: awards.php?awarded-deleted');
?>