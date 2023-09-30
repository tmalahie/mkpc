<?php
header('Content-type: application/json');
include('../includes/language.php');
include('../includes/initdb.php');
require_once('../includes/utils-date.php');
require_once('../includes/getRights.php');
$isModerator = hasRight('moderator');
$cc = isset($_POST['cc']) ? $_POST['cc'] : 150;
$manage = isset($_POST['manage']);
$moderate = $isModerator && isset($_POST['moderate']);
$sManage = $manage || $moderate;
$type = isset($_POST['type']) ? $_POST['type'] : '';
$userId = null;
if (!$manage && isset($_POST['user']))
    $userId = intval($_POST['user']);
if ($manage) {
    include('../includes/getId.php');
	$pIDs = $identifiants;
}
if ($userId) {
	$getResults = mysql_query('SELECT r.*,c.code,r.date,(r.player='.$userId.') AS shown FROM `mkrecords` r LEFT JOIN `mkprofiles` p ON r.player=p.id LEFT JOIN `mkcountries` c ON p.country=c.id WHERE r.class="'. $cc .'" AND r.type="'. $type .'" AND r.best=1 ORDER BY r.time');
    if (isset($_POST['count']))
        $getCount = mysql_query('SELECT r.circuit,COUNT(*) AS nb FROM `mkrecords` r WHERE r.class="'. $cc .'" AND r.type="'. $type .'" AND r.player='.$userId.' AND r.best=1 GROUP BY r.circuit');
}
else {
    $cIDs = isset($_POST['cIDs']) ? explode(',', $_POST['cIDs']) : array();
    foreach ($cIDs as &$cID)
        $cID = intval($cID);
    unset($cID);
	if ($type && empty($cIDs))
		$cIDs = array(0);
	$getResults = mysql_query('SELECT r.*,c.code,r.date'.(empty($pIDs)?($moderate?',1 AS shown':''):',(r.identifiant="'.$pIDs[0].'" AND r.identifiant2="'.$pIDs[1].'" AND r.identifiant3="'.$pIDs[2].'" AND r.identifiant4="'.$pIDs[3].'") AS shown').' FROM `mkrecords` r LEFT JOIN `mkprofiles` p ON r.player=p.id LEFT JOIN `mkcountries` c ON p.country=c.id WHERE r.class="'.$cc.'" AND r.type="'.$type.'"'.(empty($cIDs)?'':' AND r.circuit IN ('.implode(',',$cIDs).')').' AND r.best=1 ORDER BY r.time');
    if (isset($_POST['count']))
        $getCount = mysql_query('SELECT r.circuit,COUNT(*) AS nb FROM `mkrecords` r WHERE r.class="'. $cc .'" AND r.type="'. $type .'" AND r.best=1'.(empty($pIDs)?'':' AND (r.identifiant="'.$pIDs[0].'" AND r.identifiant2="'.$pIDs[1].'" AND r.identifiant3="'.$pIDs[2].'" AND r.identifiant4="'.$pIDs[3].'")').(empty($cIDs)?'':' AND r.circuit IN ('.implode(',',$cIDs).') GROUP BY r.circuit'));
}
if ($type) {
    function getCircuitIndex($result) {
        global $cIDs;
        return array_search($result['circuit'],$cIDs);
    }
    $classement = array_fill(0, count($cIDs), array());
}
else {
    function getCircuitIndex($result) {
        return $result['circuit']-1;
    }
    include_once('../pages/circuitNames.php');
    $entry = array('list' => array());
    if (isset($getCount))
        $entry['count'] = 0;
    $classement = array_fill(0, $nbVSCircuits, $entry);
}
while ($result = mysql_fetch_array($getResults)) {
    $entry = [htmlspecialchars($result['name']),$result['perso'],intval($result['time']),intval($result['player']),$result['code'],pretty_dates_short($result['date'],array('shorter'=>true,'new'=>false))];
    if (isset($result['shown']))
        $entry[] = $result['shown'];
    if ($sManage)
        $entry[] = $result['id'];
    $classement[getCircuitIndex($result)]['list'][] = $entry;
}
if (isset($getCount)) {
    while ($result = mysql_fetch_array($getCount))
        $classement[getCircuitIndex($result)]['count'] = intval($result['nb']);
}
echo json_encode($classement);
mysql_close();