<?php
if (isset($_POST['circuit']) &&  isset($_POST['type'])) {
	include('escape_all.php');
	$type = $_POST['type'];
	include('circuitTables.php');
	if (in_array($type, $circuitTables)) {
		include('session.php');
		$circuit = $_POST['circuit'];
		include('initdb.php');
		if ($getCircuit = mysql_fetch_array(mysql_query('SELECT * FROM `'. $type .'` WHERE id="'. $circuit .'"'))) {
			$getMsgs = mysql_query('SELECT c.id,c.message,UNIX_TIMESTAMP(c.date) AS time,c.auteur,j.nom FROM `mkcomments` c LEFT JOIN `mkjoueurs` j ON c.auteur=j.id WHERE c.circuit="'. $circuit .'" AND c.type="'. $type .'" ORDER BY c.id DESC');
			$msgs = array();
			function escape($str) {
				return str_replace("\t", '\t', str_replace("\r", '\r', str_replace("\n", '\n', str_replace('"','\\"',str_replace('\\','\\\\',$str)))));
			}
			echo '{';
			if ($id) {
				if ($getAuteur = mysql_fetch_array(mysql_query('SELECT nom,banned FROM `mkjoueurs` WHERE id="'. $id .'"'))) {
					if (!$getAuteur['banned']) {
						require_once('getRights.php');
						echo '"id":'.$id.',';
						echo '"pseudo":"'.$getAuteur['nom'].'",';
						echo '"admin":'.(hasRight('moderator')?1:0).',';
					}
					else
						echo '"banned":'.$getAuteur['banned'].',';
				}
			}
			echo '"comments":[';
			while ($msg = mysql_fetch_array($getMsgs)) {
				if (isset($virgule))
					echo ',';
				else
					$virgule = true;
				echo '{';
					echo '"id":'.$msg['id'].',';
					echo '"message":"'.escape($msg['message']).'",';
					echo '"auteur":"'.escape($msg['nom']).'",';
					echo '"auteurID":'.$msg['auteur'].',';
					echo '"date":"'.$msg['time'].'"';
				echo '}';
			}
			echo ']';
			echo '}';
			include('getId.php');
			if (($getCircuit['identifiant'] == $identifiants[0]) && ($getCircuit['identifiant2'] == $identifiants[1]) && ($getCircuit['identifiant3'] == $identifiants[2]) && ($getCircuit['identifiant4'] == $identifiants[3]))
				mysql_query('DELETE n FROM `mknotifs` n INNER JOIN `mkcomments` m ON m.id=n.link WHERE n.type="circuit_comment" AND m.type="'. $type .'" AND m.circuit="'. $circuit .'"');
			if ($id) {
				mysql_query('DELETE n FROM `mknotifs` n INNER JOIN `mkcomments` m ON m.id=n.link WHERE n.user="'. $id .'" AND n.type="answer_comment" AND m.type="'. $type .'" AND m.circuit="'. $circuit .'"');
				$notifTypes = array(
					'mkcircuits' => 0,
					'circuits' => 1,
					'arenes' => 2,
					'mkcups' => 3,
					'mkmcups' => 4
				);
				$notifType = $notifTypes[$type];
				mysql_query('DELETE FROM `mknotifs` WHERE user="'. $id .'" AND type="follower_circuit" AND link="'. $notifType .','. $circuit .'"');
			}
		}
		mysql_close();
	}
}
?>