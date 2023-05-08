<?php
include('../includes/handle_sub.php');
$isBattle = isset($_GET['battle']);
if ($inscrit) {
	$message = $language ? 
	'You are now registered ! You start with 5000 points.<br />
	In a race, you will win or lose points depending on your place.<br />
	Win as many races as you can in order to climb the <a href="bestscores.php">ranking</a> ! Good luck !' :
	'Vous &ecirc;tes &agrave; pr&eacute;sent inscrit ! Vous commencez avec 5000 points.<br />
	Lors d\'une course, vous gagnerez ou vous perderez des points en fonction de votre place.<br />
	Remportez un maximum de courses afin de grimper dans le <a href="bestscores.php'. ($isBattle ? '?battle':'') .'">classement</a> ! Bonne chance !';
}
?>
<!DOCTYPE html>
<html lang="<?php echo $language ? 'en':'fr'; ?>">
<head>
<title><?php echo $language ? 'Register Mario Kart PC':'Inscription Mario Kart PC'; ?></title>
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
$page = 'game';
include('../includes/menu.php');
?>
<main>
	<h1><?php echo $language ? 'Register Mario Kart PC':'Inscription Mario Kart PC'; ?></h1>
	<?php
	if (isset($message)) {
		if ($inscrit)
			echo '<p id="success">'. $message .'</p>';
		else
			echo '<p id="echec">'.$message.'</p>';
	}
	if (!$inscrit) {
		?>
		<form method="post" action="inscription.php<?php echo ($isBattle ? '?battle':''); ?>">
		<table class="signup">
		<tr><td class="ligne"><label for="pseudo"><?php echo $language ? 'Choose a nick':'Choisissez un pseudo'; ?> :</label></td><td><input type="text" name="pseudo" id="pseudo" value="<?php if (isset($pseudo)) echo $pseudo ?>" maxlength="30" /></td></tr>
		<tr><td class="ligne"><label for="code"><?php echo $language ? 'Choose a password':'Choisissez un mot de passe'; ?> :</label></td><td><input type="password" name="code" id="code" value="<?php if (isset($code)) echo $code ?>" maxlength="30" /></td></tr>
		<tr><td class="ligne"><label for="confirm"><?php echo $language ? 'Re-enter password':'Retapez le mot de passe'; ?> :</label></td><td><input type="password" name="confirm" id="confirm" value="<?php if (isset($confirm)) echo $confirm ?>" maxlength="30" /></td></tr>
		<tr><td class="ligne"><label for="email"><?php echo $language ? 'Email address <em>(optional)</em>':'Adresse email <em>(facultatif)</em>'; ?> :</label></td><td><input type="email" name="email" id="email" value="<?php if (isset($email)) echo $email ?>" /></td></tr>
		<tr><td class="ligne"><label for="country"><?php echo $language ? 'Country <em>(optional)</em>':'Pays <em>(facultatif)</em>'; ?> :</label></td><td><select name="country" id="country" value="<?php if (isset($country)) echo $country ?>"><?php
		include('../includes/list-countries.php');
		?></td></tr>
		<tr><td colspan="2"><input type="submit" value="<?php echo $language ? 'Submit':'Valider'; ?>" class="action_button" /></td></tr>
		</table>
		</form>
		<?php
	}
	?>
	<p class="forumButtons"><a href="online.php<?php echo ($isBattle ? '?battle':''); ?>"><?php echo $language ? 'Back to online mode':'Retour au mode en ligne'; ?></a></p>
</main>
<?php
include('../includes/footer.php');
if (!isset($_POST['country']))
	echo '<script type="text/javascript" src="scripts/autoselect-country.js"></script>';
mysql_close();
?>
</body>
</html>