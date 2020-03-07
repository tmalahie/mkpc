<?php
require_once('touch.php');
$MAX_FILES = 2000;
$CACHE_FOLDER = 'images/creation_icons/';
function thumbnail($original_src,$cache_src, $maxw, $maxh) {
    list($width, $height) = getimagesize($original_src);
    $thumb = imagecreatetruecolor($maxw,$maxh);

    switch (exif_imagetype($original_src)) {
    case 1 :
        $source = imagecreatefromgif($original_src);
        break;
    case 2 :
        $source = imagecreatefromjpeg($original_src);
        break;
    case 3 :
        $source = imagecreatefrompng($original_src);
        break;
    default :
        return;
    }
    
    $x = 0;
    $y = 0;
    
    $newWidth = $maxw;
    $newHeight = $maxh;
    
    if ($height*$maxw > $maxh*$width) {
        $newHeight = $maxw*$height/$width;
        $y = ($newHeight-$maxh)/2;
    }
    else {
        $newWidth = $maxh*$width/$height;
        $x = ($newWidth-$maxw)/2;
    }

    imagecopyresampled($thumb, $source, -$x,-$y, 0, 0, $newWidth,$newHeight, $width,$height);

    imagepng($thumb, $cache_src);
    imagedestroy($thumb);
    imagedestroy($source);
}
function cachePath($cache_src) {
    global $CACHE_FOLDER;
    return $CACHE_FOLDER.$cache_src;
}
function cacheExists($cache_src) {
    return file_exists(cachePath($cache_src));
}
function setCacheFile($original_src,$cache_src, $minW,$minH, $thumbnailize=true) {
	global $CACHE_FOLDER, $MAX_FILES;
	$absolutePath = cachePath($cache_src);
	if (file_exists($absolutePath))
        touch_async($absolutePath);
    else {
        if ($thumbnailize)
    		thumbnail($original_src,$absolutePath, $minW,$minH);
        else
            copy($original_src,$absolutePath);
		$dh  = opendir($CACHE_FOLDER);
		$oldestTime = 2147483647;
		$n = 0;
		while (false !== ($filename = readdir($dh))) {
		    if (($filename != '.') && ($filename != '..')) {
		    	$cTime = filectime($CACHE_FOLDER.$filename);
		    	if ($cTime < $oldestTime) {
		    		$oldestTime = $cTime;
		    		$oldestFile = $filename;
		    	}
		    	$n++;
		    }
		}
        closedir($dh);
		if ($n > $MAX_FILES)
			unlink($CACHE_FOLDER.$oldestFile);
	}
	return $absolutePath;
}
?>