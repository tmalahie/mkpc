<?php
if (isset($_GET['id'])) {
	include('../includes/session.php');
	if ($id) {
		include('../includes/initdb.php');
		$getBanned = mysql_fetch_array(mysql_query('SELECT banned FROM `mkjoueurs` WHERE id="'. $id .'"'));
		if ($getBanned && $getBanned['banned']) {
			mysql_close();
			exit;
		}
		require_once('../includes/getRights.php');
		require_once('../includes/utils-logs.php');
		$getCom = mysql_query('SELECT author,news,message,date FROM `mknewscoms` WHERE id="'. $_GET['id'] .'"');
		if ($comment = mysql_fetch_array($getCom)) {
			if (($comment['author']==$id) || hasRight('moderator')) {
				mysql_query('DELETE FROM `mknewscoms` WHERE id="'. $_GET['id'] .'"');
				mysql_query('UPDATE `mknews` SET nbcomments=nbcomments-1 WHERE id="'. $comment['news'] .'"');
				if ($comment['author']!=$id)
					insertLog($id, 'DNewscom '. $_GET['id'], array(
						'type' => 'news_comment',
						'id' => intval($_GET['id']),
						'news' => snapshotQuery('SELECT id,title FROM `mknews` WHERE id="'. $comment['news'] .'"'),
						'author' => snapshotMember($comment['author']),
						'date' => $comment['date'],
						'content' => $comment['message']
					));
				mysql_close();
				header('location: news.php?id='.$comment['news'].'#news-comment-ctn-0');
			}
		}
		mysql_close();
	}
}
?>