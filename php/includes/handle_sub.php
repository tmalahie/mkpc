<?php
include('getId.php');
include('language.php');
include('session.php');
$inscrit = false;
include('initdb.php');
if (isset($_POST['pseudo']) && isset($_POST['code']) && isset($_POST['confirm']) && isset($_POST['email']) && isset($_POST['country'])) {
	$pseudo = $_POST['pseudo'];
	$code = $_POST['code'];
	$confirm = $_POST['confirm'];
	$email = $_POST['email'];
	$country = $_POST['country'];
	include('utils-cooldown.php');
	if (!$pseudo)
		$message = $language ? 'Please enter a username':'Veuillez entrer un pseudo';
	elseif (!$code)
		$message = $language ? 'Please choose a password':'Veuillez choisir un mot de passe';
	elseif ($code != $confirm)
		$message = $language ? 'You made a mistake re-entering your password':'Vous avez fait une erreur en retapant votre mot de passe';
	elseif (!preg_match('#^[a-zA-Z0-9_\-]+$#', $pseudo))
		$message = $language ? 'You username mustn\'t contain special chars.<br />Allowed chars are : letters, numbers, the dash - and the underscore _':'Votre pseudo ne doit pas contenir de caract&egrave;res spéciaux.<br />Les caract&egrave;res autoris&eacute;s sont les lettres sans accents, les chiffres, le tiret - et le underscore _';
	elseif ($email && !preg_match("#^[a-z0-9._-]+@[a-z0-9._-]{2,}\.[a-z]{2,4}$#i", $email))
		$message = ($language ? 'Please enter a valid email address':'Veuillez entrer une adresse email valide');
	elseif ($getData=mysql_fetch_array(mysql_query('SELECT deleted FROM `mkjoueurs` WHERE nom="'.$pseudo.'"'))) {
		if ($getData['deleted'])
			$message = $language ? 'This account is already used and deleted. If you want to recover it, simply relog-in.':'Ce pseudo est déjà utilisé par un compte supprimé. S\'il s\'agit du vôtre et que vous souhaitez le récupérer, reconnectez-vous simplement avec vos identifiants.';
		else
			$message = $language ? 'This username is already used. Please choose another one.':'Ce pseudo est d&eacute;ja utilis&eacute;. Veuillez en choisir un autre.';
	}
	elseif (mysql_numrows(mysql_query('SELECT * FROM `ip_bans` WHERE ip1="'. $identifiants[0] .'" AND ip2="'. $identifiants[1] .'" AND ip3="'. $identifiants[2] .'" AND ip4="'. $identifiants[3] .'"')))
		 $message = $language ? 'You have been banned.':'Vous avez &eacute;t&eacute; banni.';
	elseif (isAccountCooldowned()) {
		logCooldownEvent('profile');
		$message = $language ? 'Too many accounts created recently, please try again later.':'Trop de comptes créés récemment, veuillez réessayer ultérieurement.';
	}
	else {
		if ($getCountryId = mysql_fetch_array(mysql_query('SELECT id FROM mkcountries WHERE code="'. $country .'"')))
			$countryId = $getCountryId['id'];
		else
			$countryId = 0;
		mysql_query('INSERT INTO `mkjoueurs` VALUES (null, 0, "'.$pseudo.'", "'.password_hash($code,PASSWORD_DEFAULT).'", 0, 0, 0, 5000, 5000, 0, 2, 0,0)');
		$id = mysql_insert_id();
		if ($id) {
			$_SESSION['mkid'] = $id;
			require_once('credentials.php');
			setcookie('mkp', credentials_encrypt($id,$_POST['code']), 4294967295,'/');
			$date = date('Y-m-d');
			mysql_query('INSERT INTO `mkprofiles` VALUES('. $id .', NULL,0,0,0,"","'.$_POST['pseudo'].'",0,"'.$email.'",'.$countryId.',NULL,"'.$date.'",NULL,"")');
			include('setId.php');
			mysql_query('UPDATE `mkrecords` SET player='.$id.' WHERE identifiant='.$identifiants[0].' AND identifiant2='.$identifiants[1].' AND identifiant3='.$identifiants[2].' AND identifiant4='.$identifiants[3].' AND (player=0 OR name="'.$pseudo.'")');
			mysql_query('UPDATE `mkgametime` SET player='.$id.' WHERE identifiant='.$identifiants[0].' AND player=0');
			mysql_query('UPDATE `mkgametimehist` SET player='.$id.' WHERE identifiant='.$identifiants[0].' AND player=0');
			$inscrit = true;
		}
		else
			$message = $language ? 'Sorry, an unknown error occured, please try again later.':'Désolé, une erreur inconnue est survenue, veuillez réessayer plus tard.';
	}
}
?>