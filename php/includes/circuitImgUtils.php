<?php
define('CIRCUIT_REL_PATH', 'images/uploads/');
define('CIRCUIT_BASE_PATH', '../../'.CIRCUIT_REL_PATH);
function getCircuitImgUrl($circuitImg) {
    if ($circuitImg->local)
        return CIRCUIT_REL_PATH.$circuitImg->url;
    return $circuitImg->url;
}
function getRefCircuitImg($circuitImg,$baseCircuitImg) {
    if (isset($circuitImg->override)) {
        $lapId = $circuitImg->override;
        if (!$lapId)
            return $baseCircuitImg;
        if (isset($baseCircuitImg->lapOverrides->$lapId))
            return $baseCircuitImg->lapOverrides->$lapId;
        return $baseCircuitImg;
    }
    return $circuitImg;
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
function getBaseCircuitImgDataRaw(&$baseCircuitImg,&$circuitImg, $lap) {
    if ($lap) {
        if (!isset($baseCircuitImg->lapOverrides))
            $baseCircuitImg->lapOverrides = new stdClass();
        $baseCircuitImg->lapOverrides->$lap = $circuitImg;
        return mysql_real_escape_string(json_encode($baseCircuitImg));
    }
    else {
        if (isset($baseCircuitImg->lapOverrides))
            $circuitImg->lapOverrides = $baseCircuitImg->lapOverrides;
        return mysql_real_escape_string(json_encode($circuitImg));
    }
}
function getCircuitLocalFile($circuitImg) {
    if ($circuitImg->local)
        return array('path' => CIRCUIT_BASE_PATH.$circuitImg->url);
    $file = tmpfile();
    $fileStream = stream_get_meta_data($file);
    $filePath = $fileStream['uri'];
    $context = stream_context_create(
        array(
            'http' => array(
                'follow_location' => false
            )
        )
    );
    file_put_contents($filePath, @file_get_contents($circuitImg->url, false, $context));
    return array(
        'path' => $filePath,
        'tmp_file' => $file
    );
}
?>