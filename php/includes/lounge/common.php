<?php
define('LOUNGE_DEFAULT_MMR', 600);
define('LOUNGE_MMR_MIN', 0);
define('LOUNGE_CURRENT_SEASON', 1);
// Fallback when a tier row carries no min_players of its own.
define('LOUNGE_DEFAULT_MIN_PLAYERS', 4);
define('LOUNGE_QUEUE_READY_THRESHOLD', 8);
define('LOUNGE_AFK_SECONDS', 300);
define('LOUNGE_HEARTBEAT_POLL_SECONDS', 5);
// Rule 3aa: wait 5 minutes after a lineup gathers, to let a 5th-8th player join.
define('LOUNGE_LOCK_WAIT_SECONDS', 300);
define('LOUNGE_VOTE_WAIT_SECONDS', 120);
// Rule 3a: 15 seconds in a list before you may drop out of it.
define('LOUNGE_DROP_DELAY_SECONDS', 15);
// What an indecisive player votes for: counted as no preference, never as a mode.
define('LOUNGE_RANDOM_VOTE', 'Random');
// Discord notifications: the reminder cadence and how often #mllu is rewritten when nothing
// is happening. Off by default so a fresh install and CI never talk to Discord.
define('LOUNGE_DISCORD_ENABLED', 0);
define('LOUNGE_DISCORD_HERE_MINUTES', 30);
define('LOUNGE_DISCORD_MLLU_SECONDS', 30);
// Rule 4i: how long a captain has to make each pick before it is made for them.
define('LOUNGE_DRAFT_PICK_SECONDS', 45);
define('LOUNGE_RACES_PER_MATCH', 12);
define('LOUNGE_STRIKES_BEFORE_BAN', 3);
define('LOUNGE_BAN_MINUTES', 60);
// Rule 4da: 5 minutes to join the room past the designated join time.
define('LOUNGE_JOIN_TIMEOUT_SECONDS', 300);
// "tout les 10-15 min on reçoit un message d'alerte demandant si on est encore dans la
// queue": a tab left open keeps polling for ever, so waiting on the heartbeat alone would
// let a mogi gather around somebody who walked away.
define('LOUNGE_CONFIRM_SECONDS', 600);
define('LOUNGE_CONFIRM_GRACE_SECONDS', 120);
// Access to ranked, agreed with staff on 2026-09-06: 10000 VS points and a 14-day-old
// account. 0 disables either check.
define('LOUNGE_MIN_ACCOUNT_AGE_DAYS', 14);
define('LOUNGE_MIN_VS_POINTS', 10000);
// A mogi is 12 races of ~3 minutes. Past this it is not being played any more, whatever
// the race counter says.
define('LOUNGE_MATCH_MAX_MINUTES', 120);
// How far the room's required player count may be lowered when players fail to join or
// walk out mid-mogi. Below this there is no race worth playing.
define('LOUNGE_MIN_RACE_PLAYERS', 2);

