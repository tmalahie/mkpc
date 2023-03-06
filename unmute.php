<?php
header('Content-Type: text/plain');
include('session.php');
if ($id && isset($_POST['member']) && is_numeric($_POST['member'])) {
	include('initdb.php');
    $member = $_POST['member'];
	if (mysql_numrows(mysql_query("SELECT * FROM `mkjoueurs` WHERE id=$member"))) {
        require_once('getRights.php');
        if (hasRight('moderator')) {
            mysql_query("DELETE FROM mkmuted WHERE player=$member");
            mysql_query('INSERT INTO `mklogs` VALUES(NULL,NULL, '. $id .', "Unmute '. $member .'")');
        }
	}
	echo 1;
	mysql_close();
}
?>