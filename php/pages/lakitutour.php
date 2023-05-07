<?php
if (isset($_GET['tour'])) {
	$tour = $_GET['tour'];
	
	if (($tour > 1) && ($tour <= 3)) {
		header ("Content-type: image/png");
		$image = imagecreatefrompng("images/lakitutour.png");

		imagestring($image, 5, 33, 1, $tour, imagecolorallocate($image, 255, 255, 255));

		imagepng($image);
	}
}
?>