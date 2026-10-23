<?php
function is_public_ip($ip) {
	if (!filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE))
		return false;
	// IPv4-mapped (::ffff:a.b.c.d) and IPv4-compatible (::a.b.c.d) addresses reach the embedded IPv4
	$packed = @inet_pton($ip);
	if ($packed !== false && strlen($packed) === 16) {
		$prefix = substr($packed, 0, 12);
		if ($prefix === str_repeat("\0", 10)."\xff\xff" || $prefix === str_repeat("\0", 12)) {
			$v4 = unpack('N', substr($packed, 12));
			return is_public_ip(long2ip($v4[1]));
		}
	}
	return true;
}
function resolve_public_ip($host) {
	if (filter_var($host, FILTER_VALIDATE_IP))
		return is_public_ip($host) ? $host : null;
	$ips = @gethostbynamel($host);
	if (!$ips)
		$ips = array();
	$aaaa = @dns_get_record($host, DNS_AAAA);
	if ($aaaa) {
		foreach ($aaaa as $record)
			if (isset($record['ipv6']))
				$ips[] = $record['ipv6'];
	}
	if (!$ips)
		return null;
	foreach ($ips as $ip) {
		if (!is_public_ip($ip))
			return null;
	}
	return $ips[0];
}
function fetch_public_url($url) {
	for ($redirects = 0; $redirects <= 5; $redirects++) {
		$parts = parse_url($url);
		if (!$parts || !isset($parts['scheme'], $parts['host']))
			return false;
		$scheme = strtolower($parts['scheme']);
		if ($scheme !== 'http' && $scheme !== 'https')
			return false;
		$host = trim($parts['host'], '[]');
		$ip = resolve_public_ip($host);
		if (!$ip)
			return false;
		$port = isset($parts['port']) ? $parts['port'] : ($scheme === 'https' ? 443 : 80);
		$ch = curl_init($url);
		curl_setopt_array($ch, array(
			CURLOPT_RETURNTRANSFER => true,
			CURLOPT_FOLLOWLOCATION => false,
			CURLOPT_CONNECTTIMEOUT => 10,
			CURLOPT_TIMEOUT => 60
		));
		if ($ip !== $host) {
			// Pin to the validated IP to defeat DNS rebinding between resolve and connect
			curl_setopt($ch, CURLOPT_RESOLVE, array($host.':'.$port.':'.(strpos($ip, ':') === false ? $ip : '['.$ip.']')));
		}
		$body = curl_exec($ch);
		$status = curl_getinfo($ch, CURLINFO_RESPONSE_CODE);
		if ($status >= 300 && $status < 400) {
			$url = curl_getinfo($ch, CURLINFO_REDIRECT_URL);
			if (!$url)
				return false;
			continue;
		}
		if ($body === false || $status < 200 || $status >= 300)
			return false;
		return $body;
	}
	return false;
}
function url_to_file_payload($url) {
	$url = preg_replace_callback('#[\x00-\x20"\'<>\\\\]#', function($matches) {
		return rawurlencode($matches[0]);
	}, stripslashes($url));
	$fileContent = preg_match('#^https?://#i', $url) ? fetch_public_url($url) : false;
	if ($fileContent) {
		$file = tmpfile();
		$fileStream = stream_get_meta_data($file);
		$filePath = $fileStream['uri'];
		file_put_contents($filePath, $fileContent);
		return array(
			'url' => $url,
			'size' => @filesize($filePath),
			'tmp_name' => $filePath,
			'tmp_file' => $file,
			'error' => 0
		);
	}
	else {
		return array(
			'url' => $url,
			'size' => 0,
			'tmp_name' => null,
			'error' => 1
		);
	}
}
?>