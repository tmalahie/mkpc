<?php
header('Content-Type: application/json');
$body = file_get_contents('php://input');
if (isset($_POST['id'])) {
	include('../includes/language.php');
	include('../includes/session.php');
	$res = array();
	include('../includes/initdb.php');
	include('../includes/utils-hash.php');
	$challengeId = $_POST['id'];
	if ($challenge = mysql_fetch_array(mysql_query('SELECT id,clist,difficulty,status FROM `mkchallenges` WHERE id="'. $challengeId .'" AND status!="deleted"'))) {
		if (!isHashValid($body)) {
			include('../includes/getId.php');
			logHashInvalid($body);
			$id = null;
		}
		if ($id)
			$q = mysql_query('INSERT IGNORE INTO `mkclwin` SET challenge="'. $challengeId .'",player="'. $id .'"');
		if ('active' === $challenge['status']) {
			if ($id) {
				if (mysql_affected_rows()) {
					require_once('../includes/challenge-consts.php');
					$reward = getChallengeReward($challenge);
					$res['pts'] = $reward;
					$getOldPts = mysql_fetch_array(mysql_query('SELECT pts_challenge FROM `mkjoueurs` WHERE id='.$id));
					$res['pts_before'] = $getOldPts['pts_challenge'];
					$res['pts_after'] = $getOldPts['pts_challenge']+$reward;
					mysql_query('UPDATE `mkjoueurs` SET pts_challenge='.$res['pts_after'].' WHERE id='. $id);
				}
				include('../includes/challengeMyRate.php');
				$myRate = getMyRating($challenge);
				if (!empty($myRate))
					$res['rating'] = $myRate['rating'];
			}
		}
		elseif ('pending_completion' === $challenge['status']) {
			if ($id)
				mysql_query('UPDATE `mkclwin` SET creator=1 WHERE challenge="'. $challengeId .'" AND player="'. $id .'"');
			mysql_query('UPDATE `mkchallenges` SET status="pending_publication" WHERE id="'. $challengeId .'"');
			$res['publish'] = true;
		}
		if ($id) {
			require_once('../includes/utils-challenges.php');
			$completedRewards = getRewardedPlayers(array(
				'challenge' => $challengeId,
				'player' => $id
			));
			if (!empty($completedRewards)) {
				$currentlyUnlocked = array();
				$getUnlocked = mysql_query('SELECT r.charid FROM mkclrewarded rp INNER JOIN mkclrewards r ON rp.reward=r.id WHERE rp.player='. $id);
				while ($unlock = mysql_fetch_array($getUnlocked))
					$currentlyUnlocked[$unlock['charid']] = true;
				foreach ($completedRewards as $reward)
					mysql_query('INSERT IGNORE INTO mkclrewarded  SET reward='. $reward['reward'] .',player='.$id);
				$rewardIds = array();
				foreach ($completedRewards as $reward) {
					$rewardIds[] = $reward['reward'];
					$res['rewards'][] = array('id' => $reward['reward']);
				}
				$newUnlocked = mysql_query('SELECT c.id,c.name,c.sprites FROM mkclrewards r INNER JOIN mkchars c ON r.charid=c.id WHERE r.id IN('. implode(',',$rewardIds) .') GROUP BY c.id');
				while ($unlocked = mysql_fetch_array($newUnlocked)) {
					if (!isset($currentlyUnlocked[$unlocked['id']])) {
						$res['unlocked'][] = array(
							'id' => $unlocked['id'],
							'name' => $unlocked['name'],
							'sprites' => $unlocked['sprites']
						);
					}
				}
			}
		}
	}
	mysql_close();
	echo json_encode($res);
}
?>