<?php
header('Content-Type: application/json');
include('../../includes/session.php');
if (!$id) {
	echo json_encode(array('error' => 'auth'));
	exit;
}
include('../../includes/initdb.php');
include('../../includes/lounge/common.php');

$limit = isset($_POST['limit']) ? intval($_POST['limit']) : 50;
if ($limit < 1) $limit = 1;
if ($limit > 200) $limit = 200;

$players = array();
$place = 0;
$previousMmr = null;
$getPlayers = mysql_query(
	'SELECT p.player, p.mmr, p.peak_mmr, p.games, p.wins, p.total_score, j.nom
	FROM `mklounge_players` p
	INNER JOIN `mkjoueurs` j ON j.id=p.player
	WHERE p.season="'. LOUNGE_CURRENT_SEASON .'" AND p.games>0 AND j.deleted=0
	ORDER BY p.mmr DESC, p.player
	LIMIT '. $limit
);
while ($row = mysql_fetch_array($getPlayers)) {
	$mmr = intval($row['mmr']);
	if (is_null($previousMmr) || ($mmr < $previousMmr)) {
		$place = count($players) + 1;
		$previousMmr = $mmr;
	}
	$games = intval($row['games']);
	$players[] = array(
		'place' => $place,
		'id' => intval($row['player']),
		'name' => $row['nom'],
		'mmr' => $mmr,
		'peak_mmr' => intval($row['peak_mmr']),
		'games' => $games,
		'wins' => intval($row['wins']),
		'avg_score' => $games ? round(intval($row['total_score']) / $games, 1) : null,
		'rank' => lounge_rank_for_mmr($mmr)
	);
}

echo json_encode(array(
	'season' => intval(LOUNGE_CURRENT_SEASON),
	'me' => intval($id),
	'players' => $players
));
mysql_close();
