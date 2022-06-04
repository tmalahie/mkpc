<?php
function isSquareTrack(&$circuit) {
    for ($i=0;$i<36;$i++) {
        if ($circuit["p$i"] < 4) {
            switch($circuit["p$i"]) {
                case 0 :
                $d = 6;
                break;
                case 1 :
                $d = 1;
                break;
                case 2 :
                $d = -6;
                break;
                case 3 :
                $d = -1;
                break;
            }
            $depart = $i;
            break;
        }
    }
    if (!isset($depart)) return true;
    if (!isset($d)) return true;
    $i = $depart;
    $direction = $d;
    $nbTurns = 0;
    $distance = 0;
    while ($direction) {
        $i += $direction;
        if ($distance >= 50)
            break;
        $distance++;
        if (!isset($circuit["p$i"]))
            continue;
        switch($circuit["p$i"]) {
            case 4 :
            $direction = ($direction==-6 ? -1 : 6);
            $nbTurns++;
            break;
            case 5 :
            $direction = ($direction==-6 ? 1 : 6);
            $nbTurns++;
            break;
            case 6 :
            $direction = ($direction==-1 ? -6 : 1);
            $nbTurns++;
            break;
            case 7 :
            $direction = ($direction==1 ? -6 : -1);
            $nbTurns++;
            break;
            case 8 :
            case 9 :
            break;
            case 10 :
                $nbTurns++;
            case 11 :
                $nbTurns++;
            break;
            default :
            if ($direction == $d)
                $direction = false;
            break;
        }
    }
    return ($nbTurns <= 4);
}
function postCircuitUpdate($type, $circuitId, &$circuit) {
    if (isSquareTrack($circuit)) {
        mysql_query('INSERT IGNORE INTO `mktrackbin` SET type="'. $type .'",circuit="'. $circuitId .'", delete_at=NOW()+INTERVAL 10 MINUTE');
        return true;
    }
    else {
        mysql_query('DELETE FROM `mktrackbin` WHERE type="'. $type .'" AND circuit="'. $circuitId .'"');
        return false;
    }
}