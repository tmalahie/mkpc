<?php
header('Content-Type: application/json');
include('../../includes/session.php');
if (!$id) {
	echo json_encode(array('error' => 'auth'));
	exit;
}
include('../../includes/initdb.php');
include('../../includes/lounge/common.php');

if (!isset($_POST['mode'])) {
	echo json_encode(array('error' => 'mode_required'));
	mysql_close();
	exit;
}

$queue = lounge_get_active_queue_for_player($id);
if (!$queue) {
	echo json_encode(array('error' => 'not_in_queue'));
	mysql_close();
	exit;
}
if ($queue['status'] !== 'voting') {
	echo json_encode(array('error' => 'not_voting', 'queue' => lounge_queue_state($queue['id'], $id)));
	mysql_close();
	exit;
}

$count = lounge_active_member_count($queue['id']);
$allowedModes = lounge_allowed_modes($count);
$mode = $_POST['mode'];
if (!in_array($mode, $allowedModes, true)) {
	echo json_encode(array('error' => 'mode_not_allowed', 'queue' => lounge_queue_state($queue['id'], $id)));
	mysql_close();
	exit;
}

mysql_query(
	'UPDATE `mklounge_queue_members` SET voted_mode="'. mysql_real_escape_string($mode) .'"
	WHERE queue="'. intval($queue['id']) .'" AND player="'. intval($id) .'" AND dropped_at IS NULL'
);

$missing = mysql_fetch_array(mysql_query(
	'SELECT COUNT(*) AS n FROM `mklounge_queue_members`
	WHERE queue="'. intval($queue['id']) .'" AND dropped_at IS NULL AND voted_mode IS NULL'
));
if ($missing && intval($missing['n']) === 0) {
	lounge_launch_match($queue['id']);
}

echo json_encode(array('queue' => lounge_queue_state($queue['id'], $id)));
mysql_close();
