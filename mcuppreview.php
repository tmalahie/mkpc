<?php
include('escape_all.php');
if (!isset($id) && isset($_GET['id']))
	$id = $_GET['id'];
if (isset($id)) {
	$isTemp = isset($temp);
	if (!$isTemp)
		include('initdb.php');
	$getTracks = mysql_query('SELECT circuit0,circuit1,circuit2,circuit3,c.mode FROM mkmcups_tracks t INNER JOIN mkcups c ON t.cup=c.id WHERE t.mcup="'.$id.'" ORDER BY t.ordering');
	$trackIDs = array();
	while ($getTrack = mysql_fetch_array($getTracks)) {
		$mode = $getTrack['mode'];
		for ($i=0;$i<4;$i++)
			$trackIDs[] = $getTrack['circuit'.$i];
	}
	$nbTracks = count($trackIDs);
	if ($nbTracks) {
		$tracksSide = floor(sqrt($nbTracks+1));
		$nbTracksInSpace = $tracksSide*$tracksSide;
		$nbTracksToDraw = min($nbTracksInSpace,$nbTracks);
		$nbTracksTotal = max($nbTracksInSpace,$nbTracks);
		if ($mode == 0)
			$baseUrl = 'trackicon.php?type=0&id=';
		else
			$baseUrl = 'trackicon.php?type=1&id=';
		$domain = $_SERVER['HTTP_HOST'];
		if ($_SERVER['SERVER_NAME'] === 'localhost') {
			$domain = $_SERVER['SERVER_NAME'];
			$relDir = substr(__DIR__, strlen($_SERVER['DOCUMENT_ROOT']));
		}
		else
			$relDir = '';
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

			$trackUrl = 'http://'.$domain.$relDir.'/'.$baseUrl.$trackID;
			$img = imagecreatefrompng($trackUrl);
			$img = imagecropcenter($img, $imgcW,$imgcW);
			imagecopy($image,$img,floor($x*$imgW),floor($y*$imgW),0,0,$imgW,$imgW);
		}
		$ext2 = 'png';
		include('saveImage.php');
	}
	if (!$isTemp)
		mysql_close();
}
?>