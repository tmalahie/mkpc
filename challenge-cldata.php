<?php
if (!empty($clRace) && $clRace['type']) {
	$clCircuit = mysql_fetch_array(mysql_query('SELECT * FROM `'. $clRace['type'] .'` WHERE id='. $clRace['circuit']));
	$clTable = $clRace['type'];
	switch ($clTable) {
	case 'circuits':
		$clCourse = 'vs';
		break;
	case 'mkcircuits':
		$clCourse = ($clCircuit['type'] ? 'battle':'vs');
		$edittingCircuit = isset($_GET['nid']);
		break;
	case 'arenes':
		$clCourse = 'battle';
		break;
	case 'mkcups':
		$clCourse = 'cup';
		$edittingCircuit = isset($_GET['nid']);
		break;
	case 'mkmcups':
		$clCourse = 'mcup';
		$edittingCircuit = isset($_GET['nid']);
		break;
	}
}
elseif (isset($_GET['page'])) {
	switch ($_GET['page']) {
	case 'circuit':
		if (isset($_GET['mid'])) {
			$clCourse = 'mcup';
			$clTable = 'mkmcups';
			$clCid = $_GET['mid'];
		}
		elseif (isset($_GET['cid'])) {
			$clCourse = 'cup';
			$clTable = 'mkcups';
			$clCid = $_GET['cid'];
		}
		elseif (isset($_GET['id']))
			$nid = $_GET['id'];
		elseif (isset($_GET['cid0']) && isset($_GET['cid1']) && isset($_GET['cid2']) && isset($_GET['cid3'])) {
			$clCourse = 'cup';
			if (isset($_GET['nid'])) {
				$clTable = 'mkcups';
				$clCid = $_GET['nid'];
				$edittingCircuit = true;
			}
		}
		elseif (isset($_GET['mid0'])) {
			$clCourse = 'mcup';
			if (isset($_GET['nid'])) {
				$clTable = 'mkmcups';
				$clCid = $_GET['nid'];
				$edittingCircuit = true;
			}
		}
		elseif (isset($_GET['nid'])) {
			$nid = $_GET['nid'];
			$edittingCircuit = true;
		}
		if (isset($nid)) {
			$clCourse = 'vs';
			$clTable = 'mkcircuits';
			$clCid = $nid;
		}
		break;
	case 'map':
		if (isset($_GET['cid0']) && isset($_GET['cid1']) && isset($_GET['cid2']) && isset($_GET['cid3'])) {
			$clCourse = 'cup';
			if (isset($_GET['nid'])) {
				$clTable = 'mkcups';
				$clCid = $_GET['nid'];
				$edittingCircuit = true;
			}
		}
		elseif (isset($_GET['mid0'])) {
			$clCourse = 'mcup';
			if (isset($_GET['nid'])) {
				$clTable = 'mkmcups';
				$clCid = $_GET['nid'];
				$edittingCircuit = true;
			}
		}
		elseif (isset($_GET['mid'])) {
			$clCourse = 'mcup';
			$clTable = 'mkmcups';
			$clCid = $_GET['mid'];
		}
		elseif (isset($_GET['cid'])) {
			$clCourse = 'cup';
			$clTable = 'mkcups';
			$clCid = $_GET['cid'];
		}
		elseif (isset($_GET['i'])) {
			$clCourse = 'vs';
			$clTable = 'circuits';
			$clCid = $_GET['i'];
		}
		break;
	case 'arena':
		$clCourse = 'battle';
		if (isset($_GET['id']))
			$nid = $_GET['id'];
		elseif (isset($_GET['nid'])) {
			$nid = $_GET['nid'];
			$edittingCircuit = true;
		}
		if (isset($nid)) {
			$clTable = 'mkcircuits';
			$clCid = $nid;
		}
		break;
	case 'battle':
		$clCourse = 'battle';
		if (isset($_GET['i'])) {
			$clTable = 'arenes';
			$clCid = $_GET['i'];
		}
		break;
	}
	if (isset($clTable) && isset($clCid)) {
		$clCircuit = mysql_fetch_array(mysql_query('SELECT * FROM `'.$clTable.'` WHERE id="'.$clCid.'"'));
		$clRace = mysql_fetch_array(mysql_query('SELECT * FROM `mkclrace` WHERE type="'. $clTable .'" AND circuit="'. $clCid .'"'));
		unset($clCid);
	}
}
if (empty($clCircuit)) unset($clCircuit);
if (empty($clRace)) unset($clRace);
if (empty($edittingCircuit)) unset($edittingCircuit);

