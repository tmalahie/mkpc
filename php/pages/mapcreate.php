<?php
header ("Content-type: image/png");
require_once('circuitEnumsQuick.php');

$map = (isset($_GET["map"])) ? $_GET["map"] : 1;
if (in_array($map, array(48)))
	$image = imagecreatetruecolor(600,600);
else
	$image = imagecreate(600,600);
if (!isset($bgColors[$map]))
	$map = 1;
$bg = $bgColors[$map];
imagecolorallocate($image, $bg[0], $bg[1], $bg[2]);

$pieceprefix = 'piececircuit'.$map.'_';
if (in_array($map, array(49,50)))
	$pieceprefix .= '_';
for ($i=0;$i<36;$i++) {
	if (isset($_GET['p'.$i])) {
		if ($piececircuit = @imagecreatefrompng('images/pieces/'.$pieceprefix.intval($_GET['p'.$i]).'.png'))
			imagecopy($image, $piececircuit, ($i%6)*100, floor($i/6)*100, 0, 0, 100, 100);
	}
}
$snes = ($map < 13);
$gba = ($map < 31) || ($map >= 52);
$elements = Array('a', 'b', 'c', 'd', 'e', 'f', 'g', 'h', 'i', 'j');
include('escape_all.php');
for ($i=0;$i<10;$i++) {
	$e = $elements[$i];
	if (($i < 4) && !$snes) {
		$acc = $gba ? 'pqrs':'uvwx';
		$f = $acc[$i];
	}
	else
		$f = $e;
	$piececircuit = imagecreatefrompng("images/pieces/piececircuit_$f.png");
	$w = imagesx($piececircuit);
	$h = imagesy($piececircuit);
	for ($j=0; isset($_GET[$e.$j]); $j++) {
		$getCoords = $_GET[$e.$j];
		imagecopy($image, $piececircuit, preg_replace("#^(\d+),\d+#", "$1", $getCoords), preg_replace("#\d+,(\d+)$#", "$1", $getCoords), 0, 0, $w,$h);
	}
}

imagepng($image);
?>