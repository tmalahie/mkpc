<?php
include('../includes/escape_all.php');
if (!isset($id) && isset($_GET['id']))
	$id = intval($_GET['id']);
if (isset($id)) {
	$isTemp = isset($temp);
	if (!$isTemp)
		include('../includes/initdb.php');
	$getTracks = mysql_query('SELECT circuit0,circuit1,circuit2,circuit3,c.mode FROM mkmcups_tracks t INNER JOIN mkcups c ON t.cup=c.id WHERE t.mcup="'.$id.'" ORDER BY t.ordering');
	$trackIDs = array();
	while ($getTrack = mysql_fetch_array($getTracks)) {
		$mode = $getTrack['mode'];
		for ($i=0;$i<4;$i++)
			$trackIDs[] = $getTrack['circuit'.$i];
	}
	$nbTracks = count($trackIDs);
	if ($nbTracks) {
		function getTracksTable($mode) {
			include('../includes/creation-entities.php');
			return $CREATION_ENTITIES[$mode]['table'];
		}
		$table = getTracksTable($mode);
		if ($mode === 2) $mode = 0;
		elseif ($mode === 3) $mode = 2;
		$getTrackThumbnails = mysql_query('SELECT circuit,thumbnail FROM `mktracksettings` WHERE type="'.$table.'" AND circuit IN ('. implode(',',$trackIDs) .') AND thumbnail IS NOT NULL');
		$trackThumbnails = array();
		while ($getTrackThumbnail = mysql_fetch_array($getTrackThumbnails))
			$trackThumbnails[$getTrackThumbnail['circuit']] = $getTrackThumbnail['thumbnail'];

		$tracksSide = floor(sqrt($nbTracks+1));
		$tracksSide = min($tracksSide,10);
		$nbTracksInSpace = $tracksSide*$tracksSide;
		$nbTracksToDraw = min($nbTracksInSpace,$nbTracks);
		$nbTracksTotal = max($nbTracksInSpace,$nbTracks);
		$resW = 120;
		$imgW = 120/$tracksSide;
		$imgcW = ceil($imgW);
		function imagecropcenter(&$img, $cropWidth,$cropHeight) {
			$width  = imagesx($img);
			$height = imagesy($img);
			$res = imagecreatetruecolor($cropWidth,$cropHeight);
			imagecopyresampled($res,$img,0,0,0,0,$cropWidth,$cropHeight,$width,$height);
			imagedestroy($img);
			$img = $res;
			return $res;
		}
		if (!$isTemp)
			header('Content-type: image/png');
		$image = imagecreatetruecolor($resW,$resW);
		imagesavealpha($image, true);
		$transparent = imagecolorallocatealpha($image, 0,0,0, 127);
		imagefill($image, 0,0, $transparent);
		imagefilledrectangle($image,0,0,$resW,$resW, $transparent);
		for ($i=0;$i<$nbTracksToDraw;$i++) {
			$inc = round($i*$nbTracksTotal/$nbTracksInSpace);
			$trackID = $trackIDs[$inc];
			$trackPos = round($i*$nbTracksTotal/$nbTracks);
			$x = $trackPos%$tracksSide;
			$y = floor($trackPos/$tracksSide);

			if (isset($trackThumbnails[$trackID])) {
				require_once('../includes/cache_creations.php');
				$trackPath = cachePath('uploads/'. $trackThumbnails[$trackID]);
			}
			else {
				require_once('../includes/generateTrackIcon.php');
				$trackPath = generateTrackIcon($trackID, $mode);
			}
			$img = @imagecreatefrompng($trackPath);
			if ($img) {
				$img = imagecropcenter($img, $imgcW,$imgcW);
				imagecopy($image,$img,floor($x*$imgW),floor($y*$imgW),0,0,$imgcW,$imgcW);
			}
		}
		$ext2 = 'png';
		include('../includes/saveImage.php');
	}
	if (!$isTemp)
		mysql_close();
}
?>