<?php
header('Content-Type: text/plain');
if (isset($_POST['id'])) {
	include('../includes/initdb.php');
	$persoId = $_POST['id'];
	if ($perso = mysql_fetch_array(mysql_query('SELECT * FROM `mkchars` WHERE id="'. $persoId .'"'))) {
		include('../includes/session.php');
		include('../includes/getId.php');
		if ($id)
			mysql_query('DELETE FROM `mknotifs` WHERE user="'. $id .'" AND type="follower_perso" AND link="'. $_POST['id'] .'"');
		$myPerso = (($perso['identifiant'] == $identifiants[0]) && ($perso['identifiant2'] == $identifiants[1]) && ($perso['identifiant3'] == $identifiants[2]) && ($perso['identifiant4'] == $identifiants[3]));
		if (!$myPerso) {
			mysql_query('INSERT IGNORE INTO `mkchisto` SET id="'.$persoId.'",identifiant="'. $identifiants[0] .'",identifiant2="'. $identifiants[1] .'",identifiant3="'. $identifiants[2] .'",identifiant4="'. $identifiants[3] .'",acceleration="'.$perso['acceleration'].'",speed="'.$perso['speed'].'",handling="'.$perso['handling'].'",mass="'.$perso['mass'].'"');
			if (isset($_POST['acceleration']) && isset($_POST['speed']) && isset($_POST['handling']) && isset($_POST['mass'])) {
				include('../includes/perso-stats.php');
				if (!cheated()) {
					$statPost = array();
					foreach ($statsRange as $stat => $range)
						$statPost[$stat] = $range['min'] + min(max($_POST[$stat],0),$statsGradient)*($range['max']-$range['min'])/$statsGradient;
					mysql_query('UPDATE `mkchisto` SET 
						acceleration="'.$statPost['acceleration'].'",
						speed="'.$statPost['speed'].'",
						handling="'.$statPost['handling'].'",
						mass="'.$statPost['mass'].'"
						WHERE id="'.$persoId.'"
						AND identifiant="'. $identifiants[0] .'" AND identifiant2="'. $identifiants[1] .'" AND identifiant3="'. $identifiants[2] .'" AND identifiant4="'. $identifiants[3] .'"
					');
				}
			}
			$customPerso = mysql_fetch_array(mysql_query('SELECT DATE(date) AS day FROM mkchisto WHERE id="'.$persoId.'" AND identifiant="'. $identifiants[0] .'" AND identifiant2="'. $identifiants[1] .'" AND identifiant3="'. $identifiants[2] .'" AND identifiant4="'. $identifiants[3] .'"'));
			if ($customPerso['day'] != date('Y-m-d'))
				mysql_query('UPDATE mkchars SET playcount=playcount+1 WHERE id="'.$persoId.'"');
			mysql_query('UPDATE `mkchisto` SET date=CURRENT_TIMESTAMP()'. (isset($_POST['list']) ? ',list="'.$_POST['list'].'"' : '') .' WHERE id="'.$persoId.'" AND identifiant="'. $identifiants[0] .'" AND identifiant2="'. $identifiants[1] .'" AND identifiant3="'. $identifiants[2] .'" AND identifiant4="'. $identifiants[3] .'"');
		}
		$currentSelection = mysql_fetch_array(mysql_query('SELECT perso1 FROM `mkpersosel` WHERE identifiant='.$identifiants[0].' AND identifiant2='.$identifiants[1].' AND identifiant3='.$identifiants[2].' AND identifiant4='.$identifiants[3]));
		if ($currentSelection) {
			if ($currentSelection['perso1'] != $persoId)
				mysql_query('UPDATE `mkpersosel` SET perso1="'.$persoId.'",perso2='.$currentSelection['perso1'].' WHERE identifiant='.$identifiants[0].' AND identifiant2='.$identifiants[1].' AND identifiant3='.$identifiants[2].' AND identifiant4='.$identifiants[3]);
		}
		else
			mysql_query('INSERT INTO `mkpersosel` SET perso1="'.$persoId.'",perso2=-1,identifiant='.$identifiants[0].',identifiant2='.$identifiants[1].',identifiant3='.$identifiants[2].',identifiant4='.$identifiants[3]);
	}
	echo 1;
	mysql_close();
}
?>