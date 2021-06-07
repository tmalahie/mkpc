<?php
if (isset($_POST['map'])) {
	include('initdb.php');
	include('getId.php');
	$map = $_POST['map'];
	$cc = isset($_POST['cc']) ? $_POST['cc'] : 150;
	$getPersos = mysql_query('SELECT g.id,g.perso,g.time,g.lap_times,r.name FROM `mkghosts` g LEFT JOIN `mkrecords` r ON g.identifiant=r.identifiant AND g.identifiant2=r.identifiant2 AND g.identifiant3=r.identifiant3 AND g.identifiant4=r.identifiant4 AND g.perso=r.perso AND g.time=r.time AND r.class=g.class AND r.circuit=g.circuit AND r.type="" WHERE g.class="'. $cc .'" AND g.circuit="'. $map .'" AND (g.identifiant!='.$identifiants[0].' OR g.identifiant2!='.$identifiants[1].' OR g.identifiant3!='.$identifiants[2].' OR g.identifiant4!='.$identifiants[3].') GROUP BY g.id,g.perso,g.time');
	echo '[';
	$colon = '';
	while ($getPerso = mysql_fetch_array($getPersos)) {
		echo $colon.'['.$getPerso['id'].',"'. $getPerso['perso'] .'","'. addslashes($getPerso['name']) .'",'. $getPerso['time'] .','.$getPerso['lap_times'].']';
		$colon = ',';
	}
	echo ']';
	mysql_close();
}
?>