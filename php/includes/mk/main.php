<?php
if ($_SERVER['HTTP_HOST'] !== 'mkpc.malahieude.net') {
	// cache busting, can be disable with ?nocache/&nocache
	$cb = '?cb='.time();
	if (isset($_GET['nocache'])) {
		$cb = '';
	}
	echo '<script type="text/javascript" src="scripts/mk.js'.$cb.'"></script>';
} else {
	echo '<script type="text/javascript" src="scripts/mk.v297.js"></script>';
}
?>
