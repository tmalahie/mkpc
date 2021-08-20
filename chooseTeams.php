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
				if (isset($_POST['cancel'])) {
					setCourseExtra($course, array('state' => 'cancelled'));
					mysql_query('UPDATE `mariokart` SET map=-1,time='.time().' WHERE id='. $course);
					mysql_query('UPDATE `mkjoueurs` j LEFT JOIN `mkplayers` p ON j.id=p.id SET j.choice_map=0,p.connecte=0 WHERE j.course='. $course);
				}
				else {
					if (isset($_POST['noteams']))
						mysql_query('UPDATE `mkplayers` SET team=-1 WHERE course="'.$course.'"');
					else {
						$getPlayers = mysql_query('SELECT id FROM `mkplayers` WHERE course="'.$course.'"');
						while ($player = mysql_fetch_array($getPlayers)) {
							$pId = $player['id'];
							if (isset($_POST["j$pId"])) {
								$team = $_POST["j$pId"];
								mysql_query('UPDATE `mkplayers` SET team='.$team.' WHERE id='.$pId);
							}
						}
					}
					$now = round(microtime(true)*1000);
					$time = $now+7000;
					if (!isset($_POST['single']))
						$time = 'GREATEST(time-6000,'.$time.')';
					mysql_query('UPDATE `mariokart` SET time='.$time.' WHERE id='. $course);
					setCourseExtra($course, array('state' => 'teams_selected'));
				}
			}
		}
		include('fetchTeams.php');
	}
}
mysql_close();
echo json_encode($res);
?>