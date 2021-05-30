<?php
mysql_connect('db','mkpc_user','mkpc_pwd');
mysql_select_db('mkpc');
mysql_set_charset('latin1');
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
