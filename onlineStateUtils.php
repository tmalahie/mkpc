<?php
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
?>