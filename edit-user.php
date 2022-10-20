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
if (isset($_POST['joueur'])) {
	$nick = $_POST['joueur'];
	if ($getId = mysql_fetch_array(mysql_query('SELECT id FROM `mkjoueurs` WHERE nom="'. $nick .'"')))
		header('location: edit-profile.php?member='. $getId['id']);
}
?>
<!DOCTYPE html>
<html lang="<?php echo $language ? 'en':'fr'; ?>">
<head>
<title><?php echo $language ? 'Manage member profile':'Gérer le profil d\'un membre'; ?> - Mario Kart PC</title>
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
?>
<main>
	<h1><?php echo $language ? 'Edit profile':'Modification du profil'; ?></h1>
	<p>
		<?php
		if ($language)
			echo "This page allows you to change the profile of a given member.";
		else
			echo "Cette page permet de modifier le profil d'un membre en particulier.";
		?>
	</p>
	<form method="post" action="edit-user.php">
	<blockquote>
		<p>
			<label for="joueur"><strong><?php echo $language ? 'Member nick':'Pseudo du membre'; ?></strong></label> : <input type="text" name="joueur" id="joueur" value="<?php if (isset($nick)) echo htmlspecialchars($nick); ?>" required="required" />
			<input type="submit" value="Ok" class="action_button" />
		</p>
	</blockquote>
	</form>
	<p><a href="admin.php"><?php echo $language ? 'Back to the admin page':'Retour à la page admin'; ?></a><br />
	<a href="index.php"><?php echo $language ? 'Back to Mario Kart PC':'Retour &agrave; Mario Kart PC'; ?></a></p>
</main>
<?php
include('footer.php');
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