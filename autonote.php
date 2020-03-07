<?php
if ($identifiants[0] != 1390635815)
	exit;
include('initdb.php');
include('getId.php');
$cups = mysql_query('SELECT * FROM `mkcups` WHERE identifiant!='.$identifiants[0].' OR identifiant2!='.$identifiants[1].' OR identifiant3!='.$identifiants[2].' OR identifiant4!='.$identifiants[3]);
while ($cup = mysql_fetch_array($cups)) {
	if (!mysql_numrows(mysql_query('SELECT * FROM `mkavis` WHERE circuit='. $cup['id'] .' AND identifiant='.$identifiants[0].' AND identifiant2='.$identifiants[1].' AND identifiant3='.$identifiants[2].' AND identifiant4='.$identifiants[3]))) {
		$mkCircuits = $cup['mode'] ? 'circuits':'mkcircuits';
		$mkNotes = $cup['mode'] ? 'notes':'mknotes';
		$circuits = mysql_query('SELECT * FROM `'. $mkCircuits .'` WHERE id='. $cup['circuit0'] .' OR id='. $cup['circuit1'] .' OR id='. $cup['circuit2'] .' OR id='. $cup['circuit3']);
		$total = 0;
		for ($n=0;$circuit=mysql_fetch_array($circuits);$n++) {
			$circuitID = max($circuit['ID'],$circuit['id']);
			if ($getNote = mysql_fetch_array(mysql_query('SELECT * FROM `'. $mkNotes .'` WHERE circuit='. $circuitID .' AND identifiant='.$identifiants[0].' AND identifiant2='.$identifiants[1].' AND identifiant3='.$identifiants[2].' AND identifiant4='.$identifiants[3]))) {
				$total += $getNote['note'];
			}
			else {
				$n = 0;
				break;
			}
		}
		if ($n) {
			$note = round($total/$n);
			echo('INSERT INTO `mkavis` VALUES('. $cup['id'] .','. $identifiants[0] .','. $identifiants[1] .','. $identifiants[2] .','. $identifiants[3] .','.$note.')');
			echo '<br />';
			mysql_query('INSERT INTO `mkavis` VALUES('. $cup['id'] .','. $identifiants[0] .','. $identifiants[1] .','. $identifiants[2] .','. $identifiants[3] .','.$note.')');
			$getAverage = mysql_fetch_array(mysql_query('SELECT AVG(note) AS notes, COUNT(*) AS nbnotes FROM `mkavis` WHERE circuit='. $cup['id']));
			echo('UPDATE `mkcups` SET note='. $getAverage['notes'].',nbnotes='.$getAverage['nbnotes'].' WHERE id='. $cup['id']);
			echo '<br />';
			echo '<br />';
			mysql_query('UPDATE `mkcups` SET note='. $getAverage['notes'].',nbnotes='.$getAverage['nbnotes'].' WHERE id='. $cup['id']);
		}
	}
}
mysql_close();
?>