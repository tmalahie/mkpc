<?php
header('Content-Type: application/json');
include('../includes/session.php');

if ($id && isset($_POST['member']) && isset($_POST['lastID'])) {
    include('../includes/initdb.php');
    include('../includes/o_consts.php');
    include('../includes/o_utils.php');

    $getMsgs = mysql_query(
        'SELECT * FROM `mkchats` 
        WHERE id < "' . $_POST['lastID'] . '" 
        AND ((sender="' . $id . '" AND receiver="' . $_POST['member'] . '") 
        OR (sender="' . $_POST['member'] . '" AND receiver="' . $id . '")) 
        ORDER BY id DESC 
        LIMIT ' . $MSGS_PACKET_SIZE
    );

    $messages = [];
    while ($msg = mysql_fetch_array($getMsgs)) {
        $messages[] = [
            (int) $msg['id'],
            (int) $msg['sender'],
            parse_msg($msg['message']),
            to_local_tz($msg['date'])
        ];
    }

    echo json_encode($messages);
    mysql_close();
}
?>
