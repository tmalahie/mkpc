<?php
include('../includes/language.php');
include('../includes/session.php');
include('../includes/initdb.php');
if ($id) {
	if (isset($_POST['ancien']) && isset($_POST['nouveau']) && isset($_POST['confirm'])) {
		$ancien = $_POST['ancien'];
		$nouveau = $_POST['nouveau'];
		$confirm = $_POST['confirm'];
		if (!$ancien)
			$message = $language ? 'Please enter your old password':'Veuillez entrer un ancien mot de passe';
		elseif (!$nouveau)
			$message = $language ? 'Please enter your new password':'Veuillez entrer votre nouveau mot de passe';
		elseif (!($getCode=mysql_fetch_array(mysql_query('SELECT code FROM `mkjoueurs` WHERE id="'.$id.'"'))) || !password_verify($ancien,$getCode['code']))
			$message = $language ? 'The old password that you entered is wrong':'L\'ancien mot de passe que vous avez entr&eacute; est incorrect';
		elseif ($nouveau != $confirm)
			$message = $language ? 'You made a mistake re-entering your password':'Vous avez fait une erreur en retapant votre nouveau mot de passe';
		else {
			mysql_query('UPDATE `mkjoueurs` SET code="'. password_hash($nouveau,PASSWORD_DEFAULT) .'" WHERE id="'. $id .'"');
			$modifie = true;
			require_once('../includes/credentials.php');
			setcookie('mkp', credentials_encrypt($id,$nouveau), 4294967295,'/');
		}
	}
}
?>
<!DOCTYPE html>
<html lang="<?php echo $language ? 'en':'fr'; ?>">
<head>
<title><?php echo $language ? 'Change password Mario Kart PC':'Modifier mot de passe Mario Kart PC'; ?></title>
<?php
include('../includes/heads.php');
?>
<link rel="stylesheet" type="text/css" href="styles/forum.css" />
<link rel="stylesheet" type="text/css" href="styles/forms.css" />

<?php
include('../includes/o_online.php');
?>
</head>
<body>
<?php
include('../includes/header.php');
$page = 'forum';
include('../includes/menu.php');
?>
<main>
	<h1><?php echo $language ? 'Change password':'Modifier mot de passe'; ?></h1>
	<?php
	if ($id) {
		if (isset($modifie))
			echo $language ? '<p id="success">Your password has been modified successfully.</p>':'<p id="success">Votre mot de passe a &eacute;t&eacute; modifi&eacute; avec succ&egrave;s.</p>';
		else {
			if (isset($message))
				echo '<p id="echec">'.$message.'</p>';
			?>
	<form method="post" action="password.php">
	<table>
	<tr><td class="ligne"><label for="ancien"><?php echo $language ? 'Old password':'Ancien mot de passe'; ?> :</label></td><td><input type="password" name="ancien" id="ancien"<?php if (isset($ancien)) echo ' value="'.htmlspecialchars($ancien).'"'; ?> maxlength="30" /></td></tr>
	<tr><td class="ligne"><label for="nouveau"><?php echo $language ? 'New password':'Nouveau mot de passe'; ?> :</label></td><td><input type="password" name="nouveau" id="nouveau"<?php if (isset($nouveau)) echo ' value="'.htmlspecialchars($nouveau).'"'; ?> maxlength="30" /></td></tr>
	<tr><td class="ligne"><label for="confirm"><?php echo $language ? 'Re-enter the password':'Retapez le mot de passe'; ?> :</label></td><td><input type="password" name="confirm" id="confirm"<?php if (isset($confirm)) echo ' value="'.htmlspecialchars($confirm).'"'; ?> maxlength="30" /></td></tr>
	<tr><td colspan="2"><input type="submit" value="<?php echo $language ? 'Submit':'Valider'; ?>" class="action_button" /></td></tr>
	</table>
	</form>
			<?php
		}
	}
	else
		include('../includes/needCo.php');
	?>
	<p class="forumButtons"><a href="forum.php"><?php echo $language ? 'Back to the forum':'Retour au forum'; ?></a></p>
</main>
<?php
include('../includes/footer.php');
mysql_close();
?>
</body>
</html>