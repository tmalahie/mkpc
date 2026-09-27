<?php
header('Content-Type: text/plain');
include('../includes/getId.php');
include('../includes/initdb.php');
include('../includes/session.php');
include('../includes/ip_banned.php');
echo getPublishRestriction($id);
mysql_close();
?>
