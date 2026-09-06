<?php
header('Content-Type: application/json');
include('../../includes/session.php');
if (!$id) {
	echo json_encode(array('error' => 'auth'));
	exit;
}
include('../../includes/initdb.php');
include('../../includes/lounge/common.php');

lounge_tick();

$state = lounge_get_player_state($id);

$tiers = array();
$getTiers = mysql_query(
	'SELECT id, code, label_en, label_fr, min_mmr, max_mmr, min_players
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
		'min_players' => intval($tier['min_players']) ? intval($tier['min_players']) : lounge_setting('default_min_players'),
		'eligible' => lounge_tier_eligible($tier, $state['mmr']),
		'queue_count' => $queueCount
	);
}

// The rules gate and the entry criteria are part of the tier screen: a player who cannot
// queue should be told why there rather than after clicking Join.
$access = mysql_fetch_array(mysql_query(
	'SELECT j.pts_vs, DATEDIFF(NOW(), p.sub_date) AS account_age
	FROM `mkjoueurs` j LEFT JOIN `mkprofiles` p ON p.id=j.id
	WHERE j.id="'. intval($id) .'"'
));

echo json_encode(array(
	'player' => $state,
	'tiers' => $tiers,
	'rules_accepted' => lounge_has_accepted_rules($id),
	'requirements' => lounge_access_requirements(),
	'vs_points' => $access ? intval($access['pts_vs']) : 0,
	'account_age_days' => ($access && !is_null($access['account_age'])) ? intval($access['account_age']) : null,
	'access_error' => lounge_access_error($id)
));
mysql_close();
