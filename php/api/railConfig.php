<?php
$configFile = __DIR__.'/../../railConfig.json';
if (is_file($configFile)) {
	$config = json_decode(file_get_contents($configFile), true);
} else {
	$config = array();
}
header('Content-Type: application/json');
echo json_encode($config, JSON_PRETTY_PRINT);