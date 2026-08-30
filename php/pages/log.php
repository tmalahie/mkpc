<?php
if (isset($_POST['msg'])) {
    include('../includes/initdb.php');
    include('../includes/session.php');
    require_once('../includes/utils-logs.php');
    insertLog($id, 'Custom '. $_POST['msg']);
    mysql_close();
}
?>