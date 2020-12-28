<?php
include('session.php');
if (!$id) {
	echo "Vous n'&ecirc;tes pas connect&eacute;";
	exit;
}
include('language.php');
include('initdb.php');
require_once('getRights.php');
if (!hasRight('moderator')) {
	echo "Vous n'&ecirc;tes pas mod&eacute;rateur";
	mysql_close();
	exit;
}
?>
<!DOCTYPE html>
<html lang="<?php echo $language ? 'en':'fr'; ?>">
<head>
<title>Mario Kart PC</title>
<?php
include('heads.php');
?>
<link rel="stylesheet" type="text/css" href="styles/classement.css" />
<link rel="stylesheet" type="text/css" href="styles/auto-complete.css" />
<style type="text/css">
#ban_msg {
	display: none;
}
#titres td:nth-child(2) {
	width: 300px;
}
</style>
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
		mysql_query('DELETE FROM `mkbans` WHERE player="'. $getId['id'] .'"');
		mysql_query('INSERT INTO `mkbans` VALUES('. $getId['id'] .',"'. $_POST['msg'] .'")');
		mysql_query('INSERT INTO `mklogs` VALUES(NULL,NULL, '. $id .', "Ban '. $getId['id'] .'")');
		if (isset($_POST['ip'])) {
			$getIp = mysql_fetch_array(mysql_query('SELECT identifiant,identifiant2,identifiant3,identifiant4 FROM `mkprofiles` WHERE id="'.$getId['id'].'"'));
			mysql_query('INSERT INTO `ip_bans` VALUES('.$getId['id'].','.$getIp['identifiant'].','.$getIp['identifiant2'].','.$getIp['identifiant3'].','.$getIp['identifiant4'].')');
		}
	}
}
$unban = isset($_GET['unban']) ? $_GET['unban']:null;
if ($unban) {
	mysql_query('UPDATE `mkjoueurs` SET banned=0 WHERE id="'. $unban .'"');
	mysql_query('DELETE FROM `ip_bans` WHERE player="'. $unban .'"');
	mysql_query('DELETE FROM `mkbans` WHERE player="'. $unban .'"');
	mysql_query('INSERT INTO `mklogs` VALUES(NULL,NULL, '. $id .', "Unban '. $unban .'")');
}
?>
<main>
	<h1><?php echo $language ? 'Ban member':'Bannir un membre'; ?></h1>
	<?php
	if ($ban)
		echo '<p><strong>'. $ban .'</strong> a été banni</p>';
	elseif ($unban) {
		if ($getNom = mysql_fetch_array(mysql_query('SELECT nom FROM `mkjoueurs` WHERE id="'. $unban .'"')))
			echo '<p><strong>'. $getNom['nom'] .'</strong> a été débanni</p>';
	}
	?>
	<div class="ranking-modes">
		<span><?php echo $language ? 'Banned members':'Membres bannis'; ?></span>
		<a href="ban-ip.php"><?php echo $language ? 'Banned IPs':'IP bannies'; ?></a>
	</div>
	<form method="post" action="ban-player.php">
	<blockquote>
	<p>
		<label for="joueur"><strong><?php echo $language ? 'Ban a player':'Bannir un joueur'; ?></strong></label> : <input type="text" name="joueur" id="joueur" />
		<div id="ban_msg">
			Message : <textarea name="msg" cols="30" rows="4"></textarea><br />
			<label><input type="checkbox" name="ip" /> <?php echo $language ? 'Also ban IP address':'Bannir également l\'adresse IP'; ?></label><br />
			<input type="submit" value="Valider" class="action_button" />
		</div>
	</p>
	</blockquote>
	</form>
	<h2><?php echo $language ? 'Banned people list':'Liste des membres bannis'; ?></h2>
	<table>
	<tr id="titres">
	<td><?php echo $language ? 'Nick':'Pseudo'; ?></td>
	<td>Message</td>
	<td><?php echo $language ? 'Unban':'Débannir'; ?></td>
	</tr>
	<?php
	$bannished = mysql_query('SELECT id,nom,msg FROM `mkjoueurs` LEFT JOIN `mkbans` ON id=player WHERE banned');	
	function controlLength($str,$maxLength) {
		$pts = '...';
		if (strlen($str) > $maxLength)
			return substr($str,0,$maxLength-strlen($pts)).$pts;
		return $str;
	}
	while ($joueur = mysql_fetch_array($bannished)) {
		?>
		<tr>
		<td><?php echo $joueur['nom']; ?></td>
		<td title="<?php echo htmlspecialchars($joueur['msg']); ?>"><?php	
			echo nl2br(htmlspecialchars(controlLength($joueur['msg'],150)));
		?></td>
		<td><a href="?unban=<?php echo $joueur['id']; ?>" class="action_button"><?php echo $language ? 'Unban':'Débannir'; ?></a></td>
		</tr>
		<?php
	}
	?>
	</table>
	<p><a href="forum.php"><?php echo $language ? 'Back to the forum':'Retour au forum'; ?></a><br />
	<a href="index.php"><?php echo $language ? 'Back to Mario Kart PC':'Retour &agrave; Mario Kart PC'; ?></a></p>
</main>
<?php
include('footer.php');
?>
<script type="text/javascript" src="scripts/jquery.min.js"></script>
<script type="text/javascript" src="scripts/auto-complete.min.js"></script>
<script type="text/javascript" src="scripts/autocomplete-player.js"></script>
<script type="text/javascript">
autocompletePlayer('#joueur', {
	onSelect: function(event, term, item) {
		preventSubmit(event);
		$("#ban_msg").show("fast");
	}
});
</script>
<?php
mysql_close();
?>
</body>
</html>