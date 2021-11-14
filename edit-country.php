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
<title><?php echo $language ? 'Manage member country':'Gérer le pays d\'un membre'; ?> - Mario Kart PC</title>
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
$success = null;
$message = null;
if (isset($_POST['joueur']) && isset($_POST['country'])) {
	$nick = $_POST['joueur'];
	$country = $_POST['country'];
	if ($getId = mysql_fetch_array(mysql_query('SELECT id FROM `mkjoueurs` WHERE nom="'. $nick .'"'))) {
		if ($getCountryId = mysql_fetch_array(mysql_query('SELECT id,name_'. ($language ? 'en':'fr') .' AS name FROM mkcountries WHERE code="'. $country .'"')))
            $countryId = $getCountryId['id'];
        else
            $countryId = 0;
        mysql_query('INSERT INTO `mklogs` VALUES(NULL,NULL, '. $id .', "Flag '. $getId['id'] .' '. $country .'")');
        mysql_query('UPDATE `mkprofiles` SET country="'. $countryId .'" WHERE id="'. $getId['id'] .'"');
        $success = $language ? '<strong>'. $nick .'</strong>\'s country has been updated to <strong>'. $getCountryId['name'] .'</strong>' : 'Le pays de <strong>'. $nick .'</strong> vient d\'être changé en <strong>'. $getCountryId['name'] .'</strong>';
	}
	else
		$message = $language ? 'This player does not exist':'Ce membre n\'existe pas';
}
?>
<main>
	<h1><?php echo $language ? 'Edit country':'Modification du pays'; ?></h1>
	<?php
	if ($success)
		echo '<p style="color: #0A0">'. $success .'</p>';
	if ($message)
		echo '<p style="color: red">'. $message .'</p>';
	?>
	<p>
		<?php
		if ($language)
			echo "This page allows you to change the country of a given member.";
		else
			echo "Cette page permet de modifier le pays d'un membre en particulier.";
		?>
	</p>
	<form method="post" action="edit-country.php">
	<blockquote>
		<p><label for="joueur"><strong><?php echo $language ? 'Member nick':'Pseudo du membre'; ?></strong></label> : <input type="text" name="joueur" id="joueur" value="<?php if (isset($nick)) echo htmlspecialchars($nick); ?>" required="required" /></p>
		<p><label for="country"><strong><?php echo $language ? 'New country':'Nouveau pays'; ?></strong></label> : <select type="text" name="country" id="country"><?php
		include('list-countries.php');
		?></select></p>
		<p><input type="submit" value="<?php echo $language ? 'Validate':'Valider'; ?>" class="action_button" /></p>
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