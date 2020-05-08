<?php
if (!isset($id) && isset($_GET['id'])) {
	include('escape_all.php');
	$id = $_GET['id'];
}
if (isset($id)) {
	$isTemp = isset($temp);
	if (!$isTemp)
		include('initdb.php');
	if ($getMain = mysql_fetch_array(mysql_query('SELECT id,img_data FROM `arenes` WHERE id="'. $id .'"'))) {
		require_once('circuitImgUtils.php');
		$circuitImg = json_decode($getMain['img_data']);
		$circuitFile = getCircuitLocalFile($circuitImg);
		$path = $circuitFile['path'];
		$ext = $circuitImg->ext;
		$ext2 = str_replace('jpg', 'jpeg', $ext);
		if (!$isTemp)
			header('Content-type: image/'.$ext2);
		
		list($w,$h) = getimagesize($path);
		if (!$w && !$h || ($w*$h >= 20000000))
			$path = CIRCUIT_BASE_PATH.'overload.'. $ext;
		eval('$image = imagecreatefrom'.$ext2.'($path);');

		if ($getCircuitData = mysql_fetch_array(mysql_query('SELECT data FROM `arenes_data` WHERE id="'. $id .'"'))) {
			$circuitData = json_decode(gzuncompress($getCircuitData['data']));
			$mainData = $circuitData->main;
			$objets = $circuitData->arme;
			$objetImg = imagecreatefrompng('images/pieces/piececircuit_o.png');
			$w = imagesx($objetImg);
			$h = imagesy($objetImg);
			foreach ($objets as $objet)
				imagecopy($image, $objetImg, $objet[0],$objet[1], 0,0, $w,$h);
			
			$decors = $circuitData->decor;
			foreach ($decors as $type=>$decorsData) {
				$decorImg = imagecreatefrompng('images/map_icons/'.$type.'.png');
				$w = imagesx($decorImg);
				$h = imagesy($decorImg);
				if ($w) {
					$r = 12/$w;
					$rW = round($r*$w);
					$rH = round($r*$h);
					foreach ($decorsData as $decorData)
						imagecopyresampled($image, $decorImg, $decorData[0]-round($rW/2),$decorData[1]-round($rH/2), 0,0, $rW,$rH, $w,$h);
				}
			}
		}

		include('saveImage.php');
	}
	if (!$isTemp)
		mysql_close();
}
?>