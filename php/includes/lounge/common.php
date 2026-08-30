<?php
define('LOUNGE_DEFAULT_MMR', 600);
define('LOUNGE_MMR_MIN', 0);
define('LOUNGE_CURRENT_SEASON', 1);
// Fallback when a tier row carries no min_players of its own.
define('LOUNGE_DEFAULT_MIN_PLAYERS', 4);
define('LOUNGE_QUEUE_READY_THRESHOLD', 8);
define('LOUNGE_AFK_SECONDS', 300);
define('LOUNGE_HEARTBEAT_POLL_SECONDS', 5);
define('LOUNGE_LOCK_WAIT_SECONDS', 30);
define('LOUNGE_VOTE_WAIT_SECONDS', 60);
define('LOUNGE_RACES_PER_MATCH', 12);
define('LOUNGE_STRIKES_BEFORE_BAN', 3);
define('LOUNGE_BAN_MINUTES', 60);
define('LOUNGE_JOIN_TIMEOUT_SECONDS', 180);
// A mogi is 12 races of ~3 minutes. Past this it is not being played any more, whatever
// the race counter says.
define('LOUNGE_MATCH_MAX_MINUTES', 120);
// How far the room's required player count may be lowered when players fail to join or
// walk out mid-mogi. Below this there is no race worth playing.
define('LOUNGE_MIN_RACE_PLAYERS', 2);

function lounge_get_season_multicup() {
	$row = mysql_fetch_array(mysql_query(
		'SELECT multicup_id FROM `mklounge_seasons` WHERE id="'. LOUNGE_CURRENT_SEASON .'"'
	));
	return $row ? intval($row['multicup_id']) : 0;
}

function lounge_rank_for_mmr($mmr) {
	$row = mysql_fetch_array(mysql_query(
		'SELECT code, label_en, label_fr, color FROM `mklounge_ranks`
		WHERE min_mmr <= "'. lounge_mmr_sql($mmr) .'"
		ORDER BY min_mmr DESC LIMIT 1'
	));
	if (!$row)
		return null;
	return array(
		'code' => $row['code'],
		'label_en' => $row['label_en'],
		'label_fr' => $row['label_fr'],
		'color' => $row['color']
	);
}

function lounge_get_player_state($playerId) {
	$row = mysql_fetch_array(mysql_query(
		'SELECT mmr, peak_mmr, games, wins, total_score, strikes, placed,
			IF(banned_until > NOW(), banned_until, NULL) AS banned_until
		FROM `mklounge_players`
		WHERE player="'. intval($playerId) .'" AND season="'. LOUNGE_CURRENT_SEASON .'"'
	));
	if ($row) {
		$games = intval($row['games']);
		return array(
			'mmr' => (int) round($row['mmr']),
			'peak_mmr' => (int) round($row['peak_mmr']),
			'games' => $games,
			'wins' => intval($row['wins']),
			'total_score' => intval($row['total_score']),
			'avg_score' => $games ? round(intval($row['total_score']) / $games, 1) : null,
			'strikes' => intval($row['strikes']),
			'banned_until' => $row['banned_until'],
			'placed' => intval($row['placed']),
			'rank' => lounge_rank_for_mmr($row['mmr'])
		);
	}
	return array(
		'mmr' => LOUNGE_DEFAULT_MMR,
		'peak_mmr' => LOUNGE_DEFAULT_MMR,
		'games' => 0,
		'wins' => 0,
		'total_score' => 0,
		'avg_score' => null,
		'strikes' => 0,
		'banned_until' => null,
		'placed' => 0,
		'rank' => lounge_rank_for_mmr(LOUNGE_DEFAULT_MMR)
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
		'SELECT id, code, label_en, label_fr, min_mmr, max_mmr
		FROM `mklounge_tiers` WHERE id="'. intval($tierId) .'"'
	));
}

function lounge_get_active_queue_for_player($playerId) {
	return mysql_fetch_array(mysql_query(
		'SELECT q.* FROM `mklounge_queues` q
		INNER JOIN `mklounge_queue_members` m ON m.queue=q.id
		WHERE m.player="'. intval($playerId) .'"
		AND m.dropped_at IS NULL
		AND q.status IN ("open","locked","voting","launching","launched")
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
		'SELECT m.player, m.joined_at, m.last_heartbeat, m.perso, j.nom,
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
			'perso' => $row['perso'],
			'joined_at' => $row['joined_at']
		);
	}
	return $members;
}

