<?php
session_start();
session_destroy();
setcookie('mkp', '', 0,'/');
?>