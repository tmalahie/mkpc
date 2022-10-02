<?php
if (isset($_POST['id']) && isset($_POST['type']) && isset($_POST['collab'])) {
    include('initdb.php');
    require_once('collabUtils.php');
    $link = getCollabLink($_POST['type'], $_POST['id'], $_POST['collab']);
    if (isset($link['rights']['use'])) {
        if ($circuit = mysql_fetch_array(mysql_query('SELECT id,nom FROM `'. $_POST['type'] .'` WHERE id="'. $_POST['id'] .'"'))) {
            include('utils-circuits.php');
            include('utils-cups.php');
            require_once('circuitEscape.php');
            function escapeUtf8($str) {
                return htmlentities(escapeCircuitNames($str));
            }
            $res = array(
                'id' => $circuit['id'],
                'nom' => $circuit['nom'],
                'category' => ($_POST['type'] === 'circuits') ? 4 : 5,
                'href' => getCollabUrlPrefix($link)
            );
            addCircuitData($res, $lCups,$mCups);
            printCupCircuit($res);
        }
    }
}