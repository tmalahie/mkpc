<?php
require_once('circuitImgUtils.php');
function file_total_quota($options=null) {
	global $identifiants;
	$ownerId = isset($options['identifiant']) ? $options['identifiant'] : $identifiants[0];
	if ($getQuota = mysql_fetch_array(mysql_query('SELECT file_quota FROM `mkidentifiants` WHERE identifiant='.$ownerId.' AND file_quota IS NOT NULL')))
		return +$getQuota['file_quota'];
	return 25000000;
}
function upload_max_size($options=null) {
	global $identifiants;
	$ownerId = isset($options['identifiant']) ? $options['identifiant'] : $identifiants[0];
	$field = empty($options['external']) ? 'upload_size' : 'external_size';
	if ($getSize = mysql_fetch_array(mysql_query('SELECT '.$field.' FROM `mkidentifiants` WHERE identifiant='.$ownerId.' AND '.$field.' IS NOT NULL')))
		return +$getSize[$field];
	return empty($options['external']) ? 1000000 : 5000000;
}
define('MAX_FILE_SIZE', file_total_quota());
function file_total_size($item=null) {
	global $identifiants;
	$ownerIds = $identifiants;
	if (isset($item['identifiants'])) {
		foreach ($item['identifiants'] as $i=>$identifiant)
			$ownerIds[$i] = $identifiant;
	}
	$total_size = 0;
	$circuits = mysql_query('SELECT ID,img_data FROM `circuits` WHERE identifiant='.$ownerIds[0].' AND identifiant2='.$ownerIds[1].' AND identifiant3='.$ownerIds[2].' AND identifiant4='.$ownerIds[3]);
	$excludedCircuitId = isset($item['circuit']) ? $item['circuit'] : null;
	$excludedLap = isset($item['lap']) ? $item['lap'] : 0;
	while ($circuit = mysql_fetch_array($circuits)) {
		$excludeCircuit = $excludedCircuitId == $circuit['ID'];
		$circuitImg = json_decode($circuit['img_data']);
		if ($circuitImg->local && (!$excludeCircuit || $excludedLap))
			$total_size += @filesize(CIRCUIT_BASE_PATH.$circuitImg->url);
		if (isset($circuitImg->lapOverrides)) {
			foreach ($circuitImg->lapOverrides as $lap=>$lapImg) {
				if (!empty($lapImg->local) && (!$excludeCircuit || ($lap != $excludedLap)))
					$total_size += @filesize(CIRCUIT_BASE_PATH.$lapImg->url);
			}
		}
	}
	$arenes = mysql_query('SELECT ID,img_data FROM `arenes` WHERE identifiant='.$ownerIds[0].' AND identifiant2='.$ownerIds[1].' AND identifiant3='.$ownerIds[2].' AND identifiant4='.$ownerIds[3] . (isset($item['arena']) ? ' AND ID != '.$item['arena'] : ''));
	while ($arene = mysql_fetch_array($arenes)) {
		$circuitImg = json_decode($arene['img_data']);
		if ($circuitImg->local)
			$total_size += @filesize(CIRCUIT_BASE_PATH.$circuitImg->url);
	}
	$persos = mysql_query('SELECT sprites FROM `mkchars` WHERE identifiant='.$ownerIds[0].' AND identifiant2='.$ownerIds[1].' AND identifiant3='.$ownerIds[2].' AND identifiant4='.$ownerIds[3] . (isset($item['perso']) ? ' AND id != '.$item['perso'] : ''));
	while ($perso = mysql_fetch_array($persos))
		$total_size += @filesize('../../images/sprites/uploads/'.$perso['sprites'].'.png');
	$decors = mysql_query('SELECT sprites FROM `mkdecors` WHERE identifiant='.$ownerIds[0].' AND JSON_EXTRACT(img_data,"$.url") IS NULL'. (isset($item['decor']) ? ' AND id != '.$item['decor'] : ''));
	while ($decor = mysql_fetch_array($decors))
		$total_size += @filesize('../../images/sprites/uploads/'.$decor['sprites'].'.png');
	$bgLayers = mysql_query('SELECT l.filename FROM `mkbglayers` l INNER JOIN `mkbgs` b ON l.bg=b.id AND l.filename!="" WHERE b.identifiant='.$ownerIds[0]. (isset($item['layer']) ? ' AND l.id != '.$item['layer'] : ''));
	while ($bgLayer = mysql_fetch_array($bgLayers))
		$total_size += @filesize('../../images/sprites/uploads/'.$bgLayer['filename']);
	return $total_size;
}
function filesize_str($file_size) {
	global $language;
	if ($file_size > MAX_FILE_SIZE)
		$file_size = MAX_FILE_SIZE;
	$file_size = round($file_size/100)/10;
	$Mo = ($file_size >= 1000);
	if ($Mo)
		$file_size = round($file_size/100)/10;
	return $file_size.' '.($language ? ($Mo?'MB':'kB'):($Mo?'Mo':'ko'));
}
function filesize_percent($file_size) {
	if ($file_size > MAX_FILE_SIZE)
		$file_size = MAX_FILE_SIZE;
	return (round($file_size*1000/MAX_FILE_SIZE)/10).' %';
}
?>