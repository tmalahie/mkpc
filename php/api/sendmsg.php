<?php
header('Content-Type: text/plain');
include('../includes/session.php');
if ($id && isset($_POST['member']) && isset($_POST['message']) && isset($_POST['lastID'])) {
	include('../includes/initdb.php');
	$getBanned = mysql_fetch_array(mysql_query('SELECT banned FROM `mkjoueurs` WHERE id="'. $id .'"'));
	echo '[';
	if (!$getBanned['banned']) {
		if (mysql_numrows(mysql_query('SELECT * FROM `mkjoueurs` WHERE id="'. $_POST['member'] .'"'))) {
			$isIgnored = mysql_numrows(mysql_query('SELECT * FROM `mkignores` WHERE ignorer="'. $_POST['member'] .'" AND ignored="'. $id .'"'));
			$seen = $isIgnored ? 1 : 0;
			mysql_query('INSERT INTO `mkchats` VALUES(NULL,"'. $id .'","'. $_POST['member'] .'","'.$_POST['message'].'",NULL,'. $seen .')');
			mysql_query('UPDATE `mkconvs` SET writting=NULL WHERE sender="'. $_POST['member'] .'" AND receiver="'. $id .'"');
			$lastMsgs = mysql_query('SELECT * FROM `mkchats` WHERE id>"'. $_POST['lastID'] .'" AND ((sender="'. $id .'" AND receiver="'. $_POST['member'] .'") OR (sender="'. $_POST['member'] .'" AND receiver="'. $id .'"))');
			$v = '';
			include('../includes/o_utils.php');
			while ($msgInfos = mysql_fetch_array($lastMsgs)) {
				echo $v;
				echo '['. $msgInfos['id'] .','.$msgInfos['sender'].',"'.parse_msg($msgInfos['message']).'","'.to_local_tz($msgInfos['date']).'"]';
				$v = ',';
			}
		}
	}
	echo ']';
	mysql_close();
}
?>