// Everything below is staff-tunable from admin-lounge.php: the ladder is still finding its
// settings, and a deploy per timer is not a workable way to run it. The constants above stay
// the defaults, so an empty `mklounge_settings` behaves exactly as before.
function lounge_settings_schema() {
	return array(
		'default_min_players' => array(
			'default' => LOUNGE_DEFAULT_MIN_PLAYERS, 'min' => 2, 'max' => 12,
			'group' => 'queue', 'unit_en' => 'players', 'unit_fr' => 'joueurs',
			'label_en' => 'Players needed to lock a queue',
			'label_fr' => 'Joueurs requis pour verrouiller une file',
			'help_en' => 'Used when the tier carries no minimum of its own.',
			'help_fr' => 'Utilisé quand le tier n\'a pas son propre minimum.'
		),
		'ready_threshold' => array(
			'default' => LOUNGE_QUEUE_READY_THRESHOLD, 'min' => 2, 'max' => 12,
			'group' => 'queue', 'unit_en' => 'players', 'unit_fr' => 'joueurs',
			'label_en' => 'Lineup size that starts the vote at once',
			'label_fr' => 'Effectif qui lance le vote immédiatement',
			'help_en' => 'A full lineup does not wait out the timer below.',
			'help_fr' => 'Un effectif complet n\'attend pas le chrono ci-dessous.'
		),
		'lock_wait_seconds' => array(
			'default' => LOUNGE_LOCK_WAIT_SECONDS, 'min' => 0, 'max' => 3600,
			'group' => 'queue', 'unit_en' => 'seconds', 'unit_fr' => 'secondes',
			'label_en' => 'Wait after a queue locks, for latecomers',
			'label_fr' => 'Attente après verrouillage, pour les retardataires',
			'help_en' => 'Rule 3aa. A lineup that just hit 4 can still grow to 8.',
			'help_fr' => 'Règle 3aa. Un effectif qui vient d\'atteindre 4 peut encore monter à 8.'
		),
		'vote_wait_seconds' => array(
			'default' => LOUNGE_VOTE_WAIT_SECONDS, 'min' => 10, 'max' => 3600,
			'group' => 'queue', 'unit_en' => 'seconds', 'unit_fr' => 'secondes',
			'label_en' => 'Time to vote for the game mode',
			'label_fr' => 'Temps pour voter le mode de jeu',
			'help_en' => 'When it runs out the majority of the votes cast decides.',
			'help_fr' => 'À l\'expiration, la majorité des votes exprimés décide.'
		),
		'drop_delay_seconds' => array(
			'default' => LOUNGE_DROP_DELAY_SECONDS, 'min' => 0, 'max' => 120,
			'group' => 'queue', 'unit_en' => 'seconds', 'unit_fr' => 'secondes',
			'label_en' => 'Time in a list before you may drop out of it',
			'label_fr' => 'Temps dans une file avant de pouvoir la quitter',
			'help_en' => 'Rule 3a says 15. Stops players flickering in and out of a gathering list.',
			'help_fr' => 'La règle 3a dit 15. Évite les allers-retours dans une file en formation.'
		),
		'afk_seconds' => array(
			'default' => LOUNGE_AFK_SECONDS, 'min' => 30, 'max' => 3600,
			'group' => 'queue', 'unit_en' => 'seconds', 'unit_fr' => 'secondes',
			'label_en' => 'Silence before a queued player is dropped',
			'label_fr' => 'Silence avant qu\'un joueur en file soit retiré',
			'help_en' => 'No heartbeat for this long costs a strike.',
			'help_fr' => 'Aucun signal pendant ce délai coûte un strike.'
		),
		'confirm_seconds' => array(
			'default' => LOUNGE_CONFIRM_SECONDS, 'min' => 60, 'max' => 7200,
			'group' => 'queue', 'unit_en' => 'seconds', 'unit_fr' => 'secondes',
			'label_en' => 'Time in a queue before "are you still there?"',
			'label_fr' => 'Temps en file avant « toujours là ? »',
			'help_en' => 'The spec asks for every 10-15 minutes.',
			'help_fr' => 'La spec demande toutes les 10-15 minutes.'
		),
		'confirm_grace_seconds' => array(
			'default' => LOUNGE_CONFIRM_GRACE_SECONDS, 'min' => 30, 'max' => 3600,
			'group' => 'queue', 'unit_en' => 'seconds', 'unit_fr' => 'secondes',
			'label_en' => 'Grace period to answer it',
			'label_fr' => 'Délai pour y répondre',
			'help_en' => 'No answer removes the player, without a strike.',
			'help_fr' => 'Sans réponse le joueur est retiré, sans strike.'
		),
		'races_per_match' => array(
			'default' => LOUNGE_RACES_PER_MATCH, 'min' => 1, 'max' => 32,
			'group' => 'match', 'unit_en' => 'races', 'unit_fr' => 'courses',
			'label_en' => 'Races per mogi',
			'label_fr' => 'Courses par mogi',
			'help_en' => 'Rule 3a says 12. Changing this only affects new matches.',
			'help_fr' => 'La règle 3a dit 12. Ne change que les parties à venir.'
		),
		'join_timeout_seconds' => array(
			'default' => LOUNGE_JOIN_TIMEOUT_SECONDS, 'min' => 30, 'max' => 3600,
			'group' => 'match', 'unit_en' => 'seconds', 'unit_fr' => 'secondes',
			'label_en' => 'Time to join the room before being marked absent',
			'label_fr' => 'Temps pour rejoindre le salon avant absence',
			'help_en' => 'Rule 4da. Past it the absentee is struck and the room shrinks.',
			'help_fr' => 'Règle 4da. Au-delà, l\'absent prend un strike et le salon rétrécit.'
		),
		'match_max_minutes' => array(
			'default' => LOUNGE_MATCH_MAX_MINUTES, 'min' => 10, 'max' => 600,
			'group' => 'match', 'unit_en' => 'minutes', 'unit_fr' => 'minutes',
			'label_en' => 'Give up on a mogi after',
			'label_fr' => 'Abandonner un mogi au bout de',
			'help_en' => 'Past this it is voided and the lineup released.',
			'help_fr' => 'Au-delà il est annulé et l\'effectif libéré.'
		),
		'min_race_players' => array(
			'default' => LOUNGE_MIN_RACE_PLAYERS, 'min' => 2, 'max' => 8,
			'group' => 'match', 'unit_en' => 'players', 'unit_fr' => 'joueurs',
			'label_en' => 'Smallest lineup still worth racing',
			'label_fr' => 'Effectif minimum pour jouer quand même',
			'help_en' => 'Below this a mogi is voided rather than played with bots.',
			'help_fr' => 'En dessous, le mogi est annulé plutôt que joué avec des bots.'
		),
		'strikes_before_ban' => array(
			'default' => LOUNGE_STRIKES_BEFORE_BAN, 'min' => 0, 'max' => 10,
			'group' => 'sanctions', 'unit_en' => 'strikes', 'unit_fr' => 'strikes',
			'label_en' => 'Strikes before a ranked ban',
			'label_fr' => 'Strikes avant un bannissement du classé',
			'help_en' => 'Rule 2b says 3. Set to 0 to never ban automatically.',
			'help_fr' => 'La règle 2b dit 3. Mettre 0 pour ne jamais bannir automatiquement.'
		),
		'ban_minutes' => array(
			'default' => LOUNGE_BAN_MINUTES, 'min' => 1, 'max' => 525600,
			'group' => 'sanctions', 'unit_en' => 'minutes', 'unit_fr' => 'minutes',
			'label_en' => 'How long that ban lasts',
			'label_fr' => 'Durée de ce bannissement',
			'help_en' => 'Rule 2b says 7 days (10080) for a first offence.',
			'help_fr' => 'La règle 2b dit 7 jours (10080) à la première infraction.'
		),
		'min_vs_points' => array(
			'default' => LOUNGE_MIN_VS_POINTS, 'min' => 0, 'max' => 1000000,
			'group' => 'sanctions', 'unit_en' => 'VS points', 'unit_fr' => 'points VS',
			'label_en' => 'Online (VS) points required to enter ranked',
			'label_fr' => 'Points en ligne (VS) requis pour le classé',
			'help_en' => 'Points from normal online play, not from ranked. 0 disables the check.',
			'help_fr' => 'Points du mode en ligne normal, pas du classé. 0 désactive la vérification.'
		),
		'min_account_age_days' => array(
			'default' => LOUNGE_MIN_ACCOUNT_AGE_DAYS, 'min' => 0, 'max' => 365,
			'group' => 'sanctions', 'unit_en' => 'days', 'unit_fr' => 'jours',
			'label_en' => 'Account age required to enter ranked',
			'label_fr' => 'Ancienneté du compte requise pour le classé',
			'help_en' => '0 disables the check.',
			'help_fr' => '0 désactive la vérification.'
		),
		'discord_enabled' => array(
			'default' => LOUNGE_DISCORD_ENABLED, 'min' => 0, 'max' => 1,
			'group' => 'discord', 'unit_en' => '1 = on', 'unit_fr' => '1 = activé',
			'label_en' => 'Post lounge notifications to Discord',
			'label_fr' => 'Publier les notifications du lounge sur Discord',
			'help_en' => 'Needs the bot credentials in php/includes/config/discord.php.',
			'help_fr' => 'Nécessite les identifiants du bot dans php/includes/config/discord.php.'
		),
		'discord_here_minutes' => array(
			'default' => LOUNGE_DISCORD_HERE_MINUTES, 'min' => 1, 'max' => 1440,
			'group' => 'discord', 'unit_en' => 'minutes', 'unit_fr' => 'minutes',
			'label_en' => 'Repeat the @here reminder every',
			'label_fr' => 'Répéter le rappel @here toutes les',
			'help_en' => 'Only while at least one player is waiting in that tier.',
			'help_fr' => 'Uniquement tant qu\'au moins un joueur attend dans ce tier.'
		),
		'discord_mllu_seconds' => array(
			'default' => LOUNGE_DISCORD_MLLU_SECONDS, 'min' => 5, 'max' => 600,
			'group' => 'discord', 'unit_en' => 'seconds', 'unit_fr' => 'secondes',
			'label_en' => 'Refresh the #mllu summary at most every',
			'label_fr' => 'Rafraîchir le récapitulatif #mllu au plus toutes les',
			'help_en' => 'A queue change rewrites it immediately whatever this says.',
			'help_fr' => 'Un changement dans une file le réécrit immédiatement quoi qu\'il arrive.'
		),
		'draft_pick_seconds' => array(
			'default' => LOUNGE_DRAFT_PICK_SECONDS, 'min' => 10, 'max' => 300,
			'group' => 'queue', 'unit_en' => 'seconds', 'unit_fr' => 'secondes',
			'label_en' => 'Time a captain has for each pick',
			'label_fr' => 'Temps dont dispose un capitaine pour chaque choix',
			'help_en' => 'On a timeout the highest-rated players left are picked for them.',
			'help_fr' => 'En cas d\'expiration, les joueurs les mieux classés restants sont choisis à sa place.'
		),
		'default_mmr' => array(
			'default' => LOUNGE_DEFAULT_MMR, 'min' => 0, 'max' => 10000,
			'group' => 'rating', 'unit_en' => 'MMR', 'unit_fr' => 'MMR',
			'label_en' => 'Starting rating for a new player',
			'label_fr' => 'Classement de départ d\'un nouveau joueur',
			'help_en' => 'Only applies to players who have not played yet.',
			'help_fr' => 'Ne concerne que les joueurs n\'ayant pas encore joué.'
		),
		'mmr_min' => array(
			'default' => LOUNGE_MMR_MIN, 'min' => 0, 'max' => 10000,
			'group' => 'rating', 'unit_en' => 'MMR', 'unit_fr' => 'MMR',
			'label_en' => 'Rating floor',
			'label_fr' => 'Plancher du classement',
			'help_en' => 'A player can never drop below this.',
			'help_fr' => 'Un joueur ne peut jamais descendre en dessous.'
		)
	);
}

