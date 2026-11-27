<?php
// Staff's table editor: load a mogi as text, preview what a change would do to the ratings,
// save it, create a table that was not played on the site, or delete one. Every change is a
// replay of the season, so a preview is that replay run in a transaction that is rolled back.
header('Content-Type: application/json');
include('../../includes/session.php');
if (!$id) {
	echo json_encode(array('error' => 'auth'));
	exit;
}
// escape_all.php would turn the table's line breaks into backslashes
$tableText = isset($_POST['text']) ? $_POST['text'] : null;
include('../../includes/initdb.php');
include('../../includes/lounge/tables.php');
require_once('../../includes/getRights.php');
require_once('../../includes/utils-logs.php');
if (!hasRight('lounge')) {
	echo json_encode(array('error' => 'forbidden'));
	mysql_close();
	exit;
}

function respond($data) {
	echo json_encode($data);
	mysql_close();
	exit;
}

function tierOptions() {
	$tiers = array();
	$res = mysql_query('SELECT id, label FROM `mklounge_tiers` ORDER BY ordering');
	while ($row = mysql_fetch_array($res))
		$tiers[] = array('id' => intval($row['id']), 'label' => $row['label']);
	return $tiers;
}

// An empty field clears what staff had set; anything else is a number.
function editedValue($name) {
	if (!isset($_POST[$name]) || is_array($_POST[$name]))
		return array(false, null);
	$value = trim($_POST[$name]);
	if ($value === '')
		return array(true, null);
	if (!is_numeric($value))
		return array(false, null);
	return array(true, floatval($value));
}

// A mogi still being played belongs to its room: its lineup is who may race and who gets
// substituted, so staff edit it once it has ended. Read under the ratings lock, which
// lounge_finish_match() holds until it sets ended_at.
function matchRunning($matchId) {
	$row = mysql_fetch_array(mysql_query('SELECT ended_at FROM `mklounge_matches` WHERE id="'. intval($matchId) .'"'));
	return $row && is_null($row['ended_at']);
}

$action = isset($_POST['action']) ? $_POST['action'] : '';
$matchId = !empty($_POST['match']) ? intval($_POST['match']) : null;
$match = $matchId ? lounge_match_payload($matchId) : null;
if ($matchId && !$match)
	respond(array('error' => 'not_found'));

if ($action === 'load') {
	respond(array(
		'text' => $match ? lounge_table_text($match) : '',
		'tiers' => tierOptions(),
		'match' => $match
	));
}

if ($action === 'delete') {
	if (!$match)
		respond(array('error' => 'not_found'));
	lounge_lock_ratings();
	if (matchRunning($matchId)) {
		lounge_unlock_ratings();
		respond(array('errors' => array(array(0, 'match_running'))));
	}
	$text = lounge_table_text($match);
	lounge_delete_table($matchId);
	lounge_recompute_season();
	lounge_unlock_ratings();
	insertLog($id, 'LoungeMatchDelete '. $matchId, array('mode' => $match['mode'], 'tier' => $match['tier_label'], 'ended_at' => $match['ended_at'], 'table' => $text));
	respond(array('deleted' => true));
}

if (($action !== 'preview') && ($action !== 'save'))
	respond(array('error' => 'action_required'));

$table = lounge_parse_table((string) $tableText);
if (isset($table['errors']))
	respond(array('errors' => $table['errors']));
$tierId = null;
$playedAt = null;
if (!$match) {
	$tierId = isset($_POST['tier']) ? intval($_POST['tier']) : 0;
	if (!mysql_fetch_array(mysql_query('SELECT 1 FROM `mklounge_tiers` WHERE id="'. $tierId .'"')))
		respond(array('errors' => array(array(0, 'tier_required'))));
	if (!empty($_POST['played_at'])) {
		$playedAt = str_replace('T', ' ', $_POST['played_at']);
		if (!preg_match('#^\d{4}-\d{2}-\d{2} \d{2}:\d{2}(:\d{2})?$#', $playedAt) || (strtotime($playedAt) > time()))
			respond(array('errors' => array(array(0, 'bad_date'))));
	}
}

// The editor shows ratings rounded and sends every field back, so a field left as it was
// arrives as the rounded value: keep what is stored rather than rounding it away.
$storedAdjusts = array();
$storedPlacements = array();
if ($match) {
	$res = mysql_query('SELECT player, mmr_adjust FROM `mklounge_match_players` WHERE `match`="'. intval($matchId) .'"');
	while ($row = mysql_fetch_array($res))
		$storedAdjusts[intval($row['player'])] = $row['mmr_adjust'];
}
// a placement belongs to the player, so it is shown on whichever table they are first rated
// in - including one being added
$tablePlayers = array();
foreach ($table['players'] as $player)
	$tablePlayers[] = intval($player['id']);
if ($tablePlayers) {
	$res = mysql_query(
		'SELECT player, placement FROM `mklounge_players`
		WHERE season="'. LOUNGE_CURRENT_SEASON .'" AND player IN ('. implode(',', $tablePlayers) .')'
	);
	while ($row = mysql_fetch_array($res))
		$storedPlacements[intval($row['player'])] = $row['placement'];
}
function untouched($value, $stored) {
	return !is_null($value) && !is_null($stored) && (round(floatval($stored)) == $value);
}

mysql_query('START TRANSACTION');
lounge_lock_ratings();
if ($match && matchRunning($matchId)) {
	mysql_query('ROLLBACK');
	lounge_unlock_ratings();
	respond(array('errors' => array(array(0, 'match_running'))));
}
$savedId = lounge_save_table($matchId, $table, $tierId, $playedAt);
$adjustments = array();
$placements = array();
foreach ($table['players'] as $player) {
	list($given, $adjust) = editedValue('adjust_'. $player['id']);
	if ($given && !untouched($adjust, isset($storedAdjusts[$player['id']]) ? $storedAdjusts[$player['id']] : null))
		$adjustments[$player['id']] = $adjust;
	list($given, $placement) = editedValue('placement_'. $player['id']);
	if ($given && !untouched($placement, isset($storedPlacements[$player['id']]) ? $storedPlacements[$player['id']] : null))
		$placements[$player['id']] = is_null($placement) ? null : max(lounge_setting('mmr_min'), $placement);
}
lounge_edit_ratings($savedId, $adjustments, $placements);
$result = lounge_match_payload($savedId);
if ($action === 'preview') {
	mysql_query('ROLLBACK');
	lounge_unlock_ratings();
	respond(array('preview' => true, 'match' => $result));
}
mysql_query('COMMIT');
lounge_unlock_ratings();
insertLog($id, ($match ? 'LoungeMatchEdit ' : 'LoungeMatchCreate ') . $savedId, array('table' => (string) $tableText));
respond(array('saved' => true, 'match' => $result));
