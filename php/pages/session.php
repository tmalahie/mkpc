<?php
@session_start();
$id = isset($_SESSION['mkid']) ? $_SESSION['mkid'] : null;
if (!$id) {
	$playerId = null;
	$playerName = null;
	$playerCode = null;
	$dbToOpen = null;
	if (isset($_COOKIE['mkp'])) {
		require_once('credentials.php');
		$playerCredentials = credentials_decrypt($_COOKIE['mkp']);
		$playerId = intval($playerCredentials[0]);
		$playerCode = $playerCredentials[1];
	}
	if ($playerId) {
		if (null === $playerName) {
			if (!isset($dbh)) {
				$dbToOpen = 1;
				include('initdb.php');
			}
		}
		if (($getCode=mysql_fetch_array(mysql_query('SELECT code FROM `mkjoueurs` WHERE id='. $playerId .' AND deleted=0'))) && password_verify($playerCode,$getCode['code'])) {
			$id = $playerId;
			$_SESSION['mkid'] = $id;
			include('setId.php');
		}
		else
			setcookie('mkp', '', 0, '/');
	}
	if ($dbToOpen)
		mysql_close();
}
?>