<?php
header('Content-Type: application/json');
$res = -1;

if (!isset($_POST['id'])) {
	echo json_encode([]);
	exit;
}

include('../includes/session.php');
if (!$id) {
	echo json_encode([]);
	exit;
}

include('../includes/initdb.php');
$query = <<<SQL
	SELECT j.pts_vs, j.pts_battle FROM `mkjoueurs` j WHERE j.id="{$_POST['id']}"
SQL;

if ($getPts = mysql_fetch_array(mysql_query($query))) {
	echo json_encode([$getPts['pts_vs'], $getPts['pts_battle']]);
} else {
	echo json_encode([]);
}

mysql_close();
?>
