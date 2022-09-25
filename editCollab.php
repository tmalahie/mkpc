<?php
if (isset($_POST['id'])) {
    include('initdb.php');
    include('getId.php');
    require_once('collabUtils.php');
    $collab = getCollabLinkById($_POST['id']);
    if ($collab && isCollabOwner($collab['type'], $collab['creation_id'])) {
        $collabValues = getCollabInputValues($_POST);
        mysql_query('UPDATE `mkcollablinks` SET rights="'. $collabValues['rights'] .'" WHERE id="'. $collab['id'] .'"');
        $collab['rights'] = $collabValues['rights'];
        echo json_encode($collab);
    }
    else
        echo '{}';
    mysql_close();
}