function lounge_queue_state($queueId, $forPlayerId = null) {
	$queue = mysql_fetch_array(mysql_query(
		'SELECT q.*, t.code AS tier_code, t.label_en AS tier_label_en, t.label_fr AS tier_label_fr,
			t.min_players,
			GREATEST(0, UNIX_TIMESTAMP(q.locked_at) + '. intval(LOUNGE_LOCK_WAIT_SECONDS) .' - UNIX_TIMESTAMP(NOW())) AS lock_seconds_left,
			GREATEST(0, UNIX_TIMESTAMP(q.ready_at) + '. intval(LOUNGE_VOTE_WAIT_SECONDS) .' - UNIX_TIMESTAMP(NOW())) AS vote_seconds_left
		FROM `mklounge_queues` q
		INNER JOIN `mklounge_tiers` t ON t.id=q.tier
		WHERE q.id="'. intval($queueId) .'"'
	));
	if (!$queue) return null;
	$members = lounge_queue_members($queueId);
	$myVote = null;
	$myPowVote = null;
	$votes = array();
	$powVotes = 0;
	if ($queue['status'] === 'voting') {
		$voteRes = mysql_query(
			'SELECT player, voted_mode, voted_pow FROM `mklounge_queue_members`
			WHERE queue="'. intval($queueId) .'" AND dropped_at IS NULL'
		);
		while ($v = mysql_fetch_array($voteRes)) {
			if ($v['voted_mode'])
				$votes[$v['voted_mode']] = (isset($votes[$v['voted_mode']]) ? $votes[$v['voted_mode']] : 0) + 1;
			if (intval($v['voted_pow']) === 1)
				$powVotes++;
			if ($forPlayerId && intval($v['player']) === intval($forPlayerId)) {
				$myVote = $v['voted_mode'];
				$myPowVote = is_null($v['voted_pow']) ? null : intval($v['voted_pow']);
			}
		}
	}
	return array(
		'id' => intval($queue['id']),
		'tier' => intval($queue['tier']),
		'tier_code' => $queue['tier_code'],
		'tier_label_en' => $queue['tier_label_en'],
		'tier_label_fr' => $queue['tier_label_fr'],
		'multicup_id' => lounge_get_season_multicup(),
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
		'my_pow_vote' => $myPowVote,
		'pow_votes' => $powVotes,
		'lock_threshold' => intval($queue['min_players']) ? intval($queue['min_players']) : LOUNGE_DEFAULT_MIN_PLAYERS,
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

function lounge_mode_team_count($mode) {
	switch ($mode) {
		case '2v2':     return 2;
		case '3v3':     return 2;
		case '4v4':     return 2;
		case '2v2v2v2': return 4;
		default:        return 0;
	}
}

// Rule 3h: the POW Block is only in the composition when the whole lineup agreed to it,
// so it is stripped out unless the vote was unanimous.
function lounge_item_distribution($withPow = true) {
	$distribution = array(
		array('fauxobjet'=>3, 'banane'=>4, 'bananeX3'=>2, 'carapace'=>5, 'bobomb'=>1),
		array('banane'=>2, 'bananeX3'=>3, 'carapace'=>5, 'carapacerouge'=>4, 'champi'=>2, 'poison'=>2, 'bobomb'=>1),
		array('bananeX3'=>3, 'carapace'=>3, 'carapacerouge'=>4, 'champi'=>4, 'poison'=>3, 'carapaceX3'=>1, 'bobomb'=>2, 'boomerang'=>2),
		array('carapacerouge'=>3, 'champi'=>4, 'poison'=>2, 'carapaceX3'=>2, 'boomerang'=>1, 'carapacerougeX3'=>1, 'megachampi'=>1),
		array('champi'=>4, 'carapacerougeX3'=>1, 'pow'=>2, 'champiX3'=>3, 'megachampi'=>2),
		array('champi'=>1, 'carapacebleue'=>1, 'champiX3'=>4, 'megachampi'=>3, 'etoile'=>2),
		array('carapacebleue'=>1, 'champiX3'=>4, 'megachampi'=>2, 'etoile'=>3, 'champior'=>2, 'billball'=>2),
		array('carapacebleue'=>2, 'champiX3'=>4, 'etoile'=>3, 'champior'=>3, 'billball'=>3, 'eclair'=>2)
	);
	if (!$withPow) {
		foreach ($distribution as $i => $tier) {
			if (isset($tier['pow']))
				unset($distribution[$i]['pow']);
		}
	}
	return $distribution;
}

function lounge_point_distribution($playerCount) {
	$distributions = array(
		4 => array(10,7,3,1),
		5 => array(10,7,5,3,1),
		6 => array(10,8,6,4,2,1),
		7 => array(10,8,6,4,3,2,1),
		8 => array(10,8,6,5,4,3,2,1)
	);
	if (isset($distributions[$playerCount]))
		return $distributions[$playerCount];
	$fallback = array();
	for ($i = 0; $i < $playerCount; $i++)
		$fallback[] = max(1, 10 - $i);
	return $fallback;
}

function lounge_build_game_rules($mode, $playerCount, $withPow = true) {
	$rules = array(
		'friendly' => 1,
		'localScore' => 1,
		'minPlayers' => $playerCount,
		'maxPlayers' => $playerCount,
		'itemDistrib' => array(
			'value' => lounge_item_distribution($withPow),
			'name' => 'CTP Distrib',
			// Both required by #link-guidelines: two players may hold a lightning at once,
			// and it is not reserved for last place. Everything else keeps MKPC's defaults,
			// which already match the guidelines' "leave all other categories ticked".
			'lightningx2' => 1,
			'lightninglast' => 0
		),
		'ptDistrib' => array(
			'value' => lounge_point_distribution($playerCount),
			'name' => $playerCount .'p'
		),
		'noBumps' => 1,
		'raceLimit' => LOUNGE_RACES_PER_MATCH,
		'lounge' => 1
	);
	$nbTeams = lounge_mode_team_count($mode);
	if ($nbTeams) {
		$rules['team'] = 1;
		$rules['manualTeams'] = 1;
		$rules['friendlyFire'] = 1;
		$rules['nbTeams'] = $nbTeams;
	}
	return $rules;
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
		'SELECT id, season, tier FROM `mklounge_queues`
		WHERE id="'. intval($queueId) .'" AND status="voting"'
	));
	if (!$queueRow) return null;

	$members = lounge_queue_members($queueId);
	$voteRes = mysql_query(
		'SELECT voted_mode, voted_pow FROM `mklounge_queue_members`
		WHERE queue="'. intval($queueId) .'" AND dropped_at IS NULL'
	);
	$votes = array();
	$powYes = 0;
	$powVoters = 0;
	while ($v = mysql_fetch_array($voteRes)) {
		if ($v['voted_mode'])
			$votes[$v['voted_mode']] = (isset($votes[$v['voted_mode']]) ? $votes[$v['voted_mode']] : 0) + 1;
		$powVoters++;
		if (intval($v['voted_pow']) === 1)
			$powYes++;
	}
	$allowedModes = lounge_allowed_modes(count($members));
	$mode = lounge_tally_vote($votes, $allowedModes);
	// Rule 3h: unanimous agreement only. A player who never voted has not agreed.
	$withPow = ($powVoters > 0 && $powYes === $powVoters);

	global $q;
	$q = mysql_query(
		'UPDATE `mklounge_queues` SET status="launching"
		WHERE id="'. intval($queueId) .'" AND status="voting"'
	);
	if (!mysql_affected_rows())
		return null;

	do {
		$key = rand();
		if (!$key) continue;
		$q = mysql_query('INSERT IGNORE INTO `mkprivgame` SET id="'. $key .'",player=0');
	} while (!mysql_affected_rows());

	$rulesJson = mysql_real_escape_string(json_encode(lounge_build_game_rules($mode, count($members), $withPow)));
	mysql_query(
		'INSERT INTO `mkgameoptions` SET id="'. $key .'", rules="'. $rulesJson .'", public=0'
	);

	mysql_query(
		'UPDATE `mklounge_queues`
		SET status="launched", launched_at=NOW(), privgame_key="'. $key .'"
		WHERE id="'. intval($queueId) .'"'
	);
	mysql_query(
		'INSERT INTO `mklounge_matches`
		(queue, season, tier, privgame_key, mode, pow, started_at)
		VALUES ("'. intval($queueId) .'", "'. intval($queueRow['season']) .'",
				"'. intval($queueRow['tier']) .'", "'. $key .'",
				"'. mysql_real_escape_string($mode) .'", "'. ($withPow ? 1 : 0) .'", NOW())'
	);

	$matchId = mysql_insert_id();
	foreach ($members as $m) {
		mysql_query(
			'INSERT INTO `mklounge_match_players` (`match`, player, perso)
			VALUES ("'. intval($matchId) .'", "'. intval($m['id']) .'", '.
			(is_null($m['perso']) ? 'NULL' : '"'. mysql_real_escape_string($m['perso']) .'"') .')'
		);
	}

	return array('mode' => $mode, 'pow' => $withPow, 'key' => $key, 'multicup_id' => lounge_get_season_multicup());
}

// Teams are picked in-game (manualTeams) and live only in `mkplayers`, a MEMORY table that
// a MySQL restart empties and that the online cleanup drops a few minutes after the room
// goes idle. So they are snapshotted at the end of every race, while the room is certainly
// still there, instead of once at the end of the mogi.
function lounge_snapshot_teams($privgameKey, $course = 0) {
	$room = $course
		? 'INNER JOIN `mkplayers` gp ON gp.id=mp.player AND gp.course="'. intval($course) .'"'
		: 'INNER JOIN `mariokart` c ON c.link="'. intval($privgameKey) .'"
		   INNER JOIN `mkplayers` gp ON gp.id=mp.player AND gp.course=c.id';
	mysql_query(
		'UPDATE `mklounge_match_players` mp
		INNER JOIN `mklounge_matches` m ON m.id=mp.`match` AND m.privgame_key="'. intval($privgameKey) .'"
		'. $room .'
		SET mp.team=gp.team
		WHERE gp.team >= 0'
	);
}

// The lounge link pins minPlayers to the lineup size, so one no-show or one player walking
// out leaves everyone else stuck on "waiting for players" for good. Staff fix that by hand
// today - #link-guidelines tells the host to lower "Minimum number of players" by one - and
// this does the same automatically. It only applies once the join window has closed, so
// nobody is left behind while they are still loading in.
function lounge_relax_room($privgameKey, $playersInRoom) {
	if ($playersInRoom < LOUNGE_MIN_RACE_PLAYERS)
		return false;
	$row = mysql_fetch_array(mysql_query(
		'SELECT o.rules FROM `mkgameoptions` o
		INNER JOIN `mklounge_queues` q ON q.privgame_key=o.id
		WHERE o.id="'. intval($privgameKey) .'" AND q.status="launched"
		AND q.launched_at < (NOW() - INTERVAL '. intval(LOUNGE_JOIN_TIMEOUT_SECONDS) .' SECOND)'
	));
	if (!$row)
		return false;
	$rules = json_decode($row['rules'], true);
	if (!is_array($rules) || !isset($rules['minPlayers']))
		return false;
	if (intval($rules['minPlayers']) <= $playersInRoom)
		return false;
	$rules['minPlayers'] = $playersInRoom;
	// "il est remplacé par un bot": keeping the field at its original size is what makes the
	// point distribution, which was built for the full lineup, still add up. CPUs are left
	// out of mkgamerank by reload.php, so they never reach the rating pass.
	$rules['cpu'] = 1;
	mysql_query(
		'UPDATE `mkgameoptions` SET rules="'. mysql_real_escape_string(json_encode($rules)) .'"
		WHERE id="'. intval($privgameKey) .'"'
	);
	return true;
}

function lounge_room_player_count($privgameKey) {
	$row = mysql_fetch_array(mysql_query(
		'SELECT COUNT(DISTINCT p.id) AS n FROM `mkplayers` p
		INNER JOIN `mariokart` m ON m.id=p.course
		WHERE m.link="'. intval($privgameKey) .'"'
	));
	return $row ? intval($row['n']) : 0;
}

// "si un joueur est déconnecté durant la partie [...] le joueur reçoit un strike". Only
// counted once the join window has closed, so a player who is merely slow to load is not
// struck, and strike_reason doubles as the claim so a walkout is never struck twice.
function lounge_strike_dropouts($privgameKey, $course = 0) {
	$inRoom = $course
		? 'LEFT JOIN `mkplayers` gp ON gp.id=mp.player AND gp.course="'. intval($course) .'"'
		: 'LEFT JOIN (`mkplayers` gp INNER JOIN `mariokart` c ON c.id=gp.course
		   AND c.link="'. intval($privgameKey) .'") ON gp.id=mp.player';
	$missing = mysql_query(
		'SELECT mp.player FROM `mklounge_match_players` mp
		INNER JOIN `mklounge_matches` m ON m.id=mp.`match` AND m.privgame_key="'. intval($privgameKey) .'"
		INNER JOIN `mklounge_queues` q ON q.id=m.queue AND q.status="launched"
			AND q.launched_at < (NOW() - INTERVAL '. intval(LOUNGE_JOIN_TIMEOUT_SECONDS) .' SECOND)
		'. $inRoom .'
		WHERE mp.strike_reason IS NULL AND gp.id IS NULL'
	);
	$players = array();
	while ($row = mysql_fetch_array($missing))
		$players[] = intval($row['player']);

	global $q;
	foreach ($players as $playerId) {
		$q = mysql_query(
			'UPDATE `mklounge_match_players` mp
			INNER JOIN `mklounge_matches` m ON m.id=mp.`match`
				AND m.privgame_key="'. intval($privgameKey) .'"
			SET mp.strike_reason="disconnect"
			WHERE mp.player="'. $playerId .'" AND mp.strike_reason IS NULL'
		);
		if (mysql_affected_rows())
			lounge_add_strike($playerId, 'disconnect');
	}
}

