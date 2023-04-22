<?php
function isSquareTrack(&$circuit) {
    for ($i=0;$i<36;$i++) {
        if ($circuit["p$i"] < 4) {
            switch($circuit["p$i"]) {
                case 0 :
                $d = 6;
                break;
                case 1 :
                $d = 1;
                break;
                case 2 :
                $d = -6;
                break;
                case 3 :
                $d = -1;
                break;
            }
            $depart = $i;
            break;
        }
    }
    if (!isset($depart)) return true;
    if (!isset($d)) return true;
    if (isset($circuit["e0"])) return false;
    if (isset($circuit["f0"])) return false;
    if (isset($circuit["g0"])) return false;
    if (isset($circuit["h0"])) return false;
    if (isset($circuit["i0"])) return false;
    if (isset($circuit["j0"])) return false;
    $nbItems = 0;
    for ($i=0;isset($circuit["o$i"]);$i++)
        $nbItems++;
    $nbBoosts = 0;
    for ($i=0;isset($circuit["a$i"]);$i++)
        $nbBoosts++;
    for ($i=0;isset($circuit["b$i"]);$i++)
        $nbBoosts++;
    for ($i=0;isset($circuit["c$i"]);$i++)
        $nbBoosts++;
    for ($i=0;isset($circuit["d$i"]);$i++)
        $nbBoosts++;
    $nbDecors = 0;
    for ($i=0;isset($circuit["t$i"]);$i++)
        $nbDecors++;
    for ($j=1;$j<10;$j++) {
        for ($i=0;isset($circuit['t'.$j.'_'.$i]);$i++)
            $nbDecors++;
    }
    if (($nbDecors*3+$nbBoosts*2+$nbItems) > 30) return false;
    $i = $depart;
    $direction = $d;
    $nbTurns = 0;
    $distance = 0;
    $last = 0;
    while ($direction) {
        $i += $direction;
        if ($distance >= 50)
            break;
        $distance++;
        if (!isset($circuit["p$i"]))
            continue;
        switch($circuit["p$i"]) {
            case 4 :
            $direction = ($direction==-6 ? -1 : 6);
            $nbTurns++;
            break;
            case 5 :
            $direction = ($direction==-6 ? 1 : 6);
            $nbTurns++;
            break;
            case 6 :
            $direction = ($direction==-1 ? -6 : 1);
            $nbTurns++;
            break;
            case 7 :
            $direction = ($direction==1 ? -6 : -1);
            $nbTurns++;
            break;
            case 8 :
                if ($last == 9)
                    $nbTurns++;
                break;
            case 9 :
                if ($last == 8)
                    $nbTurns++;
                break;
            break;
            case 10 :
                $nbTurns++;
            case 11 :
                $nbTurns++;
            break;
            default :
            if ($direction == $d)
                $direction = false;
            break;
        }
        $last = $circuit["p$i"];
    }
    return ($nbTurns <= 4);
}
function getSQLRawValue(&$value) {
    return isset($value) ? '"'.$value.'"' : 'NULL';
}
require_once('cache_creations.php');
$THUMBNAIL_FOLDER = $CACHE_FOLDER . 'uploads/';
function thumbnailize($source, $dest) {
    global $THUMBNAIL_FOLDER;
    $w_ic = 120;
    $h_ic = $w_ic;
    $res = "$dest.png";
    if (thumbnail($source,$THUMBNAIL_FOLDER.$res, $w_ic,$h_ic))
        return $res;
}
function postCircuitUpdate($type, $circuitId, $isBattle=false, &$payload=null) {
    global $THUMBNAIL_FOLDER;
    if ($payload === null) $payload = $_POST;
    if (($type === 'mkcircuits') && !$isBattle) {
        if (isSquareTrack($payload)) {
            mysql_query('INSERT IGNORE INTO `mktrackbin` SET type="'. $type .'",circuit="'. $circuitId .'", delete_at=NOW()+INTERVAL 10 MINUTE');
        }
        else {
            mysql_query('DELETE FROM `mktrackbin` WHERE type="'. $type .'" AND circuit="'. $circuitId .'"');
        }
    }
    $shouldDeleteThumbnail = !empty($payload['thumbnail_unset']);

    if (isset($_FILES['thumbnail'])) {
        $thumbnail = $_FILES['thumbnail'];
        if (!$thumbnail['error']) {
            $tmpFile = tmpfile();
            $fileStream = stream_get_meta_data($tmpFile);
            $thumbnailTmpPath = $fileStream['uri'];
            if (move_uploaded_file($thumbnail['tmp_name'], $thumbnailTmpPath)) {
                if ($thumbnailName = thumbnailize($thumbnailTmpPath, $type.'-'. $circuitId .'-'. time())) {
                    $thumbnailNameRaw = '"'. $thumbnailName .'"';
                    $shouldDeleteThumbnail = true;
                }
            }
            @unlink($thumbnailTmpPath);
        }
        else if ($thumbnail['error'] !== UPLOAD_ERR_NO_FILE)
            $shouldDeleteThumbnail = false;
    }
    if ($shouldDeleteThumbnail) {
        if ($currentThumbnail = mysql_fetch_array(mysql_query('SELECT thumbnail FROM mktracksettings WHERE circuit="'. $circuitId .'" AND type="'. $type .'"'))) {
            if ($currentThumbnail['thumbnail'] !== null) {
                @unlink($THUMBNAIL_FOLDER . $currentThumbnail['thumbnail']);
                if (!isset($thumbnailNameRaw))
                    $thumbnailNameRaw = 'NULL';
            }
        }
    }
    mysql_query(
        'INSERT INTO mktracksettings
        SET circuit="'. $circuitId .'", type="'. $type .'",
        name_en='. getSQLRawValue($payload['name_en']) .',
        name_fr='. getSQLRawValue($payload['name_fr']) .',
        '. (isset($thumbnailNameRaw) ? 'thumbnail='. $thumbnailNameRaw .',' : '') .'
        prefix='. getSQLRawValue($payload['prefix']) .'
        ON DUPLICATE KEY UPDATE
        name_en=VALUES(name_en),
        name_fr=VALUES(name_fr),
        '. (isset($thumbnailNameRaw) ? 'thumbnail=VALUES(thumbnail),' : '') .'
        prefix=VALUES(prefix)'
    );
}