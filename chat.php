<?php
session_start();
if (!empty($_SESSION['mkid'])) {
	$id = $_SESSION['mkid'];
	include('initdb.php');
	$getCourse = mysql_fetch_array(mysql_query('SELECT course FROM `mkjoueurs` WHERE id="'.$id.'"'));
	$course = $getCourse['course'];
	if ($course) {
		echo '[[';
		$joueurs = mysql_query('SELECT e.nom FROM `mkjoueurs` j INNER JOIN `mkjoueurs` e ON j.id=e.id WHERE j.course='.$course.' AND j.id!="'.$id.'"');
		if ($joueur = mysql_fetch_array($joueurs)) {
			echo '"'.$joueur['nom'].'"';
			while ($joueur = mysql_fetch_array($joueurs))
				echo ',"'.$joueur['nom'].'"';
		}
		echo '],[';
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