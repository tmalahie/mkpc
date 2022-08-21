<?php
include('session.php');
if ($id) {
    include('initdb.php');
	include('onlineUtils.php');
	$course = getCourse(array(
        'check_ban' => true
    ));
    if ($course) {
        //mysql_query('UPDATE `mkjoueurs` SET course=0 WHERE id="'.$id.'" AND course="'.$course.'"');
        mysql_query('INSERT IGNORE INTO `mkspectators` SET player='.$id.',course='.$course.',state="joined" ON DUPLICATE KEY UPDATE state=VALUES(state)');
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