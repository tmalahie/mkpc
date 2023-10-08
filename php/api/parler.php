<?php
header('Content-Type: text/plain');
session_start();
if (isset($_POST['msg'])) {
	include('../includes/session.php');
	if ($id) {
		include('../includes/getId.php');
		include('../includes/initdb.php');
		include('../includes/onlineUtils.php');
		$course = getCourse(array('check_ban' => true));
		if ($course) {
			function log_blacklist_msg($resultCode, $msgId) {
				global $id, $course;
				mysql_query('INSERT IGNORE INTO mkbadmsglog SET player='.$id.', course='.$course.', chat='.$msgId.', message="'. $_POST['msg'] .'", code="'. $resultCode .'"');
			}
			function mute_member() {
				global $id;
				$date = new \DateTime('+10 minutes');
				$dateStr = $date->format('Y-m-d H:i:s');
				mysql_query("INSERT IGNORE INTO mkmuted SET player=$id,end_date='$dateStr'");
			}
			function return_failure($resultCode, $shouldLog=true) {
				if ($shouldLog)
					log_blacklist_msg($resultCode, 0);
				echo $resultCode;
				mysql_close();
				exit;
			}
			$isMuted = mysql_fetch_array(mysql_query('SELECT player FROM mkmuted WHERE player="'. $id .'" OR identifiant="'. $identifiants[0] .'"'));
			if ($isMuted)
				return_failure(-4, false);
			$msg = $_POST['msg'];
			$courseOptions = mysql_fetch_array(mysql_query('SELECT IFNULL(g.public,m.link=0) AS public FROM `mariokart` m LEFT JOIN `mkgameoptions` g ON m.link=g.id WHERE m.id="'. $course .'"'));
			$shouldLog = false;
			if (!$courseOptions || $courseOptions['public']) {
				if (strlen($msg) > 255)
					return_failure(-3);
				$getBlacklist = mysql_query('SELECT word,action FROM mkbadwords');
				$blackListRegex = '';
				$blackListRegexParts = array(
					'mute' => array(),
					'block' => array(),
					'none' => array()
				);
				while ($blacklist = mysql_fetch_array($getBlacklist)) {
					$word = $blacklist['word'];
					$action = $blacklist['action'];
					$blackListRegexParts[$action][] = preg_quote($word);
				}
				$resultCode = 1;
				foreach ($blackListRegexParts as $action => $actionRegexPart) {
					$actionRegexPartStr = implode('|', $actionRegexPart);
					if ($actionRegexPartStr === '') continue;
					$blackListRegex = '#\b('. $actionRegexPartStr .')\b#i';
					if (preg_match($blackListRegex, $msg)) {
						switch ($action) {
						case 'mute':
							mute_member();
							return_failure(-1);
							break;
						case 'block':
							return_failure(-1);
							break;
						case 'none':
							$shouldLog = true;
							break;
						}
					}
				}
				$getRecentMsgs = mysql_fetch_array(mysql_query('SELECT COUNT(*) AS nb FROM mkchat WHERE course="'. $course .'" AND auteur="'. $id .'" AND date>NOW() - INTERVAL 10 SECOND'));
				if ($getRecentMsgs['nb'] >= 5)
					return_failure(-2);
			}
			$msg = HTMLentities($msg);
			$msg = preg_replace('#((https?|ftp|gopher|telnet|ms-help)://[\w\d:\#@%/;$()~_?\+\-=\\\.&]*)#', '<a href="$0" class="chatLink" target="_blank" rel="noopener noreferrer">$0</a>', $msg);
			mysql_query("INSERT INTO `mkchat` VALUES(null, $course, $id, '$msg',null)");
			if ($shouldLog) {
				$msgId = mysql_insert_id();
				if ($msgId)
					log_blacklist_msg(-1, $msgId);
			}
		}
		mysql_close();
	}
}
echo '1';
?>