<?php
if (isset($_POST['id']) && isset($_POST['note'])) {
	$isCup = false;
	if (isset($_POST['mc'])) {
		$table = 'mkmcups';
		$table2 = 'ratings';
		$isCup = true;
	}
	elseif (isset($_POST['cup'])) {
		$table = 'mkcups';
		$table2 = 'mkavis';
		$isCup = true;
	}
	elseif (isset($_POST['complete'])) {
		if ($_POST['complete'] == 1) {
			$table = 'circuits';
			$table2 = 'notes';
		}
		else {
			$table = 'arenes';
			$table2 = 'marks';
		}
	}
	else {
		$table = 'mkcircuits';
		$table2 = 'mknotes';
	}
	include('getId.php');
	include('initdb.php');
	$id = $_POST['id'];
	$note = $_POST['note'];
	include('ip_banned.php');
	if (isBanned()) {
		mysql_close();
		exit;
	}
	$newMark = (($note >= 0) && ($note < 5));
	if (!mysql_numrows(mysql_query('SELECT * FROM `'.$table.'` WHERE id="'.$id.'" AND identifiant='. $identifiants[0].' AND identifiant2='.$identifiants[1].' AND identifiant3='.$identifiants[2].' AND identifiant4='.$identifiants[3]))) {
		if ($getNote = mysql_fetch_array(mysql_query('SELECT note FROM `'.$table.'` WHERE id="'.$id.'"'))) {
			$getOldMark = mysql_query('SELECT note FROM `'.$table2.'` WHERE circuit="'. $id .'" AND identifiant='.$identifiants[0].' AND identifiant2='.$identifiants[1].' AND identifiant3='.$identifiants[2].' AND identifiant4='.$identifiants[3]);
			if ($oldMark = mysql_fetch_array($getOldMark)) {
				if ($newMark)
					mysql_query('UPDATE `'.$table2.'` SET note='. $note .' WHERE circuit="'.$id.'" AND identifiant='. $identifiants[0].' AND identifiant2='.$identifiants[1].' AND identifiant3='.$identifiants[2].' AND identifiant4='.$identifiants[3]);
				else
					mysql_query('DELETE FROM `'.$table2.'` WHERE circuit="'.$id.'" AND identifiant='. $identifiants[0].' AND identifiant2='.$identifiants[1].' AND identifiant3='.$identifiants[2].' AND identifiant4='.$identifiants[3]);
			}
			else if ($newMark)
				mysql_query('INSERT INTO `'.$table2.'` VALUES("'.$id.'",'.$identifiants[0].','.$identifiants[1].','.$identifiants[2].','.$identifiants[3].','.$note.')');
			else {
				mysql_close();
				exit;
			}
			$getNotes = mysql_query('SELECT note FROM `'.$table2.'` WHERE circuit="'.$id.'"');
			$total = 0;
			$nbNotes = 0;
			while ($notes = mysql_fetch_array($getNotes)) {
				$total += $notes['note'];
				$nbNotes++;
			}
			if ($nbNotes)
				$nNote = ($total/$nbNotes);
			else
				$nNote = -1;
			mysql_query('UPDATE `'.$table.'` SET note='.$nNote.',nbnotes='.$nbNotes.' WHERE id="'.$id.'"');
			echo '1';
		}
	}
	mysql_close();
}
?>