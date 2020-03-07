<?php
include('language.php');
if (isset($_GET['code'])) {
	include('initdb.php');
	$code = $_GET['code'];
	mysql_query('DELETE FROM mkpassrecovery WHERE expiry_date<CURRENT_TIMESTAMP');
	if ($getPlayer = mysql_fetch_array(mysql_query('SELECT player FROM `mkpassrecovery` WHERE token="'. $code .'"'))) {
		$player = $getPlayer['player'];
		if ($getPseudo = mysql_fetch_array(mysql_query('SELECT nom FROM `mkjoueurs` WHERE id='. $player)))
			$pseudo = $getPseudo['nom'];
		else
			$pseudo = '';
		if (isset($_POST['nouveau']) && isset($_POST['confirm'])) {
			$nouveau = $_POST['nouveau'];
			$confirm = $_POST['confirm'];
			if (!$nouveau)
				$message = $language ? 'Please enter your new password':'Veuillez entrer votre nouveau mot de passe';
			elseif ($nouveau != $confirm)
				$message = $language ? 'You made a mistake by re-entering your password':'Vous avez fait une erreur en retapant votre nouveau mot de passe';
			else {
				$modifie = true;
				mysql_query('UPDATE `mkjoueurs` SET code="'. password_hash($nouveau,PASSWORD_DEFAULT) .'",deleted=0 WHERE id="'. $player .'"');
				mysql_query('DELETE FROM mkpassrecovery WHERE token="'. $code .'"');
				if ($pseudo) {
					session_start();
					$_SESSION['mkid'] = $player;
					$id = $player;
					require_once('credentials.php');
					setcookie('mkp', credentials_encrypt($id,$nouveau), 4294967295,'/');
					include('setId.php');
				}
			}
		}
		?>
<!DOCTYPE html>
<html lang="<?php echo $language ? 'en':'fr'; ?>">
<head>
<title><?php echo $language ? 'Password recovery - Mario Kart PC':'Récupération mot de passe - Mario Kart PC'; ?></title>
<?php
include('heads.php');
?>
<link rel="stylesheet" type="text/css" href="styles/forum.css" />
<link rel="stylesheet" type="text/css" href="styles/forms.css" />
<style type="text/css">
#welcome-again {
	max-width: 500px;
	margin-left: auto;
	margin-right: auto;
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
?>
<main>
	<h1><?php echo $language ? 'Password recovery':'Récupération mot de passe'; ?></h1>
	<?php
	if (isset($modifie))
		echo $language ? '<p id="success">Your password has been modified successfully.</p>':'<p id="success">Votre mot de passe a &eacute;t&eacute; modifi&eacute; avec succ&egrave;s.</p>';
	else {
		if (isset($message))
			echo '<p id="echec">'.$message.'</p>';
		if ($pseudo) {
			?>
			<div id="welcome-again">
				<?php
				if ($language) {
					?>
					<p>Hello again, <?php echo $pseudo; ?>!</p>
					<p>On this page, you will be able to choose a new password,
					which will give you access to your account again.</p>
					<p>Welcome back :)</p>
					<?php
				}
				else {
					?>
					<p>Re-bonjour, <?php echo $pseudo; ?> !</p>
					<p>Sur cette page, vous allez pouvoir choisir un nouveau mot de passe,
						ce qui vous donnera de nouveau accès à votre compte.</p>
					<p>Bon retour parmi nous :)</p>
					<?php
				}
				?>
			</div>
			<?php
		}
		?>
		<form method="post" action="new-password.php?code=<?php echo $code; ?>">
		<table>
		<tr><td class="ligne"><label for="nouveau"><?php echo $language ? 'New password':'Nouveau mot de passe'; ?> :</label></td><td><input type="password" name="nouveau" id="nouveau" value="<?php echo $nouveau ?>" maxlength="30" /></td></tr>
		<tr><td class="ligne"><label for="confirm"><?php echo $language ? 'Re-enter the password':'Retapez le mot de passe'; ?> :</label></td><td><input type="password" name="confirm" id="confirm" value="<?php echo $confirm ?>" maxlength="30" /></td></tr>
		<tr><td colspan="2"><input type="submit" value="<?php echo $language ? 'Submit':'Valider'; ?>" class="action_button" /></td></tr>
		</table>
		</form>
		<?php
	}
	?>
	<p class="forumButtons"><a href="forum.php"><?php echo $language ? 'Back to the forum':'Retour au forum'; ?></a></p>
</main>
<?php
include('footer.php');
mysql_close();
?>
</body>
</html>
		<?php
	}
	else
		echo 'Code is invalid or has expired';
}
?>