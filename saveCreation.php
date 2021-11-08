<?php
if (isset($_POST['nom']) && isset($_POST['auteur']) && isset($_POST['map'])) {
	$allPieces = true;
	$isBattle = +isset($_POST['battle']);
	for ($i=0;$i<36;$i++) {
		if (!isset($_POST['p'.$i])) {
			$allPieces = false;
			break;
		}
	}
	if ($allPieces) {
		if ($isBattle) {
			for ($i=0;$i<8;$i++) {
				if (!isset($_POST['r'.$i]) || !isset($_POST['s'.$i])) {
					$allPieces = false;
					break;
				}
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
			require_once('circuitPrefix.php');
			$map = $_POST['map'];
			$laps = isset($_POST['nl']) ? $_POST['nl']:0;
			if (!(($laps > 0) && ($laps < 10)))
				$laps = 3;
			if (isset($_POST['id'])) {
				$circuitId = $_POST['id'];
				if (mysql_numrows(mysql_query('SELECT * FROM `mkcircuits` WHERE id="'.$circuitId.'" AND identifiant='.$identifiants[0].' AND identifiant2='.$identifiants[1].' AND identifiant3='.$identifiants[2].' AND identifiant4='.$identifiants[3]))) {
					mysql_query('UPDATE `mkcircuits` SET map="'. $map .'", laps="'. $laps .'", nom="'. $_POST['nom'] .'", auteur="'. $_POST['auteur'] .'" WHERE id="'. $circuitId .'"');
					mysql_query('DELETE FROM `mkp` WHERE circuit="'. $circuitId .'"');
					if ($isBattle)
						mysql_query('DELETE FROM `mkr` WHERE circuit="'. $circuitId .'"');
					for ($i=0;$i<$nbLettres;$i++)
						mysql_query('DELETE FROM `mk'.$lettres[$i].'` WHERE circuit="'. $circuitId .'"');
				}
				else
					$circuitId = -1;
			}
			else {
				mysql_query('INSERT INTO `mkcircuits` VALUES (null, CURRENT_TIMESTAMP(), '.$identifiants[0].','.$identifiants[1].','.$identifiants[2].','.$identifiants[3].',0,0,0,0,0,'.$isBattle.',"'.$map.'","'.$laps.'","'. $_POST['nom'] .'","'. $_POST['auteur'] .'")');
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
				if ($isBattle) {
					for ($i=0;$i<8;$i++)
						mysql_query('INSERT INTO `mkr` VALUES('.$i.',"'.$circuitId.'","'.$_POST['s'.$i].'","'.$_POST['r'.$i].'")');
				}
				for ($i=0;$i<$nbLettres;$i++) {
					$lettre = $lettres[$i];
					$multTypes = ('t' === $lettre);
					$prefixes = getLetterPrefixes($lettre,$map);
					for ($k=0;$k<$prefixes;$k++) {
						$prefix = getLetterPrefix($lettre,$k);
						for ($j=0;isset($_POST[$prefix.$j]);$j++) {
							if (preg_match("#^-?\d+,-?\d+$#", $_POST[$prefix.$j]))
								mysql_query('INSERT INTO `mk'.$lettre.'` VALUES("'.$circuitId.'",'.$_POST[$prefix.$j].($multTypes ? ",$k":'').')');
						}
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
}
?>