<?php
if (isset($_POST['id'])) {
	include('initdb.php');
	$cID = $_POST['id'];
	include('getId.php');
	include('session.php');
	require_once('getRights.php');
	if (mysql_numrows(mysql_query('SELECT * FROM `mkcups` WHERE id="'. $cID .'"'. (hasRight('moderator') ? '':' AND identifiant="'. $identifiants[0] .'" AND identifiant2="'. $identifiants[1] .'" AND identifiant3="'. $identifiants[2] .'" AND identifiant4="'. $identifiants[3] .'"')))) {
		mysql_query('DELETE FROM `mkmcups_tracks` WHERE cup="'.$cID.'"');
		mysql_query('DELETE c FROM `mkmcups` c LEFT JOIN `mkmcups_tracks` t ON c.id=t.mcup WHERE t.mcup IS NULL');
		mysql_query('DELETE FROM `mkcups` WHERE id="'.$cID.'"');
		mysql_query('UPDATE `mkclrace` l LEFT JOIN `mkchallenges` h ON h.clist=l.id AND h.status="pending_moderation" LEFT JOIN `mkcups` c ON l.circuit=c.id SET l.type="",l.circuit=NULL,h.status="pending_publication" WHERE l.type="mkcups" AND c.id IS NULL');
		mysql_query('UPDATE `mkclrace` l LEFT JOIN `mkchallenges` h ON h.clist=l.id AND h.status="pending_moderation" LEFT JOIN `mkmcups` c ON l.circuit=c.id SET l.type="",l.circuit=NULL,h.status="pending_publication" WHERE l.type="mkmcups" AND c.id IS NULL');
		if (hasRight('moderator'))
			mysql_query('INSERT INTO `mklogs` VALUES(NULL,NULL, '. $id .', "Cup '. $cID .'")');
	}
	echo 1;
	mysql_close();
}
?>