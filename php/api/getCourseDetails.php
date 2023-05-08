<?php
include('../includes/session.php');
if ($id) {
    header('Content-Type: application/json');
	include('../includes/initdb.php');
    include('../includes/getCourseParams.php');
    $players = mysql_query(
        'SELECT j.id,j.nom FROM mkplayers p INNER JOIN mkjoueurs j ON p.id=j.id
        INNER JOIN mariokart m ON p.course=m.id
        WHERE p.connecte>='.floor(($time-35)*1000/67).' AND p.controller=0
        AND m.cup="'. $nid .'" AND m.mode='. $nmode .' AND m.link='. $nlink
    );
    $activePlayers = array();
    while ($player = mysql_fetch_array($players)) {
        $activePlayers[] = array(
            'id' => $player['id'],
            'name' => $player['nom']
        );
    }
    include('../includes/onlineUtils.php');
    if (isset($_POST['course']))
        $course = intval($_POST['course']);
    else {
        $course = getCourse(array(
            'spectator' => 0,
        ));
    }
    $pendingPlayers = array();
    if ($getTime=mysql_fetch_array(mysql_query('SELECT time,map FROM `mariokart` WHERE id='. $course))) {
        $players = get_remaining_player_query($course,$getTime,true,-1);
        while ($player = mysql_fetch_array($players)) {
            $pendingPlayers[] = array(
                'id' => $player['id'],
                'name' => $player['nom']
            );
        }
    }
    $res = array(
        'active_players' => $activePlayers,
        'pending_players' => $pendingPlayers
    );
    echo json_encode($res);
    mysql_close();
}