<?php
session_start();
if (!empty($_SESSION['mkid'])) {
	$id = $_SESSION['mkid'];
	$isBattle = isset($_POST['battle']);
	include('initdb.php');
	include('setMap.php');
}
?>