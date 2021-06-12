<?php
if (($_SERVER['HTTP_HOST'] !== 'mkpc.malahieude.net') || isset($_GET['metakey']))
	echo '<script type="text/javascript" src="scripts/mk.js?reload=4"></script>';
else
	echo '<script type="text/javascript" src="scripts/mk.v8e.js"></script>';
?>