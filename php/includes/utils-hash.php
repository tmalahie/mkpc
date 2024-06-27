<?php
@include('customHash.php');
function isHashValid($body) {
    if (!isset($_SESSION['tthash'])) return false;
    $ttHash = $_SESSION['tthash'];
    list($hash, $expiry) = explode(':', $ttHash);
    if (time() > $expiry)
        return false;
    if (!function_exists('customHash'))
        return true;
    return customHash($body) === $hash;
}
function logHashInvalid($body) {
    global $identifiants;
    mysql_query('INSERT INTO mktthacker SET identifiant="'. $identifiants[0] .'",body="'. mysql_real_escape_string($body) .'", hash="'. (isset($_SESSION['tthash']) ? mysql_real_escape_string($_SESSION['tthash']) : '') .'"');
}