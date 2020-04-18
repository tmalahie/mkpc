<?php
if (isset($_GET['id'])) {
	include('initdb.php');
	$id = $_GET['id'];
	if ($getMain = mysql_fetch_array(mysql_query('SELECT * FROM `arenes` WHERE id="'. $id .'"'))) {
		$src = 'course';
		include('getExt.php');
		$ext2 = str_replace('jpg', 'jpeg', $ext);
		header('Content-type: image/'.$ext2);
		
		eval('$image = imagecreatefrom'.$ext2.'(\'images/uploads/course\'.$id.\'.\'.$ext);');

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
		
		$hps = mysql_query('SELECT * FROM `herbe` WHERE id="'.$id.'" ORDER BY prim');
		while ($hp = mysql_fetch_array($hps)) {
			imagefilledrectangle($image, $hp['posX'],$hp['posY'],$hp['posW'],$hp['posH'],$vert);
			imagerectangle($image, $hp['posX'],$hp['posY'],$hp['posW'],$hp['posH'],$noir2);
		}
		
		$murs = mysql_query('SELECT * FROM `hitbox` WHERE id="'.$id.'" ORDER BY prim');
		while ($mur = mysql_fetch_array($murs)) {
			imagefilledrectangle($image, $mur['posX'],$mur['posY'],$mur['posW'],$mur['posH'],$bleu);
			imagerectangle($image, $mur['posX'],$mur['posY'],$mur['posW'],$mur['posH'],$noir2);
		}
		
		$trous = mysql_query('SELECT * FROM `vide` WHERE id="'.$id.'" ORDER BY prim');
		while ($trou = mysql_fetch_array($trous)) {
			imagefilledrectangle($image, $trou['posX'],$trou['posY'],$trou['posW'],$trou['posH'],$orange);
			imagerectangle($image, $trou['posX'],$trou['posY'],$trou['posW'],$trou['posH'],$noir2);
			imagefilledrectangle($image, $trou['posI']-2,$trou['posJ']-2,$trou['posI']+2,$trou['posJ']+2,$rose);
		}
		
		$sauts = mysql_query('SELECT * FROM `tremplins` WHERE id="'.$id.'" ORDER BY prim');
		while ($saut = mysql_fetch_array($sauts)) {
			imagerectangle($image, $saut['posX'],$saut['posY'],$saut['posW'],$saut['posH'],$violet);
		}
		
		$accelerateurs = mysql_query('SELECT * FROM `boosts` WHERE id="'.$id.'" ORDER BY prim');
		while ($accelerateur = mysql_fetch_array($accelerateurs)) {
			imagefilledrectangle($image, $accelerateur['posX']+2,$accelerateur['posY']+2,$accelerateur['posX']+5,$accelerateur['posY']+5,$rouge);
		}
		
		for ($i=0;$i<8;$i++) {
			imagefilledrectangle($image, $getMain['pos'.$i.'X']-5, $getMain['pos'.$i.'Y']-2, $getMain['pos'.$i.'X']+5, $getMain['pos'.$i.'Y']+2, $marron);
			imagefilledrectangle($image, $getMain['pos'.$i.'X']-2, $getMain['pos'.$i.'Y']-5, $getMain['pos'.$i.'X']+2, $getMain['pos'.$i.'Y']+5, $marron);
		}
		
		$aiPoints = mysql_query('SELECT * FROM `reseau` WHERE id="'.$id.'" ORDER BY prim');
		$points = array();
		$inc = 0;
		while ($aiPoint = mysql_fetch_array($aiPoints)) {
			if (!$aiPoint['type']) {
				imagefilledrectangle($image, $aiPoint['posX']-3,$aiPoint['posY']-3,$aiPoint['posX']+3,$aiPoint['posY']+3,$rouge);
				$points[$inc] = array($aiPoint['posX'],$aiPoint['posY']);
			}
			$inc++;
		}
		mysql_data_seek($aiPoints,0);
		while ($aiPoint = mysql_fetch_array($aiPoints)) {
			if ($aiPoint['type']) {
				ImageLine($image, $points[$aiPoint['posX']][0]+1,$points[$aiPoint['posX']][1],$points[$aiPoint['posY']][0],$points[$aiPoint['posY']][1], $blanc);
				ImageLine($image, $points[$aiPoint['posX']][0],$points[$aiPoint['posX']][1]+1,$points[$aiPoint['posY']][0],$points[$aiPoint['posY']][1], $blanc);
				ImageLine($image, $points[$aiPoint['posX']][0],$points[$aiPoint['posX']][1],$points[$aiPoint['posY']][0],$points[$aiPoint['posY']][1], $rouge);
			}
		}

		$objets = mysql_query('SELECT posX,posY FROM `bonus` WHERE ID="'.$id.'"');
		$objetImg = imagecreatefrompng('images/pieces/piececircuit_o.png');
		$w = imagesx($objetImg);
		$h = imagesy($objetImg);
		while ($objet = mysql_fetch_array($objets))
			imagecopy($image, $objetImg, $objet['posX'],$objet['posY'], 0,0, $w,$h);
		
		$decors = mysql_query('SELECT posX,posY FROM `tuyaux` WHERE ID="'.$id.'"');
		$decorImg = imagecreatefrompng('images/pieces/piececircuit_t1.png');
		$w = imagesx($decorImg);
		$h = imagesy($decorImg);
		while ($decor = mysql_fetch_array($decors))
			imagecopy($image, $decorImg, $decor['posX'],$decor['posY'], 0, 0, $w,$h);

		eval('image'.$ext2.'($image);');
	}
	mysql_close();
}
?>