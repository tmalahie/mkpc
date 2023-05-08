<?php
header('Content-Type: text/plain');
include('../includes/session.php');
$res = 0;
if (isset($_POST['sender']) && isset($_POST['receiver']) && isset($_POST['signals'])) {
    include('../includes/session.php');
	if ($id) {
		$signals = json_decode($_POST['signals']);
    	include('../includes/initdb.php');
	    if ($getSender = mysql_fetch_array(mysql_query('SELECT player,course FROM mkchatvoc WHERE id="'.$_POST['sender'] .'"'))) {
		    if ($getReceiver = mysql_fetch_array(mysql_query('SELECT player,course FROM mkchatvoc WHERE id="'.$_POST['receiver'] .'"'))) {
				if (($getSender['player'] == $id) && ($getSender['course'] == $getReceiver['course'])) {
					foreach ($signals as $ignal)
						mysql_query('INSERT INTO `mkchatvocpeer` SET sender="'.$_POST['sender'].'", receiver="'.$_POST['receiver'].'", signal_data="'.mysql_real_escape_string(json_encode($ignal)).'"');
				}
			}
	    }
    	mysql_close();
    }
	echo 1;
}
?>