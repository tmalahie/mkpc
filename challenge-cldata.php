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
		$clCourse = ($clCircuit['mode'] >= 2) ? 'bcup' : 'cup';
		$edittingCircuit = isset($_GET['nid']);
		break;
	case 'mkmcups':
		$clCourse = ($clCircuit['mode'] >= 2) ? 'mbcup' : 'mcup';
		$edittingCircuit = isset($_GET['nid']);
		break;
	}
}
elseif (isset($_GET['page'])) {
	$clPage = $_GET['page'];
	switch ($clPage) {
	case 'circuit':
	case 'arena':
		$clBattle = ($clPage === 'arena');
		if (isset($_GET['mid'])) {
			$clCourse = $clBattle ? 'mbcup' : 'mcup';
			$clTable = 'mkmcups';
			$clCid = intval($_GET['mid']);
		}
		elseif (isset($_GET['cid'])) {
			$clCourse = $clBattle ? 'bcup' : 'cup';
			$clTable = 'mkcups';
			$clCid = intval($_GET['cid']);
		}
		elseif (isset($_GET['id']))
			$nid = intval($_GET['id']);
		elseif (isset($_GET['cid0']) && isset($_GET['cid1']) && isset($_GET['cid2']) && isset($_GET['cid3'])) {
			$clCourse = $clBattle ? 'bcup' : 'cup';
			if (isset($_GET['nid'])) {
				$clTable = 'mkcups';
				$clCid = intval($_GET['nid']);
				$edittingCircuit = true;
			}
		}
		elseif (isset($_GET['mid0'])) {
			$clCourse = $clBattle ? 'mbcup' : 'mcup';
			if (isset($_GET['nid'])) {
				$clTable = 'mkmcups';
				$clCid = intval($_GET['nid']);
				$edittingCircuit = true;
			}
		}
		elseif (isset($_GET['nid'])) {
			$nid = intval($_GET['nid']);
			$edittingCircuit = true;
		}
		if (isset($nid)) {
			$clCourse = $clBattle ? 'battle' : 'vs';
			$clTable = 'mkcircuits';
			$clCid = $nid;
		}
		break;
	case 'map':
	case 'battle':
		$clBattle = ($clPage === 'battle');
		if (isset($_GET['cid0']) && isset($_GET['cid1']) && isset($_GET['cid2']) && isset($_GET['cid3'])) {
			$clCourse = $clBattle ? 'bcup' : 'cup';
			if (isset($_GET['nid'])) {
				$clTable = 'mkcups';
				$clCid = intval($_GET['nid']);
				$edittingCircuit = true;
			}
		}
		elseif (isset($_GET['mid0'])) {
			$clCourse = $clBattle ? 'mbcup' : 'mcup';
			if (isset($_GET['nid'])) {
				$clTable = 'mkmcups';
				$clCid = intval($_GET['nid']);
				$edittingCircuit = true;
			}
		}
		elseif (isset($_GET['mid'])) {
			$clCourse = $clBattle ? 'mbcup' : 'mcup';
			$clTable = 'mkmcups';
			$clCid = intval($_GET['mid']);
		}
		elseif (isset($_GET['cid'])) {
			$clCourse = $clBattle ? 'bcup' : 'cup';
			$clTable = 'mkcups';
			$clCid = intval($_GET['cid']);
		}
		elseif (isset($_GET['i'])) {
			$clCourse = $clBattle ? 'battle' : 'vs';
			$clTable = $clBattle ? 'arenes' : 'circuits';
			$clCid = intval($_GET['i']);
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
		$params = array('page' => null);
		if (!empty($clRace) && $clRace['type'])
			$params['cl'] = null;
		return nextPageUrl(urlencode($_GET['page']).'.php', array('page'=>null));
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
				return getCupPage($clCircuit['mode']) .'.php?cid='. $clCircuit['id'];
			case 'mkmcups':
				return getCupPage($clCircuit['mode']) .'.php?mid='. $clCircuit['id'];
			}
		}
	}
	return 'mariokart.php';
}
function getCupPage($mode) {
	switch ($mode) {
	case 1:
		return 'map';
	case 2:
		return 'arena';
	case 3:
		return 'battle';
	default:
		return 'circuit';
	}
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
	case 'bcup':
		$theCircuit = $language ? 'cup':'à la coupe';
		break;
	case 'mcup':
	case 'mbcup':
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