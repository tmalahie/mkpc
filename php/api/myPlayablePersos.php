<?php
header('Content-Type: application/json');
include('../includes/language.php');
include('../includes/getId.php');
include('../includes/initdb.php');
require_once('../includes/persos.php');
$myPersos = mysql_query('SELECT * FROM `mkchars` WHERE identifiant='.$identifiants[0].' AND identifiant2='.$identifiants[1].' AND identifiant3='.$identifiants[2].' AND identifiant4='.$identifiants[3].' AND name!="" ORDER BY id DESC');
$res = array();
while ($data = mysql_fetch_array($myPersos)) {
	$spriteSrcs = get_sprite_srcs($data['sprites']);
	$res[] = array (
        'id' => +$data['id'],
		'name' => $data['name'],
		'shared' => +($data['author'] !== null),
		'sprites' => $data['sprites'],
		'acceleration' => +$data['acceleration'],
		'speed' => +$data['speed'],
		'handling' => +$data['handling'],
		'mass' => +$data['mass'],
		'ld' => $spriteSrcs['ld'],
		'map' => $spriteSrcs['map'],
		'podium' => $spriteSrcs['podium'],
		'music' => get_perso_music($data)
	);
}
echo json_encode($res);
mysql_close();