<?php
if (isset($_GET['ch'])) {
	include('getId.php');
	include('language.php');
	include('initdb.php');
	require_once('utils-challenges.php');
	$challenge = getChallenge($_GET['ch']);
	if ($challenge)
		$clRace = getClRace($challenge['clist']);
	include('challenge-cldata.php');
	if (!empty($clRace)) {
		if (mysql_fetch_array(mysql_query('SELECT player FROM `mkclwin` WHERE challenge="'. $challenge['id'] .'" AND creator=0')))
			mysql_query('UPDATE `mkchallenges` SET status="deleted" WHERE id="'. $challenge['id'] .'"');
		else
			mysql_query('DELETE FROM `mkchallenges` WHERE id="'. $challenge['id'] .'"');
	}
	mysql_close();
	header('location: '. nextPageUrl('challenges.php', array('cl'=>$clRace['clid'],'ch'=>null)));
}
?>