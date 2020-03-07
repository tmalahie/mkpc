<?php
if (isset($_POST['id'])) {
	include('initdb.php');
	include('getId.php');
	mysql_query('UPDATE `circuits` SET nom=NULL, auteur="" WHERE id="'. $_POST['id'] .'" AND identifiant='.$identifiants[0].' AND identifiant2='.$identifiants[1].' AND identifiant3='.$identifiants[2].' AND identifiant4='.$identifiants[3]);
	mysql_close();
	echo 1;
}
?>