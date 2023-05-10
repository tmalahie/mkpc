<?php
header('Content-Type: text/plain');
include('../includes/session.php');
include('../includes/initdb.php');
if (!$id) {
	echo '[]';
	mysql_close();
	exit;
}
$timeStamp = time();
mysql_query('INSERT INTO `mkconnectes` SET id="'. $id .'",connecte='. $timeStamp .' ON DUPLICATE KEY UPDATE connecte='. $timeStamp);
mysql_query('DELETE FROM `mkconnectes` WHERE connecte <= '. ($timeStamp-30));
$getConnectes = mysql_query(
	'SELECT c.id,j.nom,j.online,f.code FROM `mkconnectes` c
	INNER JOIN `mkjoueurs` j ON c.id=j.id AND j.online>0
	INNER JOIN `mkprofiles` p ON p.id=j.id
	LEFT JOIN `mkcountries` f ON p.country=f.id
	WHERE c.id!='.$id
);
echo '[';
echo '[';
$colon = '';
while ($player = mysql_fetch_array($getConnectes)) {
	echo $colon .'['. $player['id'].',"'. $player['nom'].'",'.$player['online'].',"'. $player['code'].'"]';
	$colon = ',';
}
echo '],[';
$colon = '';
mysql_query('UPDATE `mkinvitations` SET connecte='. $timeStamp .' WHERE demandeur="'. $id .'" AND reponse=-1');
mysql_query('DELETE FROM `mkinvitations` WHERE connecte <= '. ($timeStamp-30));
$getDemandes = mysql_query('SELECT demandeur,battle FROM `mkinvitations` WHERE receveur="'. $id .'" AND reponse=-1');
while ($getDemande = mysql_fetch_array($getDemandes)) {
	$pts_ = $getDemande['battle'] ? 'pts_battle':'pts_vs';
	if ($getPseudo = mysql_fetch_array(mysql_query('SELECT j.nom,j.'.$pts_.' AS pts FROM `mkjoueurs` j WHERE j.id='. $getDemande['demandeur']))) {
		echo $colon .'['. $getDemande['demandeur'].',"'. $getPseudo['nom'].'",'.$getPseudo['pts'].','. $getDemande['battle'] .']';
		$colon = ',';
	}
}
echo '],[';
$colon = '';
$getDemandes = mysql_query('SELECT receveur,reponse,message,battle FROM `mkinvitations` WHERE demandeur="'. $id .'" AND reponse!=-1');
while ($getDemande = mysql_fetch_array($getDemandes)) {
	if ($getPseudo = mysql_fetch_array(mysql_query('SELECT nom FROM `mkjoueurs` WHERE id='. $getDemande['receveur']))) {
		echo $colon .'['. $getDemande['receveur'].',"'. $getPseudo['nom'].'",'.$getDemande['reponse'].',"'. htmlspecialchars($getDemande['message']) .'",'. $getDemande['battle'] .']';
		$colon = ',';
	}
}
echo '],{';
$colon = '';
include('../includes/o_utils.php');
for ($i=0;isset($_POST['c'.$i])&&isset($_POST['m'.$i]);$i++) {
	mysql_query('UPDATE `mkchats` SET seen=1 WHERE id<="'. $_POST['m'.$i] .'" AND sender="'. $_POST['c'.$i] .'" AND receiver="'. $id .'"');
	if (isset($_POST['w'.$i]))
		mysql_query('UPDATE `mkconvs` SET writting='. ($_POST['w'.$i] ? 'CURRENT_TIMESTAMP()':'NULL') .' WHERE sender="'. $_POST['c'.$i] .'" AND receiver="'. $id .'"');
	$getMsgs = mysql_query('SELECT * FROM `mkchats` WHERE id>"'. $_POST['m'.$i] .'" AND ((sender="'. $id .'" AND receiver="'. $_POST['c'.$i] .'") OR (sender="'. $_POST['c'.$i] .'" AND receiver="'. $id .'")) ORDER BY id');
	$colon2 = '';
	echo $colon.'"c'.$_POST['c'.$i].'":[';
	while ($msg = mysql_fetch_array($getMsgs)) {
		echo $colon2;
		$colon2 = ',';
		echo '['.$msg['id'].','.$msg['sender'].',"'.parse_msg($msg['message']).'","'.to_local_tz($msg['date']).'"]';
	}
	echo ']';
	$colon = ',';
}
echo '},[';
$unseenSQL = 'SELECT c.id,c.sender,c.message,j.nom FROM `mkchats` c INNER JOIN(SELECT MAX(id) AS maxID, sender FROM `mkchats` WHERE receiver="'. $id .'" AND seen=0 GROUP BY sender) m ON c.id=m.maxID INNER JOIN `mkjoueurs` j ON c.sender=j.id WHERE c.receiver="'. $id .'" AND c.seen=0';
for ($i=0;isset($_POST['c'.$i])&&isset($_POST['m'.$i]);$i++)
	$unseenSQL .= ' AND c.sender!="'. $_POST['c'.$i] .'"';
$unseenMsgs = mysql_query($unseenSQL);
$colon = '';
while ($msg=mysql_fetch_array($unseenMsgs)) {
	echo $colon;
	echo '['.$msg['id'].','.$msg['sender'].',"'.$msg['nom'].'","'.parse_msg($msg['message']).'"]';
	$colon = ',';
}
echo '],[';
$getWritting = mysql_query('SELECT receiver FROM `mkconvs` WHERE sender="'. $id .'" AND writting>DATE_SUB(NOW(),INTERVAL 10 SECOND)');
$colon = '';
while ($writer = mysql_fetch_array($getWritting)) {
	echo $colon;
	echo $writer['receiver'];
	$colon = ',';
}
echo ']';
echo ']';
mysql_close();
?>
