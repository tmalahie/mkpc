<?php
if (isset($_POST['nom']) && isset($_POST['auteur']) && isset($_POST['map'])) {
	$allPieces = true;
	for ($i=0;$i<36;$i++) {
		if (!isset($_POST['p'.$i])) {
			$allPieces = false;
			break;
		}
	}
	if ($allPieces) {
		include('initdb.php');
		include('getId.php');
		include('ip_banned.php');
		if (isBanned()) {
			mysql_close();
			exit;
		}
		setcookie('mkauteur', $_POST['auteur'], 4294967295,'/');
		$lettres = Array('a', 'b', 'c', 'd', 'e', 'f', 'g', 'h', 'i', 'j', 'o', 't');
		$nbLettres = count($lettres);
		$laps = isset($_POST['nl']) ? $_POST['nl']:0;
		if (!(($laps > 0) && ($laps < 10)))
			$laps = 3;
		if (isset($_POST['id'])) {
			$circuitId = $_POST['id'];
			if (mysql_numrows(mysql_query('SELECT * FROM `mkcircuits` WHERE id="'.$circuitId.'" AND identifiant='.$identifiants[0].' AND identifiant2='.$identifiants[1].' AND identifiant3='.$identifiants[2].' AND identifiant4='.$identifiants[3]))) {
				mysql_query('UPDATE `mkcircuits` SET map="'. $_POST['map'] .'", laps="'. $laps .'", nom="'. $_POST['nom'] .'", auteur="'. $_POST['auteur'] .'" WHERE id="'. $circuitId .'"');
				mysql_query('DELETE FROM `mkp` WHERE circuit="'. $circuitId .'"');
				for ($i=0;$i<$nbLettres;$i++)
					mysql_query('DELETE FROM `mk'.$lettres[$i].'` WHERE circuit="'. $circuitId .'"');
			}
			else
				$circuitId = -1;
		}
		else {
			mysql_query('INSERT INTO `mkcircuits` VALUES (null, CURRENT_TIMESTAMP(), '.$identifiants[0].','.$identifiants[1].','.$identifiants[2].','.$identifiants[3].',-1,0,0,0,"'.$_POST['map'].'","'.$laps.'","'. $_POST['nom'] .'","'. $_POST['auteur'] .'")');
			$circuitId = mysql_insert_id();
			include('session.php');
			if ($id) {
				$getFollowers = mysql_query('SELECT follower FROM `mkfollowusers` WHERE followed="'. $id .'"');
				while ($follower = mysql_fetch_array($getFollowers))
					mysql_query('INSERT INTO `mknotifs` SET type="follower_circuit", user="'. $follower['follower'] .'", link="0,'.$circuitId.'"');
			}
		}
		if ($circuitId != -1) {
			for ($i=0;$i<36;$i++)
				mysql_query('INSERT INTO `mkp` VALUES('.$i.',"'.$circuitId.'","'.$_POST['p'.$i].'")');
			for ($i=0;$i<$nbLettres;$i++) {
				$lettre = $lettres[$i];
				for ($j=0;isset($_POST[$lettre.$j]);$j++) {
					if (preg_match("#\d+,\d#", $_POST[$lettre.$j]))
						mysql_query('INSERT INTO `mk'.$lettre.'` VALUES("'.$circuitId.'",'.$_POST[$lettre.$j].')');
				}
			}
			if (isset($_POST['cl'])) {
				include('challenge-associate.php');
				challengeAssociate('mkcircuits',$circuitId,$_POST['cl']);
			}
			include('cache_creations.php');
			@unlink(cachePath("mappreview$circuitId.png"));
			echo $circuitId;
		}
		mysql_close();
	}
}
?>