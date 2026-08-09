<?php
function isPermanentlyBanned($player) {
	$getBan = mysql_fetch_array(mysql_query('SELECT j.banned,b.end_date FROM `mkjoueurs` j LEFT JOIN `mkbans` b ON j.id=b.player WHERE j.id="'. $player .'"'));
	return $getBan && $getBan['banned'] && !$getBan['end_date'];
}
function notPermanentlyBannedSql($playerColumn) {
	return 'NOT EXISTS(SELECT * FROM `mkjoueurs` pbj LEFT JOIN `mkbans` pbb ON pbj.id=pbb.player WHERE pbj.id='. $playerColumn .' AND pbj.banned!=0 AND pbb.end_date IS NULL)';
}
?>
