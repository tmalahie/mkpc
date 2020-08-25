<?php
if (isset($_GET['id'])) {
    include('initdb.php');
    $getDecor = mysql_query('SELECT * FROM mkdecors WHERE id="'. $_GET['id'] .'"');
    if ($decor = mysql_fetch_array($getDecor)) {
        require_once('utils-decors.php');
        $decorSrcs = decor_sprite_srcs($decor['sprites']);
        $res = array(
            'id' => $decor['id'],
            'name' => $decor['name'],
            'hd' => $decorSrcs['hd'],
            'ld' => $decorSrcs['ld'],
            'map' => $decorSrcs['map']
        );
        if (isset($_GET['full'])) {
            $res['size'] = decor_sprite_sizes($decor['type'],$decorSrcs['hd']);
            $res['original_size'] = decor_sprite_sizes($decor['type'],default_decor_sprite_src($decor['type']));
        }
        echo json_encode($res);
    }
    mysql_close();
}
?>