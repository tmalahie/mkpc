<?php
include('session.php');
if ($id && isset($_POST['spectator'])) {
    include('initdb.php');
    $spectatorId = intval($_POST['spectator']);
    if ($getCourse = mysql_fetch_array(mysql_query('SELECT course,player FROM `mkspectators` WHERE id='.$spectatorId))) {
        if ($getCourse['player'] == $id)
            mysql_query('DELETE FROM `mkspectators` WHERE id='.$spectatorId);
        echo $getCourse['course'];
    }
    else
        echo 0;
    mysql_close();
}
else
    echo 0;