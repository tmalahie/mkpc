<?php
if (isset($_GET['user'])) {
	include('session.php');
	if ($id && ($id!=$_GET['user'])) {
		include('initdb.php');
		if (mysql_fetch_array(mysql_query('SELECT * FROM `mkjoueurs` WHERE id="'. $_GET['user'] .'"'))) {
			if (isset($_GET['follow'])) {
				mysql_query('INSERT IGNORE INTO `mkfollowusers` VALUES("'. $id .'","'. $_GET['user'] .'",NULL)');
				mysql_query('INSERT INTO `mknotifs` SET type="new_followuser", user="'. $_GET['user'] .'", link="'.$id.'"');
			}
			else
				mysql_query('DELETE FROM `mkfollowusers` WHERE follower="'. $id .'" AND followed="'. $_GET['user'] .'"');
		}
		mysql_close();
		header('location: profil.php?id='.urlencode($_GET['user']).'&followed='.(isset($_GET['follow'])?1:0));
	}
	else
		header('location: profil.php?id='.urlencode($_GET['user']));
}
?>