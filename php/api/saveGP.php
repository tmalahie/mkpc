<?php
header('Content-Type: text/plain');
if (isset($_POST['change']) && isset($_POST['pts'])) {
	include('../includes/escape_all.php');
	$change = $_POST['change'];
	$pts = $_POST['pts'];
	if (($pts>0) && ($pts<4) && (floor($pts)==$pts) && ($change>=0) && ($change<14) && (floor($change)==$change)) {
		include('../includes/initdb.php');
		include('../includes/fetchSaves.php');
		$avant = 0;
		for ($i=0;$i<14;$i++)
			$avant += $mkSaves[$i];
		$apres = $avant;
		if ($pts > $mkSaves[$change]) {
			$apres += ($pts-$mkSaves[$change]);
			$mkSaves[$change] = $pts;
			mysql_query('UPDATE `mksaves` SET scores="'. $mkSaves .'" WHERE identifiant="'. $identifiants[0] .'" AND identifiant2="'. $identifiants[1] .'" AND identifiant3="'. $identifiants[2] .'" AND identifiant4="'. $identifiants[3] .'"');
		}
		$newPerso = '';
		if (($avant < 3) && ($apres >= 3))
			$newPerso = 'koopa';
		if (($avant < 7) && ($apres >= 7))
			$newPerso = 'waluigi';
		if (($avant < 12) && ($apres >= 12))
			$newPerso = 'maskass';
		if (($avant < 18) && ($apres >= 18))
			$newPerso = 'birdo';
		if (($avant < 24) && ($apres >= 24))
			$newPerso = 'roi_boo';
		if (($avant < 30) && ($apres >= 30))
			$newPerso = 'frere_marto';
		if (($avant < 33) && ($apres >= 33))
			$newPerso = 'bowser_skelet';
		if (($avant < 39) && ($apres >= 39))
			$newPerso = 'flora_piranha';
		if (($avant < 42) && ($apres >= 42))
			$newPerso = 'link';
		echo '"'.$newPerso.'"';
		mysql_close();
	}
}
?>