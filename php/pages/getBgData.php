<?php
header('Content-Type: text/plain');
if (isset($_POST['id'])) {
    include('../includes/initdb.php');
    include('../includes/getId.php');
    $getBg = mysql_query('SELECT id,name FROM mkbgs WHERE id="'. $_POST['id'] .'"');
    require_once('../includes/utils-bgs.php');
    if ($bg = mysql_fetch_array($getBg))
        echo json_encode(get_bg_payload($bg));
    mysql_close();
}