if (isset($clCircuit) && !isset($clCircuit['id']))
	$clCircuit['id'] = $clCircuit['ID'];
if (!isset($clCourse))
	$clCourse = 'vs';
if (!empty($clRace)) {
	$clRace['clid'] = isset($edittingCircuit) ? null:$clRace['id'];
}
function parseChallengeConstraint(&$scope) {
	global $clRules;
	$ruleId = $scope['type'];
	if (!isset($clRules[$ruleId]))
		throw new Exception();
	if (isset($clRules[$ruleId]['parser']))
		$clRules[$ruleId]['parser']($scope);
}
function formatChallengeConstraint(&$scope, $ruleClass) {
	global $clRulesByType;
	$rule = $clRulesByType[$ruleClass][$scope->type];
	if (isset($rule['formatter']))
		$rule['formatter']($scope);
	return $scope;
}
function rulesPayloadByType($course) {
	global $clRulesByType;
	$res = array();
	foreach ($clRulesByType as $rulesClass => &$rulesList)
		$res[$rulesClass] = rulesPayload($rulesClass,$rulesList,$course);
	return $res;
}
function rulesPayload($rulesClass,&$rulesList,&$course) {
	$res = array();
	foreach ($rulesList as $rule) {
		if (isRuleElligible($rule,$course))
			$res[$rule['type']] = rulePayload($rule,$rulesClass);
	}
	return $res;
}
function rulePayload($rule,$rulesClass=null) {
	$rule['mockup'] = true;
	$description = getRuleDescription($rule,$rulesClass);
	$res = array(
		'description' => $description
	);
	if (isset($rule['scope']))
		$res['scope'] = $rule['scope'];
	return $res;
}
function getChallengeRulesByType($challenge) {
	global $clRulesByType;
	$res = array(
		'main' => null,
		'basic' => array(),
		'extra' => array(),
		'setup' => array()
	);
	$challengeData = json_decode($challenge['data']);
	$res['main'] = formatChallengeConstraint($challengeData->goal, 'main');
	foreach ($challengeData->constraints as $constraint) {
		$constraintType = $constraint->type;
		$constraintClass = null;
		foreach ($clRulesByType as $rulesClass => &$rulesList) {
			if (isset($rulesList[$constraintType])) {
				if (!isset($rulesList[$constraintType]['this_class']) || $rulesList[$constraintType]['this_class']($constraint)) {
					$constraintClass = $rulesClass;
					break;
				}
			}
		}
		if (isset($constraintClass))
			$res[$constraintClass][] = formatChallengeConstraint($constraint, $constraintClass);
	}
	return $res;
}
function backCircuitUrl() {
	global $clRace, $clCircuit;
	if (isset($_GET['page'])) {
		$page = $_GET['page'];
		$params = array('page' => null);
		if (!empty($clRace) && $clRace['type'])
			$params['cl'] = null;
		return nextPageUrl($_GET['page'].'.php', array('page'=>null));
	}
	elseif (!empty($clRace)) {
		if ($clCircuit) {
			switch ($clRace['type']) {
			case 'circuits':
				return 'map.php?i='. $clCircuit['ID'];
			case 'mkcircuits':
				return ($clCircuit['type'] ? 'arena':'circuit') .'.php?id='. $clCircuit['id'];
			case 'arenes':
				return 'battle.php?i='. $clCircuit['ID'];
			case 'mkcups':
				return ($clCircuit['mode'] ? 'map':'circuit') .'.php?cid='. $clCircuit['id'];
			case 'mkmcups':
				return ($clCircuit['mode'] ? 'map':'circuit') .'.php?mid='. $clCircuit['id'];
			}
		}
	}
	return 'mariokart.php';
}
function backCircuitText() {
	global $language, $clCourse;
	$theCircuit = 'creation';
	switch ($clCourse) {
	case 'vs':
		$theCircuit = $language ? 'circuit':'au circuit';
		break;
	case 'battle':
		$theCircuit = $language ? 'arena':'à l\'arène';
		break;
	case 'cup':
		$theCircuit = $language ? 'cup':'à la coupe';
		break;
	case 'mcup':
		$theCircuit = $language ? 'multicup':'à la multicoupe';
		break;
	}
	return $language ? "Back to $theCircuit":"Retour $theCircuit";
}
function nextPageUrl($page,$extraParams=array()) {
	$res = $_GET;
	foreach ($res as $k=>$v)
		$res[$k] = stripslashes($v);
	$res = array_merge($res,$extraParams);
	return $page.'?'.http_build_query($res);
}
?>