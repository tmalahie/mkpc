<?php
include('../includes/language.php');
include('../includes/session.php');
include('../includes/initdb.php');
require_once('../includes/getRights.php');
require_once('../includes/utils-logs.php');
if (isset($_GET['id']) && isset($_GET['reason']) && ($news=mysql_fetch_array(mysql_query('SELECT * FROM `mknews` WHERE id="'. $_GET['id'] .'"'))) && hasRight('publisher')) {
	mysql_query('UPDATE `mknews` SET status="rejected",reject_reason="'. $_GET['reason'] .'",publication_date=NULL WHERE id="'. $_GET['id'] .'"');
	if ($news['author'] != $id) {
		mysql_query('INSERT INTO `mknotifs` SET type="news_moderated", user="'. $news['author'] .'", link="'. $news['id'] .'"');
		$rejected = snapshotQuery('SELECT reject_reason FROM `mknews` WHERE id="'. $_GET['id'] .'"');
		insertLog($id, 'RNews '. $_GET['id'], array_merge(snapshotNews($news), array(
			'reject_reason' => $rejected['reject_reason'],
			'before' => array('status' => $news['status']),
			'after' => array('status' => 'rejected')
		)));
	}
	header('location: news.php?id='.urlencode($_GET['id']).'#news-status');
}
mysql_close();
?>