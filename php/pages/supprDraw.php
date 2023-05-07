<?php
header('Content-Type: text/plain');
if (isset($_POST['id'])) {
	include('initdb.php');
	include('getId.php');
	require_once('collabUtils.php');
	$trackId = intval($_POST['id']);
	$requireOwner = !hasCollabGrants('circuits', $trackId, $_POST['collab'], 'edit');
	mysql_query('UPDATE `circuits` SET nom=NULL, auteur="" WHERE id="'. $trackId .'"'. ($requireOwner ? (' AND identifiant='.$identifiants[0].' AND identifiant2='.$identifiants[1].' AND identifiant3='.$identifiants[2].' AND identifiant4='.$identifiants[3]) : ''));
	include('postCircuitUpdate.php');
	postCircuitDelete('circuits', $trackId);
	mysql_close();
	echo 1;
}
?>