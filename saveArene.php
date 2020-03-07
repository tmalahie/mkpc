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
		for ($i=0;$i<8;$i++) {
			if (!isset($_POST['r'.$i]) || !isset($_POST['s'.$i])) {
				$allPieces = false;
				break;
			}
		}
		if ($allPieces) {
			include('initdb.php');
			include('getId.php');
			setcookie('mkauteur', $_POST['auteur'], 4294967295,'/');
			$lettres = Array('a', 'b', 'c', 'd', 'e', 'f', 'g', 'h', 'i', 'j', 'o', 't');
			$nbLettres = count($lettres);
			if (isset($_POST['id'])) {
				$areneId = $_POST['id'];
				if (mysql_numrows(mysql_query('SELECT * FROM `mkcircuits` WHERE id="'.$areneId.'" AND identifiant='. $identifiants[0].' AND identifiant2='.$identifiants[1].' AND identifiant3='.$identifiants[2].' AND identifiant4='.$identifiants[3]))) {
					mysql_query('UPDATE `mkcircuits` SET map="'. $_POST['map'] .'", nom="'. $_POST['nom'] .'", auteur="'. $_POST['auteur'] .'" WHERE id="'. $areneId .'"');
					mysql_query('DELETE FROM `mkp` WHERE circuit='. $areneId);
					mysql_query('DELETE FROM `mkr` WHERE circuit='. $areneId);
					for ($i=0;$i<$nbLettres;$i++)
						mysql_query('DELETE FROM `mk'.$lettres[$i].'` WHERE circuit="'. $areneId .'"');
				}
				else
					$areneId = -1;
			}
			else {
				mysql_query('INSERT INTO `mkcircuits` VALUES (null, CURRENT_TIMESTAMP(), '.$identifiants[0].','.$identifiants[1].','.$identifiants[2].','.$identifiants[3].',-1,0,0,1,"'.$_POST['map'].'",3,"'. $_POST['nom'] .'","'. $_POST['auteur'] .'")');
				$areneId = mysql_insert_id();
				include('session.php');
				if ($id) {
					$getFollowers = mysql_query('SELECT follower FROM `mkfollowusers` WHERE followed="'. $id .'"');
					while ($follower = mysql_fetch_array($getFollowers))
						mysql_query('INSERT INTO `mknotifs` SET type="follower_circuit", user="'. $follower['follower'] .'", link="0,'.$areneId.'"');
				}
			}
			if ($areneId != -1) {
				for ($i=0;$i<36;$i++)
					mysql_query('INSERT INTO `mkp` VALUES('.$i.',"'.$areneId.'","'.$_POST['p'.$i].'")');
				for ($i=0;$i<8;$i++)
					mysql_query('INSERT INTO `mkr` VALUES('.$i.',"'.$areneId.'","'.$_POST['s'.$i].'","'.$_POST['r'.$i].'")');
				for ($i=0;$i<$nbLettres;$i++) {
					$lettre = $lettres[$i];
					for ($j=0;isset($_POST[$lettre.$j]);$j++) {
						if (preg_match("#\d+,\d#", $_POST[$lettre.$j]))
							mysql_query('INSERT INTO `mk'.$lettre.'` VALUES("'.$areneId.'",'.$_POST[$lettre.$j].')');
					}
				}
				if (isset($_POST['cl'])) {
					include('challenge-associate.php');
					challengeAssociate('mkcircuits',$areneId,$_POST['cl']);
				}
				include('cache_creations.php');
				@unlink(cachePath("mappreview$areneId.png"));
				echo $areneId;
			}
			mysql_close();
		}
	}
}
?>