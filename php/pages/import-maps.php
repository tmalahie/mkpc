<?php
if ($_SERVER['REMOTE_ADDR'] != '192.168.56.1') die($_SERVER['REMOTE_ADDR']);
$maps = json_decode(file_get_contents('mk/maps.json'));
$inc = 1;
include('../includes/initdb.php');
include('../includes/getId.php');
require_once('../includes/circuitEnums.php');
foreach ($maps as $map) {
	if ($inc > 56)
		continue;
	$id = $inc;
	mysql_query('
		INSERT INTO `circuits`
		SET id='.$id.',
		identifiant='.$identifiants[0].',
		identifiant2='.$identifiants[1].',
		identifiant3='.$identifiants[2].',
		identifiant4='.$identifiants[3].'
		ON DUPLICATE KEY update
		identifiant=VALUES(identifiant),
		identifiant2=VALUES(identifiant2),
		identifiant3=VALUES(identifiant3),
		identifiant4=VALUES(identifiant4)
	');
	//if ($id <= 40) {
		$data = array();
		$data['main'] = array(
			'startposition' => array($map->startposition[0]-5,$map->startposition[1]+6),
			'startrotation' => isset($map->startrotation) ? $map->startrotation:180,
			'startdirection' => !empty($map->startdirection) ? 0:1,
			'aiclosed' => array(1),
			'tours' => 3,
			'bgimg' => 1,
			'music' => $map->music,
			'bgcolor' => $map->bgcolor
		);
		foreach ($bgImgs as $i=>$decor) {
			if ($map->fond == $decor)
				$data['main']['bgimg'] = $i;
		}
		$data['aipoints'] = $map->aipoints;
		if (isset($data['aipoints']) && isset($data['aipoints'][0]) && isset($data['aipoints'][0][0]) && !is_array($data['aipoints'][0][0]))
			$data['aipoints'] = array($data['aipoints']);
		while (count($data['main']['aiclosed']) < count($data['aipoints']))
			$data['main']['aiclosed'][] = 1;
		$collision = $map->collision;
		foreach ($collision as &$collisionData) {
			$collisionData[2]--;
			$collisionData[3]--;
		}
		$data['collision'] = $collision;
		if (isset($map->horspistes))
			$data['horspistes'] = $map->horspistes;
		elseif (isset($map->horspiste))
			$data['horspistes'] = array('herbe'=>$map->horspiste);
		foreach ($data['horspistes'] as $type => &$hps) {
			foreach ($hps as &$hp) {
				if (isset($hp[2]) && is_numeric($hp[2])) {
					$hp[2]--;
					$hp[3]--;
				}
			}
		}
		$trous = $map->trous;
		for ($i=0;$i<4;$i++) {
			foreach ($trous[$i] as &$trouData) {
				if (count($trouData) == 6)
					$trouData = array(array($trouData[0],$trouData[1],$trouData[2]-1,$trouData[3]-1),array($trouData[4],$trouData[5]));
			}
		}
		$data['trous'] = $trous;
		$data['checkpoint'] = $map->checkpoint;
		$data['arme'] = $map->arme;
		$sauts = $map->sauts;
		foreach ($sauts as &$sautData) {
			$sautData[2]--;
			$sautData[3]--;
		}
		$data['sauts'] = $sauts;
		$data['accelerateurs'] = $map->accelerateurs;
		$data['decor'] = $map->decor;
		if (isset($map->cannons))
			$data['cannons'] = $map->cannons;
		if (isset($map->flows))
			$data['flows'] = $map->flows;
		mysql_query('
			INSERT INTO `circuits_data`
			SET id='.$id.',
			data="'.mysql_real_escape_string(gzcompress(json_encode($data))).'"
			ON DUPLICATE KEY update
			data=VALUES(data)
		');
	//}
	$inc++;
}
mysql_close();
?>