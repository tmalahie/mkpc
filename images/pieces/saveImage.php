<?php
if (isset($_POST['id']) && isset($_POST['map']) && isset($_POST['data'])) {
	$id = $_POST['id'];
	$map = $_POST['map'];
	$data = $_POST['data'];
	$data = base64_decode($data);
	$im = imagecreatefromstring($data);
	$im = imagecrop($im,['x' => 0, 'y' => 0, 'width' => 100, 'height' => 100]);
	imagepng($im, '../pieces_new/piececircuit'.$_POST['map'].'_'.$_POST['id'].'.png');
}
?>