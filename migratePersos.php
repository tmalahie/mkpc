<?php
if ($_SERVER['REMOTE_ADDR'] != '90.90.118.235') {
	echo $_SERVER['REMOTE_ADDR'];
	exit;
}
$statsRange = array (
	'acceleration' => array (
		'min' => 0.2,
		'max' => 1
	),
	'speed' => array (
		'min' => 0.8,
		'max' => 1.2
	),
	'handling' => array (
		'min' => 0.2,
		'max' => 1
	),
);
ob_start();
include('getPersos.php');
$listPersos = json_decode(ob_get_clean());
ob_start();
include('initdb.php');
function getStatDist(&$stat1,&$stat2) {
	return max(0,$stat1['acceleration']-$stat2[0])+max(0,$stat1['speed']-$stat2[1])*3+max(0,$stat1['handling']-$stat2[2])+(1-$stat2[3])/1000000;
}
function getNewStats($oldStats) {
	global $statsRange, $listPersos;
	$epsilon = 0.000001;
	$newStats = array();
	foreach ($statsRange as $key => $stat)
		$newStats[$key] = ($oldStats[$key]-$stat['min'])/($stat['max']-$stat['min']);
	$cheated = true;
	$nearest = array(0.5,0.5,0.5,0.5);
	$newStats['mass'] = 0;
	foreach ($listPersos as $stat) {
		if (($stat[0]+$epsilon >= $newStats['acceleration']) && ($stat[1]+$epsilon >= $newStats['speed']) && ($stat[2]+$epsilon >= $newStats['handling'])) {
			$newStats['mass'] = max($newStats['mass'],$stat[3]);
			$cheated = false;
		}
		elseif ($cheated) {
			if (getStatDist($newStats,$stat) < getStatDist($newStats,$nearest))
				$nearest = $stat;
		}
	}
	if ($cheated) {
		$newStats['acceleration'] = $nearest[0];
		$newStats['speed'] = $nearest[1];
		$newStats['handling'] = $nearest[2];
		$newStats['mass'] = $nearest[3];
	}
	return $newStats;
}
$getPersos = mysql_query('SELECT id,acceleration,speed,handling FROM mkchars');
while ($perso = mysql_fetch_array($getPersos)) {
	$newStats = getNewStats($perso);
	mysql_query('UPDATE mkchars SET acceleration='.$newStats['acceleration'].',
		speed='.$newStats['speed'].',
		handling='.$newStats['handling'].',
		mass='.$newStats['mass'].'
		WHERE id='.$perso['id']).';<br />';
}
$getPersos = mysql_query('SELECT id,acceleration,speed,handling FROM mkchisto');
while ($perso = mysql_fetch_array($getPersos)) {
	$newStats = getNewStats($perso);
	mysql_query('UPDATE mkchisto SET acceleration='.$newStats['acceleration'].',
		speed='.$newStats['speed'].',
		handling='.$newStats['handling'].',
		mass='.$newStats['mass'].'
		WHERE id='.$perso['id']).';<br />';
}
mysql_close();
echo 'Done';
?>