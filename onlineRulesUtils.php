<?php
require_once('onlineConsts.php');
$rulesList = array(
	'team' => array(
		'default' => 0,
		'accepted' => array(1,1)
	),
	'manualTeams' => array(
		'default' => 0,
		'accepted' => array(1,1)
	),
	'nbTeams' => array(
		'default' => 2,
		'min_value' => 2,
		'max_value' => 4
	),
	'friendly' => array(
		'default' => 0,
		'accepted' => array(1,1)
	),
	'localScore' => array(
		'default' => 0,
		'accepted' => array(1,1)
	),
	'minPlayers' => array(
		'default' => DEFAULT_MIN_PLAYERS,
		'min_value' => 2,
		'max_value' => 99
	),
	'maxPlayers' => array(
		'default' => DEFAULT_MAX_PLAYERS,
		'min_value' => 2,
		'max_value' => 99
	),
	'cc' => array(
		'default' => 150,
		'min_value' => 1,
		'max_value' => 999
	),
	'itemDistrib' => array(
		'default' => 0
	),
	'cpu' => array(
		'default' => 0
	),
	'cpuCount' => array(
		'default' => DEFAULT_CPU_COUNT,
		'min_value' => 2,
		'max_value' => 12
	),
	'cpuLevel' => array(
		'default' => 0
	),
	'cpuNames' => array(
		'default' => null,
		'sanitize' => function($cpuNames) {
			foreach ($cpuNames as $i=>$cpuName)
				$cpuNames[$i] = strip_tags($cpuName);
			return $cpuNames;
		}
	),
	'cpuChars' => array(
		'default' => null,
		'sanitize' => function($cpuChars) {
			foreach ($cpuChars as $i=>$cpuChar)
				$cpuChars[$i] = preg_replace('#[^\w\-]#', '', $cpuChar);
			return $cpuChars;
		}
	),
	'timeTrial' => array(
		'default' => 0,
		'accepted' => array(1,1)
	),
	'noBumps' => array(
		'default' => 0,
		'accepted' => array(1,1)
	)
);
function rulesEqual($rules1,$rules2) {
	global $rulesList;
	foreach ($rulesList as $key => $rule) {
		$default = $rule['default'];
		if (isset($rules1->$key))
			$val1 = $rules1->$key;
		else
			$val1 = $default;
		if (isset($rules2->$key))
			$val2 = $rules2->$key;
		else
			$val2 = $default;
		if ($val1 != $val2)
			return false;
	}
	return true;
}
function getRulesAsString($rules) {
	global $rulesList;
	$res = new stdClass();
	foreach ($rulesList as $key => $rule) {
		if (isset($rules->$key) && isRuleValid($rule,$rules->$key) && ($rules->$key != $rule['default']))
			$res->{$key} = sanitizeRule($rule,$rules->$key);
	}
	return json_encode($res);
}
function isRuleValid($rule,$value) {
	if (isset($rule['accepted'])) {
		if (!isset($rule['accepted'][$value]))
			return false;
	}
	if (isset($rule['min_value'])) {
		if ($value < $rule['min_value'])
			return false;
	}
	if (isset($rule['max_value'])) {
		if ($value > $rule['max_value'])
			return false;
	}
	return true;
}
function sanitizeRule($rule,$value) {
	if (isset($rule['sanitize']))
		return $rule['sanitize']($value);
	return $value;
}
function getCpuName($i, $rules) {
	if (empty($rules->cpuNames[$i]))
		return 'CPU '. ($i+1);
	return $rules->cpuNames[$i];
}
?>