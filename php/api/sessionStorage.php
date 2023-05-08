<?php
header('Content-Type: text/plain');
include('../includes/initdb.php');
include('../includes/session.php');
if (isset($_POST['key'])) {
    if (isset($_POST['delete'])) {
        if ($id) {
            mysql_query('DELETE FROM mksessionstorage WHERE player="'.$id.'" AND param="'. $_POST['key'] .'"');
        }
        echo 1;
    }
    elseif (isset($_POST['value'])) {
        if ($id) {
            mysql_query('INSERT INTO mksessionstorage SET player="'.$id.'",param="'. $_POST['key'] .'",value="'. $_POST['value'] .'" ON DUPLICATE KEY UPDATE player=VALUES(player),param=VALUES(param),value=VALUES(value)');
        }
        echo 1;
    }
    else {
        $res = new \stdClass();
        if ($id) {
            $getEntry = mysql_query('SELECT value FROM mksessionstorage WHERE player="'. $id .'" AND param="'. $_POST['key'] .'"');
            while ($entry = mysql_fetch_array($getEntries))
                $res->{$entry['param']} = $entry['value'];
        }
        echo json_encode($res);
    }
}
else {
    $res = new \stdClass();
    if ($id) {
        $getEntries = mysql_query('SELECT param,value FROM mksessionstorage WHERE player="'. $id .'"');
        while ($entry = mysql_fetch_array($getEntries))
            $res->{$entry['param']} = $entry['value'];
    }
    echo json_encode($res);
}
mysql_close();