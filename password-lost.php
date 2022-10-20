<?php
include('language.php');
include('initdb.php');
$howEmail = false;
$showMissNick = false;
$showEmail = false;
if (isset($_GET['pseudo'])) {
	$showEmail = true;
	$pseudo = $_GET['pseudo'];
	if ($getId = mysql_fetch_array(mysql_query('SELECT id FROM mkjoueurs WHERE nom="'. $pseudo .'"'))) {
		if ($getEmail = mysql_fetch_array(mysql_query('SELECT email FROM mkprofiles WHERE id="'. $getId['id'] .'" AND email!=""'))) {
			$success = 'mail_sent';
			$email = $getEmail['email'];
			do {
				$code = bin2hex(openssl_random_pseudo_bytes(16));
			} while (mysql_numrows(mysql_query('SELECT * FROM mkpassrecovery WHERE token="'. $code .'"')));
			mysql_query('INSERT INTO `mkpassrecovery` VALUES("'. $code .'",'.$getId['id'].',DATE_ADD(CURRENT_TIMESTAMP, INTERVAL 1 DAY))');

			$link = 'http' . (isset($_SERVER['HTTPS']) ? 's' : '') . '://' . $_SERVER['HTTP_HOST']. '/new-password.php?code='. $code;
			$title = $language?'MKPC - Forgot password':'MKPC - mot de passe oublié';
			$msg = $language ? 'Hello '.$pseudo.'
You are receiving this email because you have applied for password recovery on the Mario Kart PC site.

Here is a link that will allow you to generate a new password:
<a href="'.$link.'">'. $link .'</a>

See you soon on Mario Kart PC :)':'Bonjour '.$pseudo.'
Vous recevez cet email car vous avez fait une demande de récupération de mot de passe sur le site Mario Kart PC.

Voici un lien qui va vous permettre de générer un nouveau mot de passe:
<a href="'.$link.'">'. $link .'</a>

À bientôt sur Mario Kart PC :)';

			$msgTxt = strip_tags($msg);

			curl_init();

			$url = 'https://api.mailjet.com/v3.1/send';
			// Create a new cURL resource
			$ch = curl_init($url);

			include('config/mail.php');
			curl_setopt($ch, CURLOPT_HTTPAUTH, CURLAUTH_BASIC);
			curl_setopt($ch, CURLOPT_USERPWD, "$mailUser:$mailPwd");

			// Setup request to send json via POST
			$data = array(
				"Messages" => array(
					array(
						"From" => array(
						  "Email" => "mkpc@malahieude.net",
						  "Name" => "MKPC"
						),
						"To" => array(
							array(
								"Email" => $email,
								"Name" => $pseudo
							)
						),
						"Subject" => $title,
						"TextPart" => $msgTxt,
						"HTMLPart" => nl2br($msg)
					)
				)
			);
			$payload = json_encode($data);

			// Attach encoded JSON string to the POST fields
			curl_setopt($ch, CURLOPT_POSTFIELDS, $payload);

			// Set the content type to application/json
			curl_setopt($ch, CURLOPT_HTTPHEADER, array(
				'Content-Type:application/json'
			));

			// Return response instead of outputting
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

			// Execute the POST request
			$result = curl_exec($ch);

			// Close cURL resource
			curl_close($ch);
		}
		else
			$error = $language ? 'Sorry, this account doesn\'t have any associated email':'Désolé, L\'adresse email n\'est pas renseignée sur ce compte';
	}
	else
		$error = $language ? 'Sorry, this nickname does not exist':'Désolé, ce pseudo n\'existe pas';
}
elseif (isset($_GET['email'])) {
	$showEmail = true;
	$showMissNick = true;
	$getNicknames = mysql_query('SELECT nom FROM `mkjoueurs` j INNER JOIN `mkprofiles` p ON j.id=p.id WHERE p.email="'. $_GET['email'] .'"');
	$pseudos = array();
	while ($nickName = mysql_fetch_array($getNicknames))
		$pseudos[] = '<a href="?pseudo='.$nickName['nom'].'">'.$nickName['nom'].'</a>';
	if (empty($pseudos))
		$error = $language ? 'Sorry, this email doesn\'t have any associated account':'Désolé, cette adresse mail n\'a pas de compte associé';
	else
		$success = 'nick_found';
}
?>
<!DOCTYPE html>
<html lang="<?php echo $language ? 'en':'fr'; ?>">
<head>
<title><?php echo $language ? 'Password lost - Mario Kart PC':'Mot de passe perdu - Mario Kart PC'; ?></title>
<?php
include('heads.php');
?>
<link rel="stylesheet" type="text/css" href="styles/forum.css" />
<link rel="stylesheet" type="text/css" href="styles/forms.css" />
<style type="text/css">
#show-email, #show-pseudo, #show-ask {
	display: none;
	margin: 0 10px;
}
h2 {
	margin-bottom: 5px;
}
.big-instructions {
	font-size: 1.1em;
}
.well_input {
	font-size: 1.1em;
	position: relative;
	top: 3px;
}
#success {
	text-align: left;
}
#success strong {
	color: #097;
}
</style>
<script type="text/javascript">
function toggleView(id) {
	var show = !document.getElementById(id).style.display;
	document.getElementById(id).style.display = show?'block':'';
	var showdown = document.getElementById(id+"-down");
	if (showdown)
		showdown.innerHTML = show ? "&#9660;":"&nbsp;&#9656;&nbsp;";
}
</script>

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
	<?php
	if (isset($success)) {
		?>
		<h1><?php echo $language ? 'Password recovery':'Récupération mot de passe'; ?></h1>
		<?php
		function obfuscate_email($email) {
			$em   = explode("@", $email);
			$name = implode('@', array_slice($em, 0, count($em)-1));
			$len  = floor(strlen($name)/2);

			return substr($name,0, $len) . str_repeat('*', $len) . "@" . end($em);   
		}
		switch ($success) {
			case 'mail_sent':
				$emailObfuscated = obfuscate_email($email);
				?>
				<p class="big-instructions" id="success"><?php
				echo $language ? 'An email has been sent to the address':'Un email a été envoyé à l\'adresse';
				?> <strong><?php echo $emailObfuscated; ?></strong>.<br />
				<?php
				echo $language ? 'It contains a link which will allow you to generate a new password.':'Il contient un lien permettant de générer un nouveau mot de passe.';
				?></p>
				<p><?php
				echo $language ? 'If you didn\'t receive the email after a few minutes, you can':'Si vous n\'avez pas reçu l\'email au bout de quelques minutes, vous pouvez';
				?>
				<a href="javascript:location.reload()"><?php echo $language ? 'click here':'cliquer ici'; ?></a>
				<?php
				echo $language ? 'to send another one.':'pour en envoyer un nouveau.';
				?>
				<br />
				<?php
				echo $language ? 'Also check if it is not in your spam.':'Pensez également à regarder si celui-ci ne se trouve pas dans vos courriers indésirables (spam).';
				?></p>
				<?php
				break;
			case 'nick_found':
				?>
				<p class="big-instructions" id="success">
					<?php
					if (count($pseudos) > 1)
						echo $language ? 'The following nicks have been found: ':'Les pseudos suivants ont été trouvés : ';
					else
						echo $language ? 'The following nick has been found: ':'Le pseudo suivant a été trouvé : ';
					echo implode(', ', $pseudos);
					?>
				</p>
				<?php
				break;
		}
	}
	else {
		?>
	<h1><?php echo $language ? 'Password lost':'Mot de passe perdu'; ?></h1>
	<?php
	if (isset($error))
		echo '<p id="echec">'.$error.'</p>';
	?>
	<p>
		<div class="big-instructions"><?php
			echo $language ? 'You lost your password? There are 2 possibilities to recover it:':'Vous avez perdu votre mot de passe ? Deux possibilités pour le retrouver :';
		?></div>
		<h2><span class="show-down" id="show-email-down"><?php echo $showEmail?'&#9660;':'&nbsp;&#9656;&nbsp;'; ?></span><a href="javascript:toggleView('show-email')"><?php
			echo $language ? 'I had entered my email address on my account':'J\'ai indiqué mon adresse email sur mon compte';
		?></a></h2>
		<div class="big-instructions" id="show-email"<?php echo $showEmail?' style="display:block"':''; ?>>
			<?php
			echo $language ? 'In that case it\'s easy, you just have to enter your nick here:':'Dans ce cas c\'est facile, il vous suffit d\'entrer votre pseudo ici :';
			?>
			<form method="get" action="password-lost.php">
				<input type="text" name="pseudo" size="10" class="well_input" placeholder="<?php echo $language ? 'Nick':'Pseudo'; ?>"<?php echo isset($_GET['pseudo']) ? ' value="'. htmlspecialchars($_GET['pseudo']) .'"':''; ?> />
				<input type="submit" class="action_button" value="Ok" />
			</form>
			<?php
			echo $language ? 'An email will be sent to the address associated to the nick.':'Un email sera alors envoyé à l\'adresse associée à ce pseudo.';
			?><br />
			<a href="javascript:toggleView('show-pseudo')"><?php
			echo $language ? 'Don\'t remember your nick?':'Vous ne vous rappelez plus de votre pseudo ?'; ?></a>
			<div id="show-pseudo"<?php echo $showMissNick?' style="display:block"':''; ?>>
				<?php
				echo $language ? 'No problem, enter your email address, we\'ll find out your nick:':'Pas de problème, indiquez votre adresse mail, nous retrouverons votre pseudo :';
				?>
				<form method="get">
					<input type="email" name="email" class="well_input" placeholder="xxx.yyy@zzz.com"<?php echo isset($_GET['email']) ? ' value="'. htmlspecialchars($_GET['email']) .'"':''; ?> />
					<input type="submit" class="action_button" value="Ok" />
				</form>
			</div>
		</div>
		<h2><span class="show-down" id="show-ask-down">&nbsp;&#9656;&nbsp;</span><a href="javascript:toggleView('show-ask')"><?php
		echo $language ? 'I didn\'t indicate my email...':'Je n\'ai pas indiqué mon email...';
		?></a></h2>
		<div class="big-instructions" id="show-ask">
			<?php
			echo $language ? 'Too bad... In that case, the easiest solution is to create a temporary account and contact':'Pas de chance... Dans ce cas le plus simple est de créer un nouveau compte temporaire et de contacter';
			?>
			<a href="profil.php?id=1">Wargor</a>, <?php
			echo $language ? 'the creator of the site':'le créateur du site';
			?>.
			<?php
			echo $language ? 'He will do his best to give you back your account, promised!':'Il fera son possible pour vous redonner votre compte, promis !';
			?>
		</div>
	</p>
		<?php
	}
	?>
	<?php
	if (!isset($success) || ($success != 'nick_found')) {
		?>
		<p><a href="forum.php"><?php echo $language ? 'Back to the forum':'Retour au forum'; ?></a></p>
		<?php
	}
	else {
		?>
		<p><a href="password-lost.php"><?php echo $language ? 'Cancel':'Annuler'; ?></a></p>
		<?php
	}
	?>
</main>
<?php
include('footer.php');
mysql_close();
?>
</body>
</html>
