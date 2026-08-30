<?php
if (isset($_GET['id']) && isset($_POST['comment'])) {
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
		$getCom = mysql_query('SELECT author,news,message FROM `mknewscoms` WHERE id="'. $_GET['id'] .'"');
		if ($comment = mysql_fetch_array($getCom)) {
			if (($comment['author']==$id) || hasRight('moderator')) {
				mysql_query('UPDATE `mknewscoms` SET message="'. $_POST['comment'] .'" WHERE id="'. $_GET['id'] .'"');
				if ($comment['author']!=$id) {
					$editedComment = snapshotQuery('SELECT message FROM `mknewscoms` WHERE id="'. $_GET['id'] .'"');
					insertLog($id, 'ENewscom '. $_GET['id'], array(
						'type' => 'news_comment',
						'id' => intval($_GET['id']),
						'news' => snapshotQuery('SELECT id,title FROM `mknews` WHERE id="'. $comment['news'] .'"'),
						'author' => snapshotMember($comment['author']),
						'before' => $comment['message'],
						'after' => $editedComment['message']
					));
				}
				header('location: news.php?id='.$comment['news'].'#news-comment-ctn-'.urlencode($_GET['id']));
			}
		}
		mysql_close();
	}
}
?>