<?php
if (isset($_GET['id'])) {
    include('initdb.php');
    $getDecor = mysql_query('SELECT * FROM mkdecors WHERE id="'. $_GET['id'] .'"');
    if ($decor = mysql_fetch_array($getDecor)) {
        require_once('utils-decors.php');
        $decorSrcs = decor_sprite_srcs($decor['sprites']);
        echo json_encode(array(
            'id' => $decor['id'],
            'name' => $decor['name'],
            'hd' => $decorSrcs['hd'],
            'ld' => $decorSrcs['ld'],
            'map' => $decorSrcs['map']
        ));
    }
    mysql_close();
}
?>