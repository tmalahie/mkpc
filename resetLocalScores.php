<?php
if (isset($_POST['key'])) {
    include('initdb.php');
    $key = $_POST['key'];
    if ($linkCreator = mysql_fetch_array(mysql_query('SELECT player FROM `mkprivgame` WHERE id="'. $key .'"'))) {
        include('session.php');
        if ($linkCreator['player'] == $id) {
            mysql_query('DELETE FROM mkgamerank WHERE game="'. $key .'"');
            require_once('onlineStateUtils.php');
            resetCourseState($key);
        }
    }
    mysql_close();
    echo 1;
}
?>