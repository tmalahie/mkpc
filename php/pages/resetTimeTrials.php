<?php
header('Content-Type: text/plain');
if (isset($_POST['type']) && isset($_POST['id'])) {
    include('../includes/initdb.php');
    $id = $_POST['id'];
    $type = $_POST['type'];
    include('../includes/circuitTables.php');
    if (in_array($type, $circuitTables)) {
        if ($getCircuit = mysql_fetch_array(mysql_query('SELECT identifiant FROM `'.$type.'` WHERE id="'. $id .'"'))) {
            include('../includes/getId.php');
            if ($getCircuit['identifiant'] == $identifiants[0])
                mysql_query('DELETE FROM mkrecords WHERE type="'. $type .'" AND circuit="'. $id .'"');
        }
    }
    mysql_close();
    echo 1;
}