<?php
include('language.php');
include('session.php');
include('initdb.php');
require_once('getRights.php');
if (isset($_GET['id']) && isset($_GET['reason']) && ($news=mysql_fetch_array(mysql_query('SELECT * FROM `mknews` WHERE id="'. $_GET['id'] .'"'))) && hasRight('publisher')) {
	mysql_query('UPDATE `mknews` SET status="rejected",reject_reason="'. $_GET['reason'] .'",publication_date=NULL WHERE id="'. $_GET['id'] .'"');
	if ($news['author'] != $id) {
		mysql_query('INSERT INTO `mknotifs` SET type="news_moderated", user="'. $news['author'] .'", link="'. $news['id'] .'"');
		mysql_query('INSERT INTO `mklogs` VALUES(NULL, '. $id .', "RNews '. $_GET['id'] .'")');
	}
	header('location: news.php?id='.$_GET['id'].'#news-status');
}
mysql_close();
?>