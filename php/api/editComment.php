<?php
header('Content-Type: text/plain');
if (isset($_POST['id_msg']) && isset($_POST['message'])) {
	include('../includes/session.php');
	include('../includes/escape_all.php');
	if ($id) {
		if ($_POST['message']) {
			include('../includes/initdb.php');
			if ($getMsg = mysql_fetch_array(mysql_query('SELECT auteur,circuit,type,message FROM `mkcomments` WHERE id="'. $_POST['id_msg'] .'"'))) {
				require_once('../includes/getRights.php');
				require_once('../includes/utils-logs.php');
				if (($getMsg['auteur'] == $id) || hasRight('moderator')) {
					mysql_query('UPDATE `mkcomments` SET message="'. $_POST['message'] .'" WHERE id="'. $_POST['id_msg'] .'"');
					if ($getMsg['auteur'] != $id) {
						$editedComment = snapshotQuery('SELECT message FROM `mkcomments` WHERE id="'. $_POST['id_msg'] .'"');
						insertLog($id, 'EComment '. $_POST['id_msg'], array(
							'type' => 'track_comment',
							'id' => intval($_POST['id_msg']),
							'track' => snapshotTrack($getMsg['type'], $getMsg['circuit']),
							'author' => snapshotMember($getMsg['auteur']),
							'before' => $getMsg['message'],
							'after' => $editedComment['message']
						));
					}
					echo 1;
				}
			}
			mysql_close();
		}
	}
}
?>