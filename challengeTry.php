<?php
$res = array();
if (isset($_POST['challenge']) || isset($_GET['challenge'])) {
	include('session.php');
	include('initdb.php');
	require_once('utils-challenges.php');
	if (isset($_POST['challenge']))
		$challengeId = $_POST['challenge'];
	else {
		$challengeId = $_GET['challenge'];
		$redirect = true;
	}
	if ($challenge = mysql_fetch_array(mysql_query('SELECT clist,data FROM mkchallenges WHERE id="'. $challengeId .'"'))) {
		if ($id) {
			mysql_query('DELETE FROM `mknotifs` WHERE user="'. $id .'" AND type="follower_challenge" AND link="'. $challengeId .'"');
			if (mysql_fetch_array(mysql_query('SELECT date FROM `mkclwin` WHERE player='.$id.' AND challenge="'.$challengeId.'"')))
				$alreadySucceeded = true;
			else
				mysql_query('INSERT INTO `mkcltry` SET player='.$id.',challenge="'.$challengeId.'"');
		}
		if (!isset($alreadySucceeded) || !isset($redirect)) {
			$challengeData = json_decode($challenge['data']);
			$challengeRules = mergeChallengeRules($challengeData);
			foreach ($challengeRules as $challengeRule) {
				$clRule = $clRules[$challengeRule->type];
				if (isset($clRule['autoset']))
					$clRule['autoset']($res,$challengeRule);
			}
		}
		if (isset($redirect)) {
			if (isset($res['selectedPlayers']))
				setcookie('mkplayers', $res['selectedPlayers'], 4294967295,'/');
			if (isset($res['selectedTeams']))
				setcookie('mkteam', $res['selectedTeams'], 4294967295,'/');
			if (isset($res['selectedDifficulty']))
				setcookie('mkdifficulty', $res['selectedDifficulty'], 4294967295,'/');
			if ($clRace = mysql_fetch_array(mysql_query('SELECT type,circuit FROM mkclrace WHERE id='. $challenge['clist']))) {
				if ($circuitData = getCircuitPayload($clRace))
					$redirectUrl = $circuitData['href'];
			}
			if (!isset($alreadySucceeded))
				$_SESSION['clselected'] = $challengeId;
		}
	}
	mysql_close();
}
if (isset($redirectUrl))
	header("location: $redirectUrl");
else
	echo json_encode($res);
?>