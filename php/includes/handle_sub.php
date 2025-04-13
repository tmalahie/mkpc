<?php
include('getId.php');
include('language.php');
include('session.php');
$inscrit = false;
include('initdb.php');

if (isset($_POST['pseudo'], $_POST['code'], $_POST['confirm'], $_POST['email'], $_POST['country'])) {
	$username = $_POST['pseudo'];
	$password = $_POST['code'];
	$password2 = $_POST['confirm'];
	$email = $_POST['email'];
	$country = $_POST['country'];
	
	include('utils-cooldown.php');
	if (!$username)
		$message =_('Please enter a username');
	elseif (!$password)
		$message = _('Please choose a password');
	elseif ($password != $password2)
		$message = _('You made a mistake re-entering your password');
	elseif (strlen($password) < 8)
		$message = _('Your password must be at least 8 characters long.');
	elseif (!preg_match('#^[a-zA-Z0-9_\-]+$#', $username))
		$message = _("You username mustn't contain special chars.<br />Allowed chars are : letters, numbers, the dash - and the underscore _");
	elseif ($email && !preg_match("#^[a-z0-9._-]+@[a-z0-9._-]{2,}\.[a-z]{2,4}$#i", $email))
		$message = _('Please enter a valid email address');
	elseif ($getData=mysql_fetch_array(mysql_query('SELECT deleted FROM `mkjoueurs` WHERE nom="'.$username.'"'))) {
		if ($getData['deleted'])
			$message = _('This account is already used and deleted. If you want to recover it, simply relog-in.');
		else
			$message = _('This username is already used. Please choose another one.');
	}
	elseif (mysql_numrows(mysql_query('SELECT * FROM `ip_bans` WHERE ip1="'. $identifiants[0] .'" AND ip2="'. $identifiants[1] .'" AND ip3="'. $identifiants[2] .'" AND ip4="'. $identifiants[3] .'"')))
		 $message = _('You have been banned.');
	elseif (isAccountCooldowned()) {
		logCooldownEvent('profile');
		$message = _('Too many accounts created recently, please try again later.');
	}
	else {
		if ($getCountryId = mysql_fetch_array(mysql_query('SELECT id FROM mkcountries WHERE code="'. $country .'"')))
			$countryId = $getCountryId['id'];
		else
			$countryId = 0;
		mysql_query('INSERT INTO `mkjoueurs` VALUES (null, 0, "'.$username.'", "'.password_hash($password,PASSWORD_DEFAULT).'", 0, 0, 0, 5000, 5000, 0, 2, 0,0)');
		$id = mysql_insert_id();
		if ($id) {
			$_SESSION['mkid'] = $id;
			require_once('credentials.php');
			setcookie('mkp', credentials_encrypt($id,$_POST['code']), 4294967295,'/');
			$date = date('Y-m-d H:i:s');
			mysql_query('INSERT INTO `mkprofiles` VALUES('. $id .', NULL,0,0,0,"","'.$_POST['pseudo'].'",0,"'.$email.'",'.$countryId.',NULL,"'.$date.'",NULL,"")');
			include('setId.php');
			mysql_query('UPDATE `mkrecords` SET player='.$id.' WHERE identifiant='.$identifiants[0].' AND identifiant2='.$identifiants[1].' AND identifiant3='.$identifiants[2].' AND identifiant4='.$identifiants[3].' AND (player=0 OR name="'.$username.'")');
			mysql_query('UPDATE `mkgametime` SET player='.$id.' WHERE identifiant='.$identifiants[0].' AND player=0');
			mysql_query('UPDATE `mkgametimehist` SET player='.$id.' WHERE identifiant='.$identifiants[0].' AND player=0');
			$inscrit = true;
		}
		else
			$message = _('Sorry, an unknown error occured, please try again later.');
	}
}
?>