<?php
header('Content-Type: application/json');
include('../../includes/session.php');
if (!$id) {
	echo json_encode(array('error' => 'auth'));
	exit;
}
include('../../includes/initdb.php');
include('../../includes/lounge/common.php');

if (!isset($_POST['match'])) {
	echo json_encode(array('error' => 'match_required'));
	mysql_close();
	exit;
}
$match = lounge_match_payload(intval($_POST['match']));

echo json_encode(array(
	'me' => intval($id),
	'match' => $match
));
mysql_close();
