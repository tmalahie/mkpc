<?php
header('Content-Type: text/plain');
session_start();
echo '[';
if (!empty($_SESSION['mkid'])) {
	$id = $_SESSION['mkid'];
	$isBattle = isset($_POST['battle']);
	include('../includes/initdb.php');
	include('../includes/onlineUtils.php');
	$course = getCourse();
	if ($course) {
		$joueurs = mysql_query('SELECT j.*,i.ignorer,m.player AS muted FROM (SELECT id,nom FROM mkjoueurs WHERE course='.$course.' UNION SELECT j.id,j.nom FROM mkspectators s INNER JOIN mkjoueurs j ON s.player=j.id WHERE s.course='.$course.') j LEFT JOIN `mkignores` i ON i.ignored=j.id AND i.ignorer='.$id.' LEFT JOIN `mkmuted` m ON m.player=j.id WHERE j.id!='.$id);
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