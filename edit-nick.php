<?php
include('session.php');
if (!$id) {
	echo "Vous n'&ecirc;tes pas connect&eacute;";
	exit;
}
include('language.php');
include('initdb.php');
?>
<!DOCTYPE html>
<html lang="<?php echo $language ? 'en':'fr'; ?>">
<head>
<title>Mario Kart PC</title>
<?php
include('heads.php');
?>
<link rel="stylesheet" type="text/css" href="styles/forum.css" />
<link rel="stylesheet" type="text/css" href="styles/classement.css" />
<link rel="stylesheet" type="text/css" href="styles/forms.css" />
<style type="text/css">
.warning-msg {
    font-size: 0.8em;
    max-width: 550px;
    margin: 0.2em auto;
}
.success {
    font-weight: normal;
	margin-bottom: 10px;
}
#newpseudo {
    font-size: 1em;
    width: 10em;
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
$getPseudo = mysql_fetch_array(mysql_query('SELECT nom,banned FROM `mkjoueurs` WHERE id="'. $id .'"'));
$old = $getPseudo['nom'];
if (isset($_POST['newpseudo'])) {
    $new = $_POST['newpseudo'];
	if (!$getPseudo['banned']) {
        include('utils-nicks.php');
        $success = editNick($id,$getPseudo['nom'],$new,$message);
	}
	else
        $message = $language ? 'You have been banned, you cannot edit your nick.':'Vous avez été banni, vous ne pouvez pas modifier votre pseudo.';
}
else
	mysql_query('DELETE FROM `mknewnicks` WHERE date<DATE_SUB(NOW(), INTERVAL 1 MONTH)');
?>
<main>
	<h1><?php echo $language ? 'Edit nick':'Modification de pseudo'; ?></h1>
	<?php
	if ($success)
		echo $language ? '<p class="success">You have been renamed into <strong>'. $new .'</strong>.</p>' : '<p class="success">Vous avez été renommé en <strong>'. $new .'</strong>.</p>';
	if ($message)
		echo '<p style="color: red">'. $message .'</p>';
    if ($language) {
        ?>
        This page allows you to change your current nick.<br />
        Simply enter your new nick in the field below:
        <?php
    }
    else {
        ?>
        Cette page vous permet de changer votre pseudo actuel.<br />
        Entrez simplement votre nouveau pseudo dans le champ ci-dessous :
        <?php
    }
    ?>
	<form method="post" action="edit-nick.php">
        <p>
            <input type="text" name="newpseudo" id="newpseudo" value="<?php if (isset($new)) echo htmlspecialchars($new); else echo $old; ?>" maxlength="30" required="required" />
            <input type="submit" value="Ok" class="action_button" />
        </p>
    </form>
    <?php
    if ($language) {
        ?>
        <div class="warning-msg">N.B: Please don't abuse of this function (like for impersonating or trolling purposes)<br />
        or you will be sanctionned by the moderation team.</div>
        <?php
    }
    else {
        ?>
        <div class="warning-msg">Note: merci de ne pas abuser de cette fonction (usurpation d'identité, troll...)
            ou vous serez sanctionné par l'équipe de modération.</div>
        <?php
    }
    ?>
    <p class="forumButtons">
        <a href="profil.php?id=<?php echo $id; ?>"><?php echo $language ? 'Back to your profile':'Retour à votre profil'; ?></a><br />
        <a href="forum.php"><?php echo $language ? 'Back to the forum':'Retour au forum'; ?></a>
    </p>
</main>
<?php
include('footer.php');
mysql_close();
?>
</body>
</html>