<?php
if (isset($_GET['topic'])) {
	$tableToLock = 'mktopics';
	$idToLock = $_GET['topic'];
	$url = 'topic.php?topic='.$idToLock;
	$getItem = 'SELECT t.locked,t.titre AS title,m.auteur AS author FROM mktopics t INNER JOIN mkmessages m ON m.topic=t.id AND m.id=1 WHERE t.id="'. $idToLock .'"';
	$logAction = $_GET['value'] ? 'LTopic':'ULTopic';
	$lockType = 'forum_topic';
}
elseif (isset($_GET['news'])) {
	$tableToLock = 'mknews';
	$idToLock = $_GET['news'];
	$url = 'news.php?id='.$idToLock.'#news-comments';
	$getItem = 'SELECT locked,title,author FROM mknews WHERE id="'. $idToLock .'"';
	$logAction = $_GET['value'] ? 'LNews':'ULNews';
	$lockType = 'news';
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
			require_once('../includes/utils-logs.php');
			if (hasRight('moderator')) {
				$allowed = true;
				$moderator = true;
			}
		}
		if ($allowed) {
			mysql_query('UPDATE `'.$tableToLock.'` SET locked="'. $_GET['value'] .'" WHERE id="'. $idToLock .'"');
			if ($moderator)
				insertLog($id, $logAction .' '. $idToLock, array(
					'type' => $lockType,
					'id' => intval($idToLock),
					'title' => $item['title'],
					'author' => snapshotMember($item['author']),
					'before' => array('locked' => intval($item['locked'])),
					'after' => array('locked' => intval($_GET['value']))
				));
		}
	}
	header('location: '.$url);
	mysql_close();
}
?>