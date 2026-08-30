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
                require_once('../includes/utils-logs.php');
                if (hasRight('moderator')) {
                    $canLock = true;
                    $action = $unlocking ? 'ULComments' : 'LComments';
                    $formerSettings = mysql_fetch_array(mysql_query('SELECT lock_comments FROM mktracksettings WHERE circuit="'. $circuit .'" AND type="'. $type .'"'));
                    insertLog($id, $action .' '. $type .' '. $circuit, array_merge(snapshotTrack($type, $circuit), array(
                        'before' => array('lock_comments' => $formerSettings ? intval($formerSettings['lock_comments']) : 0),
                        'after' => array('lock_comments' => $unlocking ? 0 : 1)
                    )));
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