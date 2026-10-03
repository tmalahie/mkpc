<?php
if (isset($_POST['options'])) {
	$options = json_decode($_POST['options']);
	if ($options) {
		include('onlineRulesUtils.php');
		$rules = json_decode(getRulesAsString($options));
		// The lounge sets these when it builds a room and the options form never sends them,
		// so a save keeps what the room was built with - and cannot turn a link into a lounge one.
		$stored = mysql_fetch_array(mysql_query('SELECT rules FROM `mkgameoptions` WHERE id="'. $key .'"'));
		$storedRules = $stored ? json_decode($stored['rules']) : null;
		foreach (array('lounge', 'raceLimit', 'fixedTeams') as $rule) {
			unset($rules->$rule);
			if (isset($storedRules->$rule))
				$rules->$rule = $storedRules->$rule;
		}
		$rulesString = mysql_real_escape_string(json_encode($rules));
		mysql_query(
			'INSERT INTO `mkgameoptions` SET id="'. $key .'",
			rules="'.$rulesString.'"
			ON DUPLICATE KEY UPDATE rules="'.$rulesString.'"'
		);
	}
}
?>