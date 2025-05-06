<?php
header('Content-Type: application/json');
include('../includes/session.php');

$response = [];

if (!$id || !isset($_POST['member'], $_POST['message'], $_POST['lastID'])) {
    echo json_encode($response);
    exit;
}

include('../includes/initdb.php');

$getBanned = mysql_fetch_array(mysql_query('SELECT banned FROM `mkjoueurs` WHERE id="'. $id .'"'));

if ($getBanned && $getBanned['banned']) {
    echo json_encode($response);
    mysql_close();
    exit;
}

$memberId = $_POST['member'];
$message = $_POST['message'];
$lastID = $_POST['lastID'];

$memberExists = mysql_numrows(mysql_query('SELECT * FROM `mkjoueurs` WHERE id="'. $memberId .'"'));

if (!$memberExists) {
    echo json_encode($response);
    mysql_close();
    exit;
}

$isIgnored = mysql_numrows(mysql_query('SELECT * FROM `mkignores` WHERE ignorer="'. $memberId .'" AND ignored="'. $id .'"'));
$seen = $isIgnored ? 1 : 0;

mysql_query('INSERT INTO `mkchats` VALUES(NULL,"'. $id .'","'. $memberId .'","'. $message .'",NULL,'. $seen .')');
mysql_query('UPDATE `mkconvs` SET writting=NULL WHERE sender="'. $memberId .'" AND receiver="'. $id .'"');

$lastMsgs = mysql_query('SELECT * FROM `mkchats` WHERE id>"'. $lastID .'" AND ((sender="'. $id .'" AND receiver="'. $memberId .'") OR (sender="'. $memberId .'" AND receiver="'. $id .'"))');

include('../includes/o_utils.php');

while ($msgInfos = mysql_fetch_array($lastMsgs)) {
    $response[] = [
        (int)$msgInfos['id'],
        (int)$msgInfos['sender'],
        parse_msg($msgInfos['message']),
        to_local_tz($msgInfos['date'])
    ];
}

echo json_encode($response);
mysql_close();
?>
