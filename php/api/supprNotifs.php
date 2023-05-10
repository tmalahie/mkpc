<?php
header('Content-Type: text/plain');
include('../includes/initdb.php');
include('../includes/session.php');
include('../includes/notifsSQL.php');
mysql_query('DELETE FROM `mknotifs` WHERE '. $idsSQL);
mysql_close();
echo 1;
?>