<?php
function mkpctag($file) {
	$fallback = 'mk.js';
    $filePath = 'scripts/' . $file;
	$filePath = preg_replace('/\?.*/', '', $filePath);
    return '<script type="text/javascript" src="' . (file_exists($filePath) ? $filePath : 'scripts/' . $fallback) . '"></script>';
}

if ($_SERVER['HTTP_HOST'] !== 'mkpc.malahieude.net') {
    echo mkpctag('mk.js');
} else {
    echo mkpctag('mk.min.js?cb=' . time());
}
?>