<?php
// A mogi's table as staff edit it: the text format of Lorenzi's table maker, which they
// already paste results in. One block per team, separated by blank lines, a header line
// naming the team, then a line per player - their name, an optional [flag], and their
// score per GP separated by "|", each GP a sum such as 70+20+8. An FFA has no headers.
//
//   A
//   FwaysZedong [fr] 27|33|26
//   mudky 30|28|12
//
//   B
//   ...
require_once(__DIR__ .'/common.php');

function lounge_table_text($match) {
	$lines = array();
	$gpOf = function($player) {
		if (!empty($player['gp_scores']))
			return implode('|', $player['gp_scores']);
		$points = $player['race_points'];
		if (!$points)
			return (string) intval($player['score']);
		$gps = array();
		for ($from = 0; $from < count($points); $from += LOUNGE_RACES_PER_GP)
			$gps[] = array_sum(array_map('intval', array_slice($points, $from, LOUNGE_RACES_PER_GP)));
		return implode('|', $gps);
	};
	$line = function($player) use ($gpOf) {
		return $player['name'] . ($player['country'] ? ' ['. $player['country'] .']' : '') .' '. $gpOf($player);
	};
	if (!$match['teams']) {
		foreach ($match['players'] as $player) {
			if (!is_null($player['score']))
				$lines[] = $line($player);
		}
		return implode("\n", $lines);
	}
	foreach ($match['teams'] as $i => $team) {
		if ($i)
			$lines[] = '';
		$lines[] = chr(ord('A') + $i);
		foreach ($match['players'] as $player) {
			if (($player['team'] === $team['team']) && !is_null($player['score']))
				$lines[] = $line($player);
		}
	}
	return implode("\n", $lines);
}

// Returns array('mode' =>, 'players' => array(array('id', 'team', 'gps', 'score'))) or
// array('errors' => array(...)), each error naming its line.
function lounge_parse_table($text) {
	$errors = array();
	$blocks = array();
	$block = null;
	foreach (preg_split('#\r?\n#', $text) as $i => $raw) {
		$line = trim($raw);
		$number = $i + 1;
		if ($line === '') {
			$block = null;
			continue;
		}
		if (preg_match('#^penalty\b#i', $line)) {
			$errors[] = array($number, 'team_penalty');
			continue;
		}
		if (!preg_match('#^(.+?)(?:\s*\[([a-z]{2})\])?\s+([0-9+\-\s|]+)$#i', $line, $match)) {
			// a line with no scores is a team's header
			if (is_null($block)) {
				$blocks[] = array('header' => true, 'players' => array());
				$block = count($blocks) - 1;
			}
			else
				$errors[] = array($number, 'no_score');
			continue;
		}
		if (is_null($block)) {
			$blocks[] = array('header' => false, 'players' => array());
			$block = count($blocks) - 1;
		}
		$gps = array();
		foreach (explode('|', $match[3]) as $gp) {
			$gp = preg_replace('#\s+#', '', $gp);
			if (!preg_match('#^[+\-]?\d+([+\-]\d+)*$#', $gp)) {
				$errors[] = array($number, 'bad_score');
				continue 2;
			}
			preg_match_all('#[+\-]?\d+#', $gp, $terms);
			$gps[] = array_sum(array_map('intval', $terms[0]));
		}
		$blocks[$block]['players'][] = array('line' => $number, 'name' => trim($match[1]), 'gps' => $gps);
	}

	$teamed = false;
	foreach ($blocks as $b) {
		if ($b['header'])
			$teamed = true;
	}
	$players = array();
	$seen = array();
	$teamSizes = array();
	foreach ($blocks as $t => $b) {
		if ($teamed && !$b['header'] && $b['players'])
			$errors[] = array($b['players'][0]['line'], 'no_team');
		foreach ($b['players'] as $p) {
			$row = mysql_fetch_array(mysql_query(
				'SELECT id FROM `mkjoueurs` WHERE nom="'. mysql_real_escape_string($p['name']) .'" AND deleted=0'
			));
			if (!$row) {
				$errors[] = array($p['line'], 'unknown_player', $p['name']);
				continue;
			}
			$id = intval($row['id']);
			if (isset($seen[$id])) {
				$errors[] = array($p['line'], 'duplicate_player', $p['name']);
				continue;
			}
			$seen[$id] = true;
			$players[] = array('id' => $id, 'team' => $teamed ? count($teamSizes) : null, 'gps' => $p['gps'], 'score' => array_sum($p['gps']));
		}
		if ($teamed && $b['header'])
			$teamSizes[] = count($b['players']);
	}
	if ($teamed && $teamSizes && (count(array_unique($teamSizes)) > 1))
		$errors[] = array(0, 'uneven_teams');
	$units = $teamed ? count(array_filter($teamSizes)) : count($players);
	if (!$errors && ($units < 2))
		$errors[] = array(0, 'too_few');
	if ($errors) {
		usort($errors, function($a, $b) { return $a[0] - $b[0]; });
		return array('errors' => $errors);
	}
	$mode = $teamed ? ($teamSizes[0] .'v'. $teamSizes[0]) : 'FFA';
	if ($teamed && ($teamSizes[0] === 1))
		$mode = 'FFA';
	return array('mode' => $mode, 'players' => $players);
}

