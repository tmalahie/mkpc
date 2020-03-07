<?php
if (isset($_POST['id'])) {
	include('session.php');
	$res = array();
	include('initdb.php');
	$challengeId = $_POST['id'];
	if ($challenge = mysql_fetch_array(mysql_query('SELECT id,clist,difficulty,status FROM `mkchallenges` WHERE id="'. $challengeId .'" AND status!="deleted"'))) {
		if ($id)
			mysql_query('INSERT IGNORE INTO `mkclwin` SET challenge="'. $challengeId .'",player="'. $id .'"');
		if ('active' === $challenge['status']) {
			if ($id) {
				if (mysql_affected_rows()) {
					require_once('challenge-consts.php');
					$reward = getChallengeReward($challenge);
					$res['pts'] = $reward;
					$getOldPts = mysql_fetch_array(mysql_query('SELECT pts_challenge FROM `mkjoueurs` WHERE id='.$id));
					$res['pts_before'] = $getOldPts['pts_challenge'];
					$res['pts_after'] = $getOldPts['pts_challenge']+$reward;
					mysql_query('UPDATE `mkjoueurs` SET pts_challenge='.$res['pts_after'].' WHERE id='. $id);
				}
				include('challengeMyRate.php');
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
	}
	mysql_close();
	echo json_encode($res);
}
?>