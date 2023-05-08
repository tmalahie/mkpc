<?php
header('Content-Type: text/plain');
if (isset($_POST['id']) && isset($_POST['rating'])) {
	include('../includes/initdb.php');
	$persoId = $_POST['id'];
	$rating = $_POST['rating'];
	if ($rating >= 0 && $rating <= 5) {
		include('../includes/getId.php');
		if ($rating) {
			include('../includes/ip_banned.php');
			if (isBanned()) {
				mysql_close();
				echo 1;
				exit;
			}
		}
		$q = mysql_query('UPDATE `mkchisto` SET rating="'. $rating .'" WHERE id="'.$persoId.'" AND identifiant="'. $identifiants[0] .'" AND identifiant2="'. $identifiants[1] .'" AND identifiant3="'. $identifiants[2] .'" AND identifiant4="'. $identifiants[3] .'"');
		if (mysql_affected_rows()) {
			if ($getPersoStats = mysql_fetch_array(mysql_query('SELECT IFNULL(AVG(rating),0) AS avgrating, COUNT(*) AS nbratings FROM mkchisto WHERE id="'. $persoId .'" AND rating>0')))
				mysql_query('UPDATE mkchars SET avgrating='.$getPersoStats['avgrating'].',nbratings='.$getPersoStats['nbratings'].' WHERE id="'.$persoId.'"');
		}
	}
	mysql_close();
	echo 1;
}
?>