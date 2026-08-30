<?php
header('Content-Type: application/json');
include('../../includes/session.php');
if (!$id) {
	echo json_encode(array('error' => 'auth'));
	exit;
}
include('../../includes/initdb.php');
require_once('../../includes/onlineStateUtils.php');

$key = intval($_POST['key']);
$track = intval($_POST['track']);

// only a player of that mogi may write to its history
$participant = mysql_fetch_array(mysql_query(
	'SELECT 1 AS ok FROM `mklounge_match_players` mp
	INNER JOIN `mklounge_matches` m ON m.id=mp.`match` AND m.privgame_key="'. $key .'"
	WHERE mp.player="'. intval($id) .'" AND m.ended_at IS NULL'
));
if ($participant)
	pushCourseTrack($key, $track);

echo json_encode(array('tracks' => getCourseTracks($key)));
mysql_close();
