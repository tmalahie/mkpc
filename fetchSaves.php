<?php
if (!isset($identifiants))
	include('getId.php');
if ($getMkSave = mysql_fetch_array(mysql_query('SELECT scores FROM `mksaves` WHERE identifiant="'. $identifiants[0] .'" AND identifiant2="'. $identifiants[1] .'" AND identifiant3="'. $identifiants[2] .'" AND identifiant4="'. $identifiants[3] .'"')))
	$mkSaves = $getMkSave['scores'];
else {
	$mkSaves = '0000000000';
	mysql_query('INSERT INTO `mksaves` VALUES("'. $identifiants[0] .'","'. $identifiants[1] .'","'. $identifiants[2] .'","'. $identifiants[3] .'","'. mysql_real_escape_string($mkSaves) .'")');
}
$total1 = 0;
for ($i=0;$i<14;$i++)
	$total1 += $mkSaves[$i];
$getMkSave2 = mysql_fetch_array(mysql_query('SELECT SUM(score) AS totalScore FROM `mkwins` WHERE identifiant="'. $identifiants[0] .'" AND identifiant2="'. $identifiants[1] .'" AND identifiant3="'. $identifiants[2] .'" AND identifiant4="'. $identifiants[3] .'"'));
$total2 = $getMkSave2['totalScore'];
?>