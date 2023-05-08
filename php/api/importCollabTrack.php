<?php
header('Content-Type: text/plain');
if (isset($_POST['id']) && isset($_POST['type']) && isset($_POST['collab'])) {
    include('../includes/initdb.php');
    require_once('../includes/collabUtils.php');
    $link = getCollabLink($_POST['type'], $_POST['id'], $_POST['collab']);
    if (isset($link['rights']['use'])) {
        $isCup = ($_POST['type'] === 'mkcups');
        $isQuickTrack = ($_POST['type'] === 'mkcircuits');
		require_once('../includes/utils-cups.php');
        if (($circuit = fetchCreationData($_POST['type'], $_POST['id'], array(
            'select' => 'c.id,c.nom'.($isCup ? ',c.mode,c.circuit0,c.circuit1,c.circuit2,c.circuit3':'').($isQuickTrack ? ',c.type':'').',s.thumbnail'
        ))) && (!isset($_POST['mode']) || $circuit['mode'] == $_POST['mode'])) {
            require_once('../includes/utils-circuits.php');
            require_once('../includes/circuitEscape.php');
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
                $lCup = array(
                    'mode' => $circuit['mode'],
                    'tracks' => array()
                );
                for ($i=0;$i<4;$i++) {
                    $lCup['tracks'][] = array(
                        'id' => $circuit['circuit'.$i]
                    );
                }
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
                'nom' => $circuit['name'],
                'thumbnail' => $circuit['thumbnail'],
                'category' => $category,
                'href' => getCollabUrlPrefix($link)
            );
            addCircuitData($res, $lCups,$mCups);
            printCupCircuit($res);
        }
    }
}