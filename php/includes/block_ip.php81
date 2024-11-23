<?php
$dir = dirname(__FILE__);
$ip = $_SERVER['REMOTE_ADDR'];
http_response_code(429);
$blocked = apcu_fetch("blocked_ips:$ip");
if ($blocked)
	exit;
file_put_contents('/tmp/check2ban-'.$ip, json_encode(getallheaders()));
shell_exec("sudo $dir/block_ip.sh $ip");
apcu_store("blocked_ips:$ip", 1, 300);
