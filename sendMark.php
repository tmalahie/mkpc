<?php
if (isset($_POST['note'])) $_POST['rating'] = $_POST['note']+1;
if (isset($_POST['id']) && isset($_POST['rating'])) {
	if (isset($_POST['mc']))
		$table = 'mkmcups';
	elseif (isset($_POST['cup']))
		$table = 'mkcups';
	elseif (isset($_POST['complete'])) {
		if ($_POST['complete'] == 1)
			$table = 'circuits';
		else
			$table = 'arenes';
	}
	else
		$table = 'mkcircuits';
	include('getId.php');
	include('initdb.php');
	include('session.php');
	$circuitId = +$_POST['id'];
	$rating = +$_POST['rating'];
	include('ip_banned.php');
	if (!$id || isBanned()) {
		mysql_close();
		exit;
	}
	$newMark = (($rating >= 1) && ($rating <= 5));
	if ($getIdentifier = mysql_fetch_array(mysql_query('SELECT identifiant FROM `'.$table.'` WHERE id="'.$circuitId.'"'))) {
		if ($getIdentifier && ($getIdentifier['identifiant'] != $identifiants[0])) {
			$getOldMark = mysql_query('SELECT rating FROM `mkratings` WHERE type="'.$table.'" AND circuit="'. $circuitId .'" AND identifiant='.$identifiants[0]);
			if ($oldMark = mysql_fetch_array($getOldMark)) {
				if ($newMark)
					mysql_query('UPDATE `mkratings` SET rating='. $rating .' WHERE type="'.$table.'" AND circuit="'.$circuitId.'" AND identifiant='. $identifiants[0]);
				else
					mysql_query('DELETE FROM `mkratings` WHERE type="'.$table.'" AND circuit="'.$circuitId.'" AND identifiant='. $identifiants[0]);
			}
			else if ($newMark)
				mysql_query('INSERT INTO `mkratings` SET type="'.$table.'",circuit="'.$circuitId.'",identifiant="'.$identifiants[0].'",player="'.$id.'",rating='.$rating);
			else {
				mysql_close();
				exit;
			}
			$getNotes = mysql_query("SELECT rating FROM `mkratings` WHERE type='$table' AND circuit='$circuitId'");
			$total = 0;
			$nbNotes = 0;
			while ($ratings = mysql_fetch_array($getNotes)) {
				$total += $ratings['rating'];
				$nbNotes++;
			}
			if ($nbNotes)
				$nNote = ($total/$nbNotes);
			else
				$nNote = -1;
			mysql_query('UPDATE `'.$table.'` SET note='.$nNote.',nbnotes='.$nbNotes.' WHERE id="'.$circuitId.'"');
		}
	}
	echo '1';
	mysql_close();
}
?>