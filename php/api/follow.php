<?php
header('Content-Type: text/plain');
if (isset($_POST['topic']) && is_numeric($_POST['topic'])) {
	include('../includes/session.php');
	if ($id) {
		include('../includes/initdb.php');
		if (mysql_fetch_array(mysql_query('SELECT * FROM `mktopics` WHERE id="'. $_POST['topic'] .'"'))) {
			if (isset($_POST['follow'])) {
				$getBanned = mysql_fetch_array(mysql_query('SELECT banned FROM `mkjoueurs` WHERE id="'. $id .'"'));
				$isBanned = $getBanned && $getBanned['banned'];
				if (!$isBanned) {
					mysql_query('INSERT IGNORE INTO `mkfollowers` VALUES("'. $id .'","'. $_POST['topic'] .'")');
					if ($getFirstMessage = mysql_fetch_array(mysql_query('SELECT auteur FROM `mkmessages` WHERE topic="'. $_POST['topic'] .'" AND id=1 LIMIT 1'))) {
						if ($getFirstMessage['auteur'] != $id)
							mysql_query('INSERT INTO `mknotifs` SET type="new_followtopic", user="'. $getFirstMessage['auteur'] .'", link="'.$_POST['topic'].','.$id.'"');
					}
				}
			}
			else
				mysql_query('DELETE FROM `mkfollowers` WHERE user="'. $id .'" AND topic="'. $_POST['topic'] .'"');
		}
		mysql_close();
	}
	echo 1;
}
?>