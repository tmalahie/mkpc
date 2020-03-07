<?php
if (isset($_GET['id'])) {
	include('initdb.php');
	include('session.php');
	$persoId = $_GET['id'];
	if ($perso = mysql_fetch_array(mysql_query('SELECT * FROM `mkchars` WHERE id="'. $persoId .'"'))) {
		include('getId.php');
		if (($perso['identifiant'] == $identifiants[0]) && ($perso['identifiant2'] == $identifiants[1]) && ($perso['identifiant3'] == $identifiants[2]) && ($perso['identifiant4'] == $identifiants[3])) {
			mysql_query('UPDATE `mkchars` SET author=NULL WHERE id="'. $persoId .'"');
			header('location: persoEditor.php?unshared='.$persoId);
		}
	}
	mysql_close();
}
?>