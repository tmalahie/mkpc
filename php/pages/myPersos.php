<?php
header('Content-Type: application/json');
include('../includes/language.php');
include('../includes/getId.php');
include('../includes/initdb.php');
$getPsersos = mysql_fetch_array(mysql_query('SELECT perso1,perso2 FROM `mkpersosel` WHERE identifiant='.$identifiants[0].' AND identifiant2='.$identifiants[1].' AND identifiant3='.$identifiants[2].' AND identifiant4='.$identifiants[3]));
if ($getPsersos) {
	$perso1 = $getPsersos['perso1'];
	$perso2 = $getPsersos['perso2'];
	$persos = array($perso1,$perso2);
	function getPerso($id) {
		global $identifiants;
		return mysql_fetch_array(
			mysql_query(
				'SELECT c.*,
				IFNULL(h.acceleration,c.acceleration) AS acceleration,IFNULL(h.speed,c.speed) AS speed,IFNULL(h.handling,c.handling) AS handling,IFNULL(h.mass,c.mass) AS mass
				FROM `mkchars` c
				LEFT JOIN `mkchisto` h
				ON c.id=h.id AND h.identifiant='.$identifiants[0].' AND h.identifiant2='.$identifiants[1].' AND h.identifiant3='.$identifiants[2].' AND h.identifiant4='.$identifiants[3] .'
				WHERE c.id="'. $id .'"'
			)
		);
	}
	while ($perso1 != -1) {
		if ($getPerso1 = getPerso($perso1))
			break;
		else {
			$perso1 = $perso2;
			$perso2 = -1;
		}
	}
	if ($perso2 != -1) {
		$getPerso2 = getPerso($perso2);
		if (!$getPerso2)
			$perso2 = -1;
	}
	if ($persos != array($perso1,$perso2))
		mysql_query('UPDATE `mkpersosel` SET perso1='.$perso1.',perso2='.$perso2.' WHERE identifiant='.$identifiants[0].' AND identifiant2='.$identifiants[1].' AND identifiant3='.$identifiants[2].' AND identifiant4='.$identifiants[3]);
	function toJSON($data) {
		$spriteSrcs = get_sprite_srcs($data['sprites']);
		return array (
			'name' => $data['name'],
			'sprites' => $data['sprites'],
			'acceleration' => +$data['acceleration'],
			'speed' => +$data['speed'],
			'handling' => +$data['handling'],
			'mass' => +$data['mass'],
			'map' => $spriteSrcs['map'],
			'podium' => $spriteSrcs['podium'],
			'music' => get_perso_music($data)
		);
	}
	require_once('../includes/persos.php');
	$res = array();
	if ($perso1 != -1)
		$res[] = toJSON($getPerso1);
	if ($perso2 != -1)
		$res[] = toJSON($getPerso2);
	echo json_encode($res);
}
else
	echo "[]";
mysql_close();
?>