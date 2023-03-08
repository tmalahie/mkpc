<?php
header('Content-Type: text/plain');
include('session.php');
if ($id && isset($_POST['member']) && is_numeric($_POST['member']) && isset($_POST['duration']) && is_numeric($_POST['duration'])) {
	include('initdb.php');
    $member = $_POST['member'];
	if (mysql_numrows(mysql_query("SELECT * FROM `mkjoueurs` WHERE id=$member"))) {
        require_once('getRights.php');
        if (hasRight('moderator')) {
            $date = new \DateTime();
            $date->modify('+'. $_POST['duration'] .' minutes');
            $dateStr = $date->format('Y-m-d H:i:s');
            mysql_query("INSERT INTO mkmuted SET player=$member,end_date='$dateStr' ON DUPLICATE KEY UPDATE end_date=VALUES(end_date)");
            mysql_query('DELETE v,p FROM `mkchatvoc` v LEFT JOIN `mkchatvocpeer` p ON v.id=p.sender OR v.id=p.receiver WHERE v.player='.$member);
            mysql_query('INSERT INTO `mklogs` VALUES(NULL,NULL, '. $id .', "Mute '. $member .' '. $_POST['duration'] .'")');
        }
	}
	echo 1;
	mysql_close();
}
?>