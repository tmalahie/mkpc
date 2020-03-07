<?php
session_start();
$id = $_SESSION['mkid'];
if ($id) {
	if (isset($_POST['joueur']) && isset($_POST['map'])) {
		include('initdb.php');
		mysql_query('UPDATE `mkjoueurs` SET joueur="'. $_POST['joueur'] .'", choix="'. $_POST['map'] .'" WHERE id="'.$id.'" AND banned=0');
		include('setMap.php');
	}
}
?>