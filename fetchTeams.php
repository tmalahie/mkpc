<?php
if ($getExtra = getCourseExtra($course)) {
	$res['state'] = $getExtra->state;
	if ($getExtra->state == 'teams_selected') {
		usleep(100000);
		$getPlayers = mysql_query('SELECT id,team FROM `mkplayers` WHERE course='. $course);
		$res['teams'] = array();
		while ($player = mysql_fetch_array($getPlayers))
			$res['teams'][] = array('id' => intval($player['id']), 'team' => intval($player['team']));
		if ($getTime = mysql_fetch_array(mysql_query('SELECT time FROM `mariokart` WHERE id='. $course))) {
			$now = round(microtime(true)*1000);
			$res['time'] = $getTime['time']-$now;
		}
	}
}
?>