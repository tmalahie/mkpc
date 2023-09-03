<?php
header('Content-Type: application/json');
include('../includes/initdb.php');
session_start();
$res = array();
if (!empty($_SESSION['mkid'])) {
	$id = $_SESSION['mkid'];
	include('../includes/onlineUtils.php');
	$course = getCourse(array('spectator' => 0));
	require_once('../includes/onlineStateUtils.php');
	if ($getExtra = getCourseExtra($course)) {
		if ($getExtra->state == 'selecting_teams') {
			if (isset($_POST['cancel'])) {
				setCourseExtra($course, array('state' => 'cancelled'));
				mysql_query('UPDATE `mariokart` SET map=-1,time='.time().' WHERE id='. $course);
				mysql_query('UPDATE `mkjoueurs` j LEFT JOIN `mkplayers` p ON j.id=p.id SET j.choice_map=0,p.connecte=0 WHERE j.course='. $course);
			}
			else {
				$getPlayers = mysql_query('SELECT id FROM `mkplayers` WHERE course="'.$course.'"');
				$nbPlayers = mysql_numrows($getPlayers);
				if (isset($_POST['noteams']))
					mysql_query('UPDATE `mkplayers` SET team=-1 WHERE course="'.$course.'"');
				else {
					while ($player = mysql_fetch_array($getPlayers)) {
						$pId = $player['id'];
						if (isset($_POST["j$pId"])) {
							$team = $_POST["j$pId"];
							mysql_query('UPDATE `mkplayers` SET team='.$team.' WHERE id='.$pId);
						}
					}
				}
				$now = round(microtime(true)*1000);
				$time = $now + 5000 + getTeamPreviewTime($nbPlayers);
				if (!isset($_POST['single'])) {
					$minTime = getTeamSelectionTime($nbPlayers) - 6000;
					$time = "GREATEST(time-$minTime,$time)";
				}
				mysql_query('UPDATE `mariokart` m LEFT JOIN mkplayers p ON m.id=p.course SET m.time='.$time.',p.connecte=ROUND(m.time/67) WHERE m.id='. $course);
				setCourseExtra($course, array('state' => 'teams_selected'));
			}
		}
	}
	include('../includes/fetchTeams.php');
}
mysql_close();
echo json_encode($res);
?>