<?php
header('Content-Type: text/plain');
if (isset($_POST['clear'])) {
	$params = Array('iQuality', 'iScreenScale', 'bMusic', 'iSfx');
	foreach ($params as $param)
		setcookie($param, null, 0,'/');
}
echo 1;