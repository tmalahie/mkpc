<?php
header('Content-Type: text/plain');
if (isset($_POST['options'])) $options = $_POST['options'];
include('../includes/initdb.php');
if (isset($options)) $_POST['options'] = $options;
include('../includes/session.php');
if (isset($_POST['options'])) {
	$options = json_decode($_POST['options']);
	if ($options) {
		include('../includes/onlineRulesUtils.php');
		$getPublicOptions = mysql_query('SELECT id,rules FROM `mkgameoptions` WHERE public=1');
		while ($publicOptions = mysql_fetch_array($getPublicOptions)) {
			$rules = json_decode($publicOptions['rules']);
			if (rulesEqual($options,$rules)) {
				$matchingOptions = $publicOptions;
				break;
			}
		}
		if (isset($matchingOptions))
			$key = $matchingOptions['id'];
		else {
			$getId = mysql_fetch_array(mysql_query(
				'SELECT MIN(t1.id + 1) AS nextId
				FROM mkprivgame t1
					LEFT JOIN mkprivgame t2
					ON t1.id + 1 = t2.id
				WHERE t2.id IS NULL'
			));
			$key = $getId['nextId'];
			mysql_query(
				'INSERT INTO `mkgameoptions` SET id="'. $key .'",
				rules="'.mysql_real_escape_string(getRulesAsString($options)).'",
				public=1'
			);
			mysql_query('INSERT INTO `mkprivgame` SET id="'.$key.'",player=0');
		}
		$_SESSION['mklink'] = $key;
		echo $key;
	}
}
mysql_close();
?>