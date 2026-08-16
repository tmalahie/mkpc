<?php
header('Content-Type: application/json');
include('../../includes/session.php');
if (!$id) {
	echo json_encode(array('error' => 'auth'));
	exit;
}
include('../../includes/initdb.php');
include('../../includes/lounge/common.php');

if (!isset($_POST['tier'])) {
	echo json_encode(array('error' => 'tier_required'));
	mysql_close();
	exit;
}

$tierId = intval($_POST['tier']);
$tier = lounge_get_tier($tierId);
if (!$tier) {
	echo json_encode(array('error' => 'tier_not_found'));
	mysql_close();
	exit;
}

$playerState = lounge_get_player_state($id);
if ($playerState['banned_until']) {
	echo json_encode(array('error' => 'banned', 'banned_until' => $playerState['banned_until']));
	mysql_close();
	exit;
}

if (!lounge_tier_eligible($tier, $playerState['mmr'])) {
	echo json_encode(array('error' => 'not_eligible'));
	mysql_close();
	exit;
}

if (lounge_get_active_queue_for_player($id)) {
	echo json_encode(array('error' => 'already_queued'));
	mysql_close();
	exit;
}

mysql_query('START TRANSACTION');

$existing = mysql_fetch_array(mysql_query(
	'SELECT id FROM `mklounge_queues`
	WHERE tier="'. intval($tierId) .'"
	AND season="'. LOUNGE_CURRENT_SEASON .'"
	AND status="open"
	ORDER BY id LIMIT 1 FOR UPDATE'
));

if ($existing) {
	$queueId = intval($existing['id']);
} else {
	mysql_query(
		'INSERT INTO `mklounge_queues` (season, tier, status)
		VALUES ("'. LOUNGE_CURRENT_SEASON .'", "'. intval($tierId) .'", "open")'
	);
	$queueId = intval(mysql_insert_id());
}

$perso = isset($_POST['perso']) ? preg_replace('#[^\w\-]#', '', $_POST['perso']) : '';
$persoValue = strlen($perso) ? '"'. mysql_real_escape_string($perso) .'"' : 'NULL';

mysql_query(
	'INSERT INTO `mklounge_queue_members` (queue, player, joined_at, last_heartbeat, perso)
	VALUES ("'. $queueId .'", "'. intval($id) .'", NOW(), NOW(), '. $persoValue .')
	ON DUPLICATE KEY UPDATE dropped_at=NULL, last_heartbeat=NOW(), perso='. $persoValue
);

mysql_query('COMMIT');

lounge_update_queue_status($queueId);

echo json_encode(array('queue' => lounge_queue_state($queueId, $id)));
mysql_close();
