<?php
function handleRateLimit() {
    global $language;
    if (isset($_COOKIE['mkp']) || isset($_COOKIE['mktoken'])) return;
    require_once('../includes/apc.php');

    $ip = $_SERVER['REMOTE_ADDR'];
    $rateLimit = 5;
    $rateLimitTime = 60;
    $rate = apcu_inc('rateLimit_'.$ip, 1, $success, $rateLimitTime);
    if ($rate > $rateLimit) {
        http_response_code(429);
        sleep(1);
        exit;
    }

    $cachedKey = "home:html:$language";
    $cachedContent = apcu_fetch($cachedKey);
    if ($cachedContent) {
        echo $cachedContent;
        exit;
    }
    ob_start();
    return function() use ($cachedKey) {
        $content = ob_get_clean();
        apcu_store($cachedKey, $content, 60);
        echo $content;
    };
}