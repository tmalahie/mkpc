<?php
if ($_SERVER['HTTP_HOST'] !== 'mkpc.malahieude.net') {
	echo '<script type="text/javascript">var MK_DEV = true;</script>';
	echo '<script type="text/javascript" src="scripts/mk-dev.js"></script>';
	echo '<script type="text/javascript" src="scripts/mk.js"></script>';
}
else
	echo '<script type="text/javascript" src="scripts/mk.v332.js"></script>';
?>
