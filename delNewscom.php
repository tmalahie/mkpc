<?php
if (isset($_GET['id'])) {
	include('session.php');
	if ($id) {
		include('initdb.php');
		$getBanned = mysql_fetch_array(mysql_query('SELECT banned FROM `mkjoueurs` WHERE id="'. $id .'"'));
		if ($getBanned && $getBanned['banned']) {
			mysql_close();
			exit;
		}
		require_once('getRights.php');
		$getCom = mysql_query('SELECT author,news FROM `mknewscoms` WHERE id="'. $_GET['id'] .'"');
		if ($comment = mysql_fetch_array($getCom)) {
			if (($comment['author']==$id) || hasRight('moderator')) {
				mysql_query('DELETE FROM `mknewscoms` WHERE id="'. $_GET['id'] .'"');
				mysql_query('UPDATE `mknews` SET nbcomments=nbcomments-1 WHERE id="'. $comment['news'] .'"');
				if ($comment['author']!=$id)
					mysql_query('INSERT INTO `mklogs` VALUES(NULL, '. $id .', "DNewscom '. $_GET['id'] .'")');
				mysql_close();
				header('location: news.php?id='.$comment['news'].'#news-comment-ctn-0');
			}
		}
		mysql_close();
	}
}
?>