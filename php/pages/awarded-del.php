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
require_once('../includes/utils-logs.php');
if (!hasRight('organizer')) {
	echo "Vous n'&ecirc;tes pas animateur";
	mysql_close();
	exit;
}
if (!isset($_GET['user']) && !isset($_GET['award'])) {
	mysql_close();
	exit;
}
$awardedSnapshot = array(
	'type' => 'awarded',
	'member' => snapshotMember($_GET['user']),
	'award' => snapshotQuery('SELECT id,name FROM mkawards WHERE id="'. $_GET['award'] .'"'),
	'value' => null
);
if ($awarded = snapshotQuery('SELECT value FROM mkawarded WHERE user="'. $_GET['user'] .'" AND award="'. $_GET['award'] .'"'))
	$awardedSnapshot['value'] = $awarded['value'];
$award = mysql_fetch_array(mysql_query('DELETE FROM mkawarded WHERE user="'. $_GET['user'] .'" AND award="'. $_GET['award'] .'"'));
insertLog($id, 'SAwarded '. $_GET['user'] .' '. $_GET['award'], $awardedSnapshot);
header('location: awards.php?awarded-deleted');
?>