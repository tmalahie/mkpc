<?php
header('Content-Type: text/plain');
include('../includes/session.php');
$res = 0;
if (isset($_POST['sender']) && isset($_POST['receiver'])) {
    include('../includes/session.php');
	if ($id) {
    	include('../includes/initdb.php');
		include('../includes/onlineUtils.php');
		$course = getCourse();
	    if ($getSender = mysql_fetch_array(mysql_query('SELECT player,course FROM mkchatvoc WHERE id="'.$_POST['sender'] .'"'))) {
		    if ($getReceiver = mysql_fetch_array(mysql_query('SELECT player,course FROM mkchatvoc WHERE id="'.$_POST['receiver'] .'"'))) {
				if (($getSender['player'] == $id) && ($getSender['course'] == $getReceiver['course'])) {
					if (isset($_POST['disconnect']) && ($course == $getSender['course']))
						mysql_query('DELETE v,p FROM `mkchatvoc` v LEFT JOIN `mkchatvocpeer` p ON v.id=p.sender OR v.id=p.receiver WHERE v.id="'.$_POST['receiver'].'"');
					else {
						mysql_query('DELETE FROM `mkchatvocpeer` WHERE sender="'.$_POST['sender'].'" AND receiver="'.$_POST['receiver'].'"');
						mysql_query('DELETE FROM `mkchatvocpeer` WHERE sender="'.$_POST['receiver'].'" AND receiver="'.$_POST['sender'].'"');
					}
				}
			}
	    }
    	mysql_close();
    }
	echo 1;
}
?>