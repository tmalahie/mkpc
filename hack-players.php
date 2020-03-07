<?php
if (isset($_GET['nb']))
	setcookie('mkplayers', $_GET['nb'], 4294967295, '/');
?>