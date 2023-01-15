<?php
include('session.php');
if (!$id) {
	echo "Vous n'&ecirc;tes pas connect&eacute;";
	exit;
}
include('language.php');
include('initdb.php');
if (!$id) {
	echo "Vous n'&ecirc;tes pas connect&eacute;";
	mysql_close();
	exit;
}
require_once('getRights.php');
if (!hasRight('manager')) {
	echo "Vous n'&ecirc;tes pas mod&eacute;rateur";
	mysql_close();
	exit;
}
mysql_query('INSERT IGNORE INTO mkips (SELECT id AS player,identifiant AS ip1,identifiant2 AS ip2, identifiant3 AS ip3, identifiant4 AS ip4 FROM mkprofiles WHERE identifiant IS NOT NULL AND NOT exists (SELECT * FROM mkips WHERE player=id AND ip1=identifiant AND ip2=identifiant2 AND ip3=identifiant3 AND ip4=identifiant4))');
//mysql_query('DELETE FROM mkips WHERE identifiant=0 AND identifiant2=0 AND identifiant3=0 AND identifiant4=0');
?>
<!DOCTYPE html>
<html lang="<?php echo $language ? 'en':'fr'; ?>">
<head>
<title><?php echo $language ? 'Double accounts':'Double comptes'; ?> - Mario Kart PC</title>
<?php
include('heads.php');
?>
<link rel="stylesheet" type="text/css" href="styles/classement.css" />
<link rel="stylesheet" type="text/css" href="styles/auto-complete.css" />

<?php
include('o_online.php');
?>
</head>
<body>
<?php
include('header.php');
$page = 'forum';
include('menu.php');
$ban = isset($_POST['joueur']) ? $_POST['joueur']:null;
if ($ban) {
	if ($getId = mysql_fetch_array(mysql_query('SELECT id FROM `mkjoueurs` WHERE nom="'. $ban .'"'))) {
		mysql_query('UPDATE `mkjoueurs` SET banned=2 WHERE id='. $getId['id']);
		mysql_query('INSERT INTO `mklogs` VALUES(NULL,NULL, '. $id .', "Ban '. $getId['id'] .'")');
	}
}
$unban = isset($_GET['unban']) ? $_GET['unban']:null;
if ($unban) {
	mysql_query('UPDATE `mkjoueurs` SET banned=0 WHERE id="'. $unban .'"');
	mysql_query('DELETE FROM `ip_bans` WHERE player="'. $unban .'"');
	mysql_query('INSERT INTO `mklogs` VALUES(NULL,NULL, '. $id .', "Unban '. $unban .'")');
}
?>
<main>
	<h1><?php echo $language ? 'See double accounts':'Voir les doubles comptes'; ?></h1>
	<?php
	if (isset($_GET['pseudo'])) {
		echo '<p>';
		echo $language ? 'Double accounts of '. $_GET['pseudo'] .': ' : 'Doubles comptes de '. $_GET['pseudo'] .' : ';
		if ($getId = mysql_fetch_array(mysql_query('SELECT id FROM `mkjoueurs` WHERE nom="'. $_GET['pseudo'] .'"'))) {
			$getPlayers = mysql_query('SELECT DISTINCT res.player FROM (SELECT player FROM `mkips` m WHERE EXISTS(SELECT * FROM `mkips` m2 WHERE player='. $getId['id'] .' AND m2.ip1=m.ip1 AND m2.ip2=m.ip2 AND m2.ip3=m.ip3 AND m2.ip4=m.ip4) AND player!="'. $getId['id'] .'") res');
			$v = '';
			while ($playerId = mysql_fetch_array($getPlayers)) {
				if ($playerName = mysql_fetch_array(mysql_query('SELECT nom FROM `mkjoueurs` WHERE id='. $playerId['player']))) {
					echo $v.'<a href="profil.php?id='. $playerId['player'] .'"><strong>'.$playerName['nom'].'</strong></a>';
					$v = ', ';
				}
			}
		}
		echo '</p>';
	}
	?>
	<form method="get" action="doublecomptes.php">
	<blockquote>
	<p><label for="pseudo"><strong><?php echo $language ? 'Enter the nickname':'Pseudo du joueur'; ?></strong></label> : <input type="text" name="pseudo" id="pseudo" /> <input type="submit" value="<?php echo $language ? 'Validate' : 'Valider'; ?>" class="action_button" /></p>
	</blockquote>
	</form>
	<p><a href="forum.php"><?php echo $language ? 'Back to the forum':'Retour au forum'; ?></a><br />
	<a href="index.php"><?php echo $language ? 'Back to Mario Kart PC':'Retour &agrave; Mario Kart PC'; ?></a></p>
</main>
<?php
include('footer.php');
?>
<script type="text/javascript" src="scripts/auto-complete.min.js"></script>
<script type="text/javascript" src="scripts/autocomplete-player.js"></script>
<script type="text/javascript">
autocompletePlayer('#pseudo');
</script>
<?php
mysql_close();
?>
</body>
</html>