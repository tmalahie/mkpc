<?php
header('Content-Type: text/plain');
if (isset($_POST["error"])) {
	include('../includes/getId.php');
	include('../includes/session.php');
	include('../includes/initdb.php');
    if (!$id) $id = 0;
    $referrer = '';
    if (isset($_SERVER['HTTP_REFERER']))
        $referrer = $_SERVER['HTTP_REFERER'];
    mysql_query('INSERT INTO mkgamecrash SET player="'.$id.'",identifiant="'.$identifiants[0].'",stack="'.$_POST["error"].'",referrer="'.mysql_real_escape_string($referrer).'"');
    mysql_close();
    echo 1;
}
?>