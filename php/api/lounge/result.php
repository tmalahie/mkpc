<?php
header('Content-Type: application/json');
include('../../includes/session.php');
if (!$id) {
	echo json_encode(array('error' => 'auth'));
	exit;
}
include('../../includes/initdb.php');
include('../../includes/lounge/common.php');

if (!isset($_POST['key'])) {
	echo json_encode(array('error' => 'key_required'));
	mysql_close();
	exit;
}

lounge_tick();

echo json_encode(array(
	'player' => lounge_get_player_state($id),
	'match' => lounge_match_result(intval($_POST['key']), $id)
));
mysql_close();
