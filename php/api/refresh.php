<?php
header('Content-Type: text/plain');
include('../includes/session.php');
if ($id) {
	include('../includes/initdb.php');
	if ($getPseudo = mysql_fetch_array(mysql_query('SELECT nom FROM `mkjoueurs` WHERE id="'. $id .'"'))) {
		$pseudo = $getPseudo['nom'];
		$timestamp = time();
		mysql_query("DELETE FROM `writting` WHERE pseudo='$pseudo' OR connecte < ". ($timestamp-10));
		$writting = (isset($_POST["writting"]) ? 1:0);
		mysql_query("INSERT INTO `writting` VALUES ('$pseudo', '$timestamp', '$writting')");
		
		echo '[';
		include('../includes/print_msgs.php');
		echo ',[';
		$writes = mysql_query("SELECT * FROM `writting` WHERE pseudo != '$pseudo'");
		if ($donnees = mysql_fetch_array($writes)) {
			echo '"'.$donnees['pseudo'].'"';
			while ($donnees = mysql_fetch_array($writes))
				echo ',"'.$donnees['pseudo'].'"';
		}
		echo '],[';
		$writes = mysql_query("SELECT * FROM `writting` WHERE pseudo != '$pseudo'");
		if ($donnees = mysql_fetch_array($writes)) {
			echo $donnees['writting'];
			while ($donnees = mysql_fetch_array($writes))
				echo ','.$donnees['writting'];
		}
		echo ']]';
	}
	mysql_close();
}
?>