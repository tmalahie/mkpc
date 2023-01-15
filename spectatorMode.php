<?php
include('session.php');
if ($id) {
    include('initdb.php');
	include('onlineUtils.php');
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