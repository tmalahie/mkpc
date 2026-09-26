<?php
// Was the MySQL event `mkunban`: lifts the bans that have run their course, and logs each one.
require_once(__DIR__ .'/bootstrap.php');
exit(cron_run('unban', array(
	'UPDATE mkjoueurs j INNER JOIN mkbans b ON j.id=b.player SET j.banned=0 WHERE b.end_date<=CURDATE()',
	'DELETE i FROM ip_bans i INNER JOIN mkbans b ON i.player=b.player WHERE b.end_date<=CURDATE()',
	'INSERT INTO `mklogs` (auteur,log)
	SELECT 0 AS auteur, CONCAT("Unban ", player) AS log FROM mkbans WHERE end_date<=CURDATE()',
	'DELETE FROM mkbans WHERE end_date<=CURDATE()'
)));
