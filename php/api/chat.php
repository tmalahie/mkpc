<?php
include('../includes/session.php');
header('Content-Type: text/plain');
if ($id) {
	include('../includes/initdb.php');
	include('../includes/onlineUtils.php');
	$course = getCourse();
	if ($course) {
		mysql_query('DELETE FROM mkmuted WHERE end_date<=NOW()');
		echo '[';
		$players = mysql_query('SELECT DISTINCT j.*,v.id AS peer,v.muted,(i.ignorer IS NOT NULL) AS ignored FROM (SELECT id,nom,course FROM mkjoueurs WHERE course='.$course.' UNION SELECT j.id,j.nom,j.course FROM mkspectators s INNER JOIN mkjoueurs j ON s.player=j.id WHERE s.course='. $course .') j LEFT JOIN `mkchatvoc` v ON v.player=j.id AND v.course='.$course.' LEFT JOIN `mkignores` i ON ((i.ignored=v.player AND i.ignorer='.$id.') OR (i.ignored='.$id.' AND i.ignorer=v.player))');
		$playersData = array();
		while ($player = mysql_fetch_array($players)) {
			$playerData = array(
				'id' => +$player['id'],
				'name' => $player['nom']
			);
			if ($player['peer'])
				$playerData['peer'] = +$player['peer'];
			if ($player['muted'])
				$playerData['muted'] = 1;
			if ($player['ignored'])
				$playerData['ignored'] = 1;
			if ($player['course'] !== $course)
				$playerData['spectator'] = 1;
			$playersData[] = $playerData;
		}
		echo json_encode($playersData);
		echo ',[';
		$lastMsg = isset($_POST['lastmsg']) ? intval($_POST['lastmsg']) : 0;
		$messages = mysql_query('SELECT * FROM (SELECT c.id,j.nom,c.auteur,c.message FROM `mkchat` c INNER JOIN `mkjoueurs` j ON j.id=c.auteur LEFT JOIN `mkignores` i ON i.ignored=c.auteur AND i.ignorer='.$id.' WHERE c.course='.$course.' AND i.ignorer IS NULL AND j.banned=0 AND c.id>'.$lastMsg.' ORDER BY c.id DESC LIMIT 10) t ORDER BY t.id');
		$virgule = false;
		while ($message = mysql_fetch_array($messages)) {
			echo ($virgule ? ',':'').'["'.$message['nom'].'","'.str_replace('"','\\"',str_replace('\\','\\\\',$message['message'])).'",'.$message['id'].']';
			$virgule = true;
		}
		echo ']]';
	}
	else
		echo -1;
	mysql_close();
}
else
	echo -1;
?>