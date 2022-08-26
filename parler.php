<?php
session_start();
if (isset($_POST['msg'])) {
	include('session.php');
	if ($id) {
		include('initdb.php');
		include('onlineUtils.php');
		$course = getCourse(array('check_ban' => true));
		if ($course) {
			$msg = HTMLentities($_POST['msg']);
			$msg = preg_replace('#((https?|ftp|gopher|telnet|ms-help)://[\w\d:\#@%/;$()~_?\+\-=\\\.&]*)#', '<a href="$0" class="chatLink" target="_blank" rel="noopener noreferrer">$0</a>', $msg);
			mysql_query("INSERT INTO `mkchat` VALUES(null, $course, $id, '$msg')");
		}
		mysql_close();
	}
}
echo '1';
?>