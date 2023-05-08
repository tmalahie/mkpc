<?php
header('Content-Type: text/plain');
include('../includes/session.php');
if ($id && isset($_POST['pseudo'])) {
	include('../includes/initdb.php');
	if ($getId = mysql_fetch_array(mysql_query('SELECT id FROM `mkjoueurs` WHERE nom="'. $_POST['pseudo'] .'"')))
		mysql_query('DELETE FROM `mkignores` WHERE ignorer='.$id.' AND ignored='.$getId['id']);
	echo 1;
	mysql_close();
}
?>