// Keeps a running mogi playable: teams are captured while the room still exists, anyone who
// walked out is struck, and the room is shrunk and topped up with bots so the remaining
// players are never left waiting on someone who is not coming back.
function lounge_maintain_match($privgameKey, $course = 0, $playersInRoom = null) {
	lounge_snapshot_teams($privgameKey, $course);
	lounge_strike_dropouts($privgameKey, $course);
	if (is_null($playersInRoom))
		$playersInRoom = lounge_room_player_count($privgameKey);
	lounge_relax_room($privgameKey, $playersInRoom);
}

// Called from reload.php at the end of every race. The lounge tick only runs from its own
// endpoints, and nobody is sitting on the lounge page while a mogi is being played, so this
// is the heartbeat a match in trouble depends on.
function lounge_race_finished($privgameKey, $course, $playersInRoom) {
	lounge_maintain_match($privgameKey, $course, $playersInRoom);
}

// A launched queue that stops being played has no other way out: it is not finished (fewer
// than 12 races) and not a no-show (some races were played), so without this every member
// stays "already queued" for ever and can never enter ranked again.
//
// The match is voided rather than rated on its partial standings: the points distribution
// assumes a full mogi, and rating half of one is a decision for staff, not a default.
function lounge_abandon_match($queueId) {
	global $q;
	$q = mysql_query(
		'UPDATE `mklounge_queues` SET status="cancelled"
		WHERE id="'. intval($queueId) .'" AND status="launched"'
	);
	if (!mysql_affected_rows())
		return false;
	mysql_query(
		'UPDATE `mklounge_matches` SET ended_at=NOW(), cancelled_reason="abandoned"
		WHERE queue="'. intval($queueId) .'" AND ended_at IS NULL'
	);
	mysql_query(
		'UPDATE `mklounge_queue_members` SET dropped_at=NOW()
		WHERE queue="'. intval($queueId) .'" AND dropped_at IS NULL'
	);
	return true;
}

