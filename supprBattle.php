<?php
if (isset($_POST['id'])) {
	include('initdb.php');
	include('getId.php');
	require_once('collabUtils.php');
	$requireOwner = !hasCollabGrants('arenes', $_POST['id'], $_POST['collab'], 'edit');
	mysql_query('UPDATE `arenes` SET nom=NULL, auteur="" WHERE id="'. $_POST['id'] .'"'. ($requireOwner ? (' AND identifiant='.$identifiants[0].' AND identifiant2='.$identifiants[1].' AND identifiant3='.$identifiants[2].' AND identifiant4='.$identifiants[3]) : ''));
	mysql_close();
	echo 1;
}
?>