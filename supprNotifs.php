<?php
include('initdb.php');
include('session.php');
include('notifsSQL.php');
mysql_query('DELETE FROM `mknotifs` WHERE '. $idsSQL);
mysql_close();
echo 1;
?>