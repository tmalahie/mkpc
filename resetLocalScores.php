<?php
if (isset($_POST['key'])) {
    include('initdb.php');
    if ($linkCreator = mysql_fetch_array(mysql_query('SELECT player FROM `mkprivgame` WHERE id="'. $_POST['key'] .'"'))) {
        include('session.php');
        if ($linkCreator['player'] == $id)
            mysql_query('DELETE FROM mkgamerank WHERE game="'. $_POST['key'] .'"');
    }
    mysql_close();
    echo 1;
}
?>