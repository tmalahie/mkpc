<?php
include('escape_all.php');
if (!isset($id) && isset($_GET['id']))
	$id = $_GET['id'];
if (isset($id)) {
	$isTemp = isset($temp);
	if (!$isTemp)
		include('initdb.php');
	if ($getMain = mysql_fetch_array(mysql_query('SELECT map FROM `mkcircuits` WHERE id="'. $id .'"'))) {
		require_once('circuitEnumsQuick.php');
		if (!$isTemp)
			header('Content-type: image/png');
		
		$map = $getMain['map'];
		$snes = ($map < 13);
		$gba = ($map < 31);

		if (in_array($map, array(48)))
			$image = imagecreatetruecolor(600,600);
		else
			$image = imagecreate(600,600);
		if (isset($bgColors[$map])) {
			$bg = $bgColors[$map];
			imagecolorallocate($image, $bg[0], $bg[1], $bg[2]);

			$pieces = mysql_query('SELECT * FROM `mkp` WHERE circuit="'.$id.'"');
			while ($piece = mysql_fetch_array($pieces)) {
				$i = 
				$piececircuit = imagecreatefrompng('images/pieces/piececircuit'.$map.'_'.$piece['piece'].'.png');
				imagecopy($image, $piececircuit, ($piece['id']%6)*100, floor($piece['id']/6)*100, 0, 0, 100, 100);
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
			$piececircuit = imagecreatefrompng('images/pieces/piececircuit_'.$f.'.png');
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
			while ($getPiece = mysql_fetch_array($getPieces))
				imagecopy($image, $piececircuit, $getPiece['x']-$cw,$getPiece['y']-$ch, 0, 0, $w,$h);
		}
		foreach ($decorTypes[$map] as $i=>$decorType) {
			$piececircuit = imagecreatefrompng('images/map_icons/'.$decorType.'.png');
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
				imagecopyresampled($image, $piececircuit, $getPiece['x']-$cw,$getPiece['y']-$ch, 0, 0, $rw,$rh, $w,$h);
		}

		$ext2 = 'png';
		include('saveImage.php');
	}
	if (!$isTemp)
		mysql_close();
}
?>