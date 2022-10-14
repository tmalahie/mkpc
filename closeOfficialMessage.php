<?php
if (isset($_POST['key'])) {
    include('session.php');
    if ($id) {
        include('initdb.php');
        mysql_query('INSERT IGNORE INTO `mkofficialmsgread` SET player='.$id.', message="'. $_POST['key'] .'"');
        mysql_close();
    }
    echo 1;
}