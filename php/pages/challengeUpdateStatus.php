<?php
header('Content-Type: text/plain');
if (isset($_POST['challenge']) && isset($_POST['status'])) {
	include('../includes/getId.php');
	include('../includes/initdb.php');
	include('../includes/language.php');
	require_once('../includes/utils-challenges.php');
	$challenge = getChallenge($_POST['challenge']);
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
					require_once('../includes/getRights.php');
					if (hasRight('clvalidator')) {
						$newStatus = 'active';
						activateChallenge($challenge);
					}
					else {
						if (isset($_POST['require_confirmation'])) {
							$getOneChallenge = mysql_query('SELECT c.id FROM mkchallenges c INNER JOIN mkclrace l ON c.clist=l.id WHERE l.identifiant='.$identifiants[0].' AND l.identifiant2='.$identifiants[1].' AND l.identifiant3='.$identifiants[2].' AND l.identifiant4='.$identifiants[3].' AND c.status IN ("active","deleted") LIMIT 1');
							$challenge = mysql_fetch_array($getOneChallenge);
							if (!$challenge)
								$newStatus = 'confirmation_required';
						}
						if ('pending_moderation' === $newStatus)
							mysql_query('UPDATE `mkchallenges` SET status="pending_moderation",date=NULL WHERE id="'. $challengeId .'"');
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