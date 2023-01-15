<?php
if (isset($_GET['id'])) {
	include('initdb.php');
	include('session.php');
	if (!isset($_SESSION['csrf']) || !isset($_GET['token']) || ($_SESSION['csrf'] != $_GET['token'])) {
		echo 'Invalid token';
		mysql_close();
		exit;
	}
	$layerId = $_GET['id'];
	if ($layer = mysql_fetch_array(mysql_query('SELECT id,bg,filename FROM `mkbglayers` WHERE id="'. $layerId .'"'))) {
		include('getId.php');
		require_once('collabUtils.php');
		$requireOwner = !hasCollabGrants('mkbgs', $layer['bg'], $_GET['collab'], 'edit');
		if (mysql_fetch_array(mysql_query('SELECT id FROM `mkbgs` WHERE id="'. $layer['bg'] .'"'. ($requireOwner ? (' AND identifiant="'. $identifiants[0] .'"') : '')))) {
			mysql_query('DELETE FROM `mkbglayers` WHERE id="'. $layer['id'] .'"');
			if ($layer['filename'] !== '') {
				require_once('utils-bgs.php');
				$filePath = get_layer_path($layer['filename']);
				@unlink($filePath);
			}
		}
		$collabSuffix = isset($_GET['collab']) ? '&collab='.$_GET['collab'] : '';
		header('location: editBg.php?id='. $layer['bg'] . $collabSuffix);
	}
	mysql_close();
}
?>