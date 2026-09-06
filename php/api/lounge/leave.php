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
	echo json_encode(array('error' => 'queue_locked', 'queue' => lounge_queue_state($queue['id'], $id)));
	mysql_close();
	exit;
}

// Rule 3a: 15 seconds in the list before you may leave it, so a player cannot flicker in and
// out of a gathering lineup - MogiBot says "You can /d in 15 seconds" for the same reason.
$member = mysql_fetch_array(mysql_query(
	'SELECT GREATEST(0, UNIX_TIMESTAMP(joined_at) + '. intval(lounge_setting('drop_delay_seconds')) .'
		- UNIX_TIMESTAMP(NOW())) AS wait
	FROM `mklounge_queue_members`
	WHERE queue="'. intval($queue['id']) .'" AND player="'. intval($id) .'" AND dropped_at IS NULL'
));
if ($member && intval($member['wait']) > 0) {
	echo json_encode(array(
		'error' => 'drop_too_soon',
		'seconds_left' => intval($member['wait']),
		'queue' => lounge_queue_state($queue['id'], $id)
	));
	mysql_close();
	exit;
}

mysql_query(
	'UPDATE `mklounge_queue_members` SET dropped_at=NOW()
	WHERE queue="'. intval($queue['id']) .'" AND player="'. intval($id) .'"
	AND dropped_at IS NULL'
);

lounge_update_queue_status($queue['id']);

require_once('../../includes/lounge/discord.php');
$count = lounge_active_member_count($queue['id']);
$name = mysql_fetch_array(mysql_query('SELECT nom FROM `mkjoueurs` WHERE id="'. intval($id) .'"'));
lounge_discord_announce(
	$queue['id'],
	$name['nom'] .' has dropped from the mogi -- '. $count .' player'. (($count === 1) ? '' : 's')
);

echo json_encode(array('ok' => true, 'queue' => null));
mysql_close();
