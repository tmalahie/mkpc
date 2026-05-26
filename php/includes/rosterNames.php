<?php
function getCharacterNamesMap() {
	ob_start();
	include(__DIR__ . '/getPersos.php');
	$persosList = json_decode(ob_get_clean(), true);
	$names = array();
	foreach ($persosList as $persoId => $_) $names[$persoId] = getCharacterName($persoId);
	return $names;
}
function getCharacterName($sPerso) {
	global $language;
	if ($language) {
		if ($sPerso == "maskass")
			$res = "shy guy";
		elseif ($sPerso == "skelerex")
			$res = "dry bones";
		elseif ($sPerso == "harmonie")
			$res = "rosalina";
		elseif ($sPerso == "roi_boo")
			$res = "king boo";
		elseif ($sPerso == "frere_marto")
			$res = "hammer bro";
		elseif ($sPerso == "bowser_skelet")
			$res = "dry bowser";
		elseif ($sPerso == "flora_piranha")
			$res = "petey piranha";
	}
	else {
		if ($sPerso == "frere_marto")
			$res = "frère marto";
	}
	if (!isset($res)) $res = $sPerso;
	$res = ucwords(str_replace('_', ' ', $res));
	return $res;
}
?>
