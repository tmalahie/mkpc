<?php
$spectatorId = isset($_POST['spectator']) ? intval($_POST['spectator']) : 0;
function getCourse($opts = array()) {
    global $id, $spectatorId;
    $checkBanStatus = !empty($opts['check_ban']);
    $sId = isset($opts['spectator']) ? $opts['spectator'] : $spectatorId;
    if ($sId)
        $getCourse = mysql_fetch_array(mysql_query('SELECT s.course FROM `mkspectators` s'. ($checkBanStatus ? ' INNER JOIN `mkjoueurs` j ON s.player=j.id AND j.banned=0':'') .' WHERE s.id="'.$sId.'" AND s.player="'. $id .'"'));
    else
        $getCourse = mysql_fetch_array(mysql_query('SELECT course FROM `mkjoueurs` WHERE id="'.$id.'"'. ($checkBanStatus ? ' AND banned=0':'')));
    if ($getCourse) return $getCourse['course'];
    return 0;
}
function joinSpectatorMode($course) {
    global $id;
    mysql_query('INSERT IGNORE INTO `mkspectators` SET player='.$id.',course='.$course.',state="joined" ON DUPLICATE KEY UPDATE state=VALUES(state)');
    $getSpectatorId = mysql_fetch_array(mysql_query('SELECT id FROM `mkspectators` WHERE player="'.$id.'" AND course="'.$course.'"'));
    if ($getSpectatorId)
        return +$getSpectatorId['id'];
}