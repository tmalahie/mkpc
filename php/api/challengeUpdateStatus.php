<?php
header('Content-Type: text/plain');
if (isset($_POST['challenge']) && isset($_POST['status'])) {
	include('../includes/getId.php');
	include('../includes/initdb.php');
	include('../includes/language.php');
	require_once('../includes/utils-challenges.php');
	$challenge = getChallenge($_POST['challenge'], false, 'manage_challenges');
	$newStatus = 'unknown';
	if (isset($challenge)) {
		$oldStatus = $challenge['status'];
		$newStatus = $_POST['status'];
		$challengeId = $challenge['id'];
		switch ($newStatus) {
		case 'pending_completion':
			if (in_array($oldStatus, array('pending_publication','pending_moderation')))
				resetChallengeCompletion($challenge);
			break;
		case 'pending_publication':
			if ('pending_moderation' === $oldStatus)
				mysql_query('UPDATE `mkchallenges` SET status="pending_publication" WHERE id="'. $challengeId .'"');
			break;
		case 'pending_moderation':
			if (($getClRace = mysql_fetch_array(mysql_query('SELECT type FROM `mkclrace` WHERE id="'. $challenge['clist'] .'"'))) && $getClRace['type']) {
				if ('pending_publication' === $oldStatus) {
					include('../includes/session.php');
					include('../includes/ip_banned.php');
					require_once('../includes/getRights.php');
					require_once('../includes/utils-publishers.php');
					$publishRestriction = getPublishRestriction($id);
					if ($publishRestriction)
						$newStatus = ('logged_out' === $publishRestriction) ? 'account_required' : 'banned';
					elseif (hasRight('clvalidator')) {
						$newStatus = 'active';
						activateChallenge($challenge);
						recordPublisher('mkchallenges', $challengeId, $id);
					}
					else {
						if ($isBanned = mysql_fetch_array(mysql_query('SELECT ban_until_date FROM `mkclbans` WHERE identifiant='. $identifiants[0]))) {
							if ($isBanned['ban_until_date'] && strtotime($isBanned['ban_until_date']) < time())
								mysql_query('DELETE FROM `mkclbans` WHERE identifiant='. $identifiants[0]);
							else
								$newStatus = 'banned';
						}
						if (isset($_POST['require_confirmation'])) {
							$getOneChallenge = mysql_query('SELECT c.id FROM mkchallenges c INNER JOIN mkpublishers p ON p.type="mkchallenges" AND p.creation_id=c.id WHERE p.last_editor="'. $id .'" AND c.status IN ("active","deleted") LIMIT 1');
							$challenge = mysql_fetch_array($getOneChallenge);
							if (!$challenge)
								$newStatus = 'confirmation_required';
						}
						if ('pending_moderation' === $newStatus) {
							mysql_query('UPDATE `mkchallenges` SET status="pending_moderation",date=NULL WHERE id="'. $challengeId .'"');
							recordPublisher('mkchallenges', $challengeId, $id);
						}
					}
				}
			}
			else
				$newStatus = 'pending_circuit';
			break;
		}
	}
	mysql_close();
	echo $newStatus;
}
?>