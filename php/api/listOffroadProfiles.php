<?php
header('Content-Type: application/json');
include('../includes/initdb.php');
include('../includes/getId.php');
$res = array();
$query = mysql_query('SELECT id, name, profile FROM `mkoffroads` WHERE identifiant="'. $identifiants[0] .'" ORDER BY creation_date ASC, id ASC');
while ($row = mysql_fetch_array($query)) {
	$profile = json_decode($row['profile'], true);
	if (!is_array($profile))
		$profile = new stdClass();
	$res[] = array(
		'id' => intval($row['id']),
		'name' => $row['name'],
		'profile' => $profile
	);
}
echo json_encode($res);
mysql_close();
?>
