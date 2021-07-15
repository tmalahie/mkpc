<?php
if (isset($_GET['id'])) {
	include('initdb.php');
	include('session.php');
	$persoId = $_GET['id'];
	if ($perso = mysql_fetch_array(mysql_query('SELECT * FROM `mkchars` WHERE id="'. $persoId .'"'))) {
		include('getId.php');
		if (($perso['identifiant'] == $identifiants[0]) && ($perso['identifiant2'] == $identifiants[1]) && ($perso['identifiant3'] == $identifiants[2]) && ($perso['identifiant4'] == $identifiants[3])) {
			$type = isset($_GET['map']) ? 'map' : (isset($_GET['podium']) ? 'podium' : null);
			if ($type) {
				require_once('persos.php');
				$spriteSrcs = get_sprite_srcs($perso['sprites']);
				if ($spriteSrcs[$type] != $spriteSrcs['ld'])
					unlink($spriteSrcs[$type]);
				header('location: persoOptions.php?id='. $persoId);
			}
		}
	}
	mysql_close();
}
?>