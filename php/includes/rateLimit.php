<?php
function handleRateLimit() {
    global $language;
    if (isset($_COOKIE['mkp']) || isset($_COOKIE['mktoken'])) return;
    require_once('../includes/apc.php');

    $ip = $_SERVER['REMOTE_ADDR'];
    $rateLimit = 9;
    $rateLimitLow = 4;
    $rateLimitTime = 60;
    $rate = apcu_inc('rateLimit_'.$ip, 1, $success, $rateLimitTime);
    if ($rate > $rateLimitLow) {
        $ddosProtect = apcu_fetch('pending_ddos');
	if ($ddosProtect)
            $rateLimit = $rateLimitLow;
    }
    if ($rate > $rateLimit) {
        sleep(1);
        http_response_code(429);
        $fail2ban = apcu_inc('fail2ban_'.$ip, 1, $success, $rateLimitTime);
        if (($fail2ban > $rateLimit) && $ip) {
            if (empty($ddosProtect))
                apcu_store('pending_ddos', 1, $rateLimitTime);
            file_put_contents('/tmp/fail2ban-'.$ip, json_encode(getallheaders()));
            $dir = dirname(__FILE__);
            shell_exec("sudo $dir/block_ip.sh $ip");
        }
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