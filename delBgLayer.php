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
		if (mysql_fetch_array(mysql_query('SELECT id FROM `mkbgs` WHERE id="'. $layer['bg'] .'" AND identifiant="'. $identifiants[0] .'"'))) {
			mysql_query('DELETE FROM `mkbglayers` WHERE id="'. $layer['id'] .'"');
			if ($layer['filename'] !== '') {
				require_once('utils-bgs.php');
				$filePath = get_layer_path($layer['filename']);
				@unlink($filePath);
			}
		}
		header('location: editBg.php?id='. $layer['bg']);
	}
	mysql_close();
}
?>