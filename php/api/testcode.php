<?php
header('Content-Type: text/plain');
if (isset($_POST['pseudo']) && isset($_POST['code'])) {
	include('../includes/initdb.php');
	$id = 0;
	if (($getId=mysql_fetch_array(mysql_query('SELECT * FROM `mkjoueurs` WHERE nom="'.$_POST['pseudo'].'" AND banned=0 AND deleted=0'))) && password_verify($_POST['code'],$getId['code'])) {
		include('../includes/getId.php');
		function ip_banned() {
			global $identifiants;
			return mysql_numrows(mysql_query('SELECT * FROM `ip_bans` WHERE ip1="'.$identifiants[0].'" AND ip2="'.$identifiants[1].'" AND ip3="'.$identifiants[2].'" AND ip4="'.$identifiants[3].'"'));
		}
		if (ip_banned())
			$id = 0;
		else {
			session_start();
			$id = $getId['id'];
			$_SESSION['mkid'] = $id;
			require_once('../includes/credentials.php');
			setcookie('mkp', credentials_encrypt($id,$_POST['code']), 4294967295,'/');
			include('../includes/setId.php');
			if (ip_banned()) {
				include('../includes/language.php');
				mysql_query('UPDATE `mkjoueurs` SET banned=2 WHERE id="'.$id.'"');
				mysql_query('INSERT IGNORE INTO `mkbans` VALUES('.$id.',"'. ($language ? 'Auto-ban by IP' : 'Auto-ban par IP') .'",NULL)');
				setcookie('mkp', '', 0,'/');
				session_destroy();
				$id = 0;
			}
		}
	}
	else
		setcookie('mkp', '', 0,'/');
	mysql_close();
	echo $id;
}
?>