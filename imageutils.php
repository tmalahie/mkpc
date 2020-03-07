<?php
function imagecropreal($img, $rect) {
	$res = imagecreatetruecolor($rect['width'],$rect['height']);
	imagealphablending($res, false);
	imagesavealpha($res,true);
	imagefill($res, 0,0, imagecolorallocatealpha($res, 0,0,0, 127));
	imagecopy($res, $img, 0,0,$rect['x'],$rect['y'],$rect['width'],$rect['height']);
	return $res;
}
?>