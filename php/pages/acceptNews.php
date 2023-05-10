<?php
include('../includes/language.php');
include('../includes/session.php');
include('../includes/initdb.php');
require_once('../includes/getRights.php');
if (isset($_GET['id']) && ($news=mysql_fetch_array(mysql_query('SELECT * FROM `mknews` WHERE id="'. $_GET['id'] .'"'))) && hasRight('publisher')) {
	mysql_query('UPDATE `mknews` SET status="accepted",publication_date=NULL WHERE id="'. $_GET['id'] .'"');
	if ($news['author'] != $id) {
		mysql_query('INSERT INTO `mknotifs` SET type="news_moderated", user="'. $news['author'] .'", link="'. $news['id'] .'"');
		mysql_query('INSERT INTO `mklogs` VALUES(NULL,NULL, '. $id .', "ANews '. $_GET['id'] .'")');
	}
	$getFollowers = mysql_query('SELECT follower FROM `mkfollowusers` WHERE followed="'. $news['author'] .'"');
	while ($follower = mysql_fetch_array($getFollowers))
		mysql_query('INSERT INTO `mknotifs` SET type="follower_news", user="'. $follower['follower'] .'", link="'.$_GET['id'].'"');
	header('location: news.php?id='.urlencode($_GET['id']));
}
mysql_close();
?>