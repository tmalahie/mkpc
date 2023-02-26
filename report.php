<?php
if (isset($_POST['type']) && isset($_POST['link'])) {
    include('session.php');
    echo 1;
    if (!$id)
        exit;
    include('initdb.php');
    $getBanned = mysql_fetch_array(mysql_query('SELECT banned FROM `mkjoueurs` WHERE id="'. $id .'"'));
    if ($getBanned && $getBanned['banned']) {
        mysql_close();
        exit;
    }
    $type = $_POST['type'];
    $link = $_POST['link'];
    $q = mysql_query('INSERT IGNORE INTO mkreportshist SET type="'.$type.'",link="'.$link.'",reporter='.$id);
    if (mysql_affected_rows())
        mysql_query('INSERT INTO mkreports SET type="'.$type.'",link="'.$link.'",count=1,state="pending" ON DUPLICATE KEY UPDATE count=count+1');
    mysql_close();
}