function lounge_setting($name) {
	global $loungeSettingsCache;
	$schema = lounge_settings_schema();
	if (!isset($schema[$name]))
		return null;
	if (!isset($loungeSettingsCache)) {
		$loungeSettingsCache = array();
		$res = mysql_query('SELECT name, value FROM `mklounge_settings`');
		while ($row = mysql_fetch_array($res))
			$loungeSettingsCache[$row['name']] = $row['value'];
	}
	if (!isset($loungeSettingsCache[$name]))
		return intval($schema[$name]['default']);
	// A row left over from a renamed or retuned setting must never widen a bound.
	return max($schema[$name]['min'], min($schema[$name]['max'], intval($loungeSettingsCache[$name])));
}

function lounge_set_setting($name, $value) {
	$schema = lounge_settings_schema();
	if (!isset($schema[$name]))
		return false;
	$value = max($schema[$name]['min'], min($schema[$name]['max'], intval($value)));
	mysql_query(
		'INSERT INTO `mklounge_settings` (name, value) VALUES ("'. mysql_real_escape_string($name) .'", "'. $value .'")
		ON DUPLICATE KEY UPDATE value="'. $value .'"'
	);
	global $loungeSettingsCache;
	$loungeSettingsCache[$name] = $value;
	return true;
}

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
		'mmr' => lounge_setting('default_mmr'),
		'peak_mmr' => lounge_setting('default_mmr'),
		'games' => 0,
		'wins' => 0,
		'total_score' => 0,
		'avg_score' => null,
		'strikes' => 0,
		'banned_until' => null,
		'placed' => 0,
		'rank' => lounge_rank_for_mmr(lounge_setting('default_mmr'))
	);
}

