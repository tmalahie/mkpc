<?php
if (isset($_POST['id']) && isset($_POST['nom']) && isset($_POST['auteur'])) {
	include('../includes/initdb.php');
	include('../includes/getId.php');
	include('../includes/ip_banned.php');
	if (isBanned()) {
		mysql_close();
		exit;
	}
	setcookie('mkauteur', $_POST['auteur'], 4294967295,'/');
	$trackId = intval($_POST['id']);

	require_once('../includes/collabUtils.php');
	$requireOwner = !hasCollabGrants('circuits', $trackId, $_POST['collab'], 'edit');
	if ($getCircuit = mysql_fetch_array(mysql_query('SELECT publication_date FROM circuits WHERE id="'.$trackId.'"'. ($requireOwner ? (' AND identifiant='.$identifiants[0].' AND identifiant2='.$identifiants[1].' AND identifiant3='.$identifiants[2].' AND identifiant4='.$identifiants[3]) : '')))) {
		include('../includes/utils-cooldown.php');
		if (!$getCircuit['publication_date'] && isTrackCooldowned(array('type' => 'circuits'))) {
			logCooldownEvent('track');
			echo 1;
			mysql_close();
			exit;
		}
		mysql_query('UPDATE `circuits` SET nom="'.$_POST['nom'].'",auteur="'.$_POST['auteur'].'",publication_date=IFNULL(publication_date,CURRENT_TIMESTAMP()) WHERE id="'.$trackId.'"');
		include('../includes/session.php');
		if ($id && !$getCircuit['publication_date']) {
			$getFollowers = mysql_query('SELECT follower FROM `mkfollowusers` WHERE followed="'. $id .'"');
			while ($follower = mysql_fetch_array($getFollowers))
				mysql_query('INSERT INTO `mknotifs` SET type="follower_circuit", user="'. $follower['follower'] .'", link="1,'.$trackId.'"');
		}
		include('../includes/postCircuitUpdate.php');
		postCircuitUpdate('circuits', $trackId);
	}
	echo $trackId;
	mysql_close();
}
?>