<?php
function rateLimitIp() {
	require_once('../includes/apc.php');
	$ip = $_SERVER['REMOTE_ADDR'];
	$rateLimit = 10;
	$rateLimitTime = 60;
	$rate = apcu_fetch('rateLimit_'.$ip);
	if (!$rate) $rate = 0;
	$rate++;
	apcu_store('rateLimit_'.$ip, $rate, $rateLimitTime);
	if ($rate >= $rateLimit)
		exit;
}
rateLimitIp();