function lounge_access_error($playerId) {
	$row = mysql_fetch_array(mysql_query(
		'SELECT j.banned, j.deleted, j.pts_vs,
			DATEDIFF(NOW(), p.sub_date) AS account_age
		FROM `mkjoueurs` j
		LEFT JOIN `mkprofiles` p ON p.id=j.id
		WHERE j.id="'. intval($playerId) .'"'
	));
	if (!$row || $row['deleted'])
		return 'no_account';
	if ($row['banned'])
		return 'site_banned';
	$minAge = lounge_setting('min_account_age_days');
	if ($minAge && !is_null($row['account_age']) && intval($row['account_age']) < $minAge)
		return 'account_too_new';
	if (intval($row['pts_vs']) < lounge_setting('min_vs_points'))
		return 'not_enough_points';
	if (!lounge_has_accepted_rules($playerId))
		return 'rules_not_accepted';
	return null;
}

// Staff want every player to have seen the rules once before their first queue, the way any
// terms-of-use checkbox works. Recorded per season, so a new season asks again.
function lounge_has_accepted_rules($playerId) {
	return (bool) mysql_fetch_array(mysql_query(
		'SELECT 1 AS ok FROM `mklounge_players`
		WHERE player="'. intval($playerId) .'" AND season="'. LOUNGE_CURRENT_SEASON .'"
		AND rules_accepted_at IS NOT NULL'
	));
}

