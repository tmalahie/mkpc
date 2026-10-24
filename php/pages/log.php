<?php
if (isset($_POST['msg'])) {
    include('../includes/session.php');
    include('../includes/language.php');
    if (!$id) {
        echo t('admin.you_arent_logged');
        exit;
    }
    if (!isset($_POST['token'], $_SESSION['csrf']) || $_POST['token'] !== $_SESSION['csrf']) {
        echo 'Invalid token';
        exit;
    }
    include('../includes/initdb.php');
    require_once('../includes/getRights.php');
    if (!hasRight('manager')) {
        echo t('admin.you_arent_admin');
        mysql_close();
        exit;
    }
    require_once('../includes/utils-logs.php');
    insertLog($id, 'Custom '. $_POST['msg']);
    mysql_close();
}
?>