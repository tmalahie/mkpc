<?php
if (isset($_GET['user']) && is_numeric($_GET['user'])) {
	$user = intval($_GET['user']);
	include('../includes/session.php');
	if ($id && ($id!=$user)) {
		include('../includes/initdb.php');
		if (isset($_GET['follow'])) {
			if (mysql_fetch_array(mysql_query('SELECT * FROM `mkjoueurs` WHERE id="'. $user .'"'))) {
				require_once('../includes/banUtils.php');
				$getBanned = mysql_fetch_array(mysql_query('SELECT banned FROM `mkjoueurs` WHERE id="'. $id .'"'));
				$isBanned = $getBanned && $getBanned['banned'];
				if (!$isBanned && isVisibleFollower($user)) {
					mysql_query('INSERT IGNORE INTO `mkfollowusers` VALUES("'. $id .'","'. $user .'",NULL)');
					mysql_query('INSERT INTO `mknotifs` SET type="new_followuser", user="'. $user .'", link="'.$id.'"');
				}
			}
		}
		else
			mysql_query('DELETE FROM `mkfollowusers` WHERE follower="'. $id .'" AND followed="'. $user .'"');
		mysql_close();
		if (isset($_GET['src']) && ($_GET['src'] === 'follows'))
			header('location: listFollowed.php'. (isset($_GET['page']) ? '?page='.urlencode($_GET['page']):''));
		else
			header('location: profil.php?id='.$user.'&followed='.(isset($_GET['follow'])?1:0));
	}
	else
		header('location: profil.php?id='.$user);
}
?>
