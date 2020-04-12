<?php
include('escape_all.php');
if (!isset($id) && isset($_GET['id']))
	$id = $_GET['id'];
if (isset($id)) {
	$isTemp = isset($temp);
	if (!$isTemp)
		include('initdb.php');
	if ($getMain = mysql_fetch_array(mysql_query('SELECT map FROM `mkcircuits` WHERE id="'. $id .'"'))) {
		if (!$isTemp)
			header('Content-type: image/png');
		
		$map = $getMain['map'];
		$snes = ($map < 13);

		$image = imagecreate(600,600);
		$bg = Array();
		switch ($map) {
			case 1 :
			$bg = Array(0,161,0);
			break;
			case 2 :
			$bg = Array(0,152,0);
			break;
			case 3 :
			$bg = Array(111,130,239);
			break;
			case 4 :
			$bg = Array(95,66,15);
			break;
			case 5 :
			$bg = Array(255,255,255);
			break;
			case 6 :
			$bg = Array(0,0,0);
			break;
			case 7 :
			$bg = Array(185,7,0);
			break;
			case 8 :
			$bg = Array(0,0,0);
			break;
			case 9 :
			$bg = Array(167,148,92);
			break;
			case 10 :
			$bg = Array(207,187,157);
			break;
			case 11 :
			$bg = Array(208,240,248);
			break;
			case 12 :
			$bg = Array(99,97,99);
			break;
			case 14 :
			$bg = Array(8,200,0);
			break;
			case 15 :
			$bg = Array(60,248,248);
			break;
			case 16 :
			$bg = Array(210,192,218);
			break;
			case 17 :
			$bg = Array(242,143,0);
			break;
			case 18 :
			$bg = Array(236,242,245);
			break;
			case 19 :
			$bg = Array(224,248,248);
			break;
			case 20 :
			$bg = Array(171,82,8);
			break;
			case 21 :
			$bg = Array(0,0,0);
			break;
			case 22 :
			$bg = Array(108,169,246);
			break;
			case 23 :
			$bg = Array(248,223,73);
			break;
			case 24 :
			$bg = Array(208,23,8);
			break;
			case 25 :
			$bg = Array(0,0,88);
			break;
			case 26 :
			$bg = Array(32,160,16);
			break;
			case 27 :
			case 28 :
			$bg = Array(216,16,8);
			break;
			case 29 :
			$bg = Array(32,160,16);
			break;
			case 30 :
			$bg = Array(248,224,72);
			break;
			case 31 :
			$bg = Array(6,152,0);
		}
		imagecolorallocate($image, $bg[0], $bg[1], $bg[2]);

		$pieces = mysql_query('SELECT * FROM `mkp` WHERE circuit="'.$id.'"');
		while ($piece = mysql_fetch_array($pieces)) {
			$i = 
			$piececircuit = imagecreatefrompng('images/pieces/piececircuit'.$map.'_'.$piece['piece'].'.png');
			imagecopymerge($image, $piececircuit, ($piece['id']%6)*100, floor($piece['id']/6)*100, 0, 0, 100, 100, 100);
		}
		$elements = Array('a', 'b', 'c', 'd', 'e', 'f', 'g', 'h', 'i', 'j','o');
		$nbElements = count($elements);
		for ($i=0;$i<$nbElements;$i++) {
			$e = $elements[$i];
			if (($i < 4) && !$snes) {
				$acc = 'pqrs';
				$f = $acc[$i];
			}
			else
				$f = $e;
			$getPieces = mysql_query('SELECT x,y FROM `mk'.$e.'` WHERE circuit="'.$id.'"');
			$piececircuit = imagecreatefrompng('images/pieces/piececircuit_'.$f.'.png');
			$w = imagesx($piececircuit);
			$h = imagesy($piececircuit);
			while ($getPiece = mysql_fetch_array($getPieces))
				imagecopymerge($image, $piececircuit, $getPiece['x'],$getPiece['y'], 0, 0, $w,$h, 100);
		}
		$piececircuit = imagecreatefrompng('images/pieces/piececircuit_t'.$map.'.png');
		$w = imagesx($piececircuit);
		$h = imagesy($piececircuit);
		$getPieces = mysql_query('SELECT x,y FROM `mkt` WHERE circuit="'.$id.'"');
		while ($getPiece = mysql_fetch_array($getPieces))
			imagecopymerge($image, $piececircuit, $getPiece['x'],$getPiece['y'], 0, 0, $w,$h, 100);

		$ext2 = 'png';
		include('saveImage.php');
	}
	if (!$isTemp)
		mysql_close();
}
?>