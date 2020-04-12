<?php
header ("Content-type: image/png");

$image = imagecreate(600,600);
$map = (isset($_GET["map"])) ? $_GET["map"] : 1;
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

for ($i=0;$i<36;$i++) {
	if (isset($_GET['p'.$i])) {
		$piececircuit = imagecreatefrompng('images/pieces/piececircuit'.$map.'_'.$_GET['p'.$i].'.png');

		imagecopymerge($image, $piececircuit, ($i%6)*100, floor($i/6)*100, 0, 0, 100, 100, 100);
	}
}
$snes = ($map < 13);
$elements = Array('a', 'b', 'c', 'd', 'e', 'f', 'g', 'h', 'i', 'j');
include('escape_all.php');
for ($i=0;$i<10;$i++) {
	$e = $elements[$i];
	if (($i < 4) && !$snes) {
		$acc = 'pqrs';
		$f = $acc[$i];
	}
	else
		$f = $e;
	$piececircuit = imagecreatefrompng("images/pieces/piececircuit_$f.png");
	$w = imagesx($piececircuit);
	$h = imagesy($piececircuit);
	for ($j=0; isset($_GET[$e.$j]); $j++) {
		$getCoords = $_GET[$e.$j];
		imagecopymerge($image, $piececircuit, preg_replace("#^(\d+),\d+#", "$1", $getCoords), preg_replace("#\d+,(\d+)$#", "$1", $getCoords), 0, 0, $w,$h, 100);
	}
}

imagepng($image);
?>