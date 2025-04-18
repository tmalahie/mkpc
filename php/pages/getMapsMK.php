<?php
include('../includes/initdb.php');
$mariokart = mysql_query('SELECT * FROM `mkmaps'.$_POST["id"].'`');
$maps = array();
while ($donnees = mysql_fetch_array($mariokart)) {
	$maps[] = $donnees["map"];
}
echo json_encode($maps);
mysql_close();
?>