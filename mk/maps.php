<?php
header('Content-Type: text/javascript');
header('Cache-Control: max-age=600000');
?>function listMaps(){return<?php
    echo file_get_contents('maps.json');
?>}