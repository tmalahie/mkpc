<?php
header('Content-Type: application/json');
include('../../includes/session.php');
if (!$id) {
	echo json_encode(array('error' => 'auth'));
	exit;
}
include('../../includes/initdb.php');
include('../../includes/lounge/common.php');

lounge_tick();

$playerState = lounge_get_player_state($id);
$queue = lounge_get_active_queue_for_player($id);
if (!$queue) {
	echo json_encode(array('player' => $playerState, 'queue' => null));
	mysql_close();
	exit;
}

mysql_query(
	'UPDATE `mklounge_queue_members` SET last_heartbeat=NOW()
	WHERE queue="'. intval($queue['id']) .'" AND player="'. intval($id) .'"
	AND dropped_at IS NULL'
);

echo json_encode(array('player' => $playerState, 'queue' => lounge_queue_state($queue['id'], $id)));
mysql_close();
