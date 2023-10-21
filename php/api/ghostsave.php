<?php
header('Content-Type: text/plain');
if (isset($_POST['map'])) {
	include('../includes/initdb.php');
	include('../includes/getId.php');
	$map = $_POST['map'];
	$type = isset($_POST['type']) ? $_POST['type'] : '';
	$cc = isset($_POST['cc']) ? $_POST['cc'] : 150;
	if ($getPerso = mysql_fetch_array(mysql_query('SELECT g.id,g.perso,g.time,g.lap_times,r.name FROM `mkghosts` g LEFT JOIN `mkrecords` r ON g.identifiant=r.identifiant AND g.identifiant2=r.identifiant2 AND g.identifiant3=r.identifiant3 AND g.identifiant4=r.identifiant4 AND g.perso=r.perso AND g.time=r.time AND r.class=g.class AND r.circuit=g.circuit AND r.type=g.type WHERE g.class="'.$cc.'" AND g.type="'.$type.'" AND g.circuit="'.$map.'" AND g.identifiant='.$identifiants[0].' AND g.identifiant2='.$identifiants[1].' AND g.identifiant3='.$identifiants[2].' AND g.identifiant4='.$identifiants[3].' GROUP BY g.id,g.perso,g.time'))) {
		require_once('../includes/utils-tt.php');
		echo '['.$getPerso['id'].',"'. $getPerso['perso'] .'","'. addslashes($getPerso['name'] ?? '') .'",'.$getPerso['time'].','.$getPerso['lap_times'].',';
		print_ghost_frames($getPerso['id']);
		echo ']';
	}
	mysql_close();
}
?>