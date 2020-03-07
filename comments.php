<!DOCTYPE html>
<html lang="<?php echo $language ? 'en':'fr'; ?>">
<head>
<title>Mario Kart PC</title>
<meta charset="utf-8" />
</head>
<body>
<?php
include('initdb.php');
mysql_query("SET NAMES 'utf8'");
mysql_query("SET CHARACTER SET utf8");
mysql_query("SET COLLATION_CONNECTION = 'utf8_unicode_ci'");
$getComments = mysql_query('SELECT c.circuit,c.type,c.auteur,c.message,c.date,j.nom FROM (SELECT * FROM `mkcomments` ORDER BY id DESC) as c, `mkjoueurs` j WHERE c.auteur=j.id GROUP BY c.type,c.circuit ORDER BY c.id DESC');
echo '<ul>';
while ($comment = mysql_fetch_array($getComments)) {
	if ($getCircuit = mysql_fetch_array(mysql_query('SELECT * FROM `'. $comment['type'] .'` WHERE id='. $comment['circuit']))) {
		switch ($comment['type']) {
		case 'mkcircuits' :
			$url = ($getCircuit['type']===1 ? 'circuit.php':'arena.php') . '?id='. $getCircuit['id'];
			break;
		case 'arenes' :
			$url = 'battle.php?i='. $getCircuit['ID'];
			break;
		case 'circuits' :
			$url = 'map.php?i='. $getCircuit['ID'];
		}
		echo '<li>['. $comment['date'] .'] Dans <a href="'. $url .'" target="_blank">'. ($getCircuit['nom'] ? htmlentities(utf8_decode($getCircuit['nom'])):'Sans titre') .'</a> par <em>'. htmlentities(utf8_decode($comment['nom'])) .'</em> : <strong>'. htmlentities(utf8_decode(iconv('utf-8', 'windows-1252',$comment['message']))) .'</strong></li>';
	}
}
echo '</ul>';
mysql_close();
?>
</body>
</html>