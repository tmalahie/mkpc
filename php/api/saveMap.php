<?php
header('Content-Type: text/plain');
$data = json_decode(file_get_contents('php://input'));
if (is_object($data) && isset($data->id) && isset($data->payload)) {
	$id = $data->id;
	$payload = $data->payload;
	include('../includes/initdb.php');
	include('../includes/getId.php');
	require_once('../includes/collabUtils.php');
	$requireOwner = !hasCollabGrants('circuits', $data->id, $data->collab, 'edit');
	if ($getCircuit = mysql_fetch_array(mysql_query('SELECT img_data FROM circuits WHERE id="'.mysql_real_escape_string($id).'"'. ($requireOwner ? (' AND identifiant='.$identifiants[0].' AND identifiant2='.$identifiants[1].' AND identifiant3='.$identifiants[2].' AND identifiant4='.$identifiants[3]):'')))) {
		mysql_query('INSERT INTO circuits_data VALUES("'.mysql_real_escape_string($id).'","'.mysql_real_escape_string(gzcompress(json_encode($payload))).'") ON DUPLICATE KEY UPDATE data=VALUES(data)');
		if (isset($data->lap_img) && isset($circuitImg->lapOverrides)) {
			$circuitImg = json_decode($circuit['img_data']);
			$lapOverrides = $circuitImg->lapOverrides;
			$newLapOverrides = new stdClass();
			$newImgs = array();
			foreach ($data->lap_img as $lapId => $lapData) {
				$newLapData = current(array_filter($lapOverrides, function($lapOverride) use ($lapData) {
					return $lapOverride->url === $lapData->url && $lapOverride->local === $lapData->local;
				}));
				if ($newLapData !== false) {
					$newLapOverrides->$lapId = $newLapData;
					if ($lapData->local)
						$newImgs[$newLapData->url] = true;
				}
			}
			foreach ($lapOverrides as $lapId => $lapData) {
				if (!isset($newLapOverrides->$lapId) && $lapData->local && !isset($newImgs[$lapData->url])) {
					require_once('../includes/circuitImgUtils.php');
					$path = CIRCUIT_BASE_PATH.$lapData->url;
					@unlink($path);
				}
			}
		}
	}
	echo 1;
	mysql_close();
	require_once('../includes/cache_creations.php');
	@unlink(cachePath("racepreview$id.png"));
}
?>