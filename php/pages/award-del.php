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
if (!isset($_GET['id'])) {
	mysql_close();
	exit;
}
$awardSnapshot = snapshotQuery('SELECT id,name,link,notif_msg_en,notif_msg_fr FROM mkawards WHERE id="'. $_GET['id'] .'"');
$award = mysql_fetch_array(mysql_query('DELETE FROM mkawards WHERE id="'. $_GET['id'] .'"'));
insertLog($id, 'SAward '. $_GET['id'], array_merge(
	array('type' => 'award', 'id' => intval($_GET['id'])),
	$awardSnapshot ? $awardSnapshot : array()
));
mysql_close();
header('location: awards.php?award-deleted');
?>