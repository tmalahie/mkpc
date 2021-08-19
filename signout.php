<?php
include('language.php');
include('session.php');
include('initdb.php');
if ($id) {
	if (isset($_POST['code'])) {
		if (($getCode=mysql_fetch_array(mysql_query('SELECT code,banned FROM `mkjoueurs` WHERE id="'.$id.'"'))) && password_verify($_POST['code'],$getCode['code'])) {
			if (!$getCode['banned']) {
				mysql_query('UPDATE `mkjoueurs` SET deleted=1 WHERE id="'.$id.'"');
				session_destroy();
				$suppred = true;
				setcookie('mkp', null, 0,'/');
			}
			else
				$message = $language ? 'You have been banned, you cannot delete your account.':'Vous avez été banni, vous ne pouvez pas supprimer votre compte.';
		}
		else
			$message = $language ? 'The password you entered is wrong.':'Le mot de passe que vous avez entr&eacute; est incorrect.';
	}
}
?>
<!DOCTYPE html>
<html lang="<?php echo $language ? 'en':'fr'; ?>">
<head>
<title><?php echo $language ? 'Delete account Mario Kart PC':'Supprimer compte Mario Kart PC'; ?></title>
<?php
include('heads.php');
?>
<link rel="stylesheet" type="text/css" href="styles/forum.css" />
<link rel="stylesheet" type="text/css" href="styles/forms.css" />

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
<h1><?php echo $language ? 'Delete account':'Supprimer compte'; ?></h1>
<?php
if ($id) {
	if (isset($suppred)) {
		?>
		<p id="success"><?php echo $language ? 'Your account have been removed successfully':'Votre compte a &eacute;t&eacute; supprim&eacute;'; ?>.</p>
		<p class="forumButtons"><a href="index.php"><?php echo $language ? 'Back to Mario Kart PC':'Retour &agrave; Mario Kart PC'; ?></a></p>
		<?php
	}
	else {
		if (isset($message))
			echo '<p id="echec">'.$message.'</p>';
		else
			echo $language ?
			'<p>We are sorry to see you leave the site.<br />
			If you change your mind, you can still restore your account by reconnecting again with your credentials.<br />
			To confirm your choice, enter your password in the field below, which will trigger the deletion of the account:' :
			'<p>Nous sommes désolés de vous voir quitter le site.<br />
			Si vous changez d\'avis, vous pourrez toujours restaurer votre compte en vous reconnectant à nouveau avec vos identifiants.<br />
			Pour confirmer votre choix, entrez votre mot de passe dans le champ ci-dessous, ce qui déclenchera la suppression du compte :</p>';
		?>
<form method="post" action="signout.php">
<table>
<tr><td><input type="password" name="code" /></td><td><input type="submit" value="<?php echo $language ? 'Submit':'Valider'; ?>" class="action_button" /></td></tr>
</table>
</form>
<p class="forumButtons"><a href="forum.php"><?php echo $language ? 'Back to the forum':'Retour au forum'; ?></a></p>
		<?php
	}
}
else
	include('needCo.php');
?>
</main>
<?php
include('footer.php');
mysql_close();
?>
</body>
</html>