<?php
header('Content-Type: text/plain');
include('../includes/session.php');
if ($id) {
	if (isset($_POST['msg'])) {
		include('../includes/initdb.php');
		require_once('../includes/getRights.php');
		require_once('../includes/utils-logs.php');
		if (hasRight('moderator')) {
			$chatSnapshot = array('type' => 'chat_message', 'id' => intval($_POST['msg']));
			if ($chatMessage = snapshotQuery('SELECT pseudo,message FROM minichat WHERE id="'. $_POST['msg'] .'"'))
				$chatSnapshot = array_merge($chatSnapshot, $chatMessage);
			mysql_query('DELETE FROM minichat WHERE id="'. $_POST['msg'] .'"');
			insertLog($id, 'Chat '. $_POST['msg'], $chatSnapshot);
		}
		mysql_close();
	}
}
echo 1;
?>