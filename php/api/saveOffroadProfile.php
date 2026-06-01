<?php
header('Content-Type: application/json');
$data = json_decode(file_get_contents('php://input'), true);
if (!is_array($data) || !isset($data['name'])) {
	echo json_encode(array('error' => 'Invalid input'));
	exit;
}
$name = trim((string)$data['name']);
if ($name === '' || strlen($name) > 255) {
	echo json_encode(array('error' => 'Invalid name'));
	exit;
}
$strength = isset($data['strength']) ? floatval($data['strength']) : 0.5;
if ($strength < 0) $strength = 0;
if ($strength > 1) $strength = 1;
$slippery = !empty($data['slippery']);
$drifting = !empty($data['drifting']);
$billBoost = isset($data['billBoost']) ? !empty($data['billBoost']) : true;
$profile = array(
	'strength' => $strength,
	'slippery' => $slippery,
	'drifting' => $drifting,
	'billBoost' => $billBoost
);
if ($slippery) {
	$factor = isset($data['slipperyFactor']) ? floatval($data['slipperyFactor']) : 0.5;
	if ($factor < 0) $factor = 0;
	if ($factor > 1) $factor = 1;
	$profile['slipperyFactor'] = $factor;
}
include('../includes/initdb.php');
include('../includes/getId.php');
$profileJson = json_encode($profile);
$nameEsc = mysql_real_escape_string($name);
$profileEsc = mysql_real_escape_string($profileJson);
$identifiant = intval($identifiants[0]);
if (isset($data['id'])) {
	$id = intval($data['id']);
	$check = mysql_query('SELECT id FROM `mkoffroads` WHERE id="'. $id .'" AND identifiant="'. $identifiant .'"');
	if (!mysql_numrows($check)) {
		echo json_encode(array('error' => 'Not found'));
		mysql_close();
		exit;
	}
	mysql_query('UPDATE `mkoffroads` SET name="'. $nameEsc .'", profile="'. $profileEsc .'" WHERE id="'. $id .'"');
	echo json_encode(array('id' => $id, 'name' => $name, 'profile' => $profile));
}
else {
	mysql_query('INSERT INTO `mkoffroads` (name, creation_date, identifiant, profile) VALUES ("'. $nameEsc .'", CURRENT_TIMESTAMP(), "'. $identifiant .'", "'. $profileEsc .'")');
	$newId = intval(mysql_insert_id());
	echo json_encode(array('id' => $newId, 'name' => $name, 'profile' => $profile));
}
mysql_close();
?>
