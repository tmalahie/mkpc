<?php
header('Content-Type: text/plain');
if (isset($_POST['id'])) {
	include('../includes/initdb.php');
	$cID = intval($_POST['id']);
	include('../includes/getId.php');
	include('../includes/session.php');
	require_once('../includes/getRights.php');
	require_once('../includes/collabUtils.php');
	require_once('../includes/utils-logs.php');
	$skipOwnerCheck = hasRight('moderator') || hasCollabGrants('mkcups', $cID, $_POST['collab'], 'edit');
	if (mysql_numrows(mysql_query('SELECT * FROM `mkcups` WHERE id="'. $cID .'"'. ($skipOwnerCheck ? '':' AND identifiant="'. $identifiants[0] .'" AND identifiant2="'. $identifiants[1] .'" AND identifiant3="'. $identifiants[2] .'" AND identifiant4="'. $identifiants[3] .'"')))) {
		$cupSnapshot = hasRight('moderator') ? snapshotTrack('mkcups', $cID) : null;
		mysql_query('DELETE FROM `mkmcups_tracks` WHERE cup="'.$cID.'"');
		mysql_query('DELETE c FROM `mkmcups` c LEFT JOIN `mkmcups_tracks` t ON c.id=t.mcup WHERE t.mcup IS NULL');
		mysql_query('DELETE FROM `mkcups` WHERE id="'.$cID.'"');
		mysql_query('UPDATE `mkclrace` l LEFT JOIN `mkchallenges` h ON h.clist=l.id AND h.status="pending_moderation" LEFT JOIN `mkcups` c ON l.circuit=c.id SET l.type="",l.circuit=NULL,h.status="pending_publication" WHERE l.type="mkcups" AND c.id IS NULL');
		mysql_query('UPDATE `mkclrace` l LEFT JOIN `mkchallenges` h ON h.clist=l.id AND h.status="pending_moderation" LEFT JOIN `mkmcups` c ON l.circuit=c.id SET l.type="",l.circuit=NULL,h.status="pending_publication" WHERE l.type="mkmcups" AND c.id IS NULL');
		include('../includes/postCircuitUpdate.php');
		postCircuitDelete('mkcups', $cID);
		if (hasRight('moderator'))
			insertLog($id, 'Cup '. $cID, $cupSnapshot);
	}
	echo 1;
	mysql_close();
}
?>