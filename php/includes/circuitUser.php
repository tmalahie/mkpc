<?php
function removeSpecialChars($str) {
	$unwanted_array = array('Š'=>'S', 'š'=>'s', 'Ž'=>'Z', 'ž'=>'z', 'À'=>'A', 'Á'=>'A', 'Â'=>'A', 'Ã'=>'A', 'Ä'=>'A', 'Å'=>'A', 'Æ'=>'A', 'Ç'=>'C', 'È'=>'E', 'É'=>'E',
		'Ê'=>'E', 'Ë'=>'E', 'Ì'=>'I', 'Í'=>'I', 'Î'=>'I', 'Ï'=>'I', 'Ñ'=>'N', 'Ò'=>'O', 'Ó'=>'O', 'Ô'=>'O', 'Õ'=>'O', 'Ö'=>'O', 'Ø'=>'O', 'Ù'=>'U',
		'Ú'=>'U', 'Û'=>'U', 'Ü'=>'U', 'Ý'=>'Y', 'Þ'=>'B', 'ß'=>'Ss', 'à'=>'a', 'á'=>'a', 'â'=>'a', 'ã'=>'a', 'ä'=>'a', 'å'=>'a', 'æ'=>'a', 'ç'=>'c',
		'è'=>'e', 'é'=>'e', 'ê'=>'e', 'ë'=>'e', 'ì'=>'i', 'í'=>'i', 'î'=>'i', 'ï'=>'i', 'ð'=>'o', 'ñ'=>'n', 'ò'=>'o', 'ó'=>'o', 'ô'=>'o', 'õ'=>'o',
		'ö'=>'o', 'ø'=>'o', 'ù'=>'u', 'ú'=>'u', 'û'=>'u', 'ý'=>'y', 'þ'=>'b', 'ÿ'=>'y' );
	$str = strtr($str, $unwanted_array);
	$str = preg_replace("#[\W\-_]#", '', $str);
	return $str;
}
function findCircuitUser($pseudo,$id,$table) {
	if ($cicuitIdentifiants = mysql_fetch_array(mysql_query('SELECT identifiant,identifiant2,identifiant3,identifiant4 FROM `'. $table .'` WHERE id="'. $id .'"'))) {
		$getProfiles = mysql_query('SELECT mkjoueurs.id,nom FROM `mkprofiles` INNER JOIN `mkjoueurs` ON mkprofiles.id=mkjoueurs.id WHERE identifiant='. $cicuitIdentifiants['identifiant'] .' AND identifiant2='. $cicuitIdentifiants['identifiant2'] .' AND identifiant3='. $cicuitIdentifiants['identifiant3'] .' AND identifiant4='. $cicuitIdentifiants['identifiant4'] .' AND deleted=0');
		$possiblePseudos = array();
		while ($profile = mysql_fetch_array($getProfiles)) {
			$possiblePseudos[] = array(
				'id' => $profile['id'],
				'nom' => strtolower($profile['nom'])
			);
		}
		$pseudo = strtolower($pseudo);
		foreach ($possiblePseudos as $possiblePseudo) {
			if ($possiblePseudo['nom'] == $pseudo)
				return $possiblePseudo['id'];
		}
		$pseudo = removeSpecialChars($pseudo);
		foreach ($possiblePseudos as $possiblePseudo) {
			if (str_replace('_','',$possiblePseudo['nom']) == $pseudo)
				return $possiblePseudo['id'];
		}
	}
	return 'null';
}
require_once('utils-date.php');
function formatDate($cDate) {
	global $language;
	if (!$cDate) return '';
	$dt = new \DateTime($cDate, new \DateTimeZone('Europe/Paris'));
	$ts = $dt->getTimestamp();
	$new = (time()-$ts < 86400);
	if (isset($_COOKIE['tz']))
		$dt->setTimezone(new \DateTimeZone($_COOKIE['tz']));
	if ($new)
		$format = 'H:i';
	else
		$format = (date('Y') == $dt->format('Y')) ? ($language ? 'd-m':'d / m') : ($language ? 'Y-m-d':'d/m/Y');
	return $dt->format($format);
}
?>