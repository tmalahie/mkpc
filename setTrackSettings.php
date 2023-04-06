<?php
header('Content-Type: text/plain');
if (isset($_POST['type']) && isset($_POST['circuit'])) {
    include('circuitTables.php');
    include('initdb.php');
    $type = $_POST['type'];
    if (in_array($type, $circuitTables)) {
        $circuit = $_POST['circuit'];
        if ($getCircuit = mysql_fetch_array(mysql_query('SELECT identifiant FROM `'. $type .'` WHERE id="'. $circuit .'"'))) {
            include('getId.php');
            if ($getCircuit['identifiant'] == $identifiants[0]) {
                function getSQLRawValue(&$value) {
                    return isset($value) ? '"'.$value.'"' : 'NULL';
                }
                mysql_query(
                    'INSERT INTO mktracksettings
                    SET circuit="'. $circuit .'", type="'. $type .'",
                    description='. getSQLRawValue($_POST['description']) .',
                    name_en='. getSQLRawValue($_POST['name_en']) .',
                    name_fr='. getSQLRawValue($_POST['name_fr']) .',
                    prefix='. getSQLRawValue($_POST['prefix']) .'
                    ON DUPLICATE KEY UPDATE
                    description=VALUES(description),
                    name_en=VALUES(name_en),
                    name_fr=VALUES(name_fr),
                    prefix=VALUES(prefix)'
                );
            }
        }
        echo 1;
    }
}
?>