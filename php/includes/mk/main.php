<?php
echo '<script type="text/javascript" src="scripts/notify.js"></script>';
if ($_SERVER['HTTP_HOST'] !== 'mkpc.malahieude.net')
	echo '<script type="text/javascript" src="scripts/mk.js"></script>';
else
	echo '<script type="text/javascript" src="scripts/mk.v332.js"></script>';
?>
