<?php
ob_start();
include('getPersos.php');
$defaultPersosStats = json_decode(ob_get_clean());
$statsGradient = 24;
$statsDefault = 12;
$statsRange = array (
	'acceleration' => array (
		'min' => 0,
		'max' => 1
	),
	'speed' => array (
		'min' => 0,
		'max' => 1
	),
	'handling' => array (
		'min' => 0,
		'max' => 1
	),
	'mass' => array (
		'min' => 0,
		'max' => 1
	),
);
foreach ($defaultPersosStats as $player => &$persoStats) {
	$i = 0;
	foreach ($statsRange as $key => $statRange) {
		$persoStats[$i] = ($persoStats[$i]-$statRange['min'])/($statRange['max']-$statRange['min']);
		$i++;
	}
}
function cheated() {
	global $statsRange, $statsGradient, $defaultPersosStats;
	$res = true;
	foreach ($defaultPersosStats as $player => $persoStats) {
		$res = false;
		$i = 0;
		foreach ($statsRange as $key => $statRange) {
			$statVal = $_POST[$key];
			if ($statVal > round($persoStats[$i]*$statsGradient)) {
				$res = true;
				break;
			}
			$i++;
		}
		if (!$res)
			break;
	}
	return $res;
}
?>