<?php
if ($_SERVER['REMOTE_ADDR'] != '79.45.174.164') {
	echo $_SERVER['REMOTE_ADDR'];
	exit;
}
header('Content-type: text/plain');
include('initdb.php');
$getTables = mysql_query('SHOW TABLES');
while ($table = mysql_fetch_array($getTables)) {
	echo $table[0].':';
	$getColumns = mysql_query('SHOW COLUMNS FROM '. $table[0]);
	while ($column = mysql_fetch_array($getColumns))
		echo $column[0].',';
	echo "\n";
}
mysql_close();