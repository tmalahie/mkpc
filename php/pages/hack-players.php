<?php
if (isset($_GET['nb']))
	setcookie('mkplayers', preg_replace('#[^0-9,]#', '', $_GET['nb']), 4294967295, '/');
?>