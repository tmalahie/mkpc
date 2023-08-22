<?php
if ($getExtra = getCourseExtra($course)) {
	$res['state'] = $getExtra->state;
	if ($getExtra->state == 'teams_selected') {
		usleep(100000);
		$getPlayers = mysql_query('SELECT id,team FROM `mkplayers` WHERE course='. $course);
		$res['teams'] = array();
		$nbPlayers = 0;
		while ($player = mysql_fetch_array($getPlayers)) {
			$res['teams'][] = array('id' => intval($player['id']), 'team' => intval($player['team']));
			$nbPlayers++;
		}
		if ($getTime = mysql_fetch_array(mysql_query('SELECT time FROM `mariokart` WHERE id='. $course))) {
			require_once('onlineStateUtils.php');
			$now = round(microtime(true)*1000);
			$res['time'] = $getTime['time']-$now;
			$res['latency'] = $now-$startTs;
			$res['connect'] = round($getTime['time']/67);
			$res['previewTime'] = getTeamPreviewTime($nbPlayers);
		}
	}
}
?>