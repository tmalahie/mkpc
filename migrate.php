<?php
if (isset($_GET['url'])) {// && isset($_GET['cookies'])) {
	if (!isset($_COOKIE['migr'])) {
		$cookies = json_decode($_GET['cookies']);
		foreach ($cookies as $key=>$cookie) {
			//if (!isset($_COOKIE[$key]))
			setcookie($key, $cookie, 4294967295, '/');
		}
		setcookie('migr', 1, time()+3600*24, '/');
	}
	header('location: '. $_GET['url']);
}
?>
