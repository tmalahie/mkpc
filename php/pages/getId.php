<?php
include('utilId.php');
if (isset($_COOKIE['mktoken']))
	fetch_mkid();
if (!isset($identifiants)) {
	$remoteAddr = $_SERVER['REMOTE_ADDR'];
	$ip = explode('.', $remoteAddr);
	$nbs = count($ip);
	if ($nbs > 1) {
		$facteur = 1;
		$identifiant = 0;
		do {
			$nbs--;
			$identifiant += $ip[$nbs]*$facteur;
			$facteur *= 256;
		} while ($nbs);
		$identifiants[0] = $identifiant;
		for ($i=1;$i<4;$i++)
			$identifiants[$i] = 0;
		store_mkid();
	}
	else {
		$ip = explode(':', strtoupper($remoteAddr));
		if (count($ip) > 1) {
			$identifiants = Array();
			for ($i=0;$i<4;$i++) {
				$n = $i*2;
				$nb = 0;
				$facteur = 1;
				for ($j=0;$j<2;$j++) {
					$sequence = isset($ip[$n+$j]) ? $ip[$n+$j]:'';
					$l = strlen($sequence);
					for ($k=0;$k<4;$k++) {
						if ($k < $l) {
							$char = ord($sequence[$k]);
							if ($char >= 97)
								$char -= 87;
							else
								$char -= 48;
							$nb += $char*$facteur;
						}
						$facteur *= 16;
					}
				}
				$identifiants[$i] = $nb;
			}
		}
		else {
			$identifiants = Array();
			for ($i=0;$i<4;$i++)
				$identifiants[$i] = rand(0,4294967295);
		}
		store_mkid();
	}
}
?>