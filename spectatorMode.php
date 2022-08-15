<?php
include('session.php');
if ($id) {
    include('initdb.php');
    $getCourse = mysql_fetch_array(mysql_query('SELECT course FROM `mkjoueurs` WHERE id="'.$id.'"'));
    $course = $getCourse['course'];
    if ($course) {
        //mysql_query('UPDATE `mkjoueurs` SET course=0 WHERE id="'.$id.'" AND course="'.$course.'"');
        mysql_query('INSERT IGNORE INTO `mkspectators` (player,course) VALUES ('.$id.','.$course.')');
        $getSpectatorId = mysql_fetch_array(mysql_query('SELECT id FROM `mkspectators` WHERE player="'.$id.'" AND course="'.$course.'"'));
        if ($getSpectatorId)
            echo $getSpectatorId['id'];
        else
            echo -1;
    }
    else
        echo -1;
    mysql_close();
}
else
    echo -1;