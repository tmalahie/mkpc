<?php
define('LOUNGE_DEFAULT_MMR', 500);
define('LOUNGE_CURRENT_SEASON', 1);
define('LOUNGE_QUEUE_LOCK_THRESHOLD', 4);
define('LOUNGE_QUEUE_READY_THRESHOLD', 8);
define('LOUNGE_AFK_SECONDS', 300);
define('LOUNGE_HEARTBEAT_POLL_SECONDS', 5);
define('LOUNGE_LOCK_WAIT_SECONDS', 300);
define('LOUNGE_VOTE_WAIT_SECONDS', 60);

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
		AND q.status IN ("open","locked","voting","launched")
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

function lounge_queue_state($queueId, $forPlayerId = null) {
	$queue = mysql_fetch_array(mysql_query(
		'SELECT q.*, t.code AS tier_code, t.label_en AS tier_label_en, t.label_fr AS tier_label_fr, t.multicup_id,
			GREATEST(0, UNIX_TIMESTAMP(q.locked_at) + '. intval(LOUNGE_LOCK_WAIT_SECONDS) .' - UNIX_TIMESTAMP(NOW())) AS lock_seconds_left,
			GREATEST(0, UNIX_TIMESTAMP(q.ready_at) + '. intval(LOUNGE_VOTE_WAIT_SECONDS) .' - UNIX_TIMESTAMP(NOW())) AS vote_seconds_left
		FROM `mklounge_queues` q
		INNER JOIN `mklounge_tiers` t ON t.id=q.tier
		WHERE q.id="'. intval($queueId) .'"'
	));
	if (!$queue) return null;
	$members = lounge_queue_members($queueId);
	$myVote = null;
	$votes = array();
	if ($queue['status'] === 'voting') {
		$voteRes = mysql_query(
			'SELECT player, voted_mode FROM `mklounge_queue_members`
			WHERE queue="'. intval($queueId) .'" AND dropped_at IS NULL'
		);
		while ($v = mysql_fetch_array($voteRes)) {
			if ($v['voted_mode'])
				$votes[$v['voted_mode']] = (isset($votes[$v['voted_mode']]) ? $votes[$v['voted_mode']] : 0) + 1;
			if ($forPlayerId && intval($v['player']) === intval($forPlayerId))
				$myVote = $v['voted_mode'];
		}
	}
	return array(
		'id' => intval($queue['id']),
		'tier' => intval($queue['tier']),
		'tier_code' => $queue['tier_code'],
		'tier_label_en' => $queue['tier_label_en'],
		'tier_label_fr' => $queue['tier_label_fr'],
		'multicup_id' => intval($queue['multicup_id']),
		'status' => $queue['status'],
		'opened_at' => $queue['opened_at'],
		'locked_at' => $queue['locked_at'],
		'ready_at' => $queue['ready_at'],
		'launched_at' => $queue['launched_at'],
		'lock_seconds_left' => is_null($queue['locked_at']) ? null : intval($queue['lock_seconds_left']),
		'vote_seconds_left' => is_null($queue['ready_at']) ? null : intval($queue['vote_seconds_left']),
		'privgame_key' => $queue['privgame_key'] ? intval($queue['privgame_key']) : null,
		'members' => $members,
		'allowed_modes' => lounge_allowed_modes(count($members)),
		'my_vote' => $myVote,
		'votes' => $votes,
		'lock_threshold' => LOUNGE_QUEUE_LOCK_THRESHOLD,
		'ready_threshold' => LOUNGE_QUEUE_READY_THRESHOLD,
		'lock_wait_seconds' => LOUNGE_LOCK_WAIT_SECONDS,
		'vote_wait_seconds' => LOUNGE_VOTE_WAIT_SECONDS
	);
}

function lounge_allowed_modes($playerCount) {
	$modes = array('FFA');
	if ($playerCount === 4) $modes[] = '2v2';
	elseif ($playerCount === 6) $modes[] = '3v3';
	elseif ($playerCount === 8) { $modes[] = '4v4'; $modes[] = '2v2v2v2'; }
	return $modes;
}

function lounge_mode_to_rules($mode) {
	switch ($mode) {
		case '2v2':     return array('team' => 1, 'nbTeams' => 2);
		case '3v3':     return array('team' => 1, 'nbTeams' => 2);
		case '4v4':     return array('team' => 1, 'nbTeams' => 2);
		case '2v2v2v2': return array('team' => 1, 'nbTeams' => 4);
		default:        return array();
	}
}

function lounge_tally_vote($votes, $allowedModes) {
	if (empty($votes)) return $allowedModes[0];
	$best = null;
	$bestCount = -1;
	foreach ($allowedModes as $mode) {
		$c = isset($votes[$mode]) ? intval($votes[$mode]) : 0;
		if ($c > $bestCount) { $bestCount = $c; $best = $mode; }
	}
	return $best;
}

function lounge_start_voting($queueId) {
	mysql_query(
		'UPDATE `mklounge_queues` SET status="voting", ready_at=NOW()
		WHERE id="'. intval($queueId) .'" AND status IN ("open","locked")'
	);
}

