<?php
header('Content-Type: text/plain');
if (!isset($_POST['id'])) exit;
include('session.php');
include('initdb.php');
require_once('getRights.php');
if (hasRight('moderator')) {
    mysql_query('UPDATE mkreports SET state="archived" WHERE id="'. $_POST['id'] .'"');
    mysql_query('INSERT INTO `mklogs` VALUES(NULL,NULL, '. $id .', "AReport '. $_POST['id'] .'")');
}
echo 1;