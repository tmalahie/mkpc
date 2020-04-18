<?php
if (isset($_GET['id'])) {
	include('initdb.php');
	$id = $_GET['id'];
	if ($getMain = mysql_fetch_array(mysql_query('SELECT decor,departX,departY FROM `circuits` WHERE id="'. $id .'"'))) {
		include('getExt.php');
		$ext2 = str_replace('jpg', 'jpeg', $ext);
		header('Content-type: image/'.$ext2);
		
		eval('$image = imagecreatefrom'.$ext2.'(\'images/uploads/map\'.$id.\'.\'.$ext);');

		$noir = imagecolorallocate($image, 0, 0, 0);
		$noir2 = imagecolorallocatealpha($image, 0, 0, 0, 76);
		$marron = imagecolorallocate($image, 153, 51, 0);
		$violet = imagecolorallocate($image, 128, 0, 128);
		$bleu = imagecolorallocatealpha($image, 0, 0, 255, 76);
		$vert = imagecolorallocatealpha($image, 0, 255, 0, 76);
		$orange = imagecolorallocatealpha($image, 255, 128, 0, 31);
		$rose = imagecolorallocatealpha($image, 255, 0, 255, 76);
		$rouge = imagecolorallocate($image, 255, 0, 0);
		$jaune = imagecolorallocatealpha($image, 255, 255, 0, 76);
		$blanc = imagecolorallocate($image, 255, 255, 255);
		$blanc2 = imagecolorallocatealpha($image, 255, 255, 255, 76);
		
		$checkPoints = mysql_query('SELECT * FROM `checkpoint` WHERE id="'.$id.'" ORDER BY prim');
		while ($checkPoint = mysql_fetch_array($checkPoints)) {
			if ($checkPoint['dimension']) {
				imagefilledrectangle($image, $checkPoint['posX'],$checkPoint['posY'],$checkPoint['posL'],$checkPoint['posY']+15,$jaune);
				imagerectangle($image, $checkPoint['posX'],$checkPoint['posY'],$checkPoint['posL'],$checkPoint['posY']+15,$noir2);
			}
			else {
				imagefilledrectangle($image, $checkPoint['posX'],$checkPoint['posY'],$checkPoint['posX']+15,$checkPoint['posL'],$jaune);
				imagerectangle($image, $checkPoint['posX'],$checkPoint['posY'],$checkPoint['posX']+15,$checkPoint['posL'],$noir2);
			}
		}
		
		$hps = mysql_query('SELECT * FROM `horspiste` WHERE id="'.$id.'" ORDER BY prim');
		while ($hp = mysql_fetch_array($hps)) {
			imagefilledrectangle($image, $hp['posX'],$hp['posY'],$hp['posW'],$hp['posH'],$vert);
			imagerectangle($image, $hp['posX'],$hp['posY'],$hp['posW'],$hp['posH'],$noir2);
		}
		
		$murs = mysql_query('SELECT * FROM `collision` WHERE id="'.$id.'" ORDER BY prim');
		while ($mur = mysql_fetch_array($murs)) {
			imagefilledrectangle($image, $mur['posX'],$mur['posY'],$mur['posW'],$mur['posH'],$bleu);
			imagerectangle($image, $mur['posX'],$mur['posY'],$mur['posW'],$mur['posH'],$noir2);
		}
		
		$trous = mysql_query('SELECT * FROM `trous` WHERE id="'.$id.'" ORDER BY prim');
		while ($trou = mysql_fetch_array($trous)) {
			imagefilledrectangle($image, $trou['posX'],$trou['posY'],$trou['posW'],$trou['posH'],$orange);
			imagerectangle($image, $trou['posX'],$trou['posY'],$trou['posW'],$trou['posH'],$noir2);
			imagefilledrectangle($image, $trou['posI']-2,$trou['posJ']-2,$trou['posI']+2,$trou['posJ']+2,$rose);
		}
		
		$sauts = mysql_query('SELECT * FROM `sauts` WHERE id="'.$id.'" ORDER BY prim');
		while ($saut = mysql_fetch_array($sauts)) {
			imagerectangle($image, $saut['posX'],$saut['posY'],$saut['posW'],$saut['posH'],$violet);
		}
		
		$accelerateurs = mysql_query('SELECT * FROM `accelerateurs` WHERE id="'.$id.'" ORDER BY prim');
		while ($accelerateur = mysql_fetch_array($accelerateurs)) {
			imagefilledrectangle($image, $accelerateur['posX']+2,$accelerateur['posY']+2,$accelerateur['posX']+5,$accelerateur['posY']+5,$rouge);
		}
		
		imagefilledrectangle($image, $getMain['departX']-15, $getMain['departY']+20, $getMain['departX']+45, $getMain['departY']+40, $marron);
		imagefilledrectangle($image, $getMain['departX']+5, $getMain['departY']-10, $getMain['departX']+25, $getMain['departY']+70, $marron);
		
		$aiPoints = mysql_query('SELECT * FROM `aipoints` WHERE id="'.$id.'" ORDER BY prim');
		while ($aiPoint = mysql_fetch_array($aiPoints)) {
			if (isset($posX)) {
				ImageLine($image, $posX+1,$posY,$aiPoint['posX'],$aiPoint['posY'], $blanc);
				ImageLine($image, $posX,$posY+1,$aiPoint['posX'],$aiPoint['posY'], $blanc);
				ImageLine($image, $posX,$posY,$aiPoint['posX'],$aiPoint['posY'], $rouge);
			}
			else {
				$firstX = $aiPoint['posX'];
				$firstY = $aiPoint['posY'];
			}
			$posX = $aiPoint['posX'];
			$posY = $aiPoint['posY'];
		}
		if (isset($firstX)) {
			ImageLine($image, $posX+1,$posY,$firstX,$firstY, $blanc);
			ImageLine($image, $posX,$posY+1,$firstX,$firstY, $blanc);
			ImageLine($image, $posX,$posY,$firstX,$firstY, $rouge);
		}

		$objets = mysql_query('SELECT posX,posY FROM `arme` WHERE ID="'.$id.'"');
		$objetImg = imagecreatefrompng('images/pieces/piececircuit_o.png');
		$w = imagesx($objetImg);
		$h = imagesy($objetImg);
		while ($objet = mysql_fetch_array($objets))
			imagecopy($image, $objetImg, $objet['posX'],$objet['posY'], 0,0, $w,$h);
		
		$decors = mysql_query('SELECT posX,posY FROM `decor` WHERE ID="'.$id.'"');
		$idDecors = Array(1, 13, 3, 4, 6, 7, 8);
		$decorImg = imagecreatefrompng('images/pieces/piececircuit_t'.$idDecors[$getMain['decor']].'.png');
		$w = imagesx($decorImg);
		$h = imagesy($decorImg);
		while ($decor = mysql_fetch_array($decors))
			imagecopy($image, $decorImg, $decor['posX'],$decor['posY'], 0, 0, $w,$h);
		
		eval('image'.$ext2.'($image);');
	}
	mysql_close();
}
?>