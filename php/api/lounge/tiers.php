<?php
header('Content-Type: application/json');
include('../../includes/session.php');
if (!$id) {
	echo json_encode(array('error' => 'auth'));
	exit;
}
include('../../includes/initdb.php');
include('../../includes/lounge/common.php');

$state = lounge_get_player_state($id);

$tiers = array();
$getTiers = mysql_query(
	'SELECT id, code, label_en, label_fr, min_mmr, max_mmr, multicup_id
	FROM `mklounge_tiers` ORDER BY ordering'
);
while ($tier = mysql_fetch_array($getTiers)) {
	$queueCount = 0;
	$getCount = mysql_fetch_array(mysql_query(
		'SELECT COUNT(*) AS n FROM `mklounge_queue_members` m
		INNER JOIN `mklounge_queues` q ON m.queue=q.id
		WHERE q.tier="'. intval($tier['id']) .'"
		AND q.status IN ("open","locked","voting")
		AND m.dropped_at IS NULL'
	));
	if ($getCount)
		$queueCount = intval($getCount['n']);

	$tiers[] = array(
		'id' => intval($tier['id']),
		'code' => $tier['code'],
		'label_en' => $tier['label_en'],
		'label_fr' => $tier['label_fr'],
		'min_mmr' => intval($tier['min_mmr']),
		'max_mmr' => is_null($tier['max_mmr']) ? null : intval($tier['max_mmr']),
		'multicup_id' => intval($tier['multicup_id']),
		'eligible' => lounge_tier_eligible($tier, $state['mmr']),
		'queue_count' => $queueCount
	);
}

echo json_encode(array(
	'player' => $state,
	'tiers' => $tiers
));
mysql_close();
