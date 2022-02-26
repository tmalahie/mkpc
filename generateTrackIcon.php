<?php
include('cache_creations.php');
function getSrcFromType($type) {
    switch ($type) {
    case 0 :
        return 'mappreview';
    case 1 :
        return 'racepreview';
    case 2 :
        return 'coursepreview';
    case 4 :
        return 'mcuppreview';
    }
}
function cache($file,$id) {
    $cacheSrc = $file.$id.'.png';
    if (cacheExists($cacheSrc)) {
        $res = cachePath($cacheSrc);
        touch_async($res);
        return $res;
    }
    $w_ic = 120; $h_ic = $w_ic;
    $temp = true;
    $cacheFile = $file.$id.'.png';
    include($file .'.php');
    $res = setCacheFile($tempName, $cacheSrc, $w_ic,$h_ic, ($file!=='mcuppreview') && ($file!=='mappreview'));
    @unlink($tempName);
    return $res;
}
function generateTrackIcon($id,$type) {
	$isrc = getSrcFromType($type);
	if (isset($isrc) && is_numeric($id))
		return cache($isrc,$id);
}