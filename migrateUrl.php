<?php
include('initdb.php');
$circuits = mysql_query('SELECT id FROM circuits');
while ($circuit = mysql_fetch_array($circuits)) {
    $id = $circuit['id'];
    include('getExt.php');
    $path = 'map'.$id.'.'.$ext;
    list($w,$h) = getimagesize("images/uploads/$path");
    $imgData = array(
        'url' => $path,
        'w' => $w,
        'h' => $h,
        'ext' => $ext,
        'local' => 1
    );
    mysql_query('UPDATE circuits SET img_data="'.mysql_real_escape_string(json_encode($imgData)).'" WHERE id='.$id);
}
$circuits = mysql_query('SELECT id FROM arenes');
$src = 'course';
while ($circuit = mysql_fetch_array($circuits)) {
    $id = $circuit['id'];
    include('getExt.php');
    $path = 'course'.$id.'.'.$ext;
    list($w,$h) = getimagesize("images/uploads/$path");
    $imgData = array(
        'url' => $path,
        'w' => $w,
        'h' => $h,
        'ext' => $ext,
        'local' => 1
    );
    mysql_query('UPDATE arenes SET img_data="'.mysql_real_escape_string(json_encode($imgData)).'" WHERE id='.$id);
}
mysql_close();
?>