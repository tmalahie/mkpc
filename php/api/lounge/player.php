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

echo json_encode(array(
	'me' => intval($id),
	'ranks' => lounge_ranks(),
	'player' => lounge_player_profile(intval($_POST['player']))
));
mysql_close();
