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
        mysql_query('DELETE c,r,w FROM mkclrewards c LEFT JOIN mkclrewardchs r ON c.id=r.reward LEFT JOIN mkclrewarded w ON c.id=w.reward WHERE c.id="'. $reward['id'] .'"');
	}
	mysql_close();
	header('location: '. nextPageUrl('challengeRewards.php', array('cl'=>$clRace['clid'],'rw'=>null)));
}
?>