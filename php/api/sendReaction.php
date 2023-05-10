<?php
//header('Content-Type: text/html');
if (isset($_POST['type']) && isset($_POST['link']) && isset($_POST['reaction'])) {
	include('../includes/session.php');
	if ($id) {
		require_once('../includes/reactions.php');
		include('../includes/initdb.php');
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
			function insertReactionAndNotify($author) {
				global $id,$type,$link;
				if (isset($_POST['delete'])) {
					mysql_query('DELETE FROM `mkreactions` WHERE type="'. $type .'" AND link="'. $link .'" AND member="'. $id .'" AND reaction="'. $_POST['reaction'] .'"');
				}
				else {
					mysql_query('INSERT IGNORE INTO `mkreactions` SET type="'. $type .'",link="'. $link .'",member="'. $id .'",reaction="'. $_POST['reaction'] .'"');
					$reactionId = mysql_insert_id();
					if ($reactionId) {
						if ($author != $id)
							mysql_query('INSERT INTO `mknotifs` SET type="new_reaction", user="'. $author .'", link="'. $reactionId .'"');
					}
				}
			}
			switch ($type) {
			case 'topic' :
				$msgIdParts = explode(',', $link);
				if (count($msgIdParts) === 2) {
					$topicId = intval($msgIdParts[0]);
					$msgId = intval($msgIdParts[1]);
					if ($message = mysql_fetch_array(mysql_query('SELECT auteur FROM mkmessages WHERE topic="'. $topicId .'" AND id="'. $msgId .'"'))) {
						$link = "$topicId,$msgId";
						insertReactionAndNotify($message['auteur']);
					}
				}
				break;
			case 'news' :
				$link = intval($link);
				if ($message = mysql_fetch_array(mysql_query('SELECT author FROM mknews WHERE id="'. $link .'"'))) {
					insertReactionAndNotify($message['author']);
				}
				break;
			case 'newscom' :
				$link = intval($link);
				if ($message = mysql_fetch_array(mysql_query('SELECT author FROM mknewscoms WHERE id="'. $link .'"'))) {
					insertReactionAndNotify($message['author']);
				}
				break;
			case 'trackcom' :
				$link = intval($link);
				if ($message = mysql_fetch_array(mysql_query('SELECT auteur FROM mkcomments WHERE id="'. $link .'"'))) {
					insertReactionAndNotify($message['auteur']);
				}
				break;
			}
		}
		printReactions($type,$link, getReactions($type,$link));
		mysql_close();
	}
}
?>