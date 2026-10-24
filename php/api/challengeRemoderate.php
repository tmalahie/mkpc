<?php
header('Content-Type: text/plain');
if (isset($_POST['challenge'])) {
	include('../includes/initdb.php');
	include('../includes/session.php');
	require_once('../includes/getRights.php');
	require_once('../includes/utils-logs.php');
	if (hasRight('clvalidator')) {
		$challengeId = $_POST['challenge'];
		if ($challenge = mysql_fetch_array(mysql_query('SELECT id,name,status,difficulty,clist,data,validation FROM `mkchallenges` WHERE id="'. $challengeId .'"'))) {
            require_once('../includes/utils-publishers.php');
            $publishers = getPublishers('mkchallenges', $challengeId);
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
                if ($publishers)
                    mysql_query('INSERT IGNORE INTO `mkclwin` SET challenge="'. $challengeId .'",player="'. $publishers['last_editor'] .'",creator=1');
                $logKey = 'URChallenge';
                break;
            }
            if ($validStatus) {
                mysql_query('DELETE FROM `mknotifs` WHERE type="challenge_moderated" AND link="'. $challengeId .'"');
                $editedChallenge = mysql_fetch_array(mysql_query('SELECT id,name,status,difficulty,clist,data,validation FROM `mkchallenges` WHERE id="'. $challengeId .'"'));
                insertLog($id, $logKey .' '. $challengeId, array(
                    'type' => 'challenge',
                    'id' => intval($challengeId),
                    'before' => snapshotChallenge($challenge),
                    'after' => snapshotChallenge($editedChallenge)
                ));
            }
        }
    }
	mysql_close();
	echo 1;
}