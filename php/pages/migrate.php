<?php
if (isset($_GET['url'])) {// && isset($_GET['cookies'])) {
	if (!isset($_COOKIE['migr'])) {
		$cookies = json_decode($_GET['cookies'], true);
		if (is_array($cookies)) {
			foreach ($cookies as $key=>$cookie) {
				//if (!isset($_COOKIE[$key]))
				if (!is_string($key) || !preg_match('#^[a-zA-Z0-9_]+$#', $key) || $key === session_name() || !is_scalar($cookie))
					continue;
				setcookie($key, $cookie, 4294967295, '/');
			}
		}
		setcookie('migr', 1, time()+3600*24, '/');
	}
	header('location: '. urlencode($_GET['url']));
}
?>
