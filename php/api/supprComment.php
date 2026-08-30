<?php
header('Content-Type: text/plain');
if (isset($_POST['id_msg'])) {
	include('../includes/session.php');
	if ($id) {
		include('../includes/initdb.php');
		if ($getMsg = mysql_fetch_array(mysql_query('SELECT auteur,circuit,type,message,date FROM `mkcomments` WHERE id="'. $_POST['id_msg'] .'"'))) {
			require_once('../includes/getRights.php');
			require_once('../includes/utils-logs.php');
			if (($getMsg['auteur'] == $id) || hasRight('moderator')) {
				mysql_query('DELETE FROM `mkcomments` WHERE id="'. $_POST['id_msg'] .'"');
				$getComments = mysql_fetch_array(mysql_query('SELECT COUNT(*) AS nb FROM `mkcomments` WHERE circuit="'. $getMsg['circuit'] .'" AND type="'. $getMsg['type'] .'"'));
				mysql_query('UPDATE `'.$getMsg['type'].'` SET nbcomments="'. $getComments['nb'] .'" WHERE id="'. $getMsg['circuit'] .'"');
				if ($getMsg['auteur'] != $id)
					insertLog($id, 'SComment '. $_POST['id_msg'], array(
						'type' => 'track_comment',
						'id' => intval($_POST['id_msg']),
						'track' => snapshotTrack($getMsg['type'], $getMsg['circuit']),
						'author' => snapshotMember($getMsg['auteur']),
						'date' => $getMsg['date'],
						'content' => $getMsg['message']
					));
				echo 1;
			}
		}
		mysql_close();
	}
}
?>