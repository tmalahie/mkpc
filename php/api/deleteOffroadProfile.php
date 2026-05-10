<?php
header('Content-Type: application/json');
$data = json_decode(file_get_contents('php://input'), true);
if (!is_array($data) || !isset($data['id'])) {
	echo json_encode(array('error' => 'Invalid input'));
	exit;
}
$id = intval($data['id']);
include('../includes/initdb.php');
include('../includes/getId.php');
$identifiant = intval($identifiants[0]);
$check = mysql_query('SELECT id FROM `mkoffroads` WHERE id="'. $id .'" AND identifiant="'. $identifiant .'"');
if (!mysql_numrows($check)) {
	echo json_encode(array('error' => 'Not found'));
	mysql_close();
	exit;
}
mysql_query('DELETE FROM `mkoffroads` WHERE id="'. $id .'"');
echo json_encode(array('ok' => 1));
mysql_close();
?>
