<?php
header('Content-Type: text/plain');
$ids = array();
for ($i=0;isset($_POST['id'.$i]);$i++) {
	$id = $_POST['id'.$i];
	if (is_numeric($id))
		$ids[] = $id;
}
$nids = count($ids);
if ($nids && ($nids<=10)) {
	include('../includes/initdb.php');
	require_once('../includes/utils-tt.php');
	$getTemps = mysql_query('SELECT data FROM `mkghostsdata` WHERE id IN ('. implode(',',$ids) .') ORDER BY id');
	echo '[';
	$colon = '';
	while ($time = mysql_fetch_array($getTemps)) {
		echo $colon .'[';
		print_ghost_data($time);
		echo ']';
		$colon = ',';
	}
	echo ']';
	mysql_close();
}
?>