function lounge_is_lounge_link($privgameKey) {
	return (bool) mysql_fetch_array(mysql_query(
		'SELECT 1 AS ok FROM `mklounge_matches`
		WHERE privgame_key="'. intval($privgameKey) .'" LIMIT 1'
	));
}

function lounge_add_strike($playerId, $reason) {
	mysql_query(
		'INSERT INTO `mklounge_players` (player, season, strikes)
		VALUES ("'. intval($playerId) .'", "'. LOUNGE_CURRENT_SEASON .'", 1)
		ON DUPLICATE KEY UPDATE strikes=strikes+1'
	);
	if (!LOUNGE_STRIKES_BEFORE_BAN)
		return false;

	global $q;
	$q = mysql_query(
		'UPDATE `mklounge_players`
		SET strikes=0, banned_until=(NOW() + INTERVAL '. intval(LOUNGE_BAN_MINUTES) .' MINUTE)
		WHERE player="'. intval($playerId) .'" AND season="'. LOUNGE_CURRENT_SEASON .'"
		AND strikes >= '. intval(LOUNGE_STRIKES_BEFORE_BAN)
	);
	return (bool) mysql_affected_rows();
}

function lounge_match_race_count($privgameKey) {
	$row = mysql_fetch_array(mysql_query(
		'SELECT raceCount FROM `mkgamedata` WHERE game="'. intval($privgameKey) .'"'
	));
	return $row ? intval($row['raceCount']) : 0;
}

