<?php
header('Content-Type: text/javascript');
header('Cache-Control: max-age=600000');

$data = file_get_contents('../php/includes/mk/maps.json');
$key = 0x5A;
$out = '';

for ($i = 0; $i < strlen($data); $i++) {
    $out .= chr(ord($data[$i]) ^ $key);
}

$encoded = base64_encode($out);

echo "function listMaps(){ return JSON.parse(decodeMaps(\"$encoded\")); }";

?>


function decodeMaps(b64) {
    const key = 0x5A;
    const bin = atob(b64);
    let out = "";

    for (let i = 0; i < bin.length; i++) {
        out += String.fromCharCode(bin.charCodeAt(i) ^ key);
    }

    return out;
}
