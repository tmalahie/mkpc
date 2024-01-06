<?php
header('Content-Type: text/plain');
if (isset($_POST['challenge']) && isset($_POST['accept'])) {
	include('../includes/initdb.php');
	include('../includes/session.php');
	require_once('../includes/getRights.php');
	if (hasRight('clvalidator')) {
		$challengeId = $_POST['challenge'];
		if ($challenge = mysql_fetch_array(mysql_query('SELECT status,difficulty,clist,validation FROM `mkchallenges` WHERE id="'. $challengeId .'"'))) {
			if ('pending_moderation' === $challenge['status']) {
				$validationData = array();
				if (isset($challenge['validation'])) {
					$oldValidationData = json_decode($challenge['validation']);
					if (isset($oldValidationData->feedbacks))
						$validationData['feedbacks'] = $oldValidationData->feedbacks;
				}
				$accept = $_POST['accept'];
				$oldDifficulty = $challenge['difficulty'];
				$newDifficulty = $oldDifficulty;
				if ($accept) {
					$newStatus = 'active';
					if (isset($_POST['difficulty']) && ($_POST['difficulty'] != $challenge['difficulty'])) {
						$validationData['old_difficulty'] = $challenge['difficulty'];
						$newDifficulty = $_POST['difficulty'];
						$logKey = 'DChallenge';
					}
					else
						$logKey = 'AChallenge';
				}
				else {
					$newStatus = 'pending_completion';
					$logKey = 'RChallenge';
				}
				if (isset($_POST['msg']))
					$validationData['msg'] = stripslashes($_POST['msg']);
				$validation = mysql_real_escape_string(json_encode($validationData));
				mysql_query('UPDATE `mkchallenges` SET status="'. $newStatus .'", difficulty="'. $newDifficulty .'",validation="'.$validation.'" WHERE id="'. $challengeId .'"');
				mysql_query('INSERT INTO `mklogs` VALUES(NULL,NULL, '. $id .', "'. $logKey .' '. $challengeId .'")');

				$getWins = mysql_query('SELECT player FROM `mkclwin` WHERE challenge="'. $challengeId .'"');
				require_once('../includes/challenge-consts.php');
				$challengeRewards = getChallengeRewards();
				$challengeReward = $challengeRewards[$newDifficulty];
				$clRace = mysql_fetch_array(mysql_query('SELECT identifiant,identifiant2,identifiant3,identifiant4 FROM mkclrace WHERE id='. $challenge['clist']));
				if ($accept) {
					while ($clWin = mysql_fetch_array($getWins))
						mysql_query('UPDATE `mkjoueurs` SET pts_challenge=pts_challenge+'.$challengeReward.' WHERE id="'. $clWin['player'] .'"');
					mysql_query('UPDATE `mkchallenges` SET date=NULL WHERE id="'. $challengeId .'"');
					if ($clRace) {
						if ($getAuthor = mysql_fetch_array(mysql_query('SELECT w.player FROM `mkclwin` w INNER JOIN `mkprofiles` p ON p.id=w.player AND p.identifiant="'.$clRace['identifiant'].'" AND p.identifiant2="'.$clRace['identifiant2'].'" AND p.identifiant3="'.$clRace['identifiant3'].'" AND p.identifiant4="'.$clRace['identifiant4'].'" WHERE w.challenge="'. $challengeId .'" AND w.creator=1'))) {
							$getFollowers = mysql_query('SELECT follower FROM `mkfollowusers` WHERE followed="'. $getAuthor['player'] .'" AND follower!="'. $id .'"');
							while ($follower = mysql_fetch_array($getFollowers))
								mysql_query('INSERT INTO `mknotifs` SET type="follower_challenge", user="'. $follower['follower'] .'", link="'. $challengeId .'"');
						}
					}
				}
				else {
					mysql_query('DELETE FROM `mkclwin` WHERE challenge="'. $challengeId .'"');
					mysql_query('UPDATE `mkchallenges` SET avgrating=0,nbratings=0 WHERE id="'. $challengeId .'"');
				}
				if ($clRace)
					mysql_query('INSERT INTO `mknotifs` SET type="challenge_moderated", identifiant="'. $clRace['identifiant'] .'",identifiant2="'. $clRace['identifiant2'] .'",identifiant3="'. $clRace['identifiant3'] .'",identifiant4="'. $clRace['identifiant4'] .'", link="'. $challengeId .'"');
			}
		}
	}
	mysql_close();
	echo 1;
}
?>