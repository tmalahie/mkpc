<?php
if (isset($_GET['rw'])) {
	include('getId.php');
	include('language.php');
	include('initdb.php');
    require_once('utils-challenges.php');
    $reward = mysql_fetch_array(mysql_query('SELECT id,clist FROM mkclrewards WHERE id="'. $_GET['rw'] .'"'));
	if ($reward)
		$clRace = getClRace($reward['clist']);
	include('challenge-cldata.php');
	if (!empty($clRace)) {
        mysql_query('DELETE FROM mkclrewards WHERE id="'. $reward['id'] .'"');
        mysql_query('DELETE FROM mkclrewardchs WHERE reward="'. $reward['id'] .'"');
	}
	mysql_close();
	header('location: '. nextPageUrl('challengeRewards.php', array('cl'=>$clRace['clid'],'rw'=>null)));
}
?>