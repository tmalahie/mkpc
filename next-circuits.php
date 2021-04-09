<?php
if (isset($_POST['page'])) {
	include('initdb.php');
	include('utils-circuits.php');
	$page = +$_POST['page'];
	$tri = isset($_POST['tri']) ? $_POST['tri']:0;
	$nom = isset($_POST['nom']) ? stripslashes($_POST['nom']):'';
	$auteur = isset($_POST['auteur']) ? stripslashes($_POST['auteur']):'';
	$type = isset($_POST['type']) ? $_POST['type']:'';
	$pids = null;
	if (isset($_POST['user'])) {
		$user = $_POST['user'];
		if ($getProfile = mysql_fetch_array(mysql_query('SELECT identifiant,identifiant2,identifiant3,identifiant4 FROM `mkprofiles` WHERE id="'. $user .'"')))
			$pids = array($getProfile['identifiant'],$getProfile['identifiant2'],$getProfile['identifiant3'],$getProfile['identifiant4']);
	}
	include('creations-params.php');
	if ($type !== '') {
		$aCircuits = array($aCircuits[$type]);
		$weightsByType = array($weightsByType[$type]);
	}
	$aParams = array(
		'type' => $type,
		'tri' => $tri,
		'nom' => $nom,
		'auteur' => $auteur,
		'pids' => $pids,
		'max_circuits' => $MAX_CIRCUITS,
	);
	$nbByType = countTracksByType($aCircuits,$aParams);
	$creationsList = listCreations($page,$nbByType,$weightsByType,$aCircuits,$aParams);
	echo '{"circuits":[';
	printCircuits($creationsList);
	echo ']';
	echo '}';
	mysql_close();
}
?>