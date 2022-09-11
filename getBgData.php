<?php
if (isset($_POST['id'])) {
    include('initdb.php');
    include('getId.php');
    $getBg = mysql_query('SELECT id,name FROM mkbgs WHERE id="'. $_POST['id'] .'"');
    require_once('utils-bgs.php');
    if ($bg = mysql_fetch_array($getBg))
        echo json_encode(get_bg_payload($bg));
    mysql_close();
}