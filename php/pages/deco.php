<?php
header('Content-Type: text/plain');
session_start();
session_destroy();
setcookie('mkp', '', 0,'/');
?>