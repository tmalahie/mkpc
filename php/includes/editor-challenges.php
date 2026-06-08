<?php
require_once('utils-challenges.php');
require_once('creation-challenges.php');
// Returns a minimal list of challenges associated with a given track,
// suitable for the editor's challenge-override picker.
function getEditorCircuitChallenges($table, $circuitId) {
	$params = array();
	$clCreations = listCircuitChallenges($table, $circuitId, $params);
	$res = array();
	if (empty($clCreations['list']))
		return $res;
	foreach ($clCreations['list'] as $challenge) {
		if ($challenge['status'] === 'deleted')
			continue;
		$res[] = array(
			'id' => intval($challenge['id']),
			'name' => $challenge['name'],
			'description' => isset($challenge['description']['main']) ? $challenge['description']['main'] : ''
		);
	}
	return $res;
}
?>
