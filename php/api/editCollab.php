<?php
if (isset($_POST['id'])) {
    header('Content-Type: application/json');
    include('../includes/initdb.php');
    include('../includes/getId.php');
    require_once('../includes/collabUtils.php');
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