function lounge_finish_match($queueId) {
	$queue = mysql_fetch_array(mysql_query(
		'SELECT id, privgame_key FROM `mklounge_queues`
		WHERE id="'. intval($queueId) .'" AND status="launched" AND privgame_key IS NOT NULL'
	));
	if (!$queue)
		return false;

	// Claim the queue before doing any work. lounge_tick() runs on every poll, so two
	// requests can reach this at once; without the claim both would tally the standings
	// and both would apply the rating change, counting the match twice.
	global $q;
	$q = mysql_query(
		'UPDATE `mklounge_queues` SET status="finished"
		WHERE id="'. intval($queueId) .'" AND status="launched"'
	);
	if (!mysql_affected_rows())
		return false;

	$match = mysql_fetch_array(mysql_query(
		'SELECT id FROM `mklounge_matches`
		WHERE queue="'. intval($queueId) .'" AND ended_at IS NULL'
	));
	if (!$match)
		return false;
	$matchId = intval($match['id']);

	// Last chance to catch teams if no race-end snapshot got through; by now the room may
	// already be gone, which is exactly why lounge_race_finished() does it every race.
	lounge_snapshot_teams($queue['privgame_key']);

	$standings = array();
	$getStandings = mysql_query(
		'SELECT r.player, r.pts FROM `mkgamerank` r
		INNER JOIN `mklounge_match_players` mp
			ON mp.player=r.player AND mp.`match`="'. $matchId .'"
		WHERE r.game="'. intval($queue['privgame_key']) .'"
		ORDER BY r.pts DESC, r.player'
	);
	while ($row = mysql_fetch_array($getStandings))
		$standings[] = array('player' => intval($row['player']), 'pts' => intval($row['pts']));

	$position = 0;
	$previousPts = null;
	foreach ($standings as $i => $standing) {
		if (is_null($previousPts) || ($standing['pts'] < $previousPts)) {
			$position = $i + 1;
			$previousPts = $standing['pts'];
		}
		$isWin = ($position === 1) ? 1 : 0;
		mysql_query(
			'UPDATE `mklounge_match_players`
			SET final_score="'. $standing['pts'] .'", final_position="'. $position .'"
			WHERE `match`="'. $matchId .'" AND player="'. $standing['player'] .'"'
		);
		mysql_query(
			'INSERT INTO `mklounge_players` (player, season, games, wins, total_score)
			VALUES ("'. $standing['player'] .'", "'. LOUNGE_CURRENT_SEASON .'", 1, "'. $isWin .'", "'. $standing['pts'] .'")
			ON DUPLICATE KEY UPDATE games=games+1, wins=wins+'. $isWin .', total_score=total_score+'. $standing['pts']
		);
	}

	lounge_apply_mmr($matchId);

	mysql_query('UPDATE `mklounge_matches` SET ended_at=NOW() WHERE id="'. $matchId .'"');
	mysql_query(
		'UPDATE `mklounge_queue_members` SET dropped_at=NOW()
		WHERE queue="'. intval($queueId) .'" AND dropped_at IS NULL'
	);
	return true;
}

