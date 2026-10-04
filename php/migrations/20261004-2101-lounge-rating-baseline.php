<?php
// Ratings edited by hand before edits were recorded - and mogis rated by earlier versions of
// the formula - would be undone by the first recalculation of the season. Each difference
// between the stored history and what the formula gives is written down as the adjustment
// that explains it, so a recalculation starts by reproducing the ratings as they stand.
require_once(__DIR__ .'/../includes/lounge/common.php');

$res = mysql_query(
	'SELECT m.id, m.mode, m.cancelled_reason, mp.player, mp.team, mp.final_score,
		mp.mmr_before, mp.mmr_after, mp.mmr_penalty, mp.mmr_adjust
	FROM `mklounge_matches` m
	INNER JOIN `mklounge_match_players` mp ON mp.`match`=m.id
	WHERE m.season="'. LOUNGE_CURRENT_SEASON .'" AND mp.mmr_after IS NOT NULL
	ORDER BY m.ended_at, m.id, mp.player'
);
$matches = array();
while ($row = mysql_fetch_array($res)) {
	$matchId = intval($row['id']);
	if (!isset($matches[$matchId]))
		$matches[$matchId] = array('mode' => $row['mode'], 'rated' => is_null($row['cancelled_reason']), 'rows' => array());
	$matches[$matchId]['rows'][] = $row;
}

$min = lounge_setting('mmr_min');
$ratings = array();
$lastRow = array();
$adjustments = array();
foreach ($matches as $matchId => $match) {
	// a rating that moved between two mogis was edited by hand: the edit belongs to the mogi
	// before, or to the starting rating when there was none
	foreach ($match['rows'] as $row) {
		$playerId = intval($row['player']);
		$stored = floatval($row['mmr_before']);
		if (isset($ratings[$playerId]) && (abs($ratings[$playerId] - $stored) > 0.0005)) {
			list($previousMatch, $previousAdjust) = $lastRow[$playerId];
			$key = $previousMatch .'-'. $playerId;
			$adjustments[$key] = (isset($adjustments[$key]) ? $adjustments[$key] : $previousAdjust) + ($stored - $ratings[$playerId]);
		}
		$ratings[$playerId] = $stored;
	}
	$participants = array();
	foreach ($match['rows'] as $row) {
		if ($match['rated'] && !is_null($row['final_score']))
			$participants[] = $row;
	}
	$deltas = array();
	if (count($participants) >= 2) {
		$units = array();
		foreach ($participants as $row) {
			$playerId = intval($row['player']);
			$team = (is_null($row['team']) || (intval($row['team']) < 0)) ? null : intval($row['team']);
			$key = is_null($team) ? 'p'. $playerId : 't'. $team;
			if (!isset($units[$key]))
				$units[$key] = array('members' => array(), 'score' => 0);
			$units[$key]['members'][$playerId] = $ratings[$playerId];
			$units[$key]['score'] += intval($row['final_score']);
		}
		if (count($units) >= 2)
			$deltas = lounge_mmr_deltas(array_values($units), lounge_mmr_arity($match['mode']));
	}
	foreach ($match['rows'] as $row) {
		$playerId = intval($row['player']);
		$adjust = floatval($row['mmr_adjust']);
		$computed = max($min, $ratings[$playerId] + (isset($deltas[$playerId]) ? $deltas[$playerId] : 0) + floatval($row['mmr_penalty']) + $adjust);
		$stored = floatval($row['mmr_after']);
		// rated with another version of the formula: what it gave is kept as it was
		if (abs($computed - $stored) > 0.0005) {
			$adjust += $stored - $computed;
			$adjustments[$matchId .'-'. $playerId] = $adjust;
		}
		$ratings[$playerId] = $stored;
		$lastRow[$playerId] = array($matchId, isset($adjustments[$matchId .'-'. $playerId]) ? $adjustments[$matchId .'-'. $playerId] : $adjust);
	}
}

// and an edit made after a player's last mogi
$res = mysql_query('SELECT player, mmr FROM `mklounge_players` WHERE season="'. LOUNGE_CURRENT_SEASON .'"');
while ($row = mysql_fetch_array($res)) {
	$playerId = intval($row['player']);
	$current = floatval($row['mmr']);
	if (!isset($ratings[$playerId])) {
		if (abs($current - floatval(lounge_setting('default_mmr'))) > 0.0005)
			mysql_query('UPDATE `mklounge_players` SET placement=mmr WHERE player="'. $playerId .'" AND season="'. LOUNGE_CURRENT_SEASON .'"');
		continue;
	}
	if (abs($ratings[$playerId] - $current) > 0.0005) {
		list($previousMatch, $previousAdjust) = $lastRow[$playerId];
		$key = $previousMatch .'-'. $playerId;
		$adjustments[$key] = (isset($adjustments[$key]) ? $adjustments[$key] : $previousAdjust) + ($current - $ratings[$playerId]);
	}
}

foreach ($adjustments as $key => $adjust) {
	list($matchId, $playerId) = explode('-', $key);
	mysql_query(
		'UPDATE `mklounge_match_players` SET mmr_adjust="'. lounge_mmr_sql($adjust) .'"
		WHERE `match`="'. intval($matchId) .'" AND player="'. intval($playerId) .'"'
	);
}
