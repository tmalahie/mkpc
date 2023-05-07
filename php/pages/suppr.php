<?php
$cID = isset($_GET['i']) ? intval($_GET['i']) : 0;
include('getId.php');
include('initdb.php');
include('session.php');
if (!isset($_SESSION['csrf']) || !isset($_GET['token']) || ($_SESSION['csrf'] != $_GET['token'])) {
	echo 'Invalid token';
	mysql_close();
	exit;
}
require_once('getRights.php');
if ($circuit = mysql_fetch_array(mysql_query('SELECT id,img_data FROM `circuits` WHERE id="'.$cID.'"'. (hasRight('moderator') ? '':' AND identifiant='.$identifiants[0].' AND identifiant2='.$identifiants[1].' AND identifiant3='.$identifiants[2].' AND identifiant4='.$identifiants[3])))) {
	mysql_query('DELETE FROM `mkmcups_tracks` WHERE cup IN (SELECT id FROM `mkcups` WHERE (circuit0="'.$cID.'" OR circuit1="'.$cID.'" OR circuit2="'.$cID.'" OR circuit3="'.$cID.'") AND mode=1)');
	mysql_query('DELETE c FROM `mkmcups` c LEFT JOIN `mkmcups_tracks` t ON c.id=t.mcup WHERE t.mcup IS NULL');
	mysql_query('DELETE FROM `mkcups` WHERE (circuit0="'.$cID.'" OR circuit1="'.$cID.'" OR circuit2="'.$cID.'" OR circuit3="'.$cID.'") AND mode=1');
	mysql_query('DELETE FROM `circuits` WHERE ID="'.$cID.'"');
	mysql_query('DELETE r,c FROM `mkclrace` r LEFT JOIN `mkchallenges` c ON c.clist=r.id AND c.status!="active" WHERE r.type="circuits" AND r.circuit="'.$cID.'"');
	mysql_query('UPDATE `mkclrace` l LEFT JOIN `mkchallenges` h ON h.clist=l.id AND h.status="pending_moderation" LEFT JOIN `mkcups` c ON l.circuit=c.id SET l.type="",l.circuit=NULL,h.status="pending_publication" WHERE l.type="mkcups" AND c.id IS NULL');
	mysql_query('UPDATE `mkclrace` l LEFT JOIN `mkchallenges` h ON h.clist=l.id AND h.status="pending_moderation" LEFT JOIN `mkmcups` c ON l.circuit=c.id SET l.type="",l.circuit=NULL,h.status="pending_publication" WHERE l.type="mkmcups" AND c.id IS NULL');
	mysql_query('DELETE FROM `circuits_data` WHERE id="'.$cID.'"');
	require_once('circuitImgUtils.php');
	$circuitImg = json_decode($circuit['img_data']);
	deleteCircuitFile($circuitImg);
	include('postCircuitUpdate.php');
	postCircuitDelete('circuits', $cID);
	if (hasRight('moderator'))
		mysql_query('INSERT INTO `mklogs` VALUES(NULL,NULL, '. $id .', "CCircuit '. $cID .'")');
}
mysql_close();
header('Location: draw.php');
?>