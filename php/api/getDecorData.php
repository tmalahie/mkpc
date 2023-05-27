<?php
header('Content-Type: text/plain');
if (isset($_GET['id'])) {
    include('../includes/initdb.php');
    $getDecor = mysql_query('SELECT * FROM mkdecors WHERE id="'. $_GET['id'] .'"');
    if ($decor = mysql_fetch_array($getDecor)) {
        require_once('../includes/utils-decors.php');
        $decorSrcs = get_decor_srcs($decor);
        $res = array(
            'id' => $decor['id'],
            'name' => $decor['name'],
            'hd' => $decorSrcs['hd'],
            'ld' => $decorSrcs['ld'],
            'map' => $decorSrcs['map']
        );
        if (isset($_GET['full'])) {
            $decorType = $decor['type'];
            $res['size'] = get_decor_sizes($decor);
            $res['original_size'] = decor_sprite_sizes($decorType,default_decor_sprite_src($decorType));
            if (isset($CUSTOM_DECOR_TYPES[$decorType]['linked_sprite'])) {
                $linkedSprite = $CUSTOM_DECOR_TYPES[$decorType]['linked_sprite'];
                $res['extra'][$linkedSprite] = array(
                    'id' => $res['id'],
                    'hd' => $res['hd'],
                    'ld' => $res['ld'],
                    'map' => $res['map'],
                    'size' => $res['size'],
                    'original_size' => decor_sprite_sizes($linkedSprite,default_decor_sprite_src($linkedSprite))
                );
                $res['hd'] = "images/sprites/sprite_$decorType.png";
                $res['ld'] = "images/map_icons/$decorType.png";
                $res['map'] = $res['ld'];
                $res['size'] = $res['original_size'];
            }
            else {
                $getDecorExtra = mysql_query('SELECT * FROM mkdecors WHERE extra_parent_id="'. $decor['id'] .'"');
                while ($decorExtra = mysql_fetch_array($getDecorExtra)) {
                    $decorExtraSrcs = get_decor_srcs($decorExtra);
                    $res['extra'][$decorExtra['type']] = array(
                        'id' => $decorExtra['id'],
                        'hd' => $decorExtraSrcs['hd'],
                        'ld' => $decorExtraSrcs['ld'],
                        'map' => $decorExtraSrcs['map'],
                        'size' => get_decor_sizes($decorExtra),
                        'original_size' => decor_sprite_sizes($decorExtra['type'],default_decor_sprite_src($decorExtra['type']))
                    );
                }
            }
            if ($decor['options'])
                $res['options'] = json_decode($decor['options']);
        }
        echo json_encode($res);
    }
    mysql_close();
}
?>