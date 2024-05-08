<?php
header('Content-type: application/json');
include('../includes/language.php');
include('../includes/session.php');
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
    $userId = $id;
}
$cIDs = isset($_POST['cIDs']) ? explode(',', $_POST['cIDs']) : array();
foreach ($cIDs as &$cID)
    $cID = intval($cID);
unset($cID);
if ($type) {
    if (empty($cIDs))
        $cIDs = array(0);
    $cIDsFilter = ' AND r.circuit IN ('.implode(',',$cIDs).')';
}
else {
    include_once('../pages/circuitNames.php');
    if (empty($cIDs)) {
        for ($i=0;$i<$nbVSCircuits;$i++)
            $cIDs[] = $i+1;
        $cIDsFilter = '';
    }
    else
        $cIDsFilter = ' AND r.circuit IN ('.implode(',',$cIDs).')';
}
if ($userId)
	$resultsQueries = array('SELECT r.*,c.code,r.date,1+COUNT(r2.circuit) AS rank FROM `mkrecords` r LEFT JOIN `mkrecords` r2 ON r2.class=r.class AND r2.type=r.type AND r2.circuit=r.circuit AND r2.best=1 AND r2.time<r.time LEFT JOIN `mkprofiles` p ON r.player=p.id LEFT JOIN `mkcountries` c ON p.country=c.id WHERE r.class="'. $cc .'" AND r.type="'. $type .'"'. $cIDsFilter .' AND r.best=1 AND r.player='.$userId.' GROUP BY r.circuit ORDER BY r.time');
elseif (isset($_POST['name'])) {
    $name = $_POST['name'];
    $resultsQueries = array('SELECT r.*,c.code,r.date,1+COUNT(r2.circuit) AS rank FROM `mkrecords` r LEFT JOIN `mkrecords` r2 ON r2.class=r.class AND r2.type=r.type AND r2.circuit=r.circuit AND r2.best=1 AND r2.time<r.time LEFT JOIN `mkprofiles` p ON r.player=p.id LEFT JOIN `mkcountries` c ON p.country=c.id WHERE r.class="'. $cc .'" AND r.type="'. $type .'"'. $cIDsFilter .' AND r.best=1 AND r.name="'.$name.'" GROUP BY r.circuit ORDER BY r.time');
}
else {
    $hasFilter = !empty($pIDs);
    if ($hasFilter)
        $page = null;
    else
        $page = isset($_POST['page']) ? $_POST['page'] : 0;
    $resPerPage = 20;
    $resultsQueries = array();
    foreach ($cIDs as $cID) {
        $resultsQueries[] = 'SELECT r.*,c.code,r.date'. ($hasFilter ? ',1+COUNT(r2.circuit) AS rank':'') .' FROM `mkrecords` r'. ($hasFilter ? ' LEFT JOIN `mkrecords` r2 ON r2.class=r.class AND r2.type=r.type AND r2.circuit=r.circuit AND r2.best=1 AND r2.time<r.time' : '') .' LEFT JOIN `mkprofiles` p ON r.player=p.id LEFT JOIN `mkcountries` c ON p.country=c.id WHERE r.class="'.$cc.'" AND r.type="'.$type.'" AND r.circuit='. $cID .' AND r.best=1'. (empty($pIDs)?'':' AND r.identifiant="'.$pIDs[0].'" AND r.identifiant2="'.$pIDs[1].'" AND r.identifiant3="'.$pIDs[2].'" AND r.identifiant4="'.$pIDs[3].'"') . ($hasFilter ? ' GROUP BY r.circuit':'') .' ORDER BY r.time'. ($page===null ? '' : ' LIMIT '.($page*$resPerPage).','.$resPerPage);
    }
}
if (isset($_POST['count']))
    $getCount = mysql_query('SELECT r.circuit,COUNT(*) AS nb FROM `mkrecords` r WHERE r.class="'. $cc .'" AND r.type="'. $type .'" AND r.best=1'.(empty($pIDs)?'':' AND (r.identifiant="'.$pIDs[0].'" AND r.identifiant2="'.$pIDs[1].'" AND r.identifiant3="'.$pIDs[2].'" AND r.identifiant4="'.$pIDs[3].'")').(($cIDsFilter||!$type)?"$cIDsFilter GROUP BY r.circuit":''));

$entry = array('list' => array());
if (isset($getCount))
    $entry['count'] = 0;
if ($cIDsFilter) {
    function getCircuitIndex($result) {
        global $cIDs;
        return array_search($result['circuit'],$cIDs);
    }
    $classement = array_fill(0, count($cIDs), $entry);
}
else {
    function getCircuitIndex($result) {
        return $result['circuit']-1;
    }
    include_once('../pages/circuitNames.php');
    $classement = array_fill(0, $nbVSCircuits, $entry);
}
foreach ($classement as $i => &$entry)
    $entry['id'] = $cIDs[$i];
unset($entry);
foreach ($resultsQueries as $query) {
    $getResults = mysql_query($query);
    $baseRank = 1;
    if (isset($page))
        $baseRank += $page*$resPerPage;
    while ($result = mysql_fetch_array($getResults)) {
        if (isset($result['rank']))
            $rank = $result['rank'];
        else
            $rank = $baseRank + count($classement[getCircuitIndex($result)]['list']);
        $entry = [$rank,htmlspecialchars($result['name']),$result['perso'],intval($result['time']),intval($result['player']),$result['code'],pretty_dates_short($result['date'],array('shorter'=>true,'year'=>true,'new'=>false))];
        if ($sManage)
            $entry[] = $result['id'];
        $classement[getCircuitIndex($result)]['list'][] = $entry;
    }
}
if (isset($getCount)) {
    while ($result = mysql_fetch_array($getCount))
        $classement[getCircuitIndex($result)]['count'] = intval($result['nb']);
}
echo json_encode($classement);
mysql_close();