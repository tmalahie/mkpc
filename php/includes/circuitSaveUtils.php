<?php
function getNewImgData($data, &$circuit) {
    $circuitImg = json_decode($circuit['img_data']);
    if (isset($data->imgOverrides) && isset($circuitImg->lapOverrides)) {
        $lapOverrides = $circuitImg->lapOverrides;
        $newLapOverrides = new stdClass();
        $newImgs = array();
        foreach ($data->imgOverrides as $lapId => $imgOverride) {
            $newLapOverride = null;
            if (isset($imgOverride->url)) {
                foreach ($lapOverrides as $lapOverride) {
                    if (isset($lapOverride->url) && $lapOverride->url === $imgOverride->url && $lapOverride->local === $imgOverride->local) {
                        $newLapOverride = $lapOverride;
                        break;
                    }
                }
            }
            elseif (isset($imgOverride->override)) {
                $newLapOverride = (object) array(
                    'local' => 0,
                    'override' => intval($imgOverride->override)
                );
            }
            if (!$newLapOverride && isset($lapOverrides->$lapId))
                $newLapOverride = $lapOverrides->$lapId;
            if ($newLapOverride) {
                $newLapOverrides->$lapId = $newLapOverride;
                if ($newLapOverride->local)
                    $newImgs[$newLapOverride->url] = true;
            }
        }
        foreach ($lapOverrides as $lapOverride) {
            if ($lapOverride->local && !isset($newImgs[$lapOverride->url])) {
                require_once('../includes/circuitImgUtils.php');
                $path = CIRCUIT_BASE_PATH.$lapOverride->url;
                @unlink($path);
            }
        }
        $circuitImg->lapOverrides = $newLapOverrides;
        return $circuitImg;
    }
    return null;
}