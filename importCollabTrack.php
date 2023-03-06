<?php
header('Content-Type: text/plain');
if (isset($_POST['id']) && isset($_POST['type']) && isset($_POST['collab'])) {
    include('initdb.php');
    require_once('collabUtils.php');
    $link = getCollabLink($_POST['type'], $_POST['id'], $_POST['collab']);
    if (isset($link['rights']['use'])) {
        $isCup = ($_POST['type'] === 'mkcups');
        if ($circuit = mysql_fetch_array(mysql_query('SELECT id,nom'.($isCup ? ',mode,circuit0,circuit1,circuit2,circuit3':'').' FROM `'. $_POST['type'] .'` WHERE id="'. $_POST['id'] .'"'. (isset($_POST['mode']) ? ' AND mode="'. $_POST['mode'] .'"':'')))) {
            include('utils-circuits.php');
            include('utils-cups.php');
            require_once('circuitEscape.php');
            function escapeUtf8($str) {
                return htmlentities(escapeCircuitNames($str));
            }
            $lCups = null;
            $mCups = null;
            switch ($_POST['type']) {
            case 'mkcups':
                $category = $circuit['mode'] ? 2:3;
                $lCup = array();
                for ($i=0;$i<4;$i++)
                    $lCup[] = $circuit['circuit'.$i];
                $lCups = array(
                    $circuit['id'] => $lCup
                );
                break;
            case 'circuits':
                $category = 4;
                break;
            default:
                $category = 5;
                break;
            }
            $res = array(
                'id' => $circuit['id'],
                'nom' => $circuit['nom'],
                'category' => $category,
                'href' => getCollabUrlPrefix($link)
            );
            addCircuitData($res, $lCups,$mCups);
            printCupCircuit($res);
        }
    }
}