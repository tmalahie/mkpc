<?php
include('utilId.php');
if ($profile = mysql_fetch_array(mysql_query('SELECT identifiant,identifiant2,identifiant3,identifiant4 FROM `mkprofiles` WHERE id='. $id))) {
	if (!isset($identifiants) && isset($_COOKIE['mktoken']))
		fetch_mkid();
	if ($profile['identifiant'] === null) {
		if (isset($identifiants))
			mysql_query('UPDATE `mkprofiles` SET identifiant="'. $identifiants[0] .'",identifiant2="'. $identifiants[1] .'",identifiant3="'. $identifiants[2] .'",identifiant4="'. $identifiants[3] .'" WHERE id='. $id);
	}
	else {
		if (isset($identifiants))
			mysql_query('INSERT IGNORE INTO `mkips` SET player='. $id .',ip1="'. $identifiants[0] .'",ip2="'. $identifiants[1] .'",ip3="'. $identifiants[2] .'",ip4="'. $identifiants[3] .'"');
		else
			$identifiants = array();
		$idKeys = array('identifiant', 'identifiant2', 'identifiant3', 'identifiant4');
		$i = 0;
		foreach ($idKeys as $idKey) {
			$identifiants[$i] = $profile[$idKey];
			$i++;
		}
		store_mkid();
	}
}
?>