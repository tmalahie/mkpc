<?php
if (isset($_POST['challenge']) && isset($_POST['status'])) {
	include('getId.php');
	include('initdb.php');
	require_once('utils-challenges.php');
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
					include('session.php');
					require_once('getRights.php');
					if (hasRight('clvalidator')) {
						$newStatus = 'active';
						activateChallenge($challenge);
					}
					else
						mysql_query('UPDATE `mkchallenges` SET status="pending_moderation",date=NULL WHERE id="'. $challengeId .'"');
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