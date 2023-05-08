<?php
header('Content-Type: text/plain');
if (isset($_POST['key'])) {
    include('../includes/session.php');
    if ($id) {
        include('../includes/initdb.php');
        mysql_query('INSERT IGNORE INTO `mkofficialmsgread` SET player='.$id.', message="'. $_POST['key'] .'"');
        mysql_close();
    }
    echo 1;
}