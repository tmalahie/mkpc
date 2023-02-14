<?php
if (isset($_GET['news']) && isset($_POST['comment'])) {
	include('session.php');
	if ($id) {
		include('initdb.php');
		if ($news = mysql_fetch_array(mysql_query('SELECT * FROM `mknews` WHERE id="'. $_GET['news'] .'" AND locked=0'))) {
			$getBanned = mysql_fetch_array(mysql_query('SELECT banned FROM `mkjoueurs` WHERE id="'. $id .'"'));
			if ($getBanned && $getBanned['banned']) {
				mysql_close();
				exit;
			}
			include('getId.php');
			include('utils-cooldown.php');
			if (isNewsComCooldowned()) {
				logCooldownEvent('news_com');
				header('location: news.php?id='.$_GET['news']);
				mysql_close();
				exit;
			}
			mysql_query('INSERT INTO `mknewscoms` SET news="'. $_GET['news'] .'",author="'. $id .'", message="'. $_POST['comment'] .'"');
			$commentId = mysql_insert_id();
			mysql_query('UPDATE `mknews` SET nbcomments=nbcomments+1 WHERE id="'. $_GET['news'] .'"');
			if ($news['author'] != $id)
				mysql_query('INSERT INTO `mknotifs` SET type="news_comment", user="'. $news['author'] .'", link="'. $commentId .'"');
			$otherComments = mysql_query('SELECT DISTINCT author FROM `mknewscoms` WHERE news="'. $_GET['news'] .'" AND author!="'. $id .'" AND author!="'. $news['author'] .'"');
			while ($otherComment = mysql_fetch_array($otherComments))
				mysql_query('INSERT INTO `mknotifs` SET type="answer_newscom", user="'. $otherComment['author'] .'", link="'. $commentId .'"');
			mysql_query('UPDATE `mkprofiles` SET last_connect=NULL WHERE id='. $id);
			header('location: news.php?id='.$_GET['news'].'#news-comment-ctn-'.$commentId);
		}
		mysql_close();
	}
}
?>