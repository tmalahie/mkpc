<?php
header('Content-Type: text/plain');
include('../includes/session.php');
if ($id && isset($_POST['member']) && is_numeric($_POST['member'])) {
	include('../includes/initdb.php');
    $member = intval($_POST['member']);
	if (mysql_numrows(mysql_query("SELECT * FROM `mkjoueurs` WHERE id=$member"))) {
        require_once('../includes/getRights.php');
        if (hasRight('moderator')) {
            mysql_query("DELETE FROM mkmuted WHERE player=$member");
            if (isset($_POST['ip'])) {
                if ($getIp = mysql_fetch_array(mysql_query('SELECT identifiant FROM `mkprofiles` WHERE id='. $member)))
                    mysql_query('DELETE FROM `mkmuted` WHERE identifiant='. $getIp['identifiant']);
            }
            mysql_query('INSERT INTO `mklogs` VALUES(NULL,NULL, '. $id .', "Unmute '. $member .'")');
        }
	}
	echo 1;
	mysql_close();
}
?>