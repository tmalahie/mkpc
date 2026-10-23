<?php
function generate_token() {
	return bin2hex(random_bytes(16));
}
function assign_token() {
	if (!isset($_SESSION['csrf']))
		$_SESSION['csrf'] = generate_token();
}
?>