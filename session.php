<?php
error_reporting(E_ERROR);
session_start();
$id = $_SESSION['mkid'];
if (!$id) {
	$playerId = null;
	$playerName = null;
	$playerCode = null;
	$dbToOpen = null;
	if (isset($_COOKIE['mkpseudo']) && isset($_COOKIE['mkcode'])) {
		$playerName = $_COOKIE['mkpseudo'];
		$playerCode = $_COOKIE['mkcode'];
		if ($dbToOpen = !@mysql_ping())
			include('initdb.php');
		if (($getId=mysql_fetch_array(mysql_query('SELECT id,code FROM `mkjoueurs` WHERE nom="'. mysql_real_escape_string($playerName) .'"')))) {
			$playerId = $getId['id'];
			require_once('credentials.php');
			setcookie('mkp', credentials_encrypt($playerId,$playerCode), 4294967295,'/');
		}
		setcookie('mkpseudo', null, 0,'/');
		setcookie('mkcode', null, 0,'/');
	}
	if (isset($_COOKIE['mkp'])) {
		require_once('credentials.php');
		$playerCredentials = credentials_decrypt($_COOKIE['mkp']);
		$playerId = +$playerCredentials[0];
		$playerCode = $playerCredentials[1];
	}
	if ($playerId) {
		if (null === $playerName) {
			if ($dbToOpen = !@mysql_ping())
				include('initdb.php');
		}
		if (($getCode=mysql_fetch_array(mysql_query('SELECT code FROM `mkjoueurs` WHERE id='. $playerId .' AND deleted=0'))) && password_verify($playerCode,$getCode['code'])) {
			$id = $playerId;
			$_SESSION['mkid'] = $id;
			include('setId.php');
		}
		else
			setcookie('mkp', null, 0, '/');
	}
	if ($dbToOpen)
		mysql_close();
}
?>