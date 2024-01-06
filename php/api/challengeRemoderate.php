<?php
header('Content-Type: text/plain');
if (isset($_POST['challenge'])) {
	include('../includes/initdb.php');
	include('../includes/session.php');
	require_once('../includes/getRights.php');
	if (hasRight('clvalidator')) {
		$challengeId = $_POST['challenge'];
		if ($challenge = mysql_fetch_array(mysql_query('SELECT status,difficulty,clist,validation FROM `mkchallenges` WHERE id="'. $challengeId .'"'))) {
            $clRace = mysql_fetch_array(mysql_query('SELECT identifiant,identifiant2,identifiant3,identifiant4 FROM mkclrace WHERE id='. $challenge['clist']));
            $validStatus = false;
            $validation = '';
            if (!empty($challenge['validation'])) {
                $validationData = json_decode($challenge['validation']);
                if (isset($validationData->feedbacks)) {
					$validationData = array(
						'feedbacks' => $validationData->feedbacks
					);
					$validation = mysql_real_escape_string(json_encode($validationData));
                }
            }
            switch ($challenge['status']) {
            case 'active':
                $validStatus = true;
				mysql_query('UPDATE `mkchallenges` SET status="pending_moderation",validation="'.$validation.'" WHERE id="'. $challengeId .'"');
                $getWins = mysql_query('SELECT player FROM `mkclwin` WHERE challenge="'. $challengeId .'"');
                require_once('../includes/challenge-consts.php');
				$challengeRewards = getChallengeRewards();
                $challengeReward = $challengeRewards[$challenge['difficulty']];
                while ($clWin = mysql_fetch_array($getWins))
                    mysql_query('UPDATE `mkjoueurs` SET pts_challenge=pts_challenge-'.$challengeReward.' WHERE id="'. $clWin['player'] .'"');
                $logKey = 'UAChallenge';
                break;
            case 'pending_completion':
                $validStatus = true;
                mysql_query('UPDATE `mkchallenges` SET status="pending_moderation",validation="'.$validation.'" WHERE id="'. $challengeId .'"');
                if ($clRace) {
                    if ($getPlayer = mysql_fetch_array(mysql_query('SELECT p.id FROM mkprofiles p INNER JOIN mkjoueurs j ON p.id=j.id WHERE p.identifiant="'.$clRace['identifiant'].'" AND p.identifiant2="'.$clRace['identifiant2'].'" AND p.identifiant3="'.$clRace['identifiant3'].'" AND p.identifiant4="'.$clRace['identifiant4'].'" ORDER BY j.deleted,j.pts_challenge DESC,p.id DESC LIMIT 1')))
                        mysql_query('INSERT IGNORE INTO `mkclwin` SET challenge="'. $challengeId .'",player="'. $getPlayer['id'] .'",creator=1');
                }
                $logKey = 'URChallenge';
                break;
            }
            if ($validStatus) {
                if ($clRace)
                    mysql_query('DELETE FROM `mknotifs` WHERE type="challenge_moderated" AND identifiant="'. $clRace['identifiant'] .'" AND identifiant2="'. $clRace['identifiant2'] .'" AND identifiant3="'. $clRace['identifiant3'] .'" AND identifiant4="'. $clRace['identifiant4'] .'" AND link="'. $challengeId .'"');
                mysql_query('INSERT INTO `mklogs` VALUES(NULL,NULL, '. $id .', "'. $logKey .' '. $challengeId .'")');
            }
        }
    }
	mysql_close();
	echo 1;
}