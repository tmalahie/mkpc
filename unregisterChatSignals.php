<?php
include('session.php');
$res = 0;
if (isset($_POST['sender']) && isset($_POST['receiver'])) {
    include('session.php');
	if ($id) {
    	include('initdb.php');
	    if ($getSender = mysql_fetch_array(mysql_query('SELECT player,course FROM mkchatvoc WHERE id="'.$_POST['sender'] .'"'))) {
		    if ($getReceiver = mysql_fetch_array(mysql_query('SELECT player,course FROM mkchatvoc WHERE id="'.$_POST['receiver'] .'"'))) {
				if (($getSender['player'] == $id) && ($getSender['course'] == $getReceiver['course'])) {
					mysql_query('DELETE FROM `mkchatvocpeer` WHERE sender="'.$_POST['sender'].'" AND receiver="'.$_POST['receiver'].'"');
					mysql_query('DELETE FROM `mkchatvocpeer` WHERE sender="'.$_POST['receiver'].'" AND receiver="'.$_POST['sender'].'"');
					mysql_query('UPDATE `mkchatvoc` SET syncid=syncid+1 WHERE id="'. $_POST['sender'] .'"');
				}
			}
	    }
    	mysql_close();
    }
	echo 1;
}
?>