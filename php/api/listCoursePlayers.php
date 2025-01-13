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
		$players = mysql_query('SELECT j.*,i.ignorer,m.player AS muted FROM (SELECT id,nom FROM mkjoueurs WHERE course='.$course.' UNION SELECT j.id,j.nom FROM mkspectators s INNER JOIN mkjoueurs j ON s.player=j.id WHERE s.course='.$course.') j LEFT JOIN `mkignores` i ON i.ignored=j.id AND i.ignorer='.$id.' LEFT JOIN `mkmuted` m ON m.player=j.id WHERE j.id!='.$id);
		$comma = false;
		while ($player = mysql_fetch_array($players)) {
			echo ($comma ? ',':'');
			echo '[';
			echo $player['id'].',"'.$player['nom'].'",'.($player['ignorer']?1:0).','.($player['muted']?1:0);
			echo ']';
			$comma = true;
		}
	}
	mysql_close();
}
echo ']';
?>