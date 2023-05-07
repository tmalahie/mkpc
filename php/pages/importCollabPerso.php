<?php
header('Content-Type: text/plain');
if (isset($_POST['id']) && isset($_POST['collab'])) {
    include('initdb.php');
    require_once('collabUtils.php');
    $link = getCollabLink('mkchars', $_POST['id'], $_POST['collab']);
    if (isset($link['rights']['use'])) {
        if ($perso = mysql_fetch_array(mysql_query('SELECT * FROM mkchars WHERE id="'. $_POST['id'] .'"'))) {
            require_once('persos.php');
            $spriteSrcs = get_sprite_srcs($perso['sprites']);
            $res = array (
                'id' => +$perso['id'],
                'name' => $perso['name'],
                'shared' => +($perso['author'] !== null),
                'sprites' => $perso['sprites'],
                'acceleration' => +$perso['acceleration'],
                'speed' => +$perso['speed'],
                'handling' => +$perso['handling'],
                'mass' => +$perso['mass'],
                'ld' => $spriteSrcs['ld'],
                'map' => $spriteSrcs['map'],
                'podium' => $spriteSrcs['podium'],
                'music' => get_perso_music($perso)
            );
            echo json_encode($res);
        }
    }
    mysql_close();
}