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
        break;
    case '/circuit.php':
        if (isset($queryComponents['mid'])) {
            $circuitType = 'mkmcups';
            $circuitId = intval($queryComponents['mid']);
        }
        elseif (isset($queryComponents['cid'])) {
            $circuitType = 'mkcups';
            $circuitId = intval($queryComponents['cid']);
        }
        else {
            $circuitType = 'mkcircuits';
            $circuitId = isset($queryComponents['id']) ? intval($queryComponents['id']) : 0;
        }
        break;
    case '/map.php':
        if (isset($queryComponents['mid'])) {
            $circuitType = 'mkmcups';
            $circuitId = intval($queryComponents['mid']);
        }
        elseif (isset($queryComponents['cid'])) {
            $circuitType = 'mkcups';
            $circuitId = intval($queryComponents['cid']);
        }
        else {
            $circuitType = 'circuits';
            $circuitId = isset($queryComponents['i']) ? intval($queryComponents['i']) : 0;
        }
        break;
    case '/battle.php':
        $circuitType = 'arenes';
        $circuitId = isset($queryComponents['i']) ? intval($queryComponents['i']) : 0;
        break;
    }
    if (!isset($circuitType)) return null;
    return array(
        'type' => $circuitType,
        'id' => $circuitId
    );
}