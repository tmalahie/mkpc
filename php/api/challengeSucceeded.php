<?php
if (isset($_POST['id'])) {
	header('Content-Type: application/json');
	include('../includes/language.php');
	include('../includes/session.php');
	$res = array();
	include('../includes/initdb.php');
	$challengeId = $_POST['id'];
	if ($challenge = mysql_fetch_array(mysql_query('SELECT id,clist,difficulty,status FROM `mkchallenges` WHERE id="'. $challengeId .'" AND status!="deleted"'))) {
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
				include('../includes/advent-selected-challenges.php');
				$selectedDay = null;
				foreach ($selectedChallenges as $d => $selectedChallengeId) {
					if ($selectedChallengeId == $challengeId) {
						$selectedDay = $d;
						break;
					}
				}
				$shouldCompleteAll = false;
				$selectedGroup = null;
				$challengeGroups = array(
					'19' => array(31410,39318,40041,40386)
				);
				foreach ($challengeGroups as $challengeGroupDay => $challengeGroup) {
					if (in_array($challenge['id'], $challengeGroup)) {
						$selectedDay = $challengeGroupDay;
						$shouldCompleteAll = true;
						$selectedGroup = $challengeGroup;
						break;
					}
				}
				date_default_timezone_set('Europe/Paris');
				if ($selectedDay && ($selectedDay <= date('j') && (date('n') == 12))) {
					$year = date('Y');
					$alreadyCompleted = mysql_fetch_array(mysql_query('SELECT date FROM mkadvent WHERE year="'. $year .'" AND user="'. $id .'" AND day="'. $selectedDay .'"'));
					if ($shouldCompleteAll) {
						$selectedGroupIds = implode(',', $selectedGroup);
						$pendingCompletion = mysql_fetch_array(mysql_query('SELECT * FROM mkchallenges c LEFT JOIN mkclwin w ON c.id=w.challenge AND w.player="'.$id.'" WHERE c.id IN ('.$selectedGroupIds.') AND w.id IS NULL LIMIT 1'));
						if ($pendingCompletion)
							$alreadyCompleted = true;
					}
					if (!$alreadyCompleted) {
						mysql_query('INSERT INTO mkadvent SET year="'. $year .'", user="'. $id .'", day="'. $selectedDay .'"');
						require_once('../includes/challenge-consts.php');
						$reward = getChallengeReward($challenge);
						if ($shouldCompleteAll) {
							$otherChallenges = mysql_query('SELECT difficulty FROM mkchallenges WHERE id IN ('. $selectedGroupIds .') AND id!="'. $challengeId .'"');
							while ($otherChallenge = mysql_fetch_array($otherChallenges))
								$reward += getChallengeReward($otherChallenge);
						}
						$res['pts_advent'] = $reward;
						$alreadyCompletedCount = mysql_fetch_array(mysql_query('SELECT COUNT(*) AS nb FROM mkadvent WHERE year="'. $year .'" AND user="'. $id .'"'));
						$nbCompleted = $alreadyCompletedCount['nb'];
						if (isset($adventChallengeRewardsByNb[$nbCompleted])) {
							$extraReward = $adventChallengeRewardsByNb[$nbCompleted];
							$res['pts_advent_extra'] = $extraReward;
							$reward += $extraReward;
						}
						$getOldPts = mysql_fetch_array(mysql_query('SELECT pts_challenge FROM `mkjoueurs` WHERE id='.$id));
						$res['pts_before_advent'] = $getOldPts['pts_challenge'];
						if (!isset($res['pts_before'])) $res['pts_before'] = $res['pts_before_advent'];
						$res['pts_after'] = $res['pts_before_advent'] + $reward;
						mysql_query('UPDATE `mkjoueurs` SET pts_challenge='.$res['pts_after'].' WHERE id='. $id);
					}
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