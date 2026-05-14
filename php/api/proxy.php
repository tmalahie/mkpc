<?php
// Image proxy for cross-origin assets that don't send CORS headers.
// Used as a fallback so canvases drawing those images stay untainted
// (required by WebGL's texImage2D).

define('PROXY_CACHE_DIR', __DIR__ . '/../../images/uploads/proxy_cache');
define('PROXY_CACHE_MAX_AGE', 86400); // 24h
define('PROXY_CACHE_MAX_BYTES', 100 * 1024 * 1024); // 100 MB
define('PROXY_MAX_FETCH_BYTES', 8 * 1024 * 1024); // 8 MB per fetch

function proxy_fail($code) {
	http_response_code($code);
	exit;
}

function proxy_send($contentType, $body) {
	header('Access-Control-Allow-Origin: *');
	header('Content-Type: ' . $contentType);
	header('Cache-Control: public, max-age=86400');
	header('X-Content-Type-Options: nosniff');
	echo $body;
}

function proxy_serve_from_cache($path) {
	$contents = @file_get_contents($path);
	if ($contents === false) return false;
	$nl = strpos($contents, "\n");
	if ($nl === false) return false;
	$ct = substr($contents, 0, $nl);
	if (stripos($ct, 'image/') !== 0) return false;
	proxy_send($ct, substr($contents, $nl + 1));
	return true;
}

function proxy_write_cache($path, $contentType, $body) {
	if (!is_dir(PROXY_CACHE_DIR))
		@mkdir(PROXY_CACHE_DIR, 0755, true);
	$tmp = $path . '.tmp.' . getmypid();
	if (@file_put_contents($tmp, $contentType . "\n" . $body) === false) return;
	if (!@rename($tmp, $path))
		@unlink($tmp);
}

// Runs on ~1% of requests. Deletes entries older than the max age, then
// enforces the byte cap by removing oldest files first.
function proxy_cleanup_cache() {
	if (rand(0, 99) !== 0) return;
	if (!is_dir(PROXY_CACHE_DIR)) return;
	$files = @scandir(PROXY_CACHE_DIR);
	if (!$files) return;
	$now = time();
	$entries = array();
	foreach ($files as $f) {
		if ($f === '.' || $f === '..') continue;
		$p = PROXY_CACHE_DIR . '/' . $f;
		if (!is_file($p)) continue;
		$mt = @filemtime($p);
		if ($mt === false) continue;
		// Drop stale tmp files from interrupted writes too.
		if (($now - $mt) > PROXY_CACHE_MAX_AGE) {
			@unlink($p);
			continue;
		}
		$size = @filesize($p);
		if ($size === false) continue;
		$entries[] = array('path' => $p, 'mtime' => $mt, 'size' => $size);
	}
	$total = 0;
	foreach ($entries as $e) $total += $e['size'];
	if ($total <= PROXY_CACHE_MAX_BYTES) return;
	usort($entries, function($a, $b) { return $a['mtime'] - $b['mtime']; });
	foreach ($entries as $e) {
		if ($total <= PROXY_CACHE_MAX_BYTES) break;
		if (@unlink($e['path']))
			$total -= $e['size'];
	}
}

$url = isset($_GET['url']) ? $_GET['url'] : '';
if (!$url || strlen($url) > 2048)
	proxy_fail(400);

$parts = parse_url($url);
if (!$parts || empty($parts['scheme']) || empty($parts['host']))
	proxy_fail(400);

$scheme = strtolower($parts['scheme']);
if ($scheme !== 'http' && $scheme !== 'https')
	proxy_fail(400);

$cacheKey = hash('sha256', $url);
$cachePath = PROXY_CACHE_DIR . '/' . $cacheKey;
if (is_file($cachePath) && (time() - @filemtime($cachePath)) < PROXY_CACHE_MAX_AGE) {
	if (proxy_serve_from_cache($cachePath)) {
		proxy_cleanup_cache();
		exit;
	}
}

$host = $parts['host'];
// Strip IPv6 brackets if present
$lookupHost = (strlen($host) > 1 && $host[0] === '[' && substr($host, -1) === ']')
	? substr($host, 1, -1)
	: $host;

// Resolve host to IP for SSRF validation. Reject if the resolved address
// is in private, reserved, or loopback ranges. gethostbyname returns the
// input string unchanged on failure, so guard with FILTER_VALIDATE_IP.
$ip = filter_var($lookupHost, FILTER_VALIDATE_IP) ? $lookupHost : gethostbyname($lookupHost);
if (!filter_var($ip, FILTER_VALIDATE_IP))
	proxy_fail(502);
if (!filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE))
	proxy_fail(403);
require_once('../includes/protections.php');
preventRecursiveCalls();

$port = isset($parts['port']) ? (int)$parts['port'] : ($scheme === 'https' ? 443 : 80);
if ($port < 1 || $port > 65535)
	proxy_fail(400);

$body = '';
$tooBig = false;

$ch = curl_init($url);
curl_setopt_array($ch, array(
	CURLOPT_FOLLOWLOCATION => false,
	CURLOPT_CONNECTTIMEOUT => 5,
	CURLOPT_TIMEOUT => 15,
	CURLOPT_PROTOCOLS => CURLPROTO_HTTP | CURLPROTO_HTTPS,
	// Pin to the IP we validated to defeat DNS rebinding between resolve and connect.
	CURLOPT_RESOLVE => array($host . ':' . $port . ':' . $ip),
	CURLOPT_USERAGENT => $_SERVER['HTTP_USER_AGENT'],
	CURLOPT_WRITEFUNCTION => function ($ch, $chunk) use (&$body, &$tooBig) {
		$body .= $chunk;
		if (strlen($body) > PROXY_MAX_FETCH_BYTES) {
			$tooBig = true;
			return 0;
		}
		return strlen($chunk);
	},
));

$ok = curl_exec($ch);
$status = (int)curl_getinfo($ch, CURLINFO_HTTP_CODE);
$contentType = curl_getinfo($ch, CURLINFO_CONTENT_TYPE);
curl_close($ch);

if ($tooBig)
	proxy_fail(413);
if ($ok === false || $status < 200 || $status >= 300)
	proxy_fail(502);
if (!$contentType || stripos($contentType, 'image/') !== 0)
	proxy_fail(415);

proxy_write_cache($cachePath, $contentType, $body);
proxy_send($contentType, $body);
proxy_cleanup_cache();
