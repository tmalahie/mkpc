<?php
function getCreationByUrl($url) {
    $urlComponents = parse_url($url);
    $queryComponents = array();
    if (isset($urlComponents['query']))
        parse_str($urlComponents['query'], $queryComponents);
    if (!isset($urlComponents['path'])) return null;
    switch ($urlComponents['path']) {
    case '/arena.php':
        $circuitType = 'mkcircuits';
        $circuitId = isset($queryComponents['id']) ? intval($queryComponents['id']) : 0;
        $circuitFilter = 7;
        break;
    case '/circuit.php':
        if (isset($queryComponents['mid'])) {
            $circuitType = 'mkmcups';
            $circuitId = intval($queryComponents['mid']);
            $circuitFilter = 1;
        }
        elseif (isset($queryComponents['cid'])) {
            $circuitType = 'mkcups';
            $circuitId = intval($queryComponents['cid']);
            $circuitFilter = 3;
        }
        else {
            $circuitType = 'mkcircuits';
            $circuitId = isset($queryComponents['id']) ? intval($queryComponents['id']) : 0;
            $circuitFilter = 5;
        }
        break;
    case '/map.php':
        if (isset($queryComponents['mid'])) {
            $circuitType = 'mkmcups';
            $circuitId = intval($queryComponents['mid']);
            $circuitFilter = 0;
        }
        elseif (isset($queryComponents['cid'])) {
            $circuitType = 'mkcups';
            $circuitId = intval($queryComponents['cid']);
            $circuitFilter = 2;
        }
        else {
            $circuitType = 'circuits';
            $circuitId = isset($queryComponents['i']) ? intval($queryComponents['i']) : 0;
            $circuitFilter = 4;
        }
        break;
    case '/battle.php':
        $circuitType = 'arenes';
        $circuitId = isset($queryComponents['i']) ? intval($queryComponents['i']) : 0;
        $circuitFilter = 6;
        break;
    }
    if (!isset($circuitType)) return null;
    return array(
        'filter' => $circuitFilter,
        'type' => $circuitType,
        'id' => $circuitId
    );
}