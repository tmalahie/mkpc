<?php
session_start();
echo '[';
if (!empty($_SESSION['mkid'])) {
	$id = $_SESSION['mkid'];
	$isBattle = isset($_POST['battle']);
	include('initdb.php');
	include('onlineUtils.php');
	$course = getCourse();
	if ($course) {
		$joueurs = mysql_query('SELECT j.id,j.nom,i.ignorer,m.player AS muted FROM `mkjoueurs` j LEFT JOIN `mkignores` i ON i.ignored=j.id AND i.ignorer='.$id.' LEFT JOIN `mkmuted` m ON m.player=j.id WHERE j.course='.$course.' AND j.id!="'.$id.'"');
		$virgule = false;
		while ($joueur = mysql_fetch_array($joueurs)) {
			echo ($virgule ? ',':'');
			echo '[';
			echo $joueur['id'].',"'.$joueur['nom'].'",'.($joueur['ignorer']?1:0).','.($joueur['muted']?1:0);
			echo ']';
			$virgule = true;
		}
	}
	mysql_close();
}
echo ']';
?>