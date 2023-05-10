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
	$getTemps = mysql_query('SELECT ghost,'.GHOST_MYSQL_FIELDS.' FROM `mkghostdata` WHERE ghost IN ('. implode(',',$ids) .') ORDER BY ghost,frame');
	$ghostsData = array();
	foreach ($ids as $id)
		$ghostsData[$id] = array();
	echo '[';
	while ($time = mysql_fetch_array($getTemps))
		$ghostsData[$time['ghost']][] = $time;
	$colon2 = '';
	foreach ($ids as $id) {
		$ghostData = $ghostsData[$id];
		echo $colon2 .'[';
		$colon = '';
		foreach ($ghostData as $time) {
			echo $colon;
			print_ghost_frame($time);
			$colon = ',';
		}
		echo ']';
		$colon2 = ',';
	}
	echo ']';
	mysql_close();
}
?>