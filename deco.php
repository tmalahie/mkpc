<?php
session_start();
session_destroy();
setcookie('mkpseudo', null, 0,'/');
setcookie('mkcode', null, 0,'/');
setcookie('mkp', null, 0,'/');
?>