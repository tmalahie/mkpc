<?php
include('session.php');
if ($id) {
	include('initdb.php');
	$getCourse = mysql_fetch_array(mysql_query('SELECT course FROM `mkjoueurs` WHERE id="'.$id.'"'));
	$course = $getCourse['course'];
	if ($course) {
		mysql_query('DELETE FROM mkmuted WHERE end_date<=NOW()');
		echo '[';
		$players = mysql_query('SELECT j.id,j.nom,v.id AS peer FROM `mkjoueurs` j LEFT JOIN `mkchatvoc` v ON j.id=v.player AND j.course=v.course WHERE j.course='.$course.' AND j.id!="'.$id.'"');
		$playersData = array();
		while ($player = mysql_fetch_array($players)) {
			$playerData = array(
				'id' => $player['id'],
				'name' => $player['nom']
			);
			if ($player['peer'])
				$playerData['peer'] = +$player['peer'];
			$playersData[] = $playerData;
		}
		echo json_encode($playersData);
		echo ',[';
		$messages = mysql_query('SELECT * FROM (SELECT c.id,j.nom,c.auteur,c.message FROM `mkchat` c INNER JOIN `mkjoueurs` j ON j.id=c.auteur LEFT JOIN `mkignores` i ON i.ignored=c.auteur AND i.ignorer='.$id.' WHERE c.course='.$course.' AND i.ignorer IS NULL AND j.banned=0 ORDER BY c.id DESC LIMIT 10) t ORDER BY t.id');
		$virgule = false;
		while ($message = mysql_fetch_array($messages)) {
			echo ($virgule ? ',':'').'["'.$message['nom'].'","'.str_replace('"','\\"',str_replace('\\','\\\\',$message['message'])).'"]';
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