<?php
if (isset($_POST['type']) && isset($_POST['link']) && isset($_POST['reaction'])) {
	include('session.php');
	if ($id) {
		require_once('reactions.php');
		include('initdb.php');
		$type = $_POST['type'];
		$link = $_POST['link'];
		$getBanned = mysql_fetch_array(mysql_query('SELECT banned FROM `mkjoueurs` WHERE id="'. $id .'"'));
		if ($getBanned && $getBanned['banned']) {
			printReactions($type,$link, getReactions($type,$link));
			mysql_close();
			exit;
		}
		if (!isReactionValid($_POST['reaction'])) {
			printReactions($type,$link, getReactions($type,$link));
			mysql_close();
			exit;
		}
		if (isReactionValid($_POST['reaction'])) {
			switch ($type) {
			case 'topic' :
				$msgIdParts = explode(',', $link);
				if (count($msgIdParts) === 2) {
					$topicId = +$msgIdParts[0];
					$msgId = +$msgIdParts[1];
					if ($message = mysql_fetch_array(mysql_query('SELECT auteur FROM mkmessages WHERE topic="'. $topicId .'" AND id="'. $msgId .'"'))) {
						$link = "$topicId,$msgId";
						if (isset($_POST['delete'])) {
							mysql_query('DELETE FROM `mkreactions` WHERE type="'. $type .'" AND link="'. $link .'" AND member="'. $id .'" AND reaction="'. $_POST['reaction'] .'"');
						}
						else {
							mysql_query('INSERT IGNORE INTO `mkreactions` SET type="'. $type .'",link="'. $link .'",member="'. $id .'",reaction="'. $_POST['reaction'] .'"');
							$reactionId = mysql_insert_id();
							if ($reactionId) {
								if ($message['auteur'] != $id)
									mysql_query('INSERT INTO `mknotifs` SET type="new_reaction", user="'. $message['auteur'] .'", link="'. $reactionId .'"');
							}
						}
					}
				}
			}
		}
		printReactions($type,$link, getReactions($type,$link));
		mysql_close();
	}
}
?>