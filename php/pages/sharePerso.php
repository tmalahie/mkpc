<?php
if (isset($_POST['id']) && isset($_POST['pseudo'])) {
	include('../includes/initdb.php');
	include('../includes/session.php');
	$persoId = $_POST['id'];
	if ($perso = mysql_fetch_array(mysql_query('SELECT * FROM `mkchars` WHERE id="'. $persoId .'"'))) {
		include('../includes/getId.php');
		if (($perso['identifiant'] == $identifiants[0]) && ($perso['identifiant2'] == $identifiants[1]) && ($perso['identifiant3'] == $identifiants[2]) && ($perso['identifiant4'] == $identifiants[3])) {
			setcookie('mkauteur', $_POST['pseudo'], 4294967295,'/');
			include('../includes/ip_banned.php');
			if (!isBanned())
				mysql_query('UPDATE `mkchars` SET author="'. $_POST['pseudo'] .'",publication_date=IFNULL(publication_date,CURRENT_TIMESTAMP()) WHERE id="'. $persoId .'"');
			if ($id && !$perso['publication_date']) {
				$getFollowers = mysql_query('SELECT follower FROM `mkfollowusers` WHERE followed="'. $id .'"');
				while ($follower = mysql_fetch_array($getFollowers))
					mysql_query('INSERT INTO `mknotifs` SET type="follower_perso", user="'. $follower['follower'] .'", link="'.$persoId.'"');
			}
			header('location: persoEditor.php?shared='.$persoId);
		}
	}
	mysql_close();
}
?>