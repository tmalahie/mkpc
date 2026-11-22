<?php
// Season 1 opens on 28 November 2026 at 18:00, French time. Until then the season holds what
// the testers played on mkpc-test, which shares this database: it is cleared, the season is
// given its opening time - ranked stays hidden before it - and the players staff placed start
// from their placement.
//
// Everything played before the opening is test data by definition. A mogi started after it
// is real, so the migration refuses rather than clear a season already under way.
require_once(__DIR__ .'/../includes/lounge/common.php');

$opensAt = '2026-11-28 18:00:00';

$real = mysql_fetch_array(mysql_query(
	'SELECT COUNT(*) AS n FROM `mklounge_matches`
	WHERE season=1 AND IFNULL(started_at, ended_at) >= "'. $opensAt .'"'
));
if (intval($real['n']))
	throw new RuntimeException('Season 1 has '. intval($real['n']) .' mogi(s) played since it opened: not clearing it.');

// What is cleared is kept beside the database, for a look back at the test season.
$backup = array();
foreach (array(
	'mklounge_match_players' => 'SELECT mp.* FROM `mklounge_match_players` mp INNER JOIN `mklounge_matches` m ON m.id=mp.`match` WHERE m.season=1',
	'mklounge_matches' => 'SELECT * FROM `mklounge_matches` WHERE season=1',
	'mklounge_queue_members' => 'SELECT qm.* FROM `mklounge_queue_members` qm INNER JOIN `mklounge_queues` q ON q.id=qm.queue WHERE q.season=1',
	'mklounge_queues' => 'SELECT * FROM `mklounge_queues` WHERE season=1',
	'mklounge_players' => 'SELECT * FROM `mklounge_players` WHERE season=1',
	'mklounge_seasons' => 'SELECT * FROM `mklounge_seasons` WHERE id=1'
) as $table => $query) {
	$backup[$table] = array();
	$res = mysql_query($query);
	while ($row = mysql_fetch_array($res))
		$backup[$table][] = array_filter($row, 'is_string', ARRAY_FILTER_USE_KEY);
}
$backupFile = sys_get_temp_dir() .'/mkpc-lounge-season-1-tests-'. date('Ymd-His') .'.json';
if (file_put_contents($backupFile, json_encode($backup)) === false)
	throw new RuntimeException('Could not write the backup to '. $backupFile);
echo "  test season saved to $backupFile\n";

mysql_query('DELETE mp FROM `mklounge_match_players` mp INNER JOIN `mklounge_matches` m ON m.id=mp.`match` WHERE m.season=1');
mysql_query('DELETE FROM `mklounge_matches` WHERE season=1');
mysql_query('DELETE qm FROM `mklounge_queue_members` qm INNER JOIN `mklounge_queues` q ON q.id=qm.queue WHERE q.season=1');
mysql_query('DELETE FROM `mklounge_queues` WHERE season=1');
mysql_query('DELETE FROM `mklounge_players` WHERE season=1');
mysql_query('UPDATE `mklounge_seasons` SET started_at="'. $opensAt .'" WHERE id=1');

// Staff's placements, by account id; the names are the ones staff gave, for reference.
$placements = array(
	3586 => 1900, // Fways
	142608 => 1900, // Nodac64
	6013 => 1900, // Mamdani
	32396 => 1900, // JPG
	40764 => 1900, // BFDI_Ahmad19
	48717 => 1900, // Anthcny
	26749 => 1812, // hoppingicon
	2843 => 1786, // WaluigiCeausescu
	49980 => 1690, // Pianta
	73585 => 1644, // TheNudgyHampter
	206943 => 1671, // Mapno4
	5164 => 1462, // mudky
	40396 => 1270, // Jae
	69528 => 1339, // FoxgirlThing
	6668 => 1244, // misete
	162813 => 1225, // Theodore12
	54725 => 1194, // CALEBWILLIAMS
	70405 => 1096, // Keqing
	36925 => 1036, // BowserJr03
	93180 => 1089, // Sabiocyclone68
	147254 => 994, // Firehop
	8818 => 1008, // InfiMK
	77894 => 1069, // Acria68
	142588 => 781, // Noble8DX
	142461 => 856, // Kernell
	60715 => 597, // Mia4523
	189444 => 613, // BlueKirby24
	190734 => 419, // OCM
	159617 => 453, // coolmonkey
	178029 => 503, // KeinmasMK
	80540 => 499, // HidingKorok
	151538 => 384, // Gecko_
	203715 => 465, // CookieBiscuit
	11945 => 352, // NitroraptorXTS
	57874 => 466, // -inator
	58901 => 350, // Meleemuseum
	174220 => 350, // Itari
	163657 => 350, // RedPikmin95
	169774 => 365, // -iz-
	154423 => 495 // 3_Of_A_Kind
);
// a database without these accounts - a developer's, CI's - gets none of them
$existing = array();
$res = mysql_query('SELECT id FROM `mkjoueurs` WHERE deleted=0 AND id IN ('. implode(',', array_keys($placements)) .')');
while ($row = mysql_fetch_array($res))
	$existing[intval($row['id'])] = $placements[intval($row['id'])];
lounge_edit_ratings(0, array(), $existing);
echo '  '. count($existing) .' of '. count($placements) ." placements set\n";
