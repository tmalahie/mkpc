<?php
header('Content-Type: text/plain');
if (isset($_POST['demande']) && isset($_POST['rep']) && isset($_POST['msg'])) {
	include('../includes/session.php');
	include('../includes/initdb.php');
	mysql_query('UPDATE `mkinvitations` SET reponse="'. $_POST['rep'] .'", message="'. $_POST['msg'] .'" WHERE demandeur="'. $_POST['demande'] .'" AND receveur="'. $id .'" AND battle="'. (isset($_POST['battle']) ? 1:0) .'"');
	mysql_close();
	echo 1;
}
?>