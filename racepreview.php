<?php
if (!isset($id) && isset($_GET['id'])) {
	include('escape_all.php');
	$id = intval($_GET['id']);
}
if (isset($id)) {
	$isTemp = isset($temp);
	if (!$isTemp)
		include('initdb.php');
	if ($getMain = mysql_fetch_array(mysql_query('SELECT id,img_data FROM `circuits` WHERE id="'. $id .'"'))) {
		require_once('circuitImgUtils.php');
		$circuitImg = json_decode($getMain['img_data']);
		$circuitFile = getCircuitLocalFile($circuitImg);
		$path = $circuitFile['path'];
		$ext = $circuitImg->ext;
		list($w,$h) = @getimagesize($path);
		if (!$w && !$h || ($w*$h >= 20000000))
			$path = CIRCUIT_BASE_PATH.'overload.'. $ext;
		if (!$isTemp) {
			$ext2 = str_replace('jpg', 'jpeg', $ext);
			header('Content-type: image/'.$ext2);
			
			eval('$image = imagecreatefrom'.$ext2.'($path);');

			if ($getCircuitData = mysql_fetch_array(mysql_query('SELECT data FROM `circuits_data` WHERE id="'. $id .'"'))) {
				$circuitData = json_decode(gzuncompress($getCircuitData['data']));
				$mainData = $circuitData->main;
				$objets = $circuitData->arme;
				$objetImg = imagecreatefrompng('images/pieces/piececircuit_o.png');
				$w = imagesx($objetImg);
				$h = imagesy($objetImg);
				foreach ($objets as $objet)
					imagecopy($image, $objetImg, $objet[0],$objet[1], 0,0, $w,$h);
				
				$decors = $circuitData->decor;
				$decorParams = isset($circuitData->decorparams) ? $circuitData->decorparams:new \stdClass();
				$decorExtra = isset($decorParams->extra) ? $decorParams->extra:new \stdClass();
				$assetTypes = array('pointers', 'flippers', 'bumpers','oils');
				if (!empty($circuitData->assets)) {
					$decors = new \stdClass();
					foreach ($assetTypes as $assetType) {
						if (!empty($circuitData->assets->{$assetType})) {
							$assetsList = $circuitData->assets->{$assetType};
							foreach ($assetsList as $asset) {
								$assetData = $asset[1];
								$assetData[4] = $asset[2];
								$decors->{'assets/'.$asset[0]}[] = $assetData;
							}
						}
					}
					foreach ($circuitData->decor as $key => $decor)
						$decors->{$key} = $decor;
				}
				require_once('utils-decors.php');
				foreach ($decors as $type=>$decorsData) {
					if (isset($decorExtra->{$type}) && isset($decorExtra->{$type}->custom)) {
						$customDecor = $decorExtra->{$type}->custom;
						$decorId = intval($customDecor->id);
						$actualType = $customDecor->type;
						if ($customData = mysql_fetch_array(mysql_query('SELECT sprites FROM mkdecors WHERE id='. $decorId))) {
							$decorSrcs = decor_sprite_srcs($customData['sprites']);
							$decorImg = imagecreatefrompng($decorSrcs['map']);
						}
					}
					else
						$actualType = $type;
					if (!isset($decorImg))
						$decorImg = imagecreatefrompng('images/map_icons/'.$actualType.'.png');
					if ($decorImg) {
						$w = imagesx($decorImg);
						$h = imagesy($decorImg);
						if ($w) {
							$r = 12/$w;
							$rW = round($r*$w);
							$rH = round($r*$h);
							$rX = round($rW/2);
							$rY = round($rH/2);
							foreach ($decorsData as $decorData) {
								if ('assets/' === substr($actualType, 0,7)) {
									if (isset($decorData[2]) && isset($decorData[3])) {
										$rW = round($decorData[2]);
										$rH = round($decorData[3]);
										if (isset($decorData[4])) {
											$rX = round($rW*$decorData[4][0]);
											$rY = round($rH*$decorData[4][1]);
										}
									}
								}
								imagecopyresampled($image, $decorImg, $decorData[0]-$rX,$decorData[1]-$rY, 0,0, $rW,$rH, $w,$h);
							}
						}
						unset($decorImg);
					}
				}
			}
		}
		
		include('saveImage.php');
	}
	if (!$isTemp)
		mysql_close();
}
?>