<?php
if (isset($_POST['param']) && isset($_POST['value'])) {
	$params = Array('iQuality', 'iScreenScale', 'iSound', 'iFps');
	setcookie($params[$_POST['param']], $_POST['value'], 4294967295,'/');
	echo 1;
}
?>