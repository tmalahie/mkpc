<?php
if (isset($_POST['stats'])) {
    $hash = $_POST['stats'];
    $expiry = time() + 10;
    session_start();
    $_SESSION['tthash'] = "$hash:$expiry";
}
echo 1;