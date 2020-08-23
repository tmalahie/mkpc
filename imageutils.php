<?php
function imagecropreal($img, $rect) {
	$res = imagecreatetruecolor($rect['width'],$rect['height']);
	imagealphablending($res, false);
	imagesavealpha($res,true);
	imagefill($res, 0,0, imagecolorallocatealpha($res, 0,0,0, 127));
	imagecopy($res, $img, 0,0,$rect['x'],$rect['y'],$rect['width'],$rect['height']);
	return $res;
}
function clone_img_resource($original_src,$thumb_src) {
	switch (exif_imagetype($original_src)) {
	case 1 :
		if (is_animated_gif($original_src)) {
			copy($original_src,$thumb_src);
			return;
		}
		$original = imagecreatefromgif($original_src);
		break;
	case 2 :
		$original = imagecreatefromjpeg($original_src);
		break;
	case 3 :
		if (in_array(ord(@file_get_contents($original_src, NULL, NULL, 25, 1)), array(3,6))) {
			copy($original_src,$thumb_src);
			return;
		}
		$original = imagecreatefrompng($original_src);
		break;
	default :
		return;
	}
	$w = imagesx($original);
	$h = imagesy($original);
	$thumb = imagecreatetruecolor($w,$h);
	imagesavealpha($thumb, true);
	$transparent = imagecolorallocatealpha($thumb, 0,0,0, 127);
	imagefill($thumb, 0,0, $transparent);
	imagecopy($thumb,$original, 0,0, 0,0, $w,$h);
	imagedestroy($original);
	imagepng($thumb, $thumb_src);
	imagedestroy($thumb);
}
function is_animated_gif($filename) {
    if(!($fh = @fopen($filename, 'rb')))
        return false;
    $count = 0;

    while (!feof($fh) && $count < 2) {
        $chunk = fread($fh, 1024 * 100); //read 100kb at a time
        $count += preg_match_all('#\x00\x21\xF9\x04.{4}\x00[\x2C\x21]#s', $chunk, $matches);
    }

    fclose($fh);
    return $count > 1;
}
function image_create_from($src) {
	switch (exif_imagetype($src)) {
	case 1 :
		return imagecreatefromgif($src);
	case 2 :
		return imagecreatefromjpeg($src);
	case 3 :
		return imagecreatefrompng($src);
	}
}
function imagealphamask(&$img, $r,$g,$b,$a) {
	$w = imagesx($img);
	$h = imagesy($img);
	$res = imagecreatetruecolor($w,$h);
	imagesavealpha($res, true);
	$transparent = imagecolorallocatealpha($res, $r,$g,$b, 127);
	imagefill($res, 0,0, $transparent);
	imagecopy($res,$img, 0,0, 0,0, $w,$h);
	imagedestroy($img);
	$img = imagecreatetruecolor($w,$h);
	$c = imagecolorallocate($img, $r,$g,$b);
	imagefill($img, 0,0, $c);
	imagecopymerge($img,$res, 0,0, 0,0, $w,$h, $a);
	imagecolortransparent($img, $c);
	imagecopy($res,$img, 0,0, 0,0, $w,$h);
	imagedestroy($img);
	return $res;
}
function has_transparency($src) {
	$type = ord(@file_get_contents($src, NULL, NULL, 25, 1));
	switch ($type) {
	case 2 :
	case 3 :
	case 4 :
		$imgdata = imagecreatefrompng($src);
		return (imagecolortransparent($imgdata) != -1);
	case 6 :
		$imgdata = imagecreatefrompng($src);
		$w = imagesx($imgdata);
		$h = imagesy($imgdata);

		if ($w>50 || $h>50){ //resize the image to save processing if larger than 50px:
			$thumb = imagecreatetruecolor(10, 10);
			imagealphablending($thumb, FALSE);
			imagecopyresized($thumb, $imgdata, 0, 0, 0, 0, 10, 10, $w, $h );
			$imgdata = $thumb;
			$w = imagesx($imgdata);
			$h = imagesy($imgdata);
		}
		for ($i=0;$i<$w;$i++) {
			for($j=0;$j<$h;$j++) {
				$rgba = imagecolorat($imgdata, $i, $j);
				if(($rgba & 0x7F000000) >> 24)
					return true;
			}
		}
		return false;
	default :
		if ('image/gif' === mime_content_type($src))
			return true;
		return false;
	}
}
function add_transparency($original_src,$thumb_src, $r,$g,$b) {
	$original = imagecreatefrompng($original_src);
	$w = imagesx($original);
	$h = imagesy($original);
	$thumb = imagecreatetruecolor($w,$h);
	$c = imagecolorallocate($thumb, $r,$g,$b);
	imagefill($thumb, 0,0, $c);
	imagecopy($thumb,$original, 0,0, 0,0, $w,$h);
	imagedestroy($original);
	imagecolortransparent($thumb, $c);
	imagepng($thumb, $thumb_src);
	imagedestroy($thumb);
}
function filter_img($original_src,$thumb_src, $r,$g,$b,$a) {
	$thumb = image_create_from($original_src);
	$thumb = imagealphamask($thumb, $r,$g,$b,$a);
	imagepng($thumb, $thumb_src);
	imagedestroy($thumb);
}
function resize_img_resource($original_src,$thumb_src, $minw,$minh) {
	list($width, $height) = getimagesize($original_src);
	if ($width*$minh > $height*$minw) {
		$newHeight = $minh;
		$newWidth = round($minh*$width/$height);
	}
	else {
		$newWidth = $minw;
		$newHeight = round($minw*$height/$width);
	}
	if (($newWidth >= $width) && ($newHeight >= $height)) {
		$newWidth = $width;
		$newHeight = $height;
	}
	if (is_animated_gif($original_src)) {
		$temp_dir = tempnam(sys_get_temp_dir(),'mk');
		if (file_exists($temp_dir)) @unlink($temp_dir);
		@mkdir($temp_dir);
		$temp_file = $temp_dir.'/coalesce.gif';

		$wh = $width.'x'.$height;
		$newWH = $newWidth.'x'.$newHeight;
		$thumb_gif = preg_replace("#png$#", 'gif', $thumb_src);
		system("convert $original_src -coalesce $temp_file");
		system("convert -size $wh $temp_file -resize $newWH $thumb_gif");
		rename($thumb_gif, $thumb_src);
		@unlink($temp_file);
		@rmdir($temp_dir);
	}
	else {
		$thumb = imagecreatetruecolor($newWidth,$newHeight);
		imagesavealpha($thumb, true);
		$transparent = imagecolorallocatealpha($thumb, $r,$g,$b, 127);
		imagefill($thumb, 0,0, $transparent);

		$source = image_create_from($original_src);

		imagecopyresized($thumb, $source, 0,0, 0,0, $newWidth,$newHeight, $width, $height);

		imagepng($thumb, $thumb_src);
		imagedestroy($thumb);
		imagedestroy($source);
	}
}
?>