<?php
define('LOUNGE_DEFAULT_MMR', 500);
define('LOUNGE_CURRENT_SEASON', 1);

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
