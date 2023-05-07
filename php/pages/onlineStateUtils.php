<?php
function initCourseState($key) {
	mysql_query(
		'INSERT INTO `mkgamedata`
		SET game="'. $key .'",aRaceCount=0,raceCount=0
		ON DUPLICATE KEY UPDATE aRaceCount=raceCount'
	);
}
function resetCourseState($key) {
	mysql_query('DELETE FROM `mkgamedata` WHERE game="'. $key .'"');
}
function incCourseState($key) {
	mysql_query('UPDATE `mkgamedata` SET raceCount=aRaceCount+1 WHERE game="'. $key .'"');
}
function getCourseState($key) {
	if ($getState = mysql_fetch_array(mysql_query('SELECT raceCount FROM `mkgamedata` WHERE game="'. $key .'"')))
		return $getState;
	return array(
		'raceCount' => 0
	);
}
function getCourseExtra($course) {
	if ($getExtra = mysql_fetch_array(mysql_query('SELECT * FROM `mkgamestates` WHERE id='.$course)))
		return json_decode($getExtra['extra']);
	return null;
}
function setCourseExtra($course,$extra) {
	$extraJson = mysql_real_escape_string(json_encode($extra));
	mysql_query(
		'INSERT INTO `mkgamestates`
		SET id="'. $course .'",extra="'. $extraJson .'"
		ON DUPLICATE KEY UPDATE extra="'. $extraJson .'"'
	);
}
function getTeamSelectionTime($nbPlayers) {
	return $nbPlayers*5000 + 2000;
}
function getTeamPreviewTime($nbPlayers) {
	return 1000 + 250*$nbPlayers;
	//return 500 + 375*$nbPlayers;
}