function lounge_match_result($privgameKey, $forPlayerId) {
	$match = mysql_fetch_array(mysql_query(
		'SELECT m.id, m.mode, m.ended_at,
			t.label_en AS tier_label_en, t.label_fr AS tier_label_fr
		FROM `mklounge_matches` m
		INNER JOIN `mklounge_tiers` t ON t.id=m.tier
		WHERE m.privgame_key="'. intval($privgameKey) .'"'
	));
	if (!$match)
		return null;
	$participant = mysql_fetch_array(mysql_query(
		'SELECT 1 AS ok FROM `mklounge_match_players`
		WHERE `match`="'. intval($match['id']) .'" AND player="'. intval($forPlayerId) .'"'
	));
	if (!$participant)
		return null;

	$players = array();
	$res = mysql_query(
		'SELECT mp.player, mp.final_score, mp.final_position, mp.mmr_before, mp.mmr_after, mp.mmr_delta, j.nom
		FROM `mklounge_match_players` mp
		INNER JOIN `mkjoueurs` j ON j.id=mp.player
		WHERE mp.`match`="'. intval($match['id']) .'"
		ORDER BY (mp.final_position IS NULL), mp.final_position, j.nom'
	);
	while ($row = mysql_fetch_array($res)) {
		$players[] = array(
			'id' => intval($row['player']),
			'name' => $row['nom'],
			'score' => is_null($row['final_score']) ? null : intval($row['final_score']),
			'position' => is_null($row['final_position']) ? null : intval($row['final_position']),
			'mmr_before' => is_null($row['mmr_before']) ? null : (int) round($row['mmr_before']),
			'mmr_after' => is_null($row['mmr_after']) ? null : (int) round($row['mmr_after']),
			'mmr_delta' => is_null($row['mmr_delta']) ? null : (int) round($row['mmr_delta'])
		);
	}
	return array(
		'id' => intval($match['id']),
		'mode' => $match['mode'],
		'tier_label_en' => $match['tier_label_en'],
		'tier_label_fr' => $match['tier_label_fr'],
		'ended_at' => $match['ended_at'],
		'races' => LOUNGE_RACES_PER_MATCH,
		'players' => $players
	);
}

function lounge_match_joined_players($privgameKey) {
	$joined = array();
	$res = mysql_query(
		'SELECT DISTINCT p.id AS player FROM `mkplayers` p
		INNER JOIN `mariokart` m ON m.id=p.course
		WHERE m.link="'. intval($privgameKey) .'"'
	);
	while ($row = mysql_fetch_array($res))
		$joined[intval($row['player'])] = true;
	return $joined;
}

// Dropping the member is the claim: lounge_tick() is reentrant, and only the caller that
// actually flips dropped_at gets to hand out the strike.
function lounge_strike_no_shows($queueId, $joined) {
	global $q;
	foreach (lounge_queue_members($queueId) as $member) {
		if (isset($joined[$member['id']]))
			continue;
		$q = mysql_query(
			'UPDATE `mklounge_queue_members` SET dropped_at=NOW()
			WHERE queue="'. intval($queueId) .'" AND player="'. intval($member['id']) .'"
			AND dropped_at IS NULL'
		);
		if (!mysql_affected_rows())
			continue;
		lounge_add_strike($member['id'], 'no_show');
		mysql_query(
			'UPDATE `mklounge_match_players` mp
			INNER JOIN `mklounge_matches` m ON m.id=mp.`match`
			SET mp.strike_reason="no_show"
			WHERE m.queue="'. intval($queueId) .'" AND mp.player="'. intval($member['id']) .'"'
		);
	}
}

