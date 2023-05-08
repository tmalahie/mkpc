<?php
header('Content-Type: text/plain');
if (isset($_POST['id']) && isset($_POST['collab'])) {
    include('../includes/initdb.php');
    require_once('../includes/collabUtils.php');
    $link = getCollabLink('mkbgs', $_POST['id'], $_POST['collab']);
    if (isset($link['rights']['use'])) {
        if ($bg = mysql_fetch_array(mysql_query('SELECT id,name FROM mkbgs WHERE id="'. $_POST['id'] .'"'))) {
            require_once('../includes/utils-bgs.php');
            echo json_encode(get_bg_payload($bg));
        }
    }
    mysql_close();
}