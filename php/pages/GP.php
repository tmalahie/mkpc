<?php
include('../includes/initdb.php');
include('../includes/fetchSaves.php');
mysql_close();
echo '[';
for ($i=0;$i<10;$i++)
	echo $mkSaves[$i].',';
echo ']';
?>