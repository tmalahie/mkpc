<?php
header('Content-Type: text/plain');
if (isset($_POST['id']) && isset($_POST['collab'])) {
    include('../includes/initdb.php');
    require_once('../includes/collabUtils.php');
    $link = getCollabLink('mkdecors', $_POST['id'], $_POST['collab']);
    if (isset($link['rights']['use'])) {
        if ($decor = mysql_fetch_array(mysql_query('SELECT id,name,type,sprites FROM mkdecors WHERE id="'. $_POST['id'] .'"'))) {
            require_once('../includes/utils-decors.php');
            $decorSrcs = decor_sprite_srcs($decor['sprites']);
            $res = array(
                'id' => $decor['id'],
                'name' => $decor['name'],
                'type' => $decor['type'],
                'hd' => $decorSrcs['hd'],
                'ld' => $decorSrcs['ld'],
                'map' => $decorSrcs['map']
            );
            echo json_encode($res);
        }
    }
    mysql_close();
}