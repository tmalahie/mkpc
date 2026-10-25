<?php
include('../includes/language.php');
include('../includes/initdb.php');
function send_recovery_mail($email, $name, $title, $msg) {	$msgTxt = strip_tags($msg);

	$url = 'https://api.mailjet.com/v3.1/send';
	// Create a new cURL resource
	$ch = curl_init($url);

	include('../includes/config/mail.php');
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
						"Name" => $name
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
	$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);

	// Close cURL resource
	curl_close($ch);

	if ($httpCode < 200 || $httpCode >= 300)
		error_log('Mailjet send failed (HTTP '.$httpCode.'): '.$result);
	else {
		// Mailjet stores every recipient as a contact, and the free plan stops sending past 1000 contacts
		$ch = curl_init('https://api.mailjet.com/v3/REST/contact/'.rawurlencode($email));
		curl_setopt($ch, CURLOPT_USERPWD, "$mailUser:$mailPwd");
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		$contact = json_decode(curl_exec($ch), true);
		curl_close($ch);
		if (isset($contact['Data'][0]['ID'])) {
			$ch = curl_init('https://api.mailjet.com/v4/contacts/'.$contact['Data'][0]['ID']);
			curl_setopt($ch, CURLOPT_USERPWD, "$mailUser:$mailPwd");
			curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'DELETE');
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
			curl_exec($ch);
			curl_close($ch);
		}
	}
}
function recovery_mail_title() {
	global $language;
	return $language?'MKPC - Forgot password':'MKPC - mot de passe oublié';
}
function single_account_recovery_msg($pseudo, $link) {
	global $language;
	return $language ? 'Hello '.htmlspecialchars($pseudo).',
You are receiving this email because a password reset was requested for your account on the Mario Kart PC site.

If you did not make this request, you can safely ignore this email — no changes have been made to your account.

To reset your password, please click the link below:
<a href="'.$link.'">'.$link.'</a>

⚠️ <strong>This link is personal and confidential. Do not share it with anyone under any circumstances.</strong>

See you soon on Mario Kart PC :)' : 'Bonjour '.htmlspecialchars($pseudo).',
Vous recevez cet e-mail parce qu’une demande de réinitialisation de mot de passe a été effectuée pour votre compte sur le site Mario Kart PC.

Si vous n’êtes pas à l’origine de cette demande, vous pouvez ignorer cet e-mail en toute sécurité. Aucun changement ne sera effectué sur votre compte.

Pour réinitialiser votre mot de passe, veuillez cliquer sur le lien ci-dessous :
<a href="'.$link.'">'.$link.'</a>

⚠️ <strong>Ce lien est personnel et confidentiel. Ne le partagez sous aucun prétexte.</strong>

