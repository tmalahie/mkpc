<?php
if (isset($_POST['id'])) {
	include('initdb.php');
	$cID = $_POST['id'];
	include('getId.php');
	include('session.php');
	require_once('getRights.php');
	require_once('collabUtils.php');
	$skipOwnerCheck = hasRight('moderator') || hasCollabGrants('mkcircuits', $cID, $_POST['collab'], 'edit');
	if (mysql_numrows(mysql_query('SELECT * FROM `mkcircuits` WHERE id="'. $cID .'"'. ($skipOwnerCheck ? '':' AND identifiant='.$identifiants[0].' AND identifiant2='.$identifiants[1].' AND identifiant3='.$identifiants[2].' AND identifiant4='.$identifiants[3])))) {
		mysql_query('DELETE FROM `mkmcups_tracks` WHERE cup IN (SELECT id FROM `mkcups` WHERE (circuit0="'.$cID.'" OR circuit1="'.$cID.'" OR circuit2="'.$cID.'" OR circuit3="'.$cID.'") AND mode=0)');
		mysql_query('DELETE c FROM `mkmcups` c LEFT JOIN `mkmcups_tracks` t ON c.id=t.mcup WHERE t.mcup IS NULL');
		mysql_query('DELETE FROM `mkcups` WHERE (circuit0="'.$cID.'" OR circuit1="'.$cID.'" OR circuit2="'.$cID.'" OR circuit3="'.$cID.'") AND mode=0');
		mysql_query('DELETE FROM `mkcircuits` WHERE id="'.$cID.'"');
		mysql_query('UPDATE `mkclrace` r LEFT JOIN `mkchallenges` c ON c.clist=r.id AND c.status="pending_moderation" SET r.type="",r.circuit=NULL,c.status="pending_publication" WHERE r.type="mkcircuits" AND r.circuit="'.$cID.'"');
		mysql_query('UPDATE `mkclrace` l LEFT JOIN `mkchallenges` h ON h.clist=l.id AND h.status="pending_moderation" LEFT JOIN `mkcups` c ON l.circuit=c.id SET l.type="",l.circuit=NULL,h.status="pending_publication" WHERE l.type="mkcups" AND c.id IS NULL');
		mysql_query('UPDATE `mkclrace` l LEFT JOIN `mkchallenges` h ON h.clist=l.id AND h.status="pending_moderation" LEFT JOIN `mkmcups` c ON l.circuit=c.id SET l.type="",l.circuit=NULL,h.status="pending_publication" WHERE l.type="mkmcups" AND c.id IS NULL');
		mysql_query('DELETE FROM `mkp` WHERE circuit="'.$cID.'"');
		$lettres = Array('a', 'b', 'c', 'd', 'e', 'f', 'g', 'h', 'i', 'j', 'o', 't');
		$nbLettres = count($lettres);
		for ($i=0;$i<$nbLettres;$i++)
			mysql_query('DELETE FROM `mk'.$lettres[$i].'` WHERE circuit="'.$cID.'"');
		if (hasRight('moderator'))
			mysql_query('INSERT INTO `mklogs` VALUES(NULL,NULL, '. $id .', "SCircuit '. $cID .'")');
	}
	echo 1;
	mysql_close();
}
?>