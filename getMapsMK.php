<?php
include('initdb.php');
$mariokart = mysql_query('SELECT * FROM `mkmaps'.$_POST["id"].'`');
echo '[';
while($donnees = mysql_fetch_array($mariokart))
	echo $donnees["map"].',';
echo ']';
mysql_close();
?>