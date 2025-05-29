<?php
header('Content-Type: text/plain');
$data = json_decode(file_get_contents('php://input'));
if (is_object($data) && isset($data->id) && isset($data->payload)) {
	$id = intval($data->id);
	$payload = $data->payload;
	include('../includes/initdb.php');
	include('../includes/getId.php');
	require_once('../includes/collabUtils.php');
	$requireOwner = !hasCollabGrants('circuits', $data->id, $data->collab, 'edit');
	if ($circuit = mysql_fetch_array(mysql_query('SELECT img_data FROM circuits WHERE id="'.$id.'"'. ($requireOwner ? (' AND identifiant='.$identifiants[0].' AND identifiant2='.$identifiants[1].' AND identifiant3='.$identifiants[2].' AND identifiant4='.$identifiants[3]):'')))) {
		mysql_query('INSERT INTO circuits_data VALUES("'.$id.'","'.mysql_real_escape_string(gzcompress(json_encode($payload))).'") ON DUPLICATE KEY UPDATE data=VALUES(data)');
		require_once('../includes/circuitSaveUtils.php');
		$circuitImg = getNewImgData($data, $circuit);
		if ($circuitImg)
			mysql_query('UPDATE circuits SET img_data="'.mysql_real_escape_string(json_encode($circuitImg)).'" WHERE id="'.$id.'"');
		require_once('../includes/cache_creations.php');
		@unlink(cachePath("racepreview$id.png"));
	}
	echo 1;
	mysql_close();
}
?>