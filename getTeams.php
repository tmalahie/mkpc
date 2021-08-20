<?php
include('initdb.php');
session_start();
$res = array();
if (!empty($_SESSION['mkid'])) {
	$id = $_SESSION['mkid'];
	if ($getCourse = mysql_fetch_array(mysql_query('SELECT course FROM `mkjoueurs` WHERE id='.$id))) {
		$course = $getCourse['course'];
		include('onlineStateUtils.php');
		if ($getExtra = getCourseExtra($course)) {
			if ($getExtra->state == 'selecting_teams') {
				if ($getTime = mysql_fetch_array(mysql_query('SELECT time FROM `mariokart` WHERE id='. $course))) {
					$now = round(microtime(true)*1000);
					if ($getTime['time'] <= $now) {
						$time = $now+7000;
						mysql_query('UPDATE `mariokart` SET time='.$time.' WHERE id='. $course);
						setCourseExtra($course, array('state' => 'teams_selected'));
					}
				}
			}
		}
		include('fetchTeams.php');
	}
}
mysql_close();
echo json_encode($res);
?>