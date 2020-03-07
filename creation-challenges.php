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
$clPayloadParams = array('winners' => true, 'utf8' => true, 'id' => $id);
$clId = 0;
if (isset($unsetId)) {
	unset($id);
	unset($unsetId);
}
function addCircuitChallenges(&$list, $table,$circuitId,$circuitName,&$params,$main=true) {
	global $clId;
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
	if (!isset($list[$challengeType][$circuitId])) {
		$clCreations = listCircuitChallenges($table,$circuitId,$params);
		if (isset($clCreations['id'])) {
			if ($main)
				$clId = $clCreations['id'];
			$cList = $clCreations['list'];
			if (!empty($cList))
				$list[$challengeType][$circuitId] = array('id' => $clCreations['id'], 'track' => $circuitId, 'name' => $circuitName, 'main' => $main, 'list' => $cList);
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
function addClChallenges(&$list, &$cId,&$params) {
	global $clId, $isCup, $isMCup;
	$nCid = isset($cId) ? $cId:-1;
	if ($isMCup)
		$cType = 'mcup';
	elseif ($isCup)
		$cType = 'cup';
	else
		$cType = 'track';
	if (isset($_GET['cl'])) {
		$clId = $_GET['cl'];
		$cList = listChallenges($clId,$params);
		if (!empty($cList))
			$list[$cType][$nCid] = array('id' => $clId, 'track' => $cId, 'name' => '', 'main' => true, 'list' => $cList);
	}
}
?>