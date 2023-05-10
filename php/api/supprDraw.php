<?php
header('Content-Type: text/plain');
if (isset($_POST['id'])) {
	include('../includes/initdb.php');
	include('../includes/getId.php');
	require_once('../includes/collabUtils.php');
	$trackId = intval($_POST['id']);
	$requireOwner = !hasCollabGrants('circuits', $trackId, $_POST['collab'], 'edit');
	mysql_query('UPDATE `circuits` SET nom=NULL, auteur="" WHERE id="'. $trackId .'"'. ($requireOwner ? (' AND identifiant='.$identifiants[0].' AND identifiant2='.$identifiants[1].' AND identifiant3='.$identifiants[2].' AND identifiant4='.$identifiants[3]) : ''));
	include('../includes/postCircuitUpdate.php');
	postCircuitDelete('circuits', $trackId);
	mysql_close();
	echo 1;
}
?>