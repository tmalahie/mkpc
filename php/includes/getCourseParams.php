<?php
require_once('onlineConsts.php');
$isCup = false;
$isMCup = false;
$isSingle = false;
$isBattle = isset($_POST['battle']);
if (isset($_POST['mid']) && is_numeric($_POST['mid'])) {
    $nid = $_POST['mid'];
    if (mysql_numrows(mysql_query('SELECT * FROM mkmcups WHERE id='. $nid))) {
        $isCup = true;
        $isMCup = true;
    }
}
elseif (isset($_POST['cid']) && is_numeric($_POST['cid'])) {
    $nid = $_POST['cid'];
    if (mysql_numrows(mysql_query('SELECT * FROM mkcups WHERE id='. $nid .' AND mode=1'))) {
        $isCup = true;
        $complete = true;
    }
}
elseif (isset($_POST['sid']) && is_numeric($_POST['sid'])) {
    $nid = $_POST['sid'];
    if (mysql_numrows(mysql_query('SELECT * FROM mkcups WHERE id='. $nid .' AND mode IN (0,2)'))) {
        $isCup = true;
        $complete = false;
    }
}
elseif (isset($_POST['id']) && is_numeric($_POST['id'])) {
    $nid = $_POST['id'];
    if (mysql_numrows(mysql_query('SELECT * FROM mkcircuits WHERE id='. $nid))) {
        $isCup = true;
        $complete = false;
        $isSingle = true;
    }
}
elseif (isset($_POST['i']) && is_numeric($_POST['i'])) {
    $nid = $_POST['i'];
    if (mysql_numrows(mysql_query('SELECT * FROM '.($isBattle?'arenes':'circuits').' WHERE id='. $nid))) {
        $isCup = true;
        $complete = true;
        $isSingle = true;
    }
}
else
    $nid = 0;
$nlink = 0;
$linkOptions = new stdClass();
$linkOptions->public = true;
$linkOptions->rules = new stdClass();
if (isset($_POST['key']) && is_numeric($_POST['key'])) {
    $nlink = $_POST['key'];
    if (!mysql_numrows(mysql_query('SELECT * FROM `mkprivgame` WHERE id='.$nlink)))
        $nlink = 0;
    elseif ($getOptions = mysql_fetch_array(mysql_query('SELECT rules,public FROM `mkgameoptions` WHERE id='.$nlink))) {
        $linkOptions->rules = json_decode($getOptions['rules']);
        $linkOptions->public = $getOptions['public'];
    }
    else
        $linkOptions->public = false;
}
if (!isset($linkOptions->rules->minPlayers))
    $linkOptions->rules->minPlayers = DEFAULT_MIN_PLAYERS;
if (!isset($linkOptions->rules->maxPlayers))
    $linkOptions->rules->maxPlayers = DEFAULT_MAX_PLAYERS;
$nmode = $isCup ? ($isMCup?8:($complete?1:0)+($isSingle?2:0)+($isBattle?4:0)):($isBattle ? 1:0);
$timeMs = microtime(true);
$time = floor($timeMs);
$lConnect = round(($timeMs-30)*1000/67);
function get_remaining_player_query($course, &$getTime, $shouldBeActive, $excludePlayer) {
    global $time, $lConnect;
    return mysql_query('SELECT j.id,j.nom FROM `mkjoueurs` j LEFT JOIN `mkplayers` p ON j.id=p.id WHERE j.course='. $course .' AND j.id!="'.$excludePlayer.'"'.($shouldBeActive ? (' AND (p.connecte>='. $lConnect . ($getTime['map']==-1&&$getTime['time']>=($time-5)&&$getTime['time']<($time+1000) ? ' OR p.connecte IS NULL OR p.connecte=0':'') .')'):''));
}