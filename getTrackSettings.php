<?php
header('Content-Type: application/json');
if (isset($_POST['type']) && isset($_POST['circuit'])) {
    include('initdb.php');
    $res = null;
    if ($getTrackSettings = mysql_fetch_array(mysql_query('SELECT * FROM mktracksettings WHERE type="'. $_POST['type'] .'" AND circuit="'. $_POST['circuit'] .'"'))) {
        $res = array(
            'description' => $getTrackSettings['description'],
            'name_en' => $getTrackSettings['name_en'],
            'name_fr' => $getTrackSettings['name_fr'],
            'prefix' => $getTrackSettings['prefix']
        );
    }
    echo json_encode($res);
}
?>