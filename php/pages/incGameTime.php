<?php
header('Content-Type: text/plain');
if (isset($_POST["time"])) {
	include('../includes/getId.php');
	include('../includes/session.php');
	include('../includes/initdb.php');
    $time = intval($_POST["time"]);
    if (!$id) $id = 0;
    mysql_query('INSERT INTO mkgametime SET player="'.$id.'",identifiant="'.$identifiants[0].'",time="'.$time.'" ON DUPLICATE KEY UPDATE time=time+VALUES(time)');
    mysql_query('INSERT INTO mkgametimehist SET player="'.$id.'",identifiant="'.$identifiants[0].'",time="'.$time.'"');
    mysql_close();
    echo 1;
}
?>