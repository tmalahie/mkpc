<?php
header('Content-Type: text/plain');
include('../includes/initdb.php');
include('../includes/session.php');
include('../includes/notifsSQL.php');
$notifsSQL = '';
for ($i=0;isset($_POST['id'.$i]);$i++) {
	if ($i)
		$notifsSQL .= ' OR ';
	$notifsSQL .= 'id="'. $_POST['id'.$i] .'"';
}
mysql_query('DELETE FROM `mknotifs` WHERE ('. $idsSQL .') AND ('. $notifsSQL .')');
mysql_close();
echo 1;
?>