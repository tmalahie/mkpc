<?php
header('Content-Type: text/plain');
include('../includes/session.php');
if ($id) {
    include('../includes/initdb.php');
	include('../includes/onlineUtils.php');
	$course = getCourse(array(
        'check_ban' => true
    ));
    if ($course) {
        $newSpectatorId = isset($_POST['state']) ? joinSpectatorMode($course, $_POST['state']) : joinSpectatorMode($course);
        if ($newSpectatorId)
            echo $newSpectatorId;
        else
            echo -1;
    }
    else
        echo -1;
    mysql_close();
}
else
    echo -1;