function lounge_accept_rules($playerId) {
	mysql_query(
		'INSERT INTO `mklounge_players` (player, season, rules_accepted_at)
		VALUES ("'. intval($playerId) .'", "'. LOUNGE_CURRENT_SEASON .'", NOW())
		ON DUPLICATE KEY UPDATE rules_accepted_at=IFNULL(rules_accepted_at, NOW())'
	);
}

function lounge_access_requirements() {
	return array(
		'min_vs_points' => lounge_setting('min_vs_points'),
		'min_account_age_days' => lounge_setting('min_account_age_days')
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
			COALESCE(p.mmr, '. lounge_setting('default_mmr') .') AS mmr
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
			GREATEST(0, UNIX_TIMESTAMP(q.locked_at) + '. intval(lounge_setting('lock_wait_seconds')) .' - UNIX_TIMESTAMP(NOW())) AS lock_seconds_left,
			GREATEST(0, UNIX_TIMESTAMP(q.ready_at) + '. intval(lounge_setting('vote_wait_seconds')) .' - UNIX_TIMESTAMP(NOW())) AS vote_seconds_left
		FROM `mklounge_queues` q
		INNER JOIN `mklounge_tiers` t ON t.id=q.tier
		WHERE q.id="'. intval($queueId) .'"'
	));
	if (!$queue) return null;
	$members = lounge_queue_members($queueId);
	$confirmDue = false;
	$confirmSecondsLeft = null;
	$dropSecondsLeft = 0;
	if ($forPlayerId) {
		$me = mysql_fetch_array(mysql_query(
			'SELECT (confirmed_at < (NOW() - INTERVAL '. intval(lounge_setting('confirm_seconds')) .' SECOND)) AS due,
				GREATEST(0, UNIX_TIMESTAMP(confirmed_at) + '. intval(lounge_setting('confirm_seconds') + lounge_setting('confirm_grace_seconds')) .'
					- UNIX_TIMESTAMP(NOW())) AS seconds_left,
				GREATEST(0, UNIX_TIMESTAMP(joined_at) + '. intval(lounge_setting('drop_delay_seconds')) .'
					- UNIX_TIMESTAMP(NOW())) AS drop_wait
			FROM `mklounge_queue_members`
			WHERE queue="'. intval($queueId) .'" AND player="'. intval($forPlayerId) .'" AND dropped_at IS NULL'
		));
		if ($me) {
			$confirmDue = (bool) intval($me['due']);
			$confirmSecondsLeft = intval($me['seconds_left']);
			$dropSecondsLeft = intval($me['drop_wait']);
		}
	}
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
		'lock_threshold' => intval($queue['min_players']) ? intval($queue['min_players']) : lounge_setting('default_min_players'),
		'ready_threshold' => lounge_setting('ready_threshold'),
		'drop_seconds_left' => $dropSecondsLeft,
		'confirm_due' => $confirmDue,
		'confirm_seconds_left' => $confirmSecondsLeft,
		'lock_wait_seconds' => lounge_setting('lock_wait_seconds'),
		'vote_wait_seconds' => lounge_setting('vote_wait_seconds')
	);
}

