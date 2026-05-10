<?php
header('Content-Type: text/plain');
$data = json_decode(file_get_contents('php://input'));
if (is_object($data) && isset($data->id) && isset($data->payload)) {
	$id = $data->id;
	$payload = $data->payload;
	include('../includes/initdb.php');
	include('../includes/getId.php');
	require_once('../includes/collabUtils.php');
	$requireOwner = !hasCollabGrants('arenes', $data->id, $data->collab, 'edit');
	if ($circuit = mysql_fetch_array(mysql_query('SELECT img_data FROM arenes WHERE id="'.mysql_real_escape_string($id).'"'. ($requireOwner ? (' AND identifiant='.$identifiants[0].' AND identifiant2='.$identifiants[1].' AND identifiant3='.$identifiants[2].' AND identifiant4='.$identifiants[3]) : '')))) {
		if (isset($data->expectedVersion) && empty($data->force)) {
			$existingData = mysql_fetch_array(mysql_query('SELECT data FROM arenes_data WHERE id="'.mysql_real_escape_string($id).'"'));
			$currentVersion = '';
			if ($existingData) {
				$existingDecoded = json_decode(gzuncompress($existingData['data']));
				if ($existingDecoded && isset($existingDecoded->version))
					$currentVersion = $existingDecoded->version;
			}
			if ($currentVersion !== $data->expectedVersion) {
				echo 2;
				mysql_close();
				exit;
			}
		}
		$newVersion = bin2hex(random_bytes(8));
		$payload->version = $newVersion;
		mysql_query('INSERT INTO arenes_data VALUES("'.mysql_real_escape_string($id).'","'.mysql_real_escape_string(gzcompress(json_encode($payload))).'") ON DUPLICATE KEY UPDATE data=VALUES(data)');
		require_once('../includes/circuitSaveUtils.php');
		$circuitImg = getNewImgData($data, $circuit);
		if ($circuitImg)
			mysql_query('UPDATE arenes SET img_data="'.mysql_real_escape_string(json_encode($circuitImg)).'" WHERE id="'.$id.'"');
		require_once('../includes/cache_creations.php');
		@unlink(cachePath("coursepreview$id.png"));
		echo '1:' . $newVersion;
		mysql_close();
		exit;
	}
	echo 1;
	mysql_close();
}
?>