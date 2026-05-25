<?php
header('Content-Type: application/json');
include('../../includes/session.php');
if (!$id) {
	echo json_encode(array('error' => 'auth'));
	exit;
}
include('../../includes/initdb.php');
include('../../includes/lounge/common.php');

$queue = lounge_get_active_queue_for_player($id);
if (!$queue) {
	echo json_encode(array('ok' => true, 'queue' => null));
	mysql_close();
	exit;
}

if ($queue['status'] !== 'open') {
	echo json_encode(array('error' => 'queue_locked', 'queue' => lounge_queue_state($queue['id'])));
	mysql_close();
	exit;
}

mysql_query(
	'UPDATE `mklounge_queue_members` SET dropped_at=NOW()
	WHERE queue="'. intval($queue['id']) .'" AND player="'. intval($id) .'"
	AND dropped_at IS NULL'
);

lounge_update_queue_status($queue['id']);

echo json_encode(array('ok' => true, 'queue' => null));
mysql_close();
