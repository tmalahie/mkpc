<?php
if (!isset($identifiants))
	include('getId.php');
if ($getMkSave = mysql_fetch_array(mysql_query('SELECT scores FROM `mksaves` WHERE identifiant="'. $identifiants[0] .'" AND identifiant2="'. $identifiants[1] .'" AND identifiant3="'. $identifiants[2] .'" AND identifiant4="'. $identifiants[3] .'"'))) {
	$mkSaves = $getMkSave['scores'];
	if (isset($_COOKIE['mk'])) {
		$cMK = $_COOKIE['mk'];
		for ($i=0;$i<10;$i++)
			$mkSaves[$i] = max($mkSaves[$i],$cMK[$i]);
		mysql_query('UPDATE `mksaves` SET scores="'. $mkSaves .'" WHERE identifiant="'. $identifiants[0] .'" AND identifiant2="'. $identifiants[1] .'" AND identifiant3="'. $identifiants[2] .'" AND identifiant4="'. $identifiants[3] .'"');
		$_COOKIE['mk'] = null;
		@setcookie('mk', '', 0, '/');
	}
}
else {
	if (isset($_COOKIE['mk'])) {
		$mkSaves = $_COOKIE['mk'];
		$_COOKIE['mk'] = null;
		@setcookie('mk', '', 0, '/');
	}
	else
		$mkSaves = '0000000000';
	mysql_query('INSERT INTO `mksaves` VALUES("'. $identifiants[0] .'","'. $identifiants[1] .'","'. $identifiants[2] .'","'. $identifiants[3] .'","'. mysql_real_escape_string($mkSaves) .'")');
}
$total1 = 0;
for ($i=0;$i<10;$i++)
	$total1 += $mkSaves[$i];
$getMkSave2 = mysql_fetch_array(mysql_query('SELECT SUM(score) AS totalScore FROM `mkwins` WHERE identifiant="'. $identifiants[0] .'" AND identifiant2="'. $identifiants[1] .'" AND identifiant3="'. $identifiants[2] .'" AND identifiant4="'. $identifiants[3] .'"'));
$total2 = $getMkSave2['totalScore'];
?>