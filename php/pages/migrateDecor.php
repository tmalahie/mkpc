<?php
include('../includes/session.php');
if ($id != 1) exit;
include('../includes/initdb.php');
include('../includes/utils-decors.php');
$getDecors = mysql_query('SELECT * FROM mkdecors ORDER BY id DESC');
while ($decor = mysql_fetch_array($getDecors)) {
    if (!$decor['img_data'])
        $decor['img_data'] = '{}';
    $srcs = get_decor_srcs($decor);
    $sizes = decor_sprite_sizes($decor['sprites'],$srcs['hdir'].$srcs['hd']);
    $imgData = array(
        'w' => $sizes['hd']['w'],
        'h' => $sizes['hd']['h']
    );
    if (isset($decor['imgdata']['url']))
        $imgData['url'] = $decor['imgdata']['url'];
    $imgDataJson = json_encode($imgData);
    echo('UPDATE mkdecors SET img_data="'. mysql_real_escape_string($imgDataJson) .'" WHERE id="'. $decor['id'] .'"');
}
mysql_close();