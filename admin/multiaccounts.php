<?php
include('auth.php');
mysql_query('INSERT IGNORE INTO mkips (SELECT id AS player,identifiant AS ip1,identifiant2 AS ip2, identifiant3 AS ip3, identifiant4 AS ip4 FROM mkprofiles WHERE identifiant IS NOT NULL AND NOT exists (SELECT * FROM mkips WHERE player=id AND ip1=identifiant AND ip2=identifiant2 AND ip3=identifiant3 AND ip4=identifiant4))');
?>
<!DOCTYPE html>
<html>
<head>
	<meta charset="UTF-8" />
<title>Admin MKPC</title>
<meta name="viewport" content="width=device-width, initial-scale=1">
</head>
<body>
<?php
if (isset($_GET['pseudo'])) {
	if ($getId = mysql_fetch_array(mysql_query('SELECT id FROM `mkjoueurs` WHERE nom="'. $_GET['pseudo'] .'"'))) {
		$getPlayers = mysql_query('SELECT DISTINCT res.player FROM (SELECT player FROM `mkips` m WHERE EXISTS(SELECT * FROM `mkips` m2 WHERE player='. $getId['id'] .' AND m2.ip1=m.ip1 AND m2.ip2=m.ip2 AND m2.ip3=m.ip3 AND m2.ip4=m.ip4)) res');
		$v = '';
		while ($playerId = mysql_fetch_array($getPlayers)) {
			if ($playerName = mysql_fetch_array(mysql_query('SELECT nom FROM `mkjoueurs` WHERE id='. $playerId['player']))) {
				echo $v.$playerName['nom'];
				$v = ', ';
			}
		}
	}
}
mysql_close();
?>
<form method="get" action="">
	<p>
		<input type="text" name="pseudo"<?php if (isset($_GET['pseudo'])) echo ' value="'. htmlspecialchars($_GET['pseudo']) .'"'; ?> />
		<input type="submit" value="Ok" />
	</p>
</form>
</body>
</html>