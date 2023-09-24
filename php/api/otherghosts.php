<?php
header('Content-Type: text/plain');
if (isset($_POST['map'])) {
	include('../includes/initdb.php');
	$map = $_POST['map'];
	$type = isset($_POST['type']) ? $_POST['type'] : '';
	$cc = isset($_POST['cc']) ? $_POST['cc'] : 150;
	$getPersos = mysql_query('SELECT g.id,g.perso,g.time,g.lap_times,r.name FROM `mkghosts` g LEFT JOIN `mkrecords` r FORCE INDEX FOR JOIN (`identifiant`) ON g.identifiant=r.identifiant AND g.identifiant2=r.identifiant2 AND g.identifiant3=r.identifiant3 AND g.identifiant4=r.identifiant4 AND g.perso=r.perso AND g.time=r.time AND r.class=g.class AND r.circuit=g.circuit AND r.type=g.type WHERE g.class="'. $cc .'" AND g.type="'. $type .'" AND g.circuit="'. $map .'" GROUP BY g.id,g.perso,g.time');
	echo '[';
	$colon = '';
	while ($getPerso = mysql_fetch_array($getPersos)) {
		echo $colon.'['.$getPerso['id'].',"'. $getPerso['perso'] .'","'. addslashes($getPerso['name'] ?? '') .'",'. $getPerso['time'] .','.$getPerso['lap_times'].']';
		$colon = ',';
	}
	echo ']';
	mysql_close();
}
?>