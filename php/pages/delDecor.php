<?php
if (isset($_GET['id'])) {
	include('../includes/initdb.php');
	include('../includes/session.php');
	if (!isset($_SESSION['csrf']) || !isset($_GET['token']) || ($_SESSION['csrf'] != $_GET['token'])) {
		echo 'Invalid token';
		mysql_close();
		exit;
	}
	$decorId = $_GET['id'];
	if ($decor = mysql_fetch_array(mysql_query('SELECT * FROM `mkdecors` WHERE id="'. $decorId .'"'))) {
		include('../includes/getId.php');
		require_once('../includes/collabUtils.php');
		if (($decor['identifiant'] == $identifiants[0]) || hasCollabGrants('mkdecors', $decor['extra_parent_id'], $_GET['collab'], 'edit')) {
			mysql_query('DELETE FROM `mkdecors` WHERE id="'. $decorId .'"');
			include('../includes/utils-decors.php');
			$spriteSrcs = decor_sprite_srcs($decor['sprites']);
			delete_decor_sprite_imgs($spriteSrcs);
			$getExtraDecors = mysql_query('SELECT * FROM `mkdecors` WHERE extra_parent_id="'. $decorId .'"');
			while ($extraDecor = mysql_fetch_array($getExtraDecors)) {
				$extraSpriteSrcs = decor_sprite_srcs($extraDecor['sprites']);
				delete_decor_sprite_imgs($extraSpriteSrcs);
				mysql_query('DELETE FROM `mkdecors` WHERE id="'. $extraDecor['id'] .'"');
			}
		}
		$collabSuffix = isset($_GET['collab']) ? '&collab='.urlencode($_GET['collab']) : '';
		if ($decor['extra_parent_id'])
			header('location: editDecor.php?id='.$decor['extra_parent_id'].$collabSuffix);
		else
			header('location: decorEditor.php'.$collabSuffix);
	}
	mysql_close();
}
?>