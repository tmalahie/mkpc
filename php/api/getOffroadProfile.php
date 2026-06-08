<?php
header('Content-Type: application/json');
if (!isset($_GET['id'])) {
	echo json_encode(null);
	exit;
}
include('../includes/initdb.php');
$id = intval($_GET['id']);
$row = mysql_fetch_array(mysql_query('SELECT id, name, profile FROM `mkoffroads` WHERE id="'. $id .'"'));
if (!$row) {
	echo json_encode(null);
	mysql_close();
	exit;
}
$profile = json_decode($row['profile'], true);
if (!is_array($profile))
	$profile = new stdClass();
echo json_encode(array(
	'id' => intval($row['id']),
	'name' => $row['name'],
	'profile' => $profile
));
mysql_close();
?>
