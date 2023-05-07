<?php
if (isset($_POST['msg'])) {
    include('initdb.php');
    include('session.php');
    mysql_query('INSERT INTO `mklogs` VALUES(NULL,NULL, '. intval($id) .', "Custom '. $_POST['msg'] .'")');
    mysql_close();
}
?>