// Rule 4da penalises the player who did not turn up, not the seven who did - so as long as
// enough of the lineup is in the room, the absentees are struck, the room is shrunk to the
// players actually in it, and the mogi goes ahead. Only a lineup too small to race is voided.
function lounge_handle_join_timeout($queueId) {
	$queue = mysql_fetch_array(mysql_query(
		'SELECT id, privgame_key FROM `mklounge_queues`
		WHERE id="'. intval($queueId) .'" AND status="launched" AND privgame_key IS NOT NULL'
	));
	if (!$queue)
		return false;

	$joined = lounge_match_joined_players($queue['privgame_key']);
	if (count($joined) >= LOUNGE_MIN_RACE_PLAYERS) {
		lounge_strike_no_shows($queueId, $joined);
		lounge_relax_room($queue['privgame_key'], count($joined));
		return true;
	}

	// same claim as lounge_finish_match: only one caller may void the match
	global $q;
	$q = mysql_query(
		'UPDATE `mklounge_queues` SET status="cancelled"
		WHERE id="'. intval($queueId) .'" AND status="launched"'
	);
	if (!mysql_affected_rows())
		return false;

	lounge_strike_no_shows($queueId, $joined);
	mysql_query(
		'UPDATE `mklounge_matches` SET ended_at=NOW(), cancelled_reason="no_show"
		WHERE queue="'. intval($queueId) .'" AND ended_at IS NULL'
	);
	mysql_query(
		'UPDATE `mklounge_queue_members` SET dropped_at=NOW()
		WHERE queue="'. intval($queueId) .'" AND dropped_at IS NULL'
	);
	return true;
}

// Rating model of the production ladder, which runs on Lorenzi's Game Boards under its
// "mk8dx_mmr" scheme. Constants and behaviour are documented, with the numbers this was
// validated against, in .claude/docs/lounge-mmr-and-rules.md.
function lounge_mmr_arity($mode) {
	switch ($mode) {
		case '2v2':     return 2;
		case '3v3':     return 3;
		case '4v4':     return 4;
		case '2v2v2v2': return 2;
		default:        return 1;
	}
}

function lounge_mmr_params($arity) {
	$baselines = array(40, 34, 29, 29, 29, 29);
	$scalings = array(5.6, 5.8, 6.4, 6.4, 6.4, 6.4);
	$index = min(max($arity, 1), count($baselines)) - 1;
	return array($baselines[$index], $scalings[$index]);
}

// Rating change for `rating1` alone. $whoWon is 0 when the first side won, 1 when the
// second did, and 0.5 on a tie.
function lounge_mmr_pair_change($rating1, $rating2, $whoWon, $baseline, $scaling) {
	$loser = ($whoWon > 0.5) ? $rating1 : $rating2;
	$winner = ($whoWon <= 0.5) ? $rating1 : $rating2;
	$gap = max(-9997, $loser - $winner);
	if ($whoWon == 0.5) {
		$amount = 1.5 * $scaling * ($baseline + 1) * pow(pow(pow($gap / 9998, 2), 1 / 3), 2);
		return $amount * (($rating1 < $rating2) ? 1 : -1);
	}
	$amount = 1 + $baseline * pow(1 + $gap / 9998, $scaling);
	return $amount * (($whoWon < 0.5) ? 1 : -1);
}

// $units is a list of array('members' => array(playerId => rating), 'score' => int); in FFA
// every player is their own unit. Each member is compared against every player of every
// opposing unit and averaged over them, then the unit's members are averaged together so
// teammates all move by the same amount.
function lounge_mmr_deltas($units, $arity) {
	list($baseline, $scaling) = lounge_mmr_params($arity);
	$deltas = array();
	foreach ($units as $i => $unit) {
		$memberChanges = array();
		foreach ($unit['members'] as $playerId => $rating) {
			$total = 0;
			$opponents = 0;
			foreach ($units as $j => $other) {
				if ($i === $j)
					continue;
				if ($unit['score'] > $other['score'])
					$whoWon = 0;
				elseif ($unit['score'] < $other['score'])
					$whoWon = 1;
				else
					$whoWon = 0.5;
				foreach ($other['members'] as $otherRating) {
					$total += lounge_mmr_pair_change($rating, $otherRating, $whoWon, $baseline, $scaling);
					$opponents++;
				}
			}
			$memberChanges[$playerId] = $opponents ? ($total / $opponents) : 0;
		}
		$unitDelta = count($memberChanges) ? array_sum($memberChanges) / count($memberChanges) : 0;
		foreach ($unit['members'] as $playerId => $rating)
			$deltas[$playerId] = $unitDelta;
	}
	return $deltas;
}

function lounge_mmr_sql($value) {
	return number_format($value, 6, '.', '');
}

