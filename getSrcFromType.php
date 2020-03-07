<?php
if (isset($_GET['id']) && isset($_GET['type'])) {
	include('escape_all.php');
	$id = $_GET['id'];
	$type = $_GET['type'];
}
switch ($type) {
case 0 :
	$isrc = 'mappreview';
	break;
case 1 :
	$isrc = 'racepreview';
	break;
case 2 :
	$isrc = 'coursepreview';
	break;
case 4 :
	$isrc = 'mcuppreview';
	break;
}
?>