<?php
@include('customHash.php');

function isHashValid($body) {
     // selfhost (customHash does not exist)
    if (!function_exists('customHash')) return true; // always valid
    
    // prod (wargor, customHash exists)
    $hash = customHash($body);
    if (!isset($_SESSION["tthash:$hash"]) || $_SESSION["tthash:$hash"] < time())
        return false;
    return true;
}
function logHashInvalid($body) {
    global $identifiants;
    $hash = '';
    $lastExpiry = 0;
    foreach ($_SESSION as $key => $value) {
        if (str_starts_with($key, 'tthash:') && $value > $lastExpiry) {
            $hash = substr($key, 7);
            $lastExpiry = $value;
        }
    }
    mysql_query('INSERT INTO mktthacker SET identifiant="'. $identifiants[0] .'",body="'. mysql_real_escape_string($body) .'", hash="'. mysql_real_escape_string($hash) .'"');
}