<?php
function preventRecursiveCalls() {
	if ($_SERVER['REMOTE_ADDR'] === $_SERVER['SERVER_ADDR'])
		throw new \Exception('Recursive call detected');
}