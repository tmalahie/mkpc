<?php
header('Content-Type: text/plain');
if (isset($_GET['id'])) {
    include('../includes/initdb.php');
    $getDecor = mysql_query('SELECT * FROM mkdecors WHERE id="'. $_GET['id'] .'"');
    if ($decor = mysql_fetch_array($getDecor)) {
        require_once('../includes/utils-decors.php');
        $decorSrcs = decor_sprite_srcs($decor['sprites'],$decor['url']);
        $res = array(
            'id' => $decor['id'],
            'name' => $decor['name'],
            'hd' => $decorSrcs['hd'],
            'ld' => $decorSrcs['ld'],
            'map' => $decorSrcs['map']
        );
        if (isset($_GET['full'])) {
            $res['size'] = decor_sprite_sizes($decor['type'],$decorSrcs['hdir'].$decorSrcs['hd']);
            $res['original_size'] = decor_sprite_sizes($decor['type'],default_decor_sprite_src($decor['type']));
            $getDecorExtra = mysql_query('SELECT * FROM mkdecors WHERE extra_parent_id="'. $decor['id'] .'"');
            while ($decorExtra = mysql_fetch_array($getDecorExtra)) {
                $decorExtraSrcs = decor_sprite_srcs($decorExtra['sprites'],$decorExtra['url']);
                $res['extra'][$decorExtra['type']] = array(
                    'id' => $decorExtra['id'],
                    'hd' => $decorExtraSrcs['hd'],
                    'ld' => $decorExtraSrcs['ld'],
                    'map' => $decorExtraSrcs['map'],
                    'size' => decor_sprite_sizes($decorExtra['type'],$decorExtraSrcs['hdir'].$decorExtraSrcs['hd']),
                    'original_size' => decor_sprite_sizes($decorExtra['type'],default_decor_sprite_src($decorExtra['type']))
                );
            }
            if ($decor['options'])
                $res['options'] = json_decode($decor['options']);
        }
        echo json_encode($res);
    }
    mysql_close();
}
?>