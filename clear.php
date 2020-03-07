<?php
$cID = isset($_GET['i']) ? $_GET['i']:0;
include('getId.php');
include('initdb.php');
include('session.php');
if (!isset($_SESSION['csrf']) || !isset($_GET['token']) || ($_SESSION['csrf'] != $_GET['token'])) {
	echo 'Invalid token';
	mysql_close();
	exit;
}
require_once('getRights.php');
if (mysql_numrows(mysql_query('SELECT * FROM `arenes` WHERE id="'.$cID.'"'. (hasRight('moderator') ? '':' AND identifiant='.$identifiants[0].' AND identifiant2='.$identifiants[1].' AND identifiant3='.$identifiants[2].' AND identifiant4='.$identifiants[3])))) {
	mysql_query('DELETE FROM `arenes` WHERE ID="'.$cID.'"');
	mysql_query('DELETE r,c FROM `mkclrace` r LEFT JOIN `mkchallenges` c ON c.clist=r.id AND c.status!="active" WHERE r.type="arenes" AND r.circuit="'.$cID.'"');
	mysql_query('DELETE FROM `arenes_data` WHERE id="'.$cID.'"');
	$src = 'course';
	$id2 = $id;
	$id = $cID;
	include('getExt.php');
	$id = $id2;
	unlink('images/uploads/course'.$cID.'.'.$ext);
	if (hasRight('moderator'))
		mysql_query('INSERT INTO `mklogs` VALUES(NULL, '. $id .', "CArene '. $cID .'")');
}
mysql_close();
header('Location: course.php');
?>