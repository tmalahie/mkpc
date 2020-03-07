<?php
if (isset($_GET['id']) && isset($_GET['type'])) {
	include('getSrcFromType.php');
	if (isset($isrc) && is_numeric($id)) {
		include('cache_creations.php');
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
		    $res = setCacheFile($tempName, $cacheSrc, $w_ic,$h_ic, ($file!=='mcuppreview'));
		    unlink($tempName);
		    return $res;
		}
		header('content-type: image/png');
		include('initdb.php');
		$filepath = cache($isrc,$id);
		mysql_close();
		echo file_get_contents($filepath);
	}
}
?>