<?php
function isPermanentlyBanned($player) {
	$getBan = mysql_fetch_array(mysql_query('SELECT j.banned,b.end_date FROM `mkjoueurs` j LEFT JOIN `mkbans` b ON j.id=b.player WHERE j.id="'. $player .'"'));
	return $getBan && $getBan['banned'] && !$getBan['end_date'];
}
function notPermanentlyBannedJoin($playerColumn) {
	return ' LEFT JOIN `mkjoueurs` pbj ON pbj.id='. $playerColumn .' LEFT JOIN `mkbans` pbb ON pbb.player='. $playerColumn;
}
function notPermanentlyBannedCondition() {
	return 'NOT (COALESCE(pbj.banned,0)!=0 AND pbb.end_date IS NULL)';
}
?>
