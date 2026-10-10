<?php
header('Content-Type: application/json');
include('../../includes/session.php');
if (!$id) {
	echo json_encode(array('error' => 'auth'));
	exit;
}
include('../../includes/initdb.php');
include('../../includes/lounge/common.php');

$limit = isset($_POST['limit']) ? intval($_POST['limit']) : 10;
if ($limit < 1) $limit = 1;
if ($limit > 200) $limit = 200;

require_once('../../includes/getRights.php');
echo json_encode(array(
	'me' => intval($id),
	'can_edit' => hasRight('lounge'),
	'matches' => lounge_recent_matches($limit)
));
mysql_close();
