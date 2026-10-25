<?php
include('../includes/session.php');
include('../includes/language.php');
if (!$id) {
	echo t('admin.you_arent_logged');
	exit;
}
include('../includes/initdb.php');
require_once('../includes/getRights.php');
if (!hasRight('admin')) {
	echo t('admin.you_arent_admin');
	mysql_close();
	exit;
}
require_once('../includes/tokens.php');
assign_token();
if (!isset($_GET['token']) || $_GET['token'] !== $_SESSION['csrf']) {
	echo '<a href="?token='. $_SESSION['csrf'] .'">'. ($language ? 'Recompute the last message date of every topic':'Recalculer la date du dernier message de chaque topic') .'</a>';
	mysql_close();
	exit;
}
$topics = mysql_query('SELECT * FROM `mktopics`');
while ($topic = mysql_fetch_array($topics)) {
	$getLastMessage = mysql_fetch_array(mysql_query('SELECT date FROM `mkmessages` WHERE topic="'. $topic['id'] .'" ORDER BY id DESC limit 1'));
	mysql_query('UPDATE `mktopics` SET dernier="'.$getLastMessage['date'].'" WHERE id="'. $topic['id'] .'"');
}
mysql_close();
echo 1;
?>