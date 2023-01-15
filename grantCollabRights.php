<?php
include('ip_banned.php');
if (isBanned()) {
    $hasReadGrants = false;
    $hasWriteGrants = false;
}
else {
    $hasReadGrants = isset($collab['rights']['view']);
    $hasWriteGrants = isset($collab['rights']['edit']);
}