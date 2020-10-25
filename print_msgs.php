<?php
$ignores = mysql_query('SELECT nom FROM `mkignores` INNER JOIN `mkjoueurs` ON ignored=id WHERE ignorer='. $id);
$where = '';
$a = " WHERE ";
while ($ignore = mysql_fetch_array($ignores)) {
	$where .= $a;
	$a = " AND ";
	$where .= "pseudo!='". $ignore['nom'] ."'";
}
$nbMessages = mysql_fetch_array(mysql_query("SELECT COUNT(*) AS nb FROM `minichat`$where ORDER BY id"));
$nbKept = 30;
$first = max($nbMessages['nb']-$nbKept,0);
$messages = mysql_query("SELECT * FROM `minichat`$where ORDER BY id LIMIT $first,$nbKept");
echo '[';
if ($donnees = mysql_fetch_array($messages)) {
	function escape($str) {
		return str_replace('"', '\\"', str_replace('\\', '\\\\', $str));
	}
	$styles = explode(",", $donnees["style"]);
	echo '["'.escape($donnees['id']).'","'.escape($donnees['pseudo']).'","'. escape($donnees['message']) .'",'. (in_array('gras', $styles) ? 'true':'false') .','.(in_array('souligne', $styles) ? 'true':'false').','.(in_array('italique', $styles) ? 'true':'false').','. $donnees["r"].','.$donnees["g"].','.$donnees["b"] .','.$donnees["taille"].',"'.$donnees["police"].'"]';
	while ($donnees = mysql_fetch_array($messages)) {
		$styles = explode(",", $donnees["style"]);
		echo ',["'.escape($donnees['id']).'","'.escape($donnees['pseudo']).'","'. escape($donnees['message']) .'",'. (in_array('gras', $styles) ? 'true':'false') .','.(in_array('souligne', $styles) ? 'true':'false').','.(in_array('italique', $styles) ? 'true':'false').','. $donnees["r"].','.$donnees["g"].','.$donnees["b"] .','.$donnees["taille"].',"'.$donnees["police"].'"]';
	}
}
echo ']';
?>
