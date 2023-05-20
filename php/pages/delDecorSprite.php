<?php
if (isset($_GET['id'])) {
	include('../includes/initdb.php');
	include('../includes/session.php');
	$decorId = $_GET['id'];
	if ($decor = mysql_fetch_array(mysql_query('SELECT * FROM `mkdecors` WHERE id="'. $decorId .'"'))) {
		include('../includes/getId.php');
		require_once('../includes/collabUtils.php');
		if (($decor['identifiant'] == $identifiants[0]) || hasCollabGrants('mkdecors', $decor['extra_parent_id'] ?? $decorId, $_GET['collab'], 'edit')) {
			$type = isset($_GET['map']) ? 'map' : null;
			$collabSuffix = isset($_GET['collab']) ? '&collab='.urlencode($_GET['collab']) : '';
			if ($type) {
				include('../includes/utils-decors.php');
				$spriteSrcs = decor_sprite_srcs($decor['sprites'],$decor['url']);
				if ($spriteSrcs[$type] != $spriteSrcs['ld'])
					@unlink($spriteSrcs['ldir'].$spriteSrcs[$type]);
				header('location: decorOptions.php?id='. $decorId . $collabSuffix);
			}
		}
	}
	mysql_close();
}
?>