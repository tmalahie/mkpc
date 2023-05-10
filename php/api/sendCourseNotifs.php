<?php
header('Content-Type: text/plain');
include('../includes/session.php');
if ($id) {
	include('../includes/initdb.php');
	include('../includes/putCourseNotifs.php');
	mysql_close();
}
echo 1;
?>