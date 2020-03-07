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
if (isset($_POST['joueur']) && isset($_POST['newpseudo'])) {
	$old = $_POST['joueur'];
	$new = $_POST['newpseudo'];
	if ($getId = mysql_fetch_array(mysql_query('SELECT id FROM `mkjoueurs` WHERE nom="'. $old .'"'))) {
		if (!$new)
			$message = $language ? 'Please enter a nick':'Veuillez entrer un pseudo';
		elseif (!preg_match('#^[a-zA-Z0-9_\-]+$#', $new))
			$message = $language ? 'The nick mustn\'t contain special chars.<br />Allowed chars are : letters, numbers, the dash - and the underscore _':'Le pseudo ne doit pas contenir de caract&egrave;res spéciaux.<br />Les caract&egrave;res autoris&eacute;s sont les lettres sans accents, les chiffres, le tiret - et le underscore _';
		elseif (mysql_numrows(mysql_query('SELECT * FROM `mkjoueurs` WHERE nom="'.$new.'" AND id!='. $getId['id'])))
			$message = $language ? 'This nick already exists':'Ce pseudo existe déjà';
		else {
			mysql_query('UPDATE `mkjoueurs` SET nom="'. $new .'" WHERE id='. $getId['id']);
			mysql_query('UPDATE `mkprofiles` SET nick_color="'. $new .'" WHERE id='. $getId['id']);
			mysql_query('DELETE FROM `mknewnicks` WHERE oldnick="'. $old .'"');
			mysql_query('INSERT INTO `mknewnicks` VALUES("'. $old .'",'. $getId['id'] .',NULL)');
			mysql_query('INSERT INTO `mklogs` VALUES(NULL, '. $id .', "nick '. $getId['id'] .' '. $new .'")');
			$success = true;
		}
	}
	else
		$message = $language ? 'This player does not exist':'Ce membre n\'existe pas';
}
else
	mysql_query('DELETE FROM `mknewnicks` WHERE date<DATE_SUB(NOW(), INTERVAL 1 MONTH)');
?>
<main>
	<h1><?php echo $language ? 'Edit nick':'Modification de pseudo'; ?></h1>
	<?php
	if ($success)
		echo '<p><strong>'. $old .'</strong> vient d\'être renommé en <strong>'. $new .'</strong>.</p>';
	if ($message)
		echo '<p style="color: red">'. $message .'</p>';
	?>
	<p>
		Cette page permet de modifier le pseudo d'un membre en particulier.
	</p>
	<form method="post" action="edit-pseudo.php">
	<blockquote>
		<p><label for="joueur"><strong><?php echo $language ? 'Last nick':'Ancien pseudo'; ?></strong></label> : <input type="text" name="joueur" id="joueur" value="<?php if (isset($old)) echo htmlspecialchars($old); ?>" required="required" /></p>
		<p><label for="newpseudo"><strong><?php echo $language ? 'New nick':'Nouveau pseudo'; ?></strong></label> : <input type="text" name="newpseudo" id="newpseudo" value="<?php if (isset($new)) echo htmlspecialchars($new); ?>" required="required" /></p>
		<p><input type="submit" value="Valider" class="action_button" /></p>
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