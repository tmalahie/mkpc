<?php
include('auth.php');
if (isset($_GET['pseudo']) && isset($_GET['id'])) {
	$id = $_GET['id'];
	if ($getPlayer = mysql_fetch_array(mysql_query('SELECT nom FROM `mkjoueurs` WHERE id="'. $id .'"'))) {
		if ($getPlayer['nom'] == $_GET['pseudo']) {
			mysql_query('DELETE FROM `mkjoueurs` WHERE id="'. $id .'"');
			mysql_query('DELETE FROM `mkprofiles` WHERE id="'. $id .'"');
			mysql_query('DELETE FROM `mkplayers` WHERE id="'. $id .'"');
			echo 'Done';
		}
		else
			echo 'Nick does not match';
	}
	else
		echo 'ID does not exist';
}
else
	echo 'Usage: delete.php?id=11&amp;pseudo=Timothe';
?>