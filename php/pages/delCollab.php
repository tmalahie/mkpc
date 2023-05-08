<?php
header('Content-Type: text/plain');
if (isset($_POST['id'])) {
    include('../includes/initdb.php');
    include('../includes/getId.php');
    require_once('../includes/collabUtils.php');
    $collab = getCollabLinkById($_POST['id']);
    if ($collab && isCollabOwner($collab['type'], $collab['creation_id']))
        mysql_query('DELETE FROM `mkcollablinks` WHERE id="'. $collab['id'] .'"');
    echo 1;
    mysql_close();
}