<?php
if (isset($_POST['sender']) && isset($_POST['receiver']) && isset($_POST['lastsignalid'])) {
    header('Content-Type: application/json');
    $res = array();
    include('../includes/session.php');
    if (!$id) {
        echo json_encode($res);
        exit;
    }
	include('../includes/initdb.php');
    if ($getSender = mysql_fetch_array(mysql_query('SELECT player,course FROM mkchatvoc WHERE id="'.$_POST['sender'] .'"'))) {
        if ($getReceiver = mysql_fetch_array(mysql_query('SELECT player,course FROM mkchatvoc WHERE id="'.$_POST['receiver'] .'"'))) {
            if (($getReceiver['player'] == $id) && ($getSender['course'] == $getReceiver['course'])) {
                $getSignals = mysql_query('SELECT id,signal_data FROM `mkchatvocpeer` WHERE sender="'.$_POST['sender'].'" AND receiver="'.$_POST['receiver'].'" AND id>"'.$_POST['lastsignalid'].'" ORDER BY id');
                while ($signal = mysql_fetch_array($getSignals)) {
                    $res[] = array(
                        'id' => $signal['id'],
                        'data' => $signal['signal_data']
                    );
                }
            }
        }
    }
    mysql_close();
	echo json_encode($res);
}
?>