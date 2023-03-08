<?php
if (isset($_GET['id'])) {
	include('initdb.php');
	include('session.php');
	$persoId = $_GET['id'];
	if ($perso = mysql_fetch_array(mysql_query('SELECT * FROM `mkchars` WHERE id="'. $persoId .'"'))) {
		include('getId.php');
		require_once('collabUtils.php');
		if ((($perso['identifiant'] == $identifiants[0]) && ($perso['identifiant2'] == $identifiants[1]) && ($perso['identifiant3'] == $identifiants[2]) && ($perso['identifiant4'] == $identifiants[3])) || hasCollabGrants('mkchars', $persoId, $_GET['collab'], 'edit')) {
			$type = isset($_GET['map']) ? 'map' : (isset($_GET['podium']) ? 'podium' : null);
			$collabSuffix = isset($_GET['collab']) ? '&collab='.urlencode($_GET['collab']) : '';
			if ($type) {
				require_once('persos.php');
				$spriteSrcs = get_sprite_srcs($perso['sprites']);
				if ($spriteSrcs[$type] != $spriteSrcs['ld'])
					@unlink($spriteSrcs[$type]);
				header('location: persoOptions.php?id='. $persoId . $collabSuffix);
			}
		}
	}
	mysql_close();
}
?>