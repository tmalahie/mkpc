<?php
header('Content-Type: text/plain');
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
	$requireOwner = !hasCollabGrants('arenes', $trackId, $_POST['collab'], 'edit');
	if ($getCircuit = mysql_fetch_array(mysql_query('SELECT publication_date FROM arenes WHERE id="'.$trackId.'"'. ($requireOwner ? (' AND identifiant='.$identifiants[0].' AND identifiant2='.$identifiants[1].' AND identifiant3='.$identifiants[2].' AND identifiant4='.$identifiants[3]) : '')))) {
		include('../includes/utils-cooldown.php');
		if (!$getCircuit['publication_date'] && isTrackCooldowned(array('type' => 'arenes'))) {
			logCooldownEvent('track');
			echo 1;
			mysql_close();
			exit;
		}
		mysql_query('UPDATE `arenes` SET nom="'.$_POST['nom'].'",auteur="'.$_POST['auteur'].'",publication_date=IFNULL(publication_date,CURRENT_TIMESTAMP()) WHERE id="'.$trackId.'"');
		include('../includes/session.php');
		if ($id && !$getCircuit['publication_date']) {
			$getFollowers = mysql_query('SELECT follower FROM `mkfollowusers` WHERE followed="'. $id .'"');
			while ($follower = mysql_fetch_array($getFollowers))
				mysql_query('INSERT INTO `mknotifs` SET type="follower_circuit", user="'. $follower['follower'] .'", link="2,'.$trackId.'"');
		}
		include('../includes/postCircuitUpdate.php');
		postCircuitUpdate('arenes', $trackId);
	}
	echo $trackId;
	mysql_close();
}
?>