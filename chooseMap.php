<?php
session_start();
$id = $_SESSION['mkid'];
if ($id) {
	if (isset($_POST['joueur']) && isset($_POST['map'])) {
		include('initdb.php');
		mysql_query('UPDATE `mkjoueurs` SET joueur="'. $_POST['joueur'] .'", choice_map="'. $_POST['map'] .'",choice_rand='.(isset($_POST['rand']) ? 1:0).' WHERE id="'.$id.'" AND banned=0 AND choice_map=0');
		include('setMap.php');
	}
}
?>