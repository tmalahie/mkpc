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
		$circuitImg = json_decode($circuit['img_data']);
		if (isset($data->imgOverrides) && isset($circuitImg->lapOverrides)) {
			$lapOverrides = $circuitImg->lapOverrides;
			$newLapOverrides = new stdClass();
			$newImgs = array();
			foreach ($data->imgOverrides as $lapId => $imgOverride) {
				$newLapOverride = null;
				foreach ($lapOverrides as $lapOverride) {
					if ($lapOverride->url === $imgOverride->url && $lapOverride->local === $imgOverride->local) {
						$newLapOverride = $lapOverride;
						break;
					}
				}
				if (!$newLapOverride && isset($lapOverrides->$lapId))
					$newLapOverride = $lapOverrides->$lapId;
				if ($newLapOverride) {
					$newLapOverrides->$lapId = $newLapOverride;
					if ($newLapOverride->local)
						$newImgs[$newLapOverride->url] = true;
				}
			}
			foreach ($lapOverrides as $lapOverride) {
				if ($lapOverride->local && !isset($newImgs[$lapOverride->url])) {
					require_once('../includes/circuitImgUtils.php');
					$path = CIRCUIT_BASE_PATH.$lapOverride->url;
					@unlink($path);
				}
			}
			$circuitImg->lapOverrides = $newLapOverrides;
			mysql_query('UPDATE circuits SET img_data="'.mysql_real_escape_string(json_encode($circuitImg)).'" WHERE id="'.$id.'"');
		}
		require_once('../includes/cache_creations.php');
		@unlink(cachePath("racepreview$id.png"));
	}
	echo 1;
	mysql_close();
}
?>