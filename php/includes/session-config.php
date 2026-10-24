<?php
if (session_status() === PHP_SESSION_NONE) {
	session_set_cookie_params(array(
		'lifetime' => 0,
		'path' => '/',
		'secure' => !empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off',
		'httponly' => true,
		'samesite' => 'Lax'
	));
}
