<?php
header('Content-Type: text/plain');
if (isset($_POST["error"])) {
	include('../includes/getId.php');
	include('../includes/session.php');
	include('../includes/initdb.php');
    if (!$id) $id = 0;
    mysql_query('INSERT INTO mkgamecrash SET player="'.$id.'",identifiant="'.$identifiants[0].'",stack="'.$_POST["error"].'"');
    mysql_close();
    echo 1;
}
?>