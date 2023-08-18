<?php
header('Content-Type: text/plain');
if (isset($_GET['id'])) {
	include('../includes/initdb.php');
	$perso = mysql_fetch_array(mysql_query('SELECT id,name,acceleration,speed,handling,mass,sprites FROM `mkchars` WHERE id="'. $_GET['id'] .'"'));
	if ($perso) {
		require_once('../includes/persos.php');
		$spriteSrcs = get_sprite_srcs($perso['sprites']);
		$res = array (
			'id' => $perso['id'],
			'name' => $perso['name'],
			'acceleration' => +$perso['acceleration'],
			'speed' => +$perso['speed'],
			'handling' => +$perso['handling'],
			'mass' => +$perso['mass'],
			'ld' => $spriteSrcs['ld'],
			'map' => $spriteSrcs['map'],
			'podium' => $spriteSrcs['podium'],
			'music' => get_perso_music($perso)
		);
		echo json_encode($res);
	}
	else
		echo '-1';
	mysql_close();
}
?>