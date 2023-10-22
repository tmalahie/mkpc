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
	$getTemps = mysql_query('SELECT id,data FROM `mkghostsdata` WHERE id IN ('. implode(',',$ids) .')');
	$timesById = array();
	while ($time = mysql_fetch_array($getTemps))
		$timesById[$time['id']] = $time;
	echo '[';
	$colon = '';
	foreach ($ids as $id) {
		echo $colon .'[';
		if (isset($timesById[$id]))
			print_ghost_data($timesById[$id]);
		echo ']';
		$colon = ',';
	}
	echo ']';
	mysql_close();
}
?>