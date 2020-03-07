<?php
$mkCredentialsKey = 'aaaaaa';
function credentials_raw_encrypt($plaintext) {
    global $mkCredentialsKey;
    $method = "AES-256-CBC";
    $key = hash('sha256', $mkCredentialsKey, true);
    $iv = openssl_random_pseudo_bytes(16);

    $ciphertext = openssl_encrypt($plaintext, $method, $key, OPENSSL_RAW_DATA, $iv);
    $hash = hash_hmac('sha256', $ciphertext . $iv, $key, true);

    return base64_encode($iv . $hash . $ciphertext);
}
function credentials_encrypt($id,$password) {
    return credentials_raw_encrypt("$id:$password");
}
function credentials_raw_decrypt($ivHashCiphertext) {
    global $mkCredentialsKey;
    $method = "AES-256-CBC";
    $ivHashCiphertext = base64_decode($ivHashCiphertext);
    $iv = substr($ivHashCiphertext, 0, 16);
    $hash = substr($ivHashCiphertext, 16, 32);
    $ciphertext = substr($ivHashCiphertext, 48);
    $key = hash('sha256', $mkCredentialsKey, true);

    if (!hash_equals(hash_hmac('sha256', $ciphertext . $iv, $key, true), $hash)) return null;

    return openssl_decrypt($ciphertext, $method, $key, OPENSSL_RAW_DATA, $iv);
}
function credentials_decrypt($token) {
    $rawCredentials = credentials_raw_decrypt($token);
    return explode(':', $rawCredentials, 2);
}