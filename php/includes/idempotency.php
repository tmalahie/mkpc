<?php
require_once('apc.php');
function getRequestIdempotencyId() {
    global $identifiants;
    $requestData = $identifiants[0].':'.$_SERVER['REQUEST_URI'].':'.http_build_query($_POST);
    return md5($requestData);
}
function withRequestIdempotency($opts) {
    $IDEMPOTENCY_RES_TTL = 1000;
    $idempotencyId = getRequestIdempotencyId();
    $idempotencyKey = "idempotency:$idempotencyId";
    $res = apcu_fetch($idempotencyKey);
    if ($res !== false) {
        $resJson = json_decode($res);
        if (isset($opts['is_cache_stale']) && $opts['is_cache_stale']($resJson)) {
            apcu_delete($idempotencyKey);
            unset($resJson);
        }
    }
    if (isset($resJson))
        $res = $resJson;
    else
        $res = $opts['callback']();
    apcu_store($idempotencyKey,json_encode($res), $IDEMPOTENCY_RES_TTL);
    return $res;
}