function lounge_launch_match($queueId) {
	$queueRow = mysql_fetch_array(mysql_query(
		'SELECT q.id, q.season, q.tier, t.multicup_id
		FROM `mklounge_queues` q
		INNER JOIN `mklounge_tiers` t ON t.id=q.tier
		WHERE q.id="'. intval($queueId) .'" AND q.status="voting"'
	));
	if (!$queueRow) return null;

	$members = lounge_queue_members($queueId);
	$voteRes = mysql_query(
		'SELECT voted_mode FROM `mklounge_queue_members`
		WHERE queue="'. intval($queueId) .'" AND dropped_at IS NULL'
	);
	$votes = array();
	while ($v = mysql_fetch_array($voteRes)) {
		if ($v['voted_mode'])
			$votes[$v['voted_mode']] = (isset($votes[$v['voted_mode']]) ? $votes[$v['voted_mode']] : 0) + 1;
	}
	$allowedModes = lounge_allowed_modes(count($members));
	$mode = lounge_tally_vote($votes, $allowedModes);

	global $q;
	do {
		$key = rand();
		if (!$key) continue;
		$q = mysql_query('INSERT IGNORE INTO `mkprivgame` SET id="'. $key .'",player=0');
	} while (!mysql_affected_rows());

	$rules = lounge_mode_to_rules($mode);
	if (!empty($rules)) {
		$rulesJson = mysql_real_escape_string(json_encode($rules));
		mysql_query(
			'INSERT INTO `mkgameoptions` SET id="'. $key .'", rules="'. $rulesJson .'", public=0'
		);
	}

	mysql_query(
		'UPDATE `mklounge_queues`
		SET status="launched", launched_at=NOW(), privgame_key="'. $key .'"
		WHERE id="'. intval($queueId) .'"'
	);
	mysql_query(
		'INSERT INTO `mklounge_matches`
		(queue, season, tier, privgame_key, mode, started_at)
		VALUES ("'. intval($queueId) .'", "'. intval($queueRow['season']) .'",
				"'. intval($queueRow['tier']) .'", "'. $key .'",
				"'. mysql_real_escape_string($mode) .'", NOW())'
	);

	$matchId = mysql_insert_id();
	foreach ($members as $m) {
		mysql_query(
			'INSERT INTO `mklounge_match_players` (`match`, player)
			VALUES ("'. intval($matchId) .'", "'. intval($m['id']) .'")'
		);
	}

	return array('mode' => $mode, 'key' => $key, 'multicup_id' => intval($queueRow['multicup_id']));
}

function lounge_cancel_voting($queueId, $reason) {
	$nonVoters = mysql_query(
		'SELECT player FROM `mklounge_queue_members`
		WHERE queue="'. intval($queueId) .'" AND dropped_at IS NULL AND voted_mode IS NULL'
	);
	while ($r = mysql_fetch_array($nonVoters)) {
		mysql_query(
			'INSERT INTO `mklounge_players` (player, season, strikes)
			VALUES ("'. intval($r['player']) .'", "'. LOUNGE_CURRENT_SEASON .'", 1)
			ON DUPLICATE KEY UPDATE strikes=strikes+1'
		);
	}
	mysql_query(
		'UPDATE `mklounge_queue_members` SET dropped_at=NOW()
		WHERE queue="'. intval($queueId) .'" AND dropped_at IS NULL'
	);
	mysql_query(
		'UPDATE `mklounge_queues` SET status="cancelled"
		WHERE id="'. intval($queueId) .'"'
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

	$lockTimedOut = mysql_query(
		'SELECT id FROM `mklounge_queues`
		WHERE status="locked"
		AND locked_at IS NOT NULL
		AND locked_at < (NOW() - INTERVAL '. intval(LOUNGE_LOCK_WAIT_SECONDS) .' SECOND)'
	);
	while ($row = mysql_fetch_array($lockTimedOut)) {
		lounge_start_voting(intval($row['id']));
	}

	$readyToLaunch = mysql_query(
		'SELECT id FROM `mklounge_queues`
		WHERE status="locked"
		AND id IN (
			SELECT queue FROM `mklounge_queue_members`
			WHERE dropped_at IS NULL
			GROUP BY queue
			HAVING COUNT(*) >= '. intval(LOUNGE_QUEUE_READY_THRESHOLD) .'
		)'
	);
	while ($row = mysql_fetch_array($readyToLaunch)) {
		lounge_start_voting(intval($row['id']));
	}

	$voteDeadlines = mysql_query(
		'SELECT id FROM `mklounge_queues`
		WHERE status="voting"
		AND ready_at IS NOT NULL
		AND ready_at < (NOW() - INTERVAL '. intval(LOUNGE_VOTE_WAIT_SECONDS) .' SECOND)'
	);
	while ($row = mysql_fetch_array($voteDeadlines)) {
		$queueId = intval($row['id']);
		$missing = mysql_fetch_array(mysql_query(
			'SELECT COUNT(*) AS n FROM `mklounge_queue_members`
			WHERE queue="'. $queueId .'" AND dropped_at IS NULL AND voted_mode IS NULL'
		));
		if ($missing && intval($missing['n']) > 0) {
			lounge_cancel_voting($queueId, 'vote_timeout');
		} else {
			lounge_launch_match($queueId);
		}
	}
}
