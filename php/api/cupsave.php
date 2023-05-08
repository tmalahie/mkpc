<?php
header('Content-Type: text/plain');
if (isset($_POST['cup']) && isset($_POST['pts'])) {
	include('../includes/escape_all.php');
	$cup = $_POST['cup'];
	$pts = $_POST['pts'];
	if (($pts > 0) && ($pts < 4)) {
		include('../includes/initdb.php');
		if ($getCup = mysql_fetch_array(mysql_query('SELECT * FROM `mkcups` WHERE id="'. $cup .'"'))) {
			$oldScore = 0;
			include('../includes/getId.php');
			$totalScore = mysql_fetch_array(mysql_query('SELECT SUM(score) AS totalScore FROM `mkwins` WHERE identifiant="'. $identifiants[0] .'" AND identifiant2="'. $identifiants[1] .'" AND identifiant3="'. $identifiants[2] .'" AND identifiant4="'. $identifiants[3] .'"'));
			$totalScore = $totalScore['totalScore']*1;
			$newScore = $totalScore;
			if ($oldScore = mysql_fetch_array(mysql_query('SELECT score FROM `mkwins` WHERE cup="'. $cup .'" AND identifiant="'. $identifiants[0] .'" AND identifiant2="'. $identifiants[1] .'" AND identifiant3="'. $identifiants[2] .'" AND identifiant4="'. $identifiants[3] .'"')))
				$oldScore = $oldScore['score'];
			else
				$oldScore = 0;
			if ($pts > $oldScore) {
				$newScore += ($pts-$oldScore);
				if ($oldScore)
					mysql_query('UPDATE `mkwins` SET score="'. $pts .'" WHERE cup="'. $cup .'" AND identifiant="'. $identifiants[0] .'" AND identifiant2="'. $identifiants[1] .'" AND identifiant3="'. $identifiants[2] .'" AND identifiant4="'. $identifiants[3] .'"');
				else
					mysql_query('INSERT INTO `mkwins` VALUES("'. $identifiants[0] .'","'. $identifiants[1] .'","'. $identifiants[2] .'","'. $identifiants[3] .'","'. $cup .'","'. $pts .'")');
			}
			$newPerso = '';
			if (($totalScore < 3) && ($newScore >= 3))
				$newPerso = 'bowser_jr';
			if (($totalScore < 7) && ($newScore >= 7))
				$newPerso = 'harmonie';
			if (($totalScore < 12) && ($newScore >= 12))
				$newPerso = 'diddy-kong';
			if (($totalScore < 18) && ($newScore >= 18))
				$newPerso = 'skelerex';
			if (($totalScore < 24) && ($newScore >= 24))
				$newPerso = 'funky-kong';
			if (($totalScore < 30) && ($newScore >= 30))
				$newPerso = 'toadette';
			echo '"'.$newPerso.'"';
		}
		mysql_close();
	}
}
?>