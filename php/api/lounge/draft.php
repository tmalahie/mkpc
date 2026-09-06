<?php
header('Content-Type: application/json');
include('../../includes/session.php');
if (!$id) {
	echo json_encode(array('error' => 'auth'));
	exit;
}
include('../../includes/initdb.php');
include('../../includes/lounge/common.php');

if (!isset($_POST['player'])) {
	echo json_encode(array('error' => 'player_required'));
	mysql_close();
	exit;
}

$queue = lounge_get_active_queue_for_player($id);
if (!$queue) {
	echo json_encode(array('error' => 'not_in_queue'));
	mysql_close();
	exit;
}
if ($queue['status'] !== 'drafting') {
	echo json_encode(array('error' => 'not_drafting', 'queue' => lounge_queue_state($queue['id'], $id)));
	mysql_close();
	exit;
}

$error = lounge_draft_pick($queue['id'], $id, intval($_POST['player']));
$response = array('queue' => lounge_queue_state($queue['id'], $id));
if ($error)
	$response['error'] = $error;
echo json_encode($response);
mysql_close();