function lounge_apply_mmr($matchId) {
	$match = mysql_fetch_array(mysql_query(
		'SELECT mode FROM `mklounge_matches` WHERE id="'. intval($matchId) .'"'
	));
	if (!$match)
		return false;

	$participants = array();
	$res = mysql_query(
		'SELECT mp.player, mp.team, mp.final_score, p.mmr
		FROM `mklounge_match_players` mp
		LEFT JOIN `mklounge_players` p
			ON p.player=mp.player AND p.season="'. LOUNGE_CURRENT_SEASON .'"
		WHERE mp.`match`="'. intval($matchId) .'" AND mp.mmr_after IS NULL
			AND mp.final_score IS NOT NULL'
	);
	while ($row = mysql_fetch_array($res)) {
		$team = (is_null($row['team']) || intval($row['team']) < 0) ? null : intval($row['team']);
		$participants[] = array(
			'player' => intval($row['player']),
			'team' => $team,
			'score' => intval($row['final_score']),
			'mmr' => is_null($row['mmr']) ? floatval(LOUNGE_DEFAULT_MMR) : floatval($row['mmr'])
		);
	}
	if (count($participants) < 2)
		return false;

	$units = array();
	foreach ($participants as $participant) {
		$key = is_null($participant['team']) ? 'p'. $participant['player'] : 't'. $participant['team'];
		if (!isset($units[$key]))
			$units[$key] = array('members' => array(), 'score' => 0);
		$units[$key]['members'][$participant['player']] = $participant['mmr'];
		$units[$key]['score'] += $participant['score'];
	}
	if (count($units) < 2)
		return false;

	$deltas = lounge_mmr_deltas(array_values($units), lounge_mmr_arity($match['mode']));

	foreach ($participants as $participant) {
		$playerId = $participant['player'];
		$before = $participant['mmr'];
		$after = max(LOUNGE_MMR_MIN, $before + $deltas[$playerId]);
		mysql_query(
			'UPDATE `mklounge_match_players`
			SET mmr_before="'. lounge_mmr_sql($before) .'",
				mmr_after="'. lounge_mmr_sql($after) .'",
				mmr_delta="'. lounge_mmr_sql($after - $before) .'"
			WHERE `match`="'. intval($matchId) .'" AND player="'. $playerId .'"'
		);
		mysql_query(
			'UPDATE `mklounge_players`
			SET mmr="'. lounge_mmr_sql($after) .'", peak_mmr=GREATEST(peak_mmr, "'. lounge_mmr_sql($after) .'")
			WHERE player="'. $playerId .'" AND season="'. LOUNGE_CURRENT_SEASON .'"'
		);
	}
	return true;
}

function lounge_queue_min_players($queueId) {
	$row = mysql_fetch_array(mysql_query(
		'SELECT t.min_players FROM `mklounge_queues` q
		INNER JOIN `mklounge_tiers` t ON t.id=q.tier
		WHERE q.id="'. intval($queueId) .'"'
	));
	if (!$row || !intval($row['min_players']))
		return LOUNGE_DEFAULT_MIN_PLAYERS;
	return intval($row['min_players']);
}

function lounge_update_queue_status($queueId) {
	$queue = mysql_fetch_array(mysql_query(
		'SELECT status FROM `mklounge_queues` WHERE id="'. intval($queueId) .'"'
	));
	if (!$queue) return;
	$count = lounge_active_member_count($queueId);
	$status = $queue['status'];
	$minPlayers = lounge_queue_min_players($queueId);

	if ($status === 'open' && $count >= $minPlayers) {
		mysql_query(
			'UPDATE `mklounge_queues` SET status="locked", locked_at=NOW()
			WHERE id="'. intval($queueId) .'" AND status="open"'
		);
	}
	elseif ($status === 'locked' && $count < $minPlayers) {
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
		lounge_add_strike($row['player'], 'afk');
	}
	foreach ($affected as $queueId => $_) {
		lounge_update_queue_status($queueId);
	}

	$launched = mysql_query(
		'SELECT q.id, q.privgame_key, IFNULL(d.raceCount, 0) AS races,
			(q.launched_at < (NOW() - INTERVAL '. intval(LOUNGE_JOIN_TIMEOUT_SECONDS) .' SECOND)) AS join_timed_out,
			(q.launched_at < (NOW() - INTERVAL '. intval(LOUNGE_MATCH_MAX_MINUTES) .' MINUTE)) AS match_timed_out,
			EXISTS(SELECT 1 FROM `mariokart` c WHERE c.link=q.privgame_key) AS room_alive
		FROM `mklounge_queues` q
		LEFT JOIN `mkgamedata` d ON d.game=q.privgame_key
		WHERE q.status="launched" AND q.privgame_key IS NOT NULL'
	);
	while ($row = mysql_fetch_array($launched)) {
		$races = intval($row['races']);
		if ($races >= LOUNGE_RACES_PER_MATCH)
			lounge_finish_match(intval($row['id']));
		elseif (!$races && $row['join_timed_out'] && !$row['match_timed_out'])
			lounge_handle_join_timeout(intval($row['id']));
		// `mariokart` is a MEMORY table, so the room disappearing - swept by the online
		// cleanup once it goes idle, or emptied by a MySQL restart - is the clearest signal
		// that this mogi is not being played any more.
		elseif ($row['match_timed_out'] || ($races && !$row['room_alive']))
			lounge_abandon_match(intval($row['id']));
		elseif ($races)
			lounge_maintain_match(intval($row['privgame_key']));
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
	// The official rules never penalise a missed vote, so the deadline just falls back to
	// the majority of the players who did vote rather than cancelling on the whole lineup.
	while ($row = mysql_fetch_array($voteDeadlines)) {
		lounge_launch_match(intval($row['id']));
	}
}
