<?php
session_start();
$id = $_SESSION['mkid'];
$isBattle = isset($_POST['battle']);
if ($id) {
	include('initdb.php');
	include('setMap.php');
}
?>