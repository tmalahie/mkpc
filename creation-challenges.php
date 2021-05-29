<?php
if (!isset($id)) {
	include('session.php');
	$unsetId = true;
}
$challenges = array(
	'mcup' => array(),
	'cup' => array(),
	'track' => array()
);
$clRewards = array();
$clPayloadParams = array('winners' => true, 'utf8' => true, 'id' => $id);
$clId = 0;
if (isset($unsetId)) {
	unset($id);
	unset($unsetId);
}
function addCircuitChallenges($table,$circuitId,$circuitName,&$params,$main=true) {
	global $clId, $challenges, $clRewards;
	switch ($table) {
	case 'mkmcups':
		$challengeType = 'mcup';
		break;
	case 'mkcups':
		$challengeType = 'cup';
		break;
	default:
		$challengeType = 'track';
	}
	if (!isset($challenges[$challengeType][$circuitId])) {
		$clCreations = listCircuitChallenges($table,$circuitId,$params);
		if (isset($clCreations['id'])) {
			if ($main) {
				$clId = $clCreations['id'];
				$clRewards = listClRewards($clId);
			}
			$cList = $clCreations['list'];
			if (!empty($cList))
				$challenges[$challengeType][$circuitId] = array('id' => $clCreations['id'], 'track' => $circuitId, 'name' => $circuitName, 'main' => $main, 'list' => $cList);
		}
	}
}
function listCircuitChallenges($table, $circuitId,&$params) {
	if ($getClRace = mysql_fetch_array(mysql_query('SELECT id FROM `mkclrace` WHERE type="'. $table .'" AND circuit='. $circuitId))) {
		return array(
			'id' => $getClRace['id'],
			'list' => listChallenges($getClRace['id'], $params)
		);
	}
	return array('list' => array());
}
function listClRewards($clId) {
	global $clPayloadParams;
	$playerId = intval($clPayloadParams['id']);
	$res = array();
	$getRewards = mysql_query('SELECT r.id,r.charid,w.player FROM mkclrewards r LEFT JOIN mkclrewarded w ON r.id=w.reward AND w.player='. $playerId .' WHERE r.clist ="'.$clId.'"');
	while ($reward = mysql_fetch_array($getRewards)) {
		$res[] = array(
			'id' => $reward['id'],
			'charid' => $reward['charid'],
			'unlocked' => $reward['player'] ? 1:0
		);
	}
	return $res;
}
function addClChallenges(&$cId,&$params) {
	global $clId, $isCup, $isMCup, $challenges, $clRewards;
	if (isset($_GET['cl'])) {
		$nCid = isset($cId) ? $cId:-1;
		if ($isMCup)
			$cType = 'mcup';
		elseif ($isCup)
			$cType = 'cup';
		else
			$cType = 'track';
		$clId = $_GET['cl'];
		$cList = listChallenges($clId,$params);
		$clRewards = listClRewards($clId);
		if (!empty($cList))
			$challenges[$cType][$nCid] = array('id' => $clId, 'track' => $cId, 'name' => '', 'main' => true, 'list' => $cList);
	}
}
?>