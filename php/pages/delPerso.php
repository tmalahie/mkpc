<?php
if (isset($_GET['id'])) {
	include('../includes/initdb.php');
	include('../includes/session.php');
	if (!isset($_SESSION['csrf']) || !isset($_GET['token']) || ($_SESSION['csrf'] != $_GET['token'])) {
		echo 'Invalid token';
		mysql_close();
		exit;
	}
	$persoId = $_GET['id'];
	if ($perso = mysql_fetch_array(mysql_query('SELECT * FROM `mkchars` WHERE id="'. $persoId .'"'))) {
		include('../includes/getId.php');
		if (($perso['identifiant'] == $identifiants[0]) && ($perso['identifiant2'] == $identifiants[1]) && ($perso['identifiant3'] == $identifiants[2]) && ($perso['identifiant4'] == $identifiants[3])) {
			mysql_query('DELETE FROM `mkchars` WHERE id="'. $persoId .'"');
			require_once('../includes/persos.php');
			$spriteSrcs = get_sprite_srcs($perso['sprites']);
			delete_sprite_imgs($spriteSrcs);
			header('location: persoEditor.php');
		}
	}
	mysql_close();
}
?>