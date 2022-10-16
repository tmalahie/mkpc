<?php
session_start();
if (isset($_POST['msg'])) {
	include('session.php');
	if ($id) {
		include('initdb.php');
		include('onlineUtils.php');
		$course = getCourse(array('check_ban' => true));
		function return_failure($resultCode) {
			global $id;
			mysql_query('INSERT IGNORE INTO mkbadmsglog SET player='.$id.', message="'. $_POST['msg'] .'", code="'. $resultCode .'"');
			echo $resultCode;
			mysql_close();
			exit;
		}
		if ($course) {
			$msg = $_POST['msg'];
			$courseOptions = mysql_fetch_array(mysql_query('SELECT IFNULL(g.public,m.link=0) AS public FROM `mariokart` m LEFT JOIN `mkgameoptions` g ON m.link=g.id WHERE m.id="'. $course .'"'));
			if (!$courseOptions || $courseOptions['public']) {
				if (strlen($msg) > 255)
					return_failure(-3);
				$getBlacklist = mysql_query('SELECT word FROM mkbadwords');
				$blackListRegex = '';
				$blackListRegexParts = array();
				while ($blacklist = mysql_fetch_array($getBlacklist)) {
					$word = $blacklist['word'];
					$blackListRegexParts[] = preg_quote($blacklist['word']);
				}
				$resultCode = 1;
				if (!empty($blackListRegexParts)) {
					$blackListRegex = '#\b('. implode('|', $blackListRegexParts) .')\b#i';
					if (preg_match($blackListRegex, $msg))
						return_failure(-1);
				}
				$getRecentMsgs = mysql_fetch_array(mysql_query('SELECT COUNT(*) AS nb FROM mkchat WHERE course="'. $course .'" AND auteur="'. $id .'" AND date>NOW() - INTERVAL 10 SECOND'));
				if ($getRecentMsgs['nb'] >= 5)
					return_failure(-2);
			}
			$msg = HTMLentities($msg);
			$msg = preg_replace('#((https?|ftp|gopher|telnet|ms-help)://[\w\d:\#@%/;$()~_?\+\-=\\\.&]*)#', '<a href="$0" class="chatLink" target="_blank" rel="noopener noreferrer">$0</a>', $msg);
			mysql_query("INSERT INTO `mkchat` VALUES(null, $course, $id, '$msg',null)");
		}
		mysql_close();
	}
}
echo '1';
?>