<?php
if (isset($_GET['id'])) {
	include('initdb.php');
	include('session.php');
	if (!isset($_SESSION['csrf']) || !isset($_GET['token']) || ($_SESSION['csrf'] != $_GET['token'])) {
		echo 'Invalid token';
		mysql_close();
		exit;
	}
	$bgId = $_GET['id'];
	if ($bg = mysql_fetch_array(mysql_query('SELECT * FROM `mkbgs` WHERE id="'. $bgId .'"'))) {
		include('getId.php');
		if ($bg['identifiant'] == $identifiants[0]) {
			require_once('utils-bgs.php');
			$layers = get_bg_layers($bg['id']);
			mysql_query('DELETE FROM `mkbgs` WHERE id="'. $bgId .'"');
			mysql_query('DELETE FROM `mkbglayers` WHERE bg="'. $bgId .'"');
			foreach ($layers as $layer) {
				if ($layer['local'])
					@unlink($layer['path']);
			}
		}
	}
	header('location: bgEditor.php');
	mysql_close();
}
?>