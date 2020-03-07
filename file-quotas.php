<?php
define('MAX_FILE_SIZE', 25000000);
function file_total_size($except = array()) {
	global $identifiants;
	$extensions = Array('png', 'gif', 'jpg', 'jpeg');
	$nbExt = count($extensions);
	$poids = 0;
	$circuits = mysql_query('SELECT ID FROM `circuits` WHERE identifiant='.$identifiants[0].' AND identifiant2='.$identifiants[1].' AND identifiant3='.$identifiants[2].' AND identifiant4='.$identifiants[3] . ($except['circuit'] ? ' AND ID != '.$except['circuit'] : ''));
	while ($circuit = mysql_fetch_array($circuits)) {
		$ID = $circuit['ID'];
		for ($i=0;$i<$nbExt;$i++) {
			$ext = $extensions[$i];
			if (file_exists('images/uploads/map'.$ID.'.'.$ext))
				break;
		}
		$poids += filesize('images/uploads/map'.$ID.'.'.$ext);
	}
	$arenes = mysql_query('SELECT ID FROM `arenes` WHERE identifiant='.$identifiants[0].' AND identifiant2='.$identifiants[1].' AND identifiant3='.$identifiants[2].' AND identifiant4='.$identifiants[3] . ($except['arena'] ? ' AND ID != '.$except['arena'] : ''));
	while ($arene = mysql_fetch_array($arenes)) {
		$ID = $arene['ID'];
		for ($i=0;$i<$nbExt;$i++) {
			$ext = $extensions[$i];
			if (file_exists('images/uploads/course'.$ID.'.'.$ext))
				break;
		}
		$poids += filesize('images/uploads/course'.$ID.'.'.$ext);
	}
	$persos = mysql_query('SELECT sprites FROM `mkchars` WHERE identifiant='.$identifiants[0].' AND identifiant2='.$identifiants[1].' AND identifiant3='.$identifiants[2].' AND identifiant4='.$identifiants[3] . ($except['perso'] ? ' AND ID != '.$except['perso'] : ''));
	while ($perso = mysql_fetch_array($persos))
		$poids += filesize('images/sprites/uploads/'.$perso['sprites'].'.png');
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