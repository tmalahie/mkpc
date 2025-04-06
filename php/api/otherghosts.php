<?php
header('Content-Type: application/json');

if (isset($_POST['map'])) {
    include('../includes/initdb.php');

    $map = $_POST['map'];
    $type = isset($_POST['type']) ? $_POST['type'] : '';
    $cc = isset($_POST['cc']) ? $_POST['cc'] : 150;

    $query = "
        SELECT g.id, g.perso, g.time, g.lap_times, r.name
        FROM `mkghosts` g
        LEFT JOIN `mkrecords` r
        FORCE INDEX FOR JOIN (`identifiant`)
        ON g.identifiant = r.identifiant
        AND g.identifiant2 = r.identifiant2
        AND g.identifiant3 = r.identifiant3
        AND g.identifiant4 = r.identifiant4
        AND g.perso = r.perso
        AND g.time = r.time
        AND r.class = g.class
        AND r.circuit = g.circuit
        AND r.type = g.type
        WHERE g.class = \"$cc\"
        AND g.type = \"$type\"
        AND g.circuit = \"$map\"
        GROUP BY g.id, g.perso, g.time
    ";

    $ghosts = mysql_query($query);

    $result = [];
    while ($ghost = mysql_fetch_array($ghosts)) {
        $result[] = [
            (int) $ghost['id'],
            $ghost['perso'],
            isset($ghost['name']) ? $ghost['name'] : '',
            (int) $ghost['time'],
            $ghost['lap_times']
        ];
    }

    echo json_encode($result);
    mysql_close();
}
?>