<?php
include('auth.php');
if (isset($_GET['pseudo'])) {
	if ($getId = mysql_fetch_array(mysql_query('SELECT id FROM `mkjoueurs` WHERE nom="'. $_GET['pseudo'] .'"'))) {
		$id = $getId['id'];
		$_SESSION['mkid'] = $id;
	}
}
if (isset($_GET['ip1'])) {
	include('../getId.php');
	if ($identifiants[0] != 1390635815)
		mysql_query('DELETE FROM mkips WHERE player='. $id .' AND ip1='. $identifiants[0]);
	$identifiants[0] = $_GET['ip1'];
	store_mkid();
}
?>