// The ladder's mode names are team *sizes*, not team counts: "2v2" is a lineup split into
// pairs, which is 2 teams at 4 players and 4 teams at 8 - MogiBot offers it at every even
// lineup and names it 2v2 throughout.
function lounge_mode_team_size($mode) {
	switch ($mode) {
		case '2v2': return 2;
		case '3v3': return 3;
		case '4v4': return 4;
		default:    return 0;
	}
}

// Rule 3a: vote 1 FFA, 2 2v2, 3 3v3, 4 4v4. A mode is on the ballot when the lineup divides
// into at least two teams of that size, so 6 players get FFA/2v2/3v3 and 8 get FFA/2v2/4v4.
function lounge_allowed_modes($playerCount) {
	$modes = array('FFA');
	foreach (array('2v2', '3v3', '4v4') as $mode) {
		$size = lounge_mode_team_size($mode);
		if (($playerCount >= $size * 2) && (($playerCount % $size) === 0))
			$modes[] = $mode;
	}
	return $modes;
}

function lounge_mode_team_count($mode, $playerCount) {
	$size = lounge_mode_team_size($mode);
	return $size ? (int) ($playerCount / $size) : 0;
}

// so it is stripped out unless the vote was unanimous.
function lounge_item_distribution() {
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

function lounge_build_game_rules($mode, $playerCount) {
	$rules = array(
		'friendly' => 1,
		'localScore' => 1,
		'minPlayers' => $playerCount,
		'maxPlayers' => $playerCount,
		'itemDistrib' => array(
			'value' => lounge_item_distribution(),
			'name' => 'CTP Distrib',
			// #link-guidelines: untick "Prevent 2 players from having a lightning item" and
			// nothing else, so every other flag keeps MKPC's default.
			'lightningx2' => 1
		),
		'ptDistrib' => array(
			'value' => lounge_point_distribution($playerCount),
			'name' => $playerCount .'p'
		),
		'noBumps' => 1,
		'raceLimit' => lounge_setting('races_per_match'),
		'lounge' => 1
	);
	$nbTeams = lounge_mode_team_count($mode, $playerCount);
	if ($nbTeams) {
		$rules['team'] = 1;
		$rules['manualTeams'] = 1;
		$rules['friendlyFire'] = 1;
		$rules['nbTeams'] = $nbTeams;
	}
	return $rules;
}

// A "Random" vote is a vote for nothing in particular, so it only decides the outcome when
// nothing else does: it is dropped from the tally and the winner is drawn from the modes that
// tied on top. With no real votes at all that is a draw between every allowed mode.
function lounge_tally_vote($votes, $allowedModes) {
	unset($votes[LOUNGE_RANDOM_VOTE]);
	$bestCount = 0;
	foreach ($allowedModes as $mode) {
		$c = isset($votes[$mode]) ? intval($votes[$mode]) : 0;
		if ($c > $bestCount)
			$bestCount = $c;
	}
	$tied = array();
	foreach ($allowedModes as $mode) {
		$c = isset($votes[$mode]) ? intval($votes[$mode]) : 0;
		if ($c === $bestCount)
			$tied[] = $mode;
	}
	return $tied[array_rand($tied)];
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
	$q = mysql_query(
		'UPDATE `mklounge_queues` SET status="launching"
		WHERE id="'. intval($queueId) .'" AND status="voting"'
	);
	if (!mysql_affected_rows())
		return null;

	// The link belongs to the oldest account in the lineup. Staff wanted a real owner rather
	// than nobody, so that whoever is most likely to know the system can repair the room -
	// and picking by account id makes it the same person every time rather than a race.
	$owner = 0;
	foreach ($members as $m) {
		if (!$owner || ($m['id'] < $owner))
			$owner = $m['id'];
	}
	do {
		$key = rand();
		if (!$key) continue;
		$q = mysql_query('INSERT IGNORE INTO `mkprivgame` SET id="'. $key .'",player="'. intval($owner) .'"');
	} while (!mysql_affected_rows());

	$rulesJson = mysql_real_escape_string(json_encode(lounge_build_game_rules($mode, count($members))));
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
		(queue, season, tier, privgame_key, mode, started_at)
		VALUES ("'. intval($queueId) .'", "'. intval($queueRow['season']) .'",
				"'. intval($queueRow['tier']) .'", "'. $key .'",
				"'. mysql_real_escape_string($mode) .'", NOW())'
	);

	$matchId = mysql_insert_id();
	foreach ($members as $m) {
		mysql_query(
			'INSERT INTO `mklounge_match_players` (`match`, player, perso)
			VALUES ("'. intval($matchId) .'", "'. intval($m['id']) .'", '.
			(is_null($m['perso']) ? 'NULL' : '"'. mysql_real_escape_string($m['perso']) .'"') .')'
		);
	}

	return array('mode' => $mode, 'key' => $key, 'multicup_id' => lounge_get_season_multicup());
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
	if ($playersInRoom < lounge_setting('min_race_players'))
		return false;
	$row = mysql_fetch_array(mysql_query(
		'SELECT o.rules FROM `mkgameoptions` o
		INNER JOIN `mklounge_queues` q ON q.privgame_key=o.id
		WHERE o.id="'. intval($privgameKey) .'" AND q.status="launched"
		AND q.launched_at < (NOW() - INTERVAL '. intval(lounge_setting('join_timeout_seconds')) .' SECOND)'
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
			AND q.launched_at < (NOW() - INTERVAL '. intval(lounge_setting('join_timeout_seconds')) .' SECOND)
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
	if (!lounge_setting('strikes_before_ban'))
		return false;

	global $q;
	$q = mysql_query(
		'UPDATE `mklounge_players`
		SET strikes=0, banned_until=(NOW() + INTERVAL '. intval(lounge_setting('ban_minutes')) .' MINUTE)
		WHERE player="'. intval($playerId) .'" AND season="'. LOUNGE_CURRENT_SEASON .'"
		AND strikes >= '. intval(lounge_setting('strikes_before_ban'))
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
		'races' => lounge_setting('races_per_match'),
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
	if (count($joined) >= lounge_setting('min_race_players')) {
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
	$size = lounge_mode_team_size($mode);
	return $size ? $size : 1;
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
			'mmr' => is_null($row['mmr']) ? floatval(lounge_setting('default_mmr')) : floatval($row['mmr'])
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
		$after = max(lounge_setting('mmr_min'), $before + $deltas[$playerId]);
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
		return lounge_setting('default_min_players');
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
	$cutoff = intval(lounge_setting('afk_seconds'));
	$afkRes = mysql_query(
		'SELECT m.queue, m.player FROM `mklounge_queue_members` m
		INNER JOIN `mklounge_queues` q ON q.id=m.queue
		WHERE m.dropped_at IS NULL
		AND m.last_heartbeat < (NOW() - INTERVAL '. $cutoff .' SECOND)
		AND q.status IN ("open","locked")'
	);
	$unconfirmed = mysql_query(
		'SELECT m.queue, m.player FROM `mklounge_queue_members` m
		INNER JOIN `mklounge_queues` q ON q.id=m.queue
		WHERE m.dropped_at IS NULL
		AND m.confirmed_at < (NOW() - INTERVAL '. intval(lounge_setting('confirm_seconds') + lounge_setting('confirm_grace_seconds')) .' SECOND)
		AND q.status IN ("open","locked")'
	);
	$stale = array();
	while ($row = mysql_fetch_array($unconfirmed)) {
		$stale[intval($row['queue'])] = true;
		// left the queue rather than misbehaved, so no strike - the spec only removes them
		mysql_query(
			'UPDATE `mklounge_queue_members` SET dropped_at=NOW()
			WHERE queue="'. intval($row['queue']) .'" AND player="'. intval($row['player']) .'"
			AND dropped_at IS NULL'
		);
	}
	foreach ($stale as $queueId => $_)
		lounge_update_queue_status($queueId);

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
			(q.launched_at < (NOW() - INTERVAL '. intval(lounge_setting('join_timeout_seconds')) .' SECOND)) AS join_timed_out,
			(q.launched_at < (NOW() - INTERVAL '. intval(lounge_setting('match_max_minutes')) .' MINUTE)) AS match_timed_out,
			EXISTS(SELECT 1 FROM `mariokart` c WHERE c.link=q.privgame_key) AS room_alive
		FROM `mklounge_queues` q
		LEFT JOIN `mkgamedata` d ON d.game=q.privgame_key
		WHERE q.status="launched" AND q.privgame_key IS NOT NULL'
	);
	while ($row = mysql_fetch_array($launched)) {
		$races = intval($row['races']);
		if ($races >= lounge_setting('races_per_match'))
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
		AND locked_at < (NOW() - INTERVAL '. intval(lounge_setting('lock_wait_seconds')) .' SECOND)'
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
			HAVING COUNT(*) >= '. intval(lounge_setting('ready_threshold')) .'
		)'
	);
	while ($row = mysql_fetch_array($readyToLaunch)) {
		lounge_start_voting(intval($row['id']));
	}

	require_once(__DIR__ .'/discord.php');
	lounge_discord_tick();

	$voteDeadlines = mysql_query(
		'SELECT id FROM `mklounge_queues`
		WHERE status="voting"
		AND ready_at IS NOT NULL
		AND ready_at < (NOW() - INTERVAL '. intval(lounge_setting('vote_wait_seconds')) .' SECOND)'
	);
	// The official rules never penalise a missed vote, so the deadline just falls back to
	// the majority of the players who did vote rather than cancelling on the whole lineup.
	while ($row = mysql_fetch_array($voteDeadlines)) {
		lounge_launch_match(intval($row['id']));
	}
}
