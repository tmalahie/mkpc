<?php
include('initdb.php');
include('getId.php');
$getBgs = mysql_query('SELECT id,name FROM mkbgs WHERE identifiant="'. $identifiants[0] .'" ORDER BY id DESC');
$res = array();
require_once('utils-bgs.php');
while ($bg = mysql_fetch_array($getBgs))
    $res[] = get_bg_payload($bg);
echo json_encode($res);
mysql_close();