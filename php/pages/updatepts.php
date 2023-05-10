<?php
include('../includes/session.php');
if (!$id) {
	echo "Vous n'&ecirc;tes pas connect&eacute;";
	exit;
}
include('../includes/language.php');
include('../includes/initdb.php');
if (!$id) {
	echo "Vous n'&ecirc;tes pas connect&eacute;";
	mysql_close();
	exit;
}
require_once('../includes/getRights.php');
if (!hasRight('manager')) {
	echo "Vous n'&ecirc;tes pas administrateur";
	mysql_close();
	exit;
}
?>
<!DOCTYPE html>
<html lang="<?php echo $language ? 'en':'fr'; ?>">
<head>
<title><?php echo $language ? 'Add pts':'Ajouter des points'; ?> - Mario Kart PC</title>
<?php
include('../includes/heads.php');
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
include('../includes/o_online.php');
?>
</head>
<body>
<?php
include('../includes/header.php');
$page = 'forum';
include('../includes/menu.php');
$ban = isset($_POST['joueur']) ? $_POST['joueur']:null;
$isBattle = isset($_GET['battle']);
$pts_ = 'pts_'.($isBattle ? 'battle':'vs');
if (isset($_POST['joueur']) && isset($_POST['pts']) && is_numeric($_POST['pts'])) {
	if ($getId = mysql_fetch_array(mysql_query('SELECT j.id,j.'.$pts_.' AS pts FROM `mkjoueurs` j WHERE j.nom="'. $ban .'"'))) {
		$newScore = max($getId['pts']+$_POST['pts'],1);
		mysql_query('UPDATE `mkjoueurs` SET '.$pts_.'='. $newScore .' WHERE id='. $getId['id']);
		mysql_query('INSERT INTO `mklogs` VALUES(NULL,NULL, '. $id .', "'.($isBattle ? 'B':'').'pts '. ($newScore-$getId['pts']) .' '. $getId['id'] .'")');
		$ptsPlus = true;
	}
}
?>
<main>
	<h1><?php echo $language ? 'Add pts':'Ajouter des points'; ?></h1>
	<div class="ranking-modes">
		<?php
		if ($isBattle) {
			?>
			<a href="updatepts.php"><?php echo $language ? 'VS mode':'Course VS'; ?></a><span>
			<?php echo $language ? 'Battle mode':'Bataille de ballons'; ?></span>
			<?php
		}
		else {
			?>
			<span><?php echo $language ? 'VS mode':'Course VS'; ?></span><a
			href="updatepts.php?battle"><?php echo $language ? 'Battle mode':'Bataille de ballons'; ?></a>
			<?php
		}
		?>
	</div>
	<?php
	if (isset($ptsPlus))
		echo '<p><strong>'. $_POST['joueur'] .'</strong> vient de '. (($_POST['pts']>0)?'gagner':'perdre') .' '. abs($_POST['pts']) .' pts. Son nouveau score est de <strong>'. $newScore .'</strong> pts.</p>';
	?>
	<?php
	if ($language) {
		?>
	<p>
		This page allows to add points to a player after an event (tournament for example).<br />
		The number of points can also be negative.
	</p>
		<?php
	}
	else {
		?>
	<p>
		Cette page permet d'ajouter des points à un joueur suite à un événement (tournoi par exemple).<br />
		Le nombre de points peut également être négatif.
	</p>
		<?php
	}
	?>
	<form method="post" action="updatepts.php<?php if ($isBattle) echo '?battle'; ?>">
	<blockquote>
		<p><label for="joueur"><strong><?php echo $language ? 'Player':'Joueur'; ?></strong></label> : <input type="text" name="joueur" id="joueur" required="required" /></p>
		<p><label for="pts"><strong><?php echo $language ? 'Add':'Ajouter'; ?></strong></label> <input type="number" min="-9999" max="9999" style="width:50px" name="pts" id="pts" /> pts.</p>
		<p><input type="submit" value="<?php echo $language ? 'Validate' : 'Valider'; ?>" class="action_button" /></p>
	</blockquote>
	</form>
	<p><a href="forum.php"><?php echo $language ? 'Back to the forum':'Retour au forum'; ?></a><br />
	<a href="index.php"><?php echo $language ? 'Back to Mario Kart PC':'Retour &agrave; Mario Kart PC'; ?></a></p>
</main>
<?php
include('../includes/footer.php');
?>
<script type="text/javascript" src="scripts/auto-complete.min.js"></script>
<script type="text/javascript" src="scripts/autocomplete-player.js"></script>
<script type="text/javascript">
autocompletePlayer('#joueur', {
	onSelect: function(event, term, item) {
		preventSubmit(event);
	}
});
</script>
<?php
mysql_close();
?>
</body>
</html>