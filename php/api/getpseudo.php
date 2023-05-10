<?php
header('Content-Type: text/plain');
include('../includes/language.php');
include('../includes/session.php');
if ($id) {
	include('../includes/initdb.php');
	if ($getPseudo = mysql_fetch_array(mysql_query('SELECT nom,online FROM `mkjoueurs` WHERE id="'. $id .'" AND !banned'))) {
		echo '[';
		echo $id.',"'.$getPseudo['nom'].'",'.$language.','.$getPseudo['online'].',';
		echo '[';
		$getConvs = mysql_query('SELECT c.receiver,j.nom,c.reduced FROM `mkconvs` c INNER JOIN `mkjoueurs` j ON c.receiver=j.id WHERE c.sender="'. $id .'" ORDER BY c.id');
		$v = '';
		include('../includes/o_consts.php');
		include('../includes/o_utils.php');
		while ($conv = mysql_fetch_array($getConvs)) {
			echo $v;
			$v = ',';
			echo '[';
			echo $conv['receiver'] .',"'. $conv['nom'].'",';
			echo '[';
			if ($conv['reduced'])
				$getMsgs = mysql_query('SELECT * FROM `mkchats` WHERE (sender="'. $id .'" AND receiver="'. $conv['receiver'] .'") OR (sender="'. $conv['receiver'] .'" AND receiver="'. $id .'" AND seen=1) ORDER BY id DESC LIMIT '. $MSGS_PACKET_SIZE);
			else
				$getMsgs = mysql_query('SELECT * FROM `mkchats` WHERE (sender="'. $id .'" AND receiver="'. $conv['receiver'] .'") OR (sender="'. $conv['receiver'] .'" AND receiver="'. $id .'") ORDER BY id DESC LIMIT '. $MSGS_PACKET_SIZE);
			$v2 = '';
			while ($msg = mysql_fetch_array($getMsgs)) {
				echo $v2;
				$v2 = ',';
				echo '['.$msg['id'].','.$msg['sender'].',"'.parse_msg($msg['message']).'","'.to_local_tz($msg['date']).'"]';
			}
			echo ']';
			if ($conv['reduced'])
				echo ',1';
			echo ']';
		}
		echo '],[';
		$v = '';
		$getIgn = mysql_query('SELECT ignored FROM `mkignores` WHERE ignorer="'. $id .'"');
		while ($ignored = mysql_fetch_array($getIgn)) {
			echo $v;
			echo $ignored['ignored'];
			$v = ',';
		}
		echo ']';
		echo ']';
		mysql_query('DELETE FROM `mkconnectes` WHERE id="'. $id .'"');
		if ($getPseudo['online'] > 0) {
			mysql_query('INSERT INTO `mkconnectes` VALUES('. $id .','. time() .')');
			mysql_query('UPDATE `mkprofiles` SET last_connect=NULL WHERE id='. $id);
		}
	}
	else
		echo '[]';
	mysql_close();
}
else
	echo '[]';
?>