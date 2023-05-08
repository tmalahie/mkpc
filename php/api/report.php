<?php
header('Content-Type: text/plain');
if (isset($_POST['type']) && isset($_POST['link'])) {
    include('../includes/session.php');
    echo 1;
    if (!$id)
        exit;
    include('../includes/initdb.php');
    $getBanned = mysql_fetch_array(mysql_query('SELECT banned FROM `mkjoueurs` WHERE id="'. $id .'"'));
    if ($getBanned && $getBanned['banned']) {
        mysql_close();
        exit;
    }
    $type = $_POST['type'];
    $link = $_POST['link'];
    $q = mysql_query('INSERT IGNORE INTO mkreportshist SET type="'.$type.'",link="'.$link.'",reporter='.$id);
    if (mysql_affected_rows()) {
        $reportId = mysql_insert_id();
        mysql_query('INSERT INTO mkreports SET type="'.$type.'",link="'.$link.'",count=1,state="pending" ON DUPLICATE KEY UPDATE count=count+1');
        $getMods = mysql_query('SELECT player FROM mkrights WHERE privilege IN ("moderator","admin")');
        while ($getMod = mysql_fetch_array($getMods))
            mysql_query('INSERT INTO `mknotifs` SET type="admin_report", user="'. $getMod['player'] .'", link="'. $reportId .'"');
    }
    mysql_close();
}