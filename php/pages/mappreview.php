<?php
include('../includes/escape_all.php');
if (!isset($id) && isset($_GET['id']))
	$id = intval($_GET['id']);
if (isset($id)) {
	$isTemp = isset($temp);
	if (!$isTemp)
		include('../includes/initdb.php');
	if ($getMain = mysql_fetch_array(mysql_query('SELECT map FROM `mkcircuits` WHERE id="'. $id .'"'))) {
		include('../includes/circuitEnumsQuick.php');
		if (!$isTemp)
			header('Content-type: image/png');
		
		$map = $getMain['map'];
		$snes = ($map < 13) || ($map >= 56);
		$gba = ($map < 31) || (($map >= 52) && ($map < 56));

		$wImg = 600;
		$hImg = 600;
		$rImg = 1;
		if ($isTemp && isset($w_ic) && isset($w_ic)) {
			$uFactor = 2;
			$rImg = $w_ic*$uFactor/$wImg;
			$wImg = round($w_ic*$uFactor);
			$hImg = round($h_ic*$uFactor);
		}

		if (($rImg < 1) || in_array($map, array(48)))
			$image = imagecreatetruecolor($wImg,$hImg);
		else
			$image = imagecreate($wImg,$hImg);
		if (isset($bgColors[$map])) {
			$bg = $bgColors[$map];
			imagecolorallocate($image, $bg[0], $bg[1], $bg[2]);

			$pieces = mysql_query('SELECT * FROM `mkp` WHERE circuit="'.$id.'"');
			$pW = round(100*$rImg);
			$pH = round(100*$rImg);
			while ($piece = mysql_fetch_array($pieces)) {
				if ($piececircuit = @imagecreatefrompng('../../images/pieces/piececircuit'.$map.'_'.$piece['piece'].'.png')) {
					if ($rImg === 1)
						imagecopy($image, $piececircuit, ($piece['id']%6)*$pW,floor($piece['id']/6)*$pH, 0,0, $pW,$pH);
					else
						imagecopyresampled($image, $piececircuit, ($piece['id']%6)*$pW,floor($piece['id']/6)*$pH, 0,0, $pW,$pH, 100,100);
				}
			}
		}
		$elements = Array('a', 'b', 'c', 'd', 'e', 'f', 'g', 'h', 'i', 'j','o');
		$nbElements = count($elements);
		for ($i=0;$i<$nbElements;$i++) {
			$e = $elements[$i];
			if (($i < 4) && !$snes) {
				$acc = $gba ? 'pqrs':'uvwx';
				$f = $acc[$i];
			}
			else
				$f = $e;
			$getPieces = mysql_query('SELECT x,y FROM `mk'.$e.'` WHERE circuit="'.$id.'"');
			$piececircuit = imagecreatefrompng('../../images/pieces/piececircuit_'.$f.'.png');
			$w = imagesx($piececircuit);
			$h = imagesy($piececircuit);
			if ('o' === $e) {
				$cw = round($w/2);
				$ch = round($h/2);
			}
			else {
				$cw = 0;
				$ch = 0;
			}
			while ($getPiece = mysql_fetch_array($getPieces)) {
				if ($rImg === 1)
					imagecopy($image, $piececircuit, $getPiece['x']-$cw,$getPiece['y']-$ch, 0,0, $w,$h);
				else
					imagecopyresampled($image, $piececircuit, round(($getPiece['x']-$cw)*$rImg),round(($getPiece['y']-$ch)*$rImg), 0,0, round($w*$rImg),round($h*$rImg), $w,$h);
			}
		}
		foreach ($decorTypes[$map] as $i=>$decorType) {
			$piececircuit = imagecreatefrompng('../../images/map_icons/'.$decorType.'.png');
			$w = imagesx($piececircuit);
			$h = imagesy($piececircuit);
			if ('assets/' !== substr($decorType, 0,7))
				$rw = min($w,13);
			else
				$rw = $w;
			$rh = round($rw*$h/$w);
			$cw = round($rw/2);
			$ch = round($rh/2);
			$getPieces = mysql_query('SELECT x,y FROM `mkt` WHERE circuit="'.$id.'" AND t="'.$i.'"');
			while ($getPiece = mysql_fetch_array($getPieces))
				imagecopyresampled($image, $piececircuit, round(($getPiece['x']-$cw)*$rImg),round(($getPiece['y']-$ch)*$rImg), 0,0, round($rw*$rImg),round($rh*$rImg), $w,$h);
		}

		$ext2 = 'png';
		include('../includes/saveImage.php');
	}
	if (!$isTemp)
		mysql_close();
}
?>