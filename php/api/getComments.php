<?php
header('Content-Type: text/plain');
if (isset($_POST['circuit']) &&  isset($_POST['type'])) {
	include('../includes/escape_all.php');
	$type = $_POST['type'];
	include('../includes/circuitTables.php');
	if (in_array($type, $circuitTables)) {
		include('../includes/session.php');
		$circuit = $_POST['circuit'];
		include('../includes/initdb.php');
		if ($getCircuit = mysql_fetch_array(mysql_query('SELECT * FROM `'. $type .'` WHERE id="'. $circuit .'"'))) {
			$paginationToken = isset($_POST['paginationToken']) ? intval($_POST['paginationToken']) : 0;
			$resPerPage = 50;
			$getMsgs = mysql_query('SELECT c.id,c.message,UNIX_TIMESTAMP(c.date) AS time,c.auteur,j.nom FROM `mkcomments` c LEFT JOIN `mkjoueurs` j ON c.auteur=j.id WHERE c.circuit="'. $circuit .'" AND c.type="'. $type .'"'. ($paginationToken ? " AND c.id<$paginationToken" : "") .' ORDER BY c.id DESC LIMIT '. $resPerPage);
			$getCount = mysql_fetch_array(mysql_query('SELECT COUNT(*) AS nb FROM `mkcomments` WHERE circuit="'. $circuit .'" AND type="'. $type .'"'));
			
			$msgs = array();
			while ($msg = mysql_fetch_array($getMsgs))
				$msgs[] = $msg;
			require_once('../includes/reactions.php');
			populateReactionsData('trackcom', $msgs);
			function escape($str) {
				return str_replace("\t", '\t', str_replace("\r", '\r', str_replace("\n", '\n', str_replace('"','\\"',str_replace('\\','\\\\',$str)))));
			}
			echo '{';
			if ($id) {
				if ($getAuteur = mysql_fetch_array(mysql_query('SELECT nom,banned FROM `mkjoueurs` WHERE id="'. $id .'"'))) {
					if (!$getAuteur['banned']) {
						require_once('../includes/getRights.php');
						echo '"id":'.$id.',';
						echo '"pseudo":"'.$getAuteur['nom'].'",';
						echo '"admin":'.(hasRight('moderator')?1:0).',';
					}
					else
						echo '"banned":'.$getAuteur['banned'].',';
				}
			}
			echo '"comments":[';
			foreach ($msgs as $msg) {
				if (isset($virgule))
					echo ',';
				else
					$virgule = true;
				ob_start();
				printReactions('trackcom', $msg['id'], $msg['reactions'], $id);
				$reactionsHtml = ob_get_clean();
				echo '{';
					echo '"id":'.$msg['id'].',';
					echo '"message":"'.escape($msg['message']).'",';
					echo '"auteur":"'.escape($msg['nom']).'",';
					echo '"auteurID":'.$msg['auteur'].',';
					echo '"date":"'.$msg['time'].'",';
					echo '"reactions":'.json_encode($reactionsHtml);
				echo '}';
			}
			echo ']';
			echo ',"count":'.$getCount['nb'];
			if (count($msgs) >= $resPerPage)
				echo ',"paginationToken":'.$msgs[count($msgs)-1]['id'];
			include('../includes/getId.php');
			if (($getCircuit['identifiant'] == $identifiants[0]) && ($getCircuit['identifiant2'] == $identifiants[1]) && ($getCircuit['identifiant3'] == $identifiants[2]) && ($getCircuit['identifiant4'] == $identifiants[3])) {
				echo ',"mine":true';
				mysql_query('DELETE n FROM `mknotifs` n INNER JOIN `mkcomments` m ON m.id=n.link WHERE n.identifiant='.$identifiants[0].' AND n.identifiant2='.$identifiants[1].' AND n.identifiant3='.$identifiants[2].' AND n.identifiant4='.$identifiants[3].' AND n.type="circuit_comment" AND m.type="'. $type .'" AND m.circuit="'. $circuit .'"');
			}
			echo '}';
			if ($id) {
				mysql_query('DELETE n FROM `mknotifs` n INNER JOIN `mkcomments` m ON m.id=n.link WHERE n.user="'. $id .'" AND n.type="answer_comment" AND m.type="'. $type .'" AND m.circuit="'. $circuit .'"');
				mysql_query('DELETE n FROM `mkcomments` c INNER JOIN `mkreactions` r ON r.type="trackcom" AND r.link=c.id INNER JOIN `mknotifs` n ON n.type="new_reaction" AND n.link=r.id WHERE c.type="'. $type .'" AND c.circuit="'. $circuit .'" AND c.auteur="'. $id .'" AND n.user="'. $id .'"');
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