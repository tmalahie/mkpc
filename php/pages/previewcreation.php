<?php
include('../includes/initdb.php');
if (isset($_GET['id'])) {
	$circuit = mysql_query('SELECT type FROM `mkcircuits` WHERE id="'. $_GET['id'] .'"');
	if ($circuit['type'])
		header('location: arena.php?id='. urlencode($_GET['id']));
	else
		header('location: circuit.php?id='. urlencode($_GET['id']));
}
mysql_close();
?>