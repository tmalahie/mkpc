<?php
define('CIRCUIT_BASE_PATH', 'images/uploads/');
function getCircuitImgUrl($circuitImg) {
    if ($circuitImg->local)
        return CIRCUIT_BASE_PATH.$circuitImg->url;
    return $circuitImg->url;
}
function deleteCircuitFile($circuitImg) {
    if ($circuitImg->local)
        @unlink(CIRCUIT_BASE_PATH.$circuitImg->url);
}
function getCircuitExt(&$path, $default='png') {
    $fileType = @mime_content_type($path);
    $extensions = array(
        'image/png' => 'png',
        'image/gif' => 'gif',
        'image/jpeg' => 'jpg'
    );
    if (isset($extensions[$fileType]))
        return $extensions[$fileType];
    return $default;
}
function getCircuitImgData(&$path,&$url,$local) {
    $imgData = new stdClass();
    $imgData->url = $url;
    $imgData->local = $local;
    list($w,$h) = getimagesize($path);
    return (object) array(
        'url' => $url,
        'w' => $w,
        'h' => $h,
        'ext' => getCircuitExt($path),
        'local' => intval($local)
    );
}
function getCircuitImgDataRaw(&$path,&$url,$local) {
    return mysql_real_escape_string(json_encode(getCircuitImgData($path,$url,$local)));
}
function getCircuitLocalFile($circuitImg) {
    if ($circuitImg->local)
        return array('path' => CIRCUIT_BASE_PATH.$circuitImg->url);
    $file = tmpfile();
    $fileStream = stream_get_meta_data($file);
    $filePath = $fileStream['uri'];
    file_put_contents($filePath, @file_get_contents($circuitImg->url));
    return array(
        'path' => $filePath,
        'tmp_file' => $file
    );
}
?>