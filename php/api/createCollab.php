<?php
if (isset($_POST['type']) && isset($_POST['id'])) {
    header('Content-Type: application/json');
    include('../includes/initdb.php');
    include('../includes/getId.php');
    require_once('../includes/collabUtils.php');
    if (isCollabOwner($_POST['type'], $_POST['id'])) {
        $collabValues = getCollabInputValues($_POST);

        function generateRandomString($length) {
            $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ-_';
            $charactersLength = strlen($characters);
            $randomString = '';
            for ($i = 0; $i < $length; $i++) {
                $randomString .= $characters[rand(0, $charactersLength - 1)];
            }
            return $randomString;
        }

        for ($try=0;$try<10;$try++) {
            $collabValues['secret'] = generateRandomString(24);
            $q = mysql_query('INSERT INTO `mkcollablinks` SET type="'. $collabValues['type'] .'", creation_id="'. $collabValues['creation_id'] .'", secret="'. $collabValues['secret'] .'", rights="'. $collabValues['rights'] .'"');
            if (mysql_affected_rows())
                break;
        }
        $collabValues['id'] = mysql_insert_id();

        echo json_encode(collabPayload($collabValues));
    }
    else
        echo '{}';
    mysql_close();
}