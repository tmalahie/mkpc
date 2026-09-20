<?php
header('Content-Type: application/json');
include('../../includes/session.php');
if (!$id) {
	echo json_encode(array('error' => 'auth'));
	exit;
}
include('../../includes/initdb.php');
include('../../includes/lounge/common.php');

$limit = isset($_POST['limit']) ? intval($_POST['limit']) : 50;
if ($limit < 1) $limit = 1;
if ($limit > 500) $limit = 500;
// The full table is a page of its own, reached from "view all" - the overview asks for the
// top few names and nothing else, and pays for none of the stats queries.
$full = !empty($_POST['full']);

echo json_encode(array(
	'season' => intval(LOUNGE_CURRENT_SEASON),
	'me' => intval($id),
	'total' => lounge_ladder_size(),
	'players' => lounge_leaderboard($limit, $full)
));
mysql_close();
