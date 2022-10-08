<?php
if (isset($_GET['id'])) {
	include('initdb.php');
	include('session.php');
	$decorId = $_GET['id'];
	if ($decor = mysql_fetch_array(mysql_query('SELECT * FROM `mkdecors` WHERE id="'. $decorId .'"'))) {
		include('getId.php');
		require_once('collabUtils.php');
		if (($decor['identifiant'] == $identifiants[0]) || hasCollabGrants('mkdecors', $decorId, $_GET['collab'], 'edit')) {
			$type = isset($_GET['map']) ? 'map' : null;
			$collabSuffix = isset($_GET['collab']) ? '&collab='.$_GET['collab'] : '';
			if ($type) {
				include('utils-decors.php');
				$spriteSrcs = decor_sprite_srcs($decor['sprites']);
				if ($spriteSrcs[$type] != $spriteSrcs['ld'])
					@unlink($spriteSrcs[$type]);
				header('location: decorOptions.php?id='. $decorId . $collabSuffix);
			}
		}
	}
	mysql_close();
}
?>