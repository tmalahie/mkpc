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
            if ($getCircuit['identifiant'] == $identifiants[0]) {
                if (empty($_POST['description'])) {
                    mysql_query(
                        'UPDATE mktracksettings SET description=NULL
                        WHERE circuit="'. $circuit .'" AND type="'. $type .'"'
                    );
                }
                else {
                    $description = $_POST['description'];
                    mysql_query(
                        'INSERT INTO mktracksettings
                        SET circuit="'. $circuit .'", type="'. $type .'", description="'. $description .'"
                        ON DUPLICATE KEY UPDATE description=VALUES(description)'
                    );
                }
            }
        }
        echo 1;
    }
}
?>