// Writes the parsed table over a mogi's standings, or into a new mogi when $matchId is null.
// Ratings are not touched here: the season replay that follows rates the table.
function lounge_save_table($matchId, $table, $tierId = null, $playedAt = null) {
	if (is_null($matchId)) {
		mysql_query(
			'INSERT INTO `mklounge_queues` (season, tier, status, mode)
			VALUES ("'. LOUNGE_CURRENT_SEASON .'", "'. intval($tierId) .'", "finished", "'. mysql_real_escape_string($table['mode']) .'")'
		);
		$queueId = mysql_insert_id();
		$when = $playedAt ? '"'. mysql_real_escape_string($playedAt) .'"' : 'NOW()';
		mysql_query(
			'INSERT INTO `mklounge_matches` (queue, season, tier, privgame_key, mode, started_at, ended_at)
			VALUES ("'. intval($queueId) .'", "'. LOUNGE_CURRENT_SEASON .'", "'. intval($tierId) .'", 0,
				"'. mysql_real_escape_string($table['mode']) .'", '. $when .', '. $when .')'
		);
		$matchId = mysql_insert_id();
	}
	else {
		mysql_query(
			'UPDATE `mklounge_matches` SET mode="'. mysql_real_escape_string($table['mode']) .'"
			WHERE id="'. intval($matchId) .'"'
		);
	}

	$ids = array();
	foreach ($table['players'] as $player)
		$ids[] = $player['id'];
	mysql_query(
		'DELETE FROM `mklounge_match_players`
		WHERE `match`="'. intval($matchId) .'" AND player NOT IN ('. implode(',', $ids) .')'
	);
	$scores = array();
	foreach ($table['players'] as $player)
		$scores[] = $player['score'];
	rsort($scores);
	foreach ($table['players'] as $player) {
		$position = array_search($player['score'], $scores) + 1;
		lounge_upsert_player($player['id'], array(), 'player=player');
		$values = 'team='. (is_null($player['team']) ? 'NULL' : intval($player['team'])) .',
			final_score="'. intval($player['score']) .'", final_position="'. intval($position) .'",
			gp_scores="'. implode('|', array_map('intval', $player['gps'])) .'"';
		mysql_query(
			'INSERT INTO `mklounge_match_players` SET `match`="'. intval($matchId) .'", player="'. intval($player['id']) .'", '. $values .'
			ON DUPLICATE KEY UPDATE '. $values
		);
	}
	return $matchId;
}

function lounge_delete_table($matchId) {
	$match = mysql_fetch_array(mysql_query(
		'SELECT queue, privgame_key FROM `mklounge_matches` WHERE id="'. intval($matchId) .'"'
	));
	if (!$match)
		return false;
	mysql_query('DELETE FROM `mklounge_match_players` WHERE `match`="'. intval($matchId) .'"');
	mysql_query('DELETE FROM `mklounge_matches` WHERE id="'. intval($matchId) .'"');
	// a table typed in by staff has a queue of its own, which nothing else points at
	if (!intval($match['privgame_key']))
		mysql_query('DELETE FROM `mklounge_queues` WHERE id="'. intval($match['queue']) .'"');
	return true;
}