À bientôt sur Mario Kart PC :)';
}
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
			include('../includes/getId.php');
			include('../includes/utils-cooldown.php');
			if (isPassRecoveryCooldowned())
				logCooldownEvent('pass_recovery');
			else {
				require_once('../includes/reset-password.php');
				$link = generatePasswordLink($getId['id']);
				send_recovery_mail($email, $pseudo, recovery_mail_title(), single_account_recovery_msg($pseudo, $link));
			}
		}
		else
			$error = $language ? 'Sorry, this account doesn\'t have any associated email':'Désolé, L\'adresse email n\'est pas renseignée sur ce compte';
	}
	else
		$error = $language ? 'Sorry, this username does not exist':'Désolé, ce pseudo n\'existe pas';
}
elseif (isset($_GET['email'])) {
	$showEmail = true;
	$showMissNick = true;
	$success = 'nick_mail_sent';
	include('../includes/getId.php');
	$getNicknames = mysql_query('SELECT j.id,j.nom,p.email FROM `mkjoueurs` j INNER JOIN `mkprofiles` p ON j.id=p.id WHERE p.email="'. $_GET['email'] .'" AND p.email!=""');
	$accounts = array();
	while ($nickName = mysql_fetch_array($getNicknames))
		$accounts[] = $nickName;
	if (!empty($accounts)) {
		include('../includes/utils-cooldown.php');
		if (isPassRecoveryCooldowned())
			logCooldownEvent('pass_recovery');
		else {
			require_once('../includes/reset-password.php');
			if (count($accounts) === 1) {
				$link = generatePasswordLink($accounts[0]['id']);
				$msg = single_account_recovery_msg($accounts[0]['nom'], $link);
			}
			else {
				$accountLines = array();
				foreach ($accounts as $account) {
					$link = generatePasswordLink($account['id']);
					$accountLines[] = '- <strong>'. htmlspecialchars($account['nom']) .'</strong>'. ($language ? ': ':' : ') .'<a href="'.$link.'">'.$link.'</a>';
				}
				$accountList = implode("\n", $accountLines);
				$msg = $language ? 'Hello,
You are receiving this email because a password reset was requested for the Mario Kart PC accounts linked to this address.

If you did not make this request, you can safely ignore this email — no changes have been made to your accounts.

To reset a password, please click the link next to the account:
'. $accountList .'

⚠️ <strong>These links are personal and confidential. Do not share them with anyone under any circumstances.</strong>

See you soon on Mario Kart PC :)' : 'Bonjour,
Vous recevez cet e-mail parce qu’une demande de réinitialisation de mot de passe a été effectuée pour les comptes Mario Kart PC associés à cette adresse.

Si vous n’êtes pas à l’origine de cette demande, vous pouvez ignorer cet e-mail en toute sécurité. Aucun changement ne sera effectué sur vos comptes.

Pour réinitialiser un mot de passe, veuillez cliquer sur le lien à côté du compte :
'. $accountList .'

⚠️ <strong>Ces liens sont personnels et confidentiels. Ne les partagez sous aucun prétexte.</strong>

À bientôt sur Mario Kart PC :)';
			}
			send_recovery_mail($accounts[0]['email'], $accounts[0]['nom'], recovery_mail_title(), $msg);
		}
	}
}
?>
<!DOCTYPE html>
<html lang="<?php echo $language ? 'en':'fr'; ?>">
<head>
<title><?php echo $language ? 'Password lost - Mario Kart PC':'Mot de passe perdu - Mario Kart PC'; ?></title>
<?php
include('../includes/heads.php');
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
	<?php
	if (isset($success)) {
		?>
		<h1><?php echo $language ? 'Password recovery':'Récupération mot de passe'; ?></h1>
		<?php
		function obfuscate_email($email) {
			$em   = explode("@", $email);
			$name = implode('@', array_slice($em, 0, count($em)-1));
			$len  = min(2,floor(strlen($name)/2));

			return substr($name,0, $len) . str_repeat('*', 5) . "@" . end($em);   
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
			case 'nick_mail_sent':
				?>
				<p class="big-instructions" id="success"><?php
				echo $language ? 'We have sent you an email to reset your password.<br />You should receive it in the next few minutes.':'Un email vous a été envoyé pour réinitialiser votre mot de passe.<br />Vous devriez le recevoir dans les prochaines minutes.';
				?></p>
				<p><?php
				echo $language ? 'Didn\'t receive anything?':'Vous n\'avez rien reçu ?';
				?></p>
				<ul>
					<li><?php
					echo $language ? 'Check that your email address is correct:':'Vérifiez que votre adresse email est correcte :';
					?> <strong><?php echo htmlspecialchars(stripslashes($_GET['email'])); ?></strong> (<a href="password-lost.php"><?php echo $language ? 'change':'modifier'; ?></a>)</li>
					<li><?php
					echo $language ? 'Check your spam folder.':'Vérifiez vos courriers indésirables (spam).';
					?></li>
				</ul>
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
			echo $language ? 'In that case it\'s easy, you just have to enter your username here:':'Dans ce cas c\'est facile, il vous suffit d\'entrer votre pseudo ici :';
			?>
			<form method="get" action="password-lost.php">
				<input type="text" name="pseudo" size="10" class="well_input" placeholder="<?php echo $language ? 'Username':'Pseudo'; ?>"<?php echo isset($_GET['pseudo']) ? ' value="'. htmlspecialchars($_GET['pseudo']) .'"':''; ?> />
				<input type="submit" class="action_button" value="Ok" />
			</form>
			<?php
			echo $language ? 'An email will be sent to the address associated to the username.':'Un email sera alors envoyé à l\'adresse associée à ce pseudo.';
			?><br />
			<a href="javascript:toggleView('show-pseudo')"><?php
			echo $language ? 'Don\'t remember your username?':'Vous ne vous rappelez plus de votre pseudo ?'; ?></a>
			<div id="show-pseudo"<?php echo $showMissNick?' style="display:block"':''; ?>>
				<?php
				echo $language ? 'No problem, enter your email address, we\'ll find out your username:':'Pas de problème, indiquez votre adresse mail, nous retrouverons votre pseudo :';
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
			echo $language ? 'Too bad... In that case, the easiest solution is to contact a moderator of the site, either through <a href="https://discord.gg/VkeAxaj">Discord</a> or through MKPC by creating a temporary account.':'Pas de chance... Dans ce cas le plus simple est de contacter un modérateur du site, via <a href="https://discord.gg/VkeAxaj">Discord</a> ou via MKPC en créant un compte temporaire.';
			echo $language ? ' We will do our best to recover your account!':' Nous ferons notre possible pour récupérer votre compte !';
			?>
		</div>
	</p>
		<?php
	}
	?>
	<?php
	?>
	<p><a href="forum.php"><?php echo $language ? 'Back to the forum':'Retour au forum'; ?></a></p>
</main>
<?php
include('../includes/footer.php');
mysql_close();
?>
</body>
</html>
