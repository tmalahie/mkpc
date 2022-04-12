<?php
require_once('circuitImgUtils.php');
if ($getQuota = mysql_fetch_array(mysql_query('SELECT file_quota FROM `mkidentifiants` WHERE identifiant='.$identifiants[0].' AND file_quota IS NOT NULL')))
	define('MAX_FILE_SIZE', +$getQuota['file_quota']);
else
	define('MAX_FILE_SIZE', 25000000);
function file_total_size($except = array()) {
	global $identifiants;
	$poids = 0;
	$circuits = mysql_query('SELECT ID,img_data FROM `circuits` WHERE identifiant='.$identifiants[0].' AND identifiant2='.$identifiants[1].' AND identifiant3='.$identifiants[2].' AND identifiant4='.$identifiants[3] . (isset($except['circuit']) ? ' AND ID != '.$except['circuit'] : ''));
	while ($circuit = mysql_fetch_array($circuits)) {
		$circuitImg = json_decode($circuit['img_data']);
		if ($circuitImg->local)
			$poids += @filesize(CIRCUIT_BASE_PATH.$circuitImg->url);
	}
	$arenes = mysql_query('SELECT ID,img_data FROM `arenes` WHERE identifiant='.$identifiants[0].' AND identifiant2='.$identifiants[1].' AND identifiant3='.$identifiants[2].' AND identifiant4='.$identifiants[3] . (isset($except['arena']) ? ' AND ID != '.$except['arena'] : ''));
	while ($arene = mysql_fetch_array($arenes)) {
		$circuitImg = json_decode($arene['img_data']);
		if ($circuitImg->local)
			$poids += @filesize(CIRCUIT_BASE_PATH.$circuitImg->url);
	}
	$persos = mysql_query('SELECT sprites FROM `mkchars` WHERE identifiant='.$identifiants[0].' AND identifiant2='.$identifiants[1].' AND identifiant3='.$identifiants[2].' AND identifiant4='.$identifiants[3] . (isset($except['perso']) ? ' AND id != '.$except['perso'] : ''));
	while ($perso = mysql_fetch_array($persos))
		$poids += @filesize('images/sprites/uploads/'.$perso['sprites'].'.png');
	$decors = mysql_query('SELECT sprites FROM `mkdecors` WHERE identifiant='.$identifiants[0]. (isset($except['decor']) ? ' AND id != '.$except['decor'] : ''));
	while ($decor = mysql_fetch_array($decors))
		$poids += @filesize('images/sprites/uploads/'.$decor['sprites'].'.png');
	return $poids;
}
function filesize_str($poids) {
	global $language;
	if ($poids > MAX_FILE_SIZE)
		$poids = MAX_FILE_SIZE;
	$poids = round($poids/100)/10;
	$Mo = ($poids >= 1000);
	if ($Mo)
		$poids = round($poids/100)/10;
	return $poids.' '.($language ? ($Mo?'MB':'kB'):($Mo?'Mo':'ko'));
}
function filesize_percent($poids) {
	if ($poids > MAX_FILE_SIZE)
		$poids = MAX_FILE_SIZE;
	return (round($poids*1000/MAX_FILE_SIZE)/10).' %';
}
?>