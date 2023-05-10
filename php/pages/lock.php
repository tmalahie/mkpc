<?php
if (isset($_GET['topic'])) {
	$tableToLock = 'mktopics';
	$idToLock = $_GET['topic'];
	$url = 'topic.php?topic='.$idToLock;
	$getItem = 'SELECT t.locked,m.auteur AS author FROM mktopics t INNER JOIN mkmessages m ON m.topic=t.id AND m.id=1 WHERE t.id="'. $idToLock .'"';
	$logAction = $_GET['value'] ? 'LTopic':'ULTopic';
}
elseif (isset($_GET['news'])) {
	$tableToLock = 'mknews';
	$idToLock = $_GET['news'];
	$url = 'news.php?id='.$idToLock.'#news-comments';
	$getItem = 'SELECT locked,author FROM mknews WHERE id="'. $idToLock .'"';
	$logAction = $_GET['value'] ? 'LNews':'ULNews';
}
if (isset($tableToLock) && isset($_GET['value']) && in_array($_GET['value'], array(0,1,2))) {
	include('../includes/session.php');
	include('../includes/initdb.php');
	$allowed = false;

	if ($item = mysql_fetch_array(mysql_query($getItem))) {
		$allowed = false;
		$moderator = false;
		if (($item['locked'] != 1) && ($_GET['value'] != 1)) {
			if ($item['author'] == $id)
				$allowed = true;
		}
		if (!$allowed) {
			require_once('../includes/getRights.php');
			if (hasRight('moderator')) {
				$allowed = true;
				$moderator = true;
			}
		}
		if ($allowed) {
			mysql_query('UPDATE `'.$tableToLock.'` SET locked="'. $_GET['value'] .'" WHERE id="'. $idToLock .'"');
			if ($moderator)
				mysql_query('INSERT INTO `mklogs` VALUES(NULL,NULL, '. $id .', "'. $logAction .' '. $idToLock .'")');
		}
	}
	header('location: '.$url);
	mysql_close();
}
?>