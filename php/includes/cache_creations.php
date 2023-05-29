<?php
require_once('touch.php');
$MAX_FILES = 20000;
define('CACHE_FOLDER', 'images/creation_icons/');
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
        return false;
    }
    
    $x = 0;
    $y = 0;
    
    $newWidth = $maxw;
    $newHeight = $maxh;
    
    if ($height*$maxw > $maxh*$width) {
        $newHeight = round($maxw*$height/$width);
        $y = round(($newHeight-$maxh)/2);
    }
    else {
        $newWidth = round($maxh*$width/$height);
        $x = round(($newWidth-$maxw)/2);
    }

    imagecopyresampled($thumb, $source, -$x,-$y, 0, 0, $newWidth,$newHeight, $width,$height);

    imagepng($thumb, $cache_src);
    imagedestroy($thumb);
    imagedestroy($source);
    return true;
}
function cachePath($cache_src) {
    return '../../'.CACHE_FOLDER.$cache_src;
}
function cachePathRelative($cache_src) {
    return CACHE_FOLDER.$cache_src;
}
function cacheExists($cache_src) {
    return file_exists(cachePath($cache_src));
}
function setCacheFile($original_src,$cache_src, $minW,$minH, $thumbnailize=true) {
	global $MAX_FILES;
	$absolutePath = cachePath($cache_src);
	if (file_exists($absolutePath))
        touch_async($absolutePath);
    else {
        if ($thumbnailize)
    		thumbnail($original_src,$absolutePath, $minW,$minH);
        else
            copy($original_src,$absolutePath);
        if (!rand(0,1000)) { // Clear cache once in a while
            $files = array_diff(scandir(cachePath('')), array('.', '..', '.gitignore', 'uploads'));
            $n = count($files);
            if ($n > $MAX_FILES) {
                $fileTimes = array_map(function($file) {
                    return filectime(cachePath($file));
                }, $files);
                array_multisort(
                    $fileTimes,
                    SORT_NUMERIC,
                    SORT_ASC,
                    $files
                );
                $toRemove = $n-$MAX_FILES;
                for ($i=0;$i<$toRemove;$i++)
                    @unlink(cachePath($files[$i]));
            }
        }
	}
	return $absolutePath;
}
?>