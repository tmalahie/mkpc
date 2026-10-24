<?php
if (isset($_POST['stats'])) {
    $hash = $_POST['stats'];
    $expiry = time() + 10;
    require_once('../includes/session-config.php');
    session_start();
    foreach ($_SESSION as $key => $value) {
        if (str_starts_with($key, 'tthash:') && $value < time())
            unset($_SESSION[$key]);
    }
    $_SESSION["tthash:$hash"] = $expiry;
}
echo 1;