<?php
header('Content-Type: text/plain');
if (isset($_POST['type']) && isset($_POST['circuit'])) {
    include('../includes/circuitTables.php');
    include('../includes/initdb.php');
    $type = $_POST['type'];
    if (in_array($type, $circuitTables)) {
        $circuit = $_POST['circuit'];
        if ($getCircuit = mysql_fetch_array(mysql_query('SELECT identifiant FROM `'. $type .'` WHERE id="'. $circuit .'"'))) {
            include('../includes/getId.php');
            $canLock = false;
            $unlocking = empty($_POST['locked']);
            if ($getCircuit['identifiant'] == $identifiants[0]) {
                $canLock = true;
            }
            else {
                include('../includes/session.php');
                require_once('../includes/getRights.php');
                if (hasRight('moderator')) {
                    $canLock = true;
                    $action = $unlocking ? 'ULComments' : 'LComments';
                    mysql_query('INSERT INTO `mklogs` VALUES(NULL,NULL, '. $id .', "'. $action .' '. $type .' '. $circuit .'")');
                }
            }
            if ($canLock) {
                if ($unlocking) {
                    mysql_query(
                        'UPDATE mktracksettings SET lock_comments=0
                        WHERE circuit="'. $circuit .'" AND type="'. $type .'"'
                    );
                }
                else {
                    mysql_query(
                        'INSERT INTO mktracksettings
                        SET circuit="'. $circuit .'", type="'. $type .'", lock_comments=1
                        ON DUPLICATE KEY UPDATE lock_comments=VALUES(lock_comments)'
                    );
                }
            }
        }
        echo 1;
    }
}
?>