<?php
header('Content-Type: text/plain');
if (isset($_POST['id']) && isset($_POST['type']) && isset($_POST['collab'])) {
    include('initdb.php');
    require_once('collabUtils.php');
    $link = getCollabLink($_POST['type'], $_POST['id'], $_POST['collab']);
    if (isset($link['rights']['use'])) {
        $isCup = ($_POST['type'] === 'mkcups');
        $isQuickTrack = ($_POST['type'] === 'mkcircuits');
        if ($circuit = mysql_fetch_array(mysql_query('SELECT id,nom'.($isCup ? ',mode,circuit0,circuit1,circuit2,circuit3':'').($isQuickTrack ? ',type':'').' FROM `'. $_POST['type'] .'` WHERE id="'. $_POST['id'] .'"'. (isset($_POST['mode']) ? ' AND mode="'. $_POST['mode'] .'"':'')))) {
            require_once('utils-circuits.php');
            require_once('utils-cups.php');
            require_once('circuitEscape.php');
            function escapeUtf8($str) {
                return htmlentities(escapeCircuitNames($str));
            }
            $lCups = null;
            $mCups = null;
            switch ($_POST['type']) {
            case 'mkcups':
                switch ($circuit['mode']) {
                case 1:
                    $category = 2;
                    break;
                case 2:
                    $category = 9;
                    break;
                case 3:
                    $category = 8;
                    break;
                default:
                    $category = 3;
                    break;
                }
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
            case 'arenes':
                $category = 6;
                break;
            default:
                $category = empty($circuit['type']) ? 5 : 7;
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