<?php
header('Content-Type: text/plain');
session_start();
if (!empty($_SESSION['mkid'])) {
	$id = $_SESSION['mkid'];
	include('initdb.php');
	include('setMap.php');
}
?>