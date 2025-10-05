<?php
include('../includes/session.php');
if (!$id) {
	echo "Vous n'&ecirc;tes pas connect&eacute;";
	exit;
}
include('../includes/language.php');
include('../includes/initdb.php');
require_once('../includes/getRights.php');
if (!hasRight('moderator')) {
	echo "Vous n'&ecirc;tes pas mod&eacute;rateur";
	mysql_close();
	exit;
}
?>
<!DOCTYPE html>
<html lang="<?php echo $language ? 'en':'fr'; ?>">
<head>
<title><?php echo $language ? 'Change member\'s username':'Modifier le pseudo d\'un membre'; ?> - Mario Kart PC</title>
<?php
include('../includes/heads.php');
?>
<link rel="stylesheet" type="text/css" href="styles/forum.css" />
<link rel="stylesheet" type="text/css" href="styles/classement.css" />
<link rel="stylesheet" type="text/css" href="styles/auto-complete.css" />

<?php
include('../includes/o_online.php');
?>
</head>
<body>
<?php
include('../includes/header.php');
$page = 'forum';
include('../includes/menu.php');
$success = null;
if (isset($_POST['joueur'])) {
	$nick = $_POST['joueur'];
	if ($getId = mysql_fetch_array(mysql_query('SELECT id FROM `mkjoueurs` WHERE nom="'. $nick .'"'))) {
        include('../includes/getId.php');
        require_once('../includes/reset-password.php');
        $link = generatePasswordLink($getId['id']);
        $success = ($language ? "The following password link has been generated:":"Le lien de réinitialisation de mot de passe a été généré :") . '<br /><a href="'. $link .'" target="_blank">'. $link .'</a><br />' . ($language ? "You can forward it to the member.":"Vous pouvez le transmettre au membre ayant fait la demande.");
	}
}
$autocompleteNick = '';
if (isset($_GET['member'])) {
	if ($getPseudo = mysql_fetch_array(mysql_query('SELECT nom FROM `mkjoueurs` WHERE id="'. $_GET['member'] .'"')))
		$autocompleteNick = $getPseudo['nom'];
}
?>
<main>
	<h1><?php echo $language ? 'Reset password':'Réinitialiser mot de passe'; ?></h1>
	<?php
	if ($success)
		echo '<p class="success">'. $success .'</p>';
	?>
	<p>
		<?php
		if ($language)
			echo "This page allows you to reset the password of a member.<br />Use it if someone forgot his password on an account without email filled in.";
		else
			echo "Cette page permet de réinitialiser le mot de passe d'un membre.<br />À utiliser si quelqu'un a oublié son mot de passe sur un compte sans email renseigné.";
		?>
	</p>
	<form method="post" action="reset-password.php">
		<p>
            <label for="joueur"><strong><?php echo $language ? 'Username':'Pseudo'; ?></strong></label><?php echo $language ? ':':' :'; ?> <input type="text" name="joueur" id="joueur" value="<?php echo $autocompleteNick; ?>" required="required" />
		    <input type="submit" value="Ok" class="action_button" />
        </p>
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
autocompletePlayer('#joueur');
</script>
<?php
mysql_close();
?>
</body>
</html>