<?php
function isBanned() {
	global $identifiants;
	return mysql_numrows(mysql_query('SELECT * FROM `ip_bans` WHERE ip1="'.$identifiants[0].'" AND ip2="'.$identifiants[1].'" AND ip3="'.$identifiants[2].'" AND ip4="'.$identifiants[3].'"'));
}
function getPublishRestriction() {
	global $dbh;
	if (isBanned())
		return 'banned';
	include('session.php');
	if (!$id)
		return 'logged_out';
	$getBanned = mysql_fetch_array(mysql_query('SELECT banned FROM `mkjoueurs` WHERE id="'. $id .'"'));
	if (!$getBanned || $getBanned['banned'])
		return 'banned';
	return null;
}
function canPublishCreations() {
	return !getPublishRestriction();
}
?>