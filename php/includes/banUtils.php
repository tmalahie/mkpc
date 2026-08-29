<?php
function isPermanentlyBanned($player) {
	$getBan = mysql_fetch_array(mysql_query('SELECT j.banned,b.end_date FROM `mkjoueurs` j LEFT JOIN `mkbans` b ON j.id=b.player WHERE j.id="'. $player .'"'));
	return $getBan && $getBan['banned'] && !$getBan['end_date'];
}
function isVisibleFollower($player) {
	$getFollower = mysql_fetch_array(mysql_query('SELECT j.banned,j.deleted,b.end_date FROM `mkjoueurs` j LEFT JOIN `mkbans` b ON j.id=b.player WHERE j.id="'. $player .'"'));
	return $getFollower && !$getFollower['deleted'] && !($getFollower['banned'] && !$getFollower['end_date']);
}
function visibleFollowerJoin($playerColumn) {
	return ' LEFT JOIN `mkjoueurs` pbj ON pbj.id='. $playerColumn .' LEFT JOIN `mkbans` pbb ON pbb.player='. $playerColumn;
}
function visibleFollowerCondition() {
	return '(pbj.id IS NOT NULL AND pbj.deleted=0 AND NOT (pbj.banned!=0 AND pbb.end_date IS NULL))';
}
function liftExpiredSanctions($player=null) {
	$playerFilter = ($player === null) ? '':' AND player="'. $player .'"';
	mysql_query('DELETE FROM `mkwarns` WHERE end_date IS NOT NULL AND end_date<CURDATE()'. $playerFilter);
	$banFilter = ($player === null) ? '':' AND b.player="'. $player .'"';
	mysql_query('UPDATE `mkjoueurs` j INNER JOIN `mkbans` b ON j.id=b.player SET j.banned=0 WHERE b.end_date IS NOT NULL AND b.end_date<CURDATE()'. $banFilter);
	mysql_query('DELETE FROM `mkbans` WHERE end_date IS NOT NULL AND end_date<CURDATE()'. $playerFilter);
}
function addSanction($player, $moderator, $type, $reason, $endDate=null) {
	mysql_query('INSERT INTO `mksanctions` SET player="'. $player .'",moderator="'. $moderator .'",type="'. $type .'",reason="'. $reason .'"'. ($endDate ? ',end_date="'. $endDate .'"':''));
}
?>
