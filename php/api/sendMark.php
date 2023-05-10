<?php
header('Content-Type: text/plain');
if (isset($_POST['note'])) $_POST['rating'] = $_POST['note']+1;
if (isset($_POST['id']) && isset($_POST['rating'])) {
	if (isset($_POST['mc']))
		$table = 'mkmcups';
	elseif (isset($_POST['cup']))
		$table = 'mkcups';
	elseif (isset($_POST['complete'])) {
		if (isset($_POST['battle']))
			$table = 'arenes';
		else
			$table = 'circuits';
	}
	else
		$table = 'mkcircuits';
	function exitAndFlush() {
		mysql_close();
		echo '1';
		exit;
	}
	include('../includes/getId.php');
	include('../includes/initdb.php');
	include('../includes/session.php');
	$circuitId = intval($_POST['id']);
	$rating = intval($_POST['rating']);
	include('../includes/ip_banned.php');
	if (!$id || isBanned()) exitAndFlush();
	$getBanned = mysql_query('SELECT banned FROM `mkjoueurs` WHERE id="'. $id .'"');
	if (($banned=mysql_fetch_array($getBanned)) && $banned['banned'])
		exitAndFlush();
	$newMark = (($rating >= 1) && ($rating <= 5));
	if ($getIdentifier = mysql_fetch_array(mysql_query('SELECT identifiant FROM `'.$table.'` WHERE id="'.$circuitId.'"'))) {
		if ($getIdentifier && ($getIdentifier['identifiant'] != $identifiants[0])) {
			$getOldMark = mysql_query('SELECT id FROM `mkratings` WHERE type="'.$table.'" AND circuit="'. $circuitId .'" AND identifiant='.$identifiants[0]);
			if ($oldMark = mysql_fetch_array($getOldMark)) {
				if ($newMark)
					mysql_query('UPDATE `mkratings` SET rating='. $rating .',player="'.$id.'" WHERE id='. $oldMark['id']);
				else
					mysql_query('DELETE FROM `mkratings` WHERE id='. $oldMark['id']);
			}
			else if ($newMark) {
				include('../includes/utils-cooldown.php');
				if (isRatingCooldowned())
					logCooldownEvent('track_rating');
				else
					mysql_query('INSERT INTO `mkratings` SET type="'.$table.'",circuit="'.$circuitId.'",identifiant="'.$identifiants[0].'",player="'.$id.'",rating='.$rating);
			}
			else {
				echo '1';
				mysql_close();
				exit;
			}
			require_once('../includes/utils-ratings.php');
			recomputeRating($table,$circuitId);
		}
	}
	echo '1';
	mysql_close();
}
?>