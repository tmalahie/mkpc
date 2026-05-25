<?php
define('LOUNGE_DEFAULT_MMR', 500);
define('LOUNGE_CURRENT_SEASON', 1);
define('LOUNGE_QUEUE_LOCK_THRESHOLD', 4);
define('LOUNGE_QUEUE_READY_THRESHOLD', 8);
define('LOUNGE_AFK_SECONDS', 300);
define('LOUNGE_HEARTBEAT_POLL_SECONDS', 5);

function lounge_get_player_state($playerId) {
	$row = mysql_fetch_array(mysql_query(
		'SELECT mmr, peak_mmr, games, wins, strikes, banned_until, placed
		FROM `mklounge_players`
		WHERE player="'. intval($playerId) .'" AND season="'. LOUNGE_CURRENT_SEASON .'"'
	));
	if ($row) {
		return array(
			'mmr' => intval($row['mmr']),
			'peak_mmr' => intval($row['peak_mmr']),
			'games' => intval($row['games']),
			'wins' => intval($row['wins']),
			'strikes' => intval($row['strikes']),
			'banned_until' => $row['banned_until'],
			'placed' => intval($row['placed'])
		);
	}
	return array(
		'mmr' => LOUNGE_DEFAULT_MMR,
		'peak_mmr' => LOUNGE_DEFAULT_MMR,
		'games' => 0,
		'wins' => 0,
		'strikes' => 0,
		'banned_until' => null,
		'placed' => 0
	);
}

function lounge_tier_eligible($tier, $mmr) {
	if ($tier['code'] === 'all')
		return true;
	if ($mmr < intval($tier['min_mmr']))
		return false;
	if (!is_null($tier['max_mmr']) && $mmr > intval($tier['max_mmr']))
		return false;
	return true;
}

function lounge_get_tier($tierId) {
	return mysql_fetch_array(mysql_query(
		'SELECT id, code, label_en, label_fr, min_mmr, max_mmr, multicup_id
		FROM `mklounge_tiers` WHERE id="'. intval($tierId) .'"'
	));
}

function lounge_get_active_queue_for_player($playerId) {
	return mysql_fetch_array(mysql_query(
		'SELECT q.* FROM `mklounge_queues` q
		INNER JOIN `mklounge_queue_members` m ON m.queue=q.id
		WHERE m.player="'. intval($playerId) .'"
		AND m.dropped_at IS NULL
		AND q.status IN ("open","locked","voting")
		LIMIT 1'
	));
}

function lounge_active_member_count($queueId) {
	$row = mysql_fetch_array(mysql_query(
		'SELECT COUNT(*) AS n FROM `mklounge_queue_members`
		WHERE queue="'. intval($queueId) .'" AND dropped_at IS NULL'
	));
	return $row ? intval($row['n']) : 0;
}

function lounge_queue_members($queueId) {
	$members = array();
	$res = mysql_query(
		'SELECT m.player, m.joined_at, m.last_heartbeat, j.nom,
			COALESCE(p.mmr, '. LOUNGE_DEFAULT_MMR .') AS mmr
		FROM `mklounge_queue_members` m
		INNER JOIN `mkjoueurs` j ON j.id=m.player
		LEFT JOIN `mklounge_players` p ON p.player=m.player AND p.season="'. LOUNGE_CURRENT_SEASON .'"
		WHERE m.queue="'. intval($queueId) .'" AND m.dropped_at IS NULL
		ORDER BY m.joined_at'
	);
	while ($row = mysql_fetch_array($res)) {
		$members[] = array(
			'id' => intval($row['player']),
			'name' => $row['nom'],
			'mmr' => intval($row['mmr']),
			'joined_at' => $row['joined_at']
		);
	}
	return $members;
}

function lounge_queue_state($queueId) {
	$queue = mysql_fetch_array(mysql_query(
		'SELECT q.*, t.code AS tier_code, t.label_en AS tier_label_en, t.label_fr AS tier_label_fr
		FROM `mklounge_queues` q
		INNER JOIN `mklounge_tiers` t ON t.id=q.tier
		WHERE q.id="'. intval($queueId) .'"'
	));
	if (!$queue) return null;
	$members = lounge_queue_members($queueId);
	return array(
		'id' => intval($queue['id']),
		'tier' => intval($queue['tier']),
		'tier_code' => $queue['tier_code'],
		'tier_label_en' => $queue['tier_label_en'],
		'tier_label_fr' => $queue['tier_label_fr'],
		'status' => $queue['status'],
		'opened_at' => $queue['opened_at'],
		'locked_at' => $queue['locked_at'],
		'ready_at' => $queue['ready_at'],
		'members' => $members,
		'lock_threshold' => LOUNGE_QUEUE_LOCK_THRESHOLD,
		'ready_threshold' => LOUNGE_QUEUE_READY_THRESHOLD
	);
}

function lounge_update_queue_status($queueId) {
	$queue = mysql_fetch_array(mysql_query(
		'SELECT status FROM `mklounge_queues` WHERE id="'. intval($queueId) .'"'
	));
	if (!$queue) return;
	$count = lounge_active_member_count($queueId);
	$status = $queue['status'];

	if ($status === 'open' && $count >= LOUNGE_QUEUE_LOCK_THRESHOLD) {
		mysql_query(
			'UPDATE `mklounge_queues` SET status="locked", locked_at=NOW()
			WHERE id="'. intval($queueId) .'" AND status="open"'
		);
	}
	elseif ($status === 'locked' && $count < LOUNGE_QUEUE_LOCK_THRESHOLD) {
		mysql_query(
			'UPDATE `mklounge_queues` SET status="open", locked_at=NULL
			WHERE id="'. intval($queueId) .'" AND status="locked"'
		);
	}
	if ($count === 0 && ($status === 'open' || $status === 'locked')) {
		mysql_query(
			'UPDATE `mklounge_queues` SET status="cancelled"
			WHERE id="'. intval($queueId) .'" AND status IN ("open","locked")'
		);
	}
}

function lounge_tick() {
	$cutoff = intval(LOUNGE_AFK_SECONDS);
	$afkRes = mysql_query(
		'SELECT m.queue, m.player FROM `mklounge_queue_members` m
		INNER JOIN `mklounge_queues` q ON q.id=m.queue
		WHERE m.dropped_at IS NULL
		AND m.last_heartbeat < (NOW() - INTERVAL '. $cutoff .' SECOND)
		AND q.status IN ("open","locked")'
	);
	$affected = array();
	while ($row = mysql_fetch_array($afkRes)) {
		$affected[intval($row['queue'])] = true;
		mysql_query(
			'UPDATE `mklounge_queue_members` SET dropped_at=NOW()
			WHERE queue="'. intval($row['queue']) .'" AND player="'. intval($row['player']) .'"
			AND dropped_at IS NULL'
		);
		mysql_query(
			'INSERT INTO `mklounge_players` (player, season, strikes)
			VALUES ("'. intval($row['player']) .'", "'. LOUNGE_CURRENT_SEASON .'", 1)
			ON DUPLICATE KEY UPDATE strikes=strikes+1'
		);
	}
	foreach ($affected as $queueId => $_) {
		lounge_update_queue_status($queueId);
	}
}
