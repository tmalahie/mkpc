<?php
function is_fetchable_url($url) {
	$parts = parse_url($url);
	if (!$parts || !isset($parts['scheme'], $parts['host']))
		return false;
	if (!in_array(strtolower($parts['scheme']), array('http', 'https')))
		return false;
	$host = $parts['host'];
	$ips = array();
	if (filter_var($host, FILTER_VALIDATE_IP))
		$ips[] = $host;
	else {
		$ipv4 = @gethostbynamel($host);
		if ($ipv4)
			$ips = array_merge($ips, $ipv4);
		$aaaa = @dns_get_record($host, DNS_AAAA);
		if ($aaaa) {
			foreach ($aaaa as $record)
				if (isset($record['ipv6']))
					$ips[] = $record['ipv6'];
		}
	}
	if (!$ips)
		return false;
	foreach ($ips as $ip) {
		if (!filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE))
			return false;
	}
	return true;
}
function url_to_file_payload($url) {
	if (!is_fetchable_url($url))
		return array('url' => $url, 'size' => 0, 'tmp_name' => null, 'error' => UPLOAD_ERR_NO_FILE);
	$fileContent = @file_get_contents($url);
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