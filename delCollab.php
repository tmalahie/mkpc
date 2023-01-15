<?php
if (isset($_POST['id'])) {
    include('initdb.php');
    include('getId.php');
    require_once('collabUtils.php');
    $collab = getCollabLinkById($_POST['id']);
    if ($collab && isCollabOwner($collab['type'], $collab['creation_id']))
        mysql_query('DELETE FROM `mkcollablinks` WHERE id="'. $collab['id'] .'"');
    echo 1;
    mysql_close();
}