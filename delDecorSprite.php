<?php
if (isset($_GET['id'])) {
	include('initdb.php');
	include('session.php');
	$decorId = $_GET['id'];
	if ($perso = mysql_fetch_array(mysql_query('SELECT * FROM `mkdecors` WHERE id="'. $decorId .'"'))) {
		include('getId.php');
		if ($perso['identifiant'] == $identifiants[0]) {
			$type = isset($_GET['map']) ? 'map' : null;
			if ($type) {
				include('utils-decors.php');
				$spriteSrcs = decor_sprite_srcs($perso['sprites']);
				if ($spriteSrcs[$type] != $spriteSrcs['ld'])
					unlink($spriteSrcs[$type]);
				header('location: decorOptions.php?id='. $decorId);
			}
		}
	}
	mysql_close();
}
?>