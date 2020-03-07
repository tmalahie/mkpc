<?php
$rulesList = array(
	'team' => array(
		'default' => 0,
		'accepted' => array(1,1)
	),
	'manualTeams' => array(
		'default' => 0,
		'accepted' => array(1,1)
	),
	'friendly' => array(
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
	$res = array();
	foreach ($rulesList as $key => $rule) {
		if (isset($rules->$key) && isRuleValid($rules->$key,$rules->$key) && ($rules->$key != $rule['default']))
			$res[$key] = $rules->$key;
	}
	return json_encode($res);
}
function isRuleValid($rule,$value) {
	return !isset($rule['accepted']) || isset($rule['accepted'][$value]);
}
?>