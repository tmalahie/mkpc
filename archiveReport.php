<?php
if (!isset($_POST['id'])) exit;
include('session.php');
include('initdb.php');
require_once('getRights.php');
if (hasRight('moderator'))
    mysql_query('UPDATE mkreports SET state="archived" WHERE id="'. $_POST['id'] .'"');
echo 1;