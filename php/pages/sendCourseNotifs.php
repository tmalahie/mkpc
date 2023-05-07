<?php
header('Content-Type: text/plain');
include('session.php');
if ($id) {
	include('initdb.php');
	include('putCourseNotifs.php');
	mysql_close();
}
echo 1;
?>