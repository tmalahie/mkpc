<?php
if (isset($_POST['type']) && isset($_POST['id'])) {
    include('initdb.php');
    include('getId.php');
    require_once('collabUtils.php');
    if (isCollabOwner($_POST['type'], $_POST['id'])) {
        if (isset($_POST['rights']))
            $rights = array_keys($_POST['rights']);
        else
            $rights = array();
        $rightsStr = implode(',', $rights);

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
            $key = generateRandomString(24);
            $q = mysql_query('INSERT INTO `mkcollablinks` SET type="'. $_POST['type'] .'", creation_id="'. $_POST['id'] .'", secret="'. $key .'", rights="'. $rightsStr .'"');
            if (mysql_affected_rows())
                break;
        }
        $collabId = mysql_insert_id();

        echo json_encode(array(
            'id' => $collabId,
            'key' => $key,
            'rights' => $rights
        ));
    }
    else
        echo '{}';
    mysql_close();
}