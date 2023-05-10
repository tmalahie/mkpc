<?php
header('Content-Type: text/plain');
if (isset($_POST['circuit']) &&  isset($_POST['type']) && isset($_POST['message'])) {
	include('../includes/session.php');
	include('../includes/escape_all.php');
	if ($id) {
		$message = $_POST['message'];
		if ($message) {
			$type = $_POST['type'];
			include('../includes/circuitTables.php');
			if (in_array($type, $circuitTables)) {
				$circuit = $_POST['circuit'];
				include('../includes/initdb.php');
				$getBanned = mysql_fetch_array(mysql_query('SELECT banned FROM `mkjoueurs` WHERE id="'. $id .'"'));
				if ($getBanned && $getBanned['banned']) {
					mysql_close();
					exit;
				}
				include('../includes/getId.php');
				include('../includes/utils-cooldown.php');
				if (isTrackComCooldowned()) {
					logCooldownEvent('track_com');
					mysql_close();
					exit;
				}
				if ($getCircuit = mysql_fetch_array(mysql_query('SELECT * FROM `'. $type .'` WHERE id="'. $circuit.'"'))) {
					mysql_query('INSERT INTO `mkcomments` VALUES(NULL,"'.$circuit.'","'.$type.'","'.$id.'","'.$message.'",NULL)');
					$commentID = mysql_insert_id();
					include('../includes/getId.php');
					if (($getCircuit['identifiant'] != $identifiants[0]) || ($getCircuit['identifiant2'] != $identifiants[1]) || ($getCircuit['identifiant3'] != $identifiants[2]) || ($getCircuit['identifiant4'] != $identifiants[3]))
						mysql_query('INSERT INTO `mknotifs` SET type="circuit_comment", identifiant="'. $getCircuit['identifiant'] .'",identifiant2="'. $getCircuit['identifiant2'] .'",identifiant3="'. $getCircuit['identifiant3'] .'",identifiant4="'. $getCircuit['identifiant4'] .'", link="'. $commentID .'"');
					$otherComments = mysql_query('SELECT DISTINCT auteur FROM `mkcomments` WHERE circuit="'. $circuit .'" AND type="'. $type .'" AND auteur!="'. $id .'"');
					while ($otherComment = mysql_fetch_array($otherComments))
						mysql_query('INSERT INTO `mknotifs` SET type="answer_comment", user="'. $otherComment['auteur'] .'", link="'. $commentID .'"');
					$getComments = mysql_fetch_array(mysql_query('SELECT COUNT(*) AS nb FROM `mkcomments` WHERE circuit="'. $circuit .'" AND type="'. $type .'"'));
					mysql_query('UPDATE `'.$type.'` SET nbcomments="'. $getComments['nb'] .'" WHERE id="'. $circuit .'"');
					mysql_query('UPDATE `mkprofiles` SET last_connect=NULL WHERE id='. $id);
					echo $commentID;
				}
				mysql_close();
			}
		}
	}
}
?>