<?php
session_start();
session_destroy();
require_once('credentials.php');
setcookie('mkpseudo', null, 0,'/');
setcookie('mkcode', null, 0,'/');
setcookie('mkp', null, 0,'/');
header('location: forum.php');
?>