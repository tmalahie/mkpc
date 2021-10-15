<?php
session_start();
if (!empty($_SESSION['mkid']) && isset($_POST['msg'])) {
	$id = $_SESSION['mkid'];
	include('initdb.php');
	if ($getCourse = mysql_fetch_array(mysql_query('SELECT course,banned FROM `mkjoueurs` j LEFT JOIN mkmuted m ON j.id=m.player WHERE j.id="'.$id.'" AND m.player IS NULL'))) {
		if (!$getCourse['banned']) {
			$course = $getCourse['course'];
			if ($course) {
				$msg = HTMLentities($_POST['msg']);
				$msg = preg_replace('#((https?|ftp|gopher|telnet|ms-help)://[\w\d:\#@%/;$()~_?\+\-=\\\.&]*)#', '<a href="$0" class="chatLink" target="_blank" rel="noopener noreferrer">$0</a>', $msg);
				mysql_query("INSERT INTO `mkchat` VALUES(null, $course, $id, '$msg')");
			}
		}
	}
	mysql_close();
}
echo '1';
?>