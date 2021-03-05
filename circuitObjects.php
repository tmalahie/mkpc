<?php
function objet($infos,$l,$m,$n=null,$d=null) {
	global $snes,$gba,$decorTypes;
	if (($n == null) || $snes)
		$n = $l;
	if (($d != null) && !$snes && !$gba)
		$n = $d;
	$url = 'images/pieces/piececircuit_'.$n.'.png';
	$className = '';
	$styles = '';
	$attrs = ' ';
	$prefix = $l;
	if ('t' === $l) {
		$className = 'decor';
		if (isset($decorTypes[$m[0]][$m[1]]))
			$decorType = $decorTypes[$m[0]][$m[1]];
		else {
			$decorType = $decorTypes[$m[0]][0];
			$styles .= 'display:none;';
		}
		$url = 'images/map_icons/'.$decorType.'.png';
		if (preg_match('#^assets/#', $decorType))
			$className .= ' decor-asset';
		if ($m[1]) {
			$attrs .= 'data-n="'.$m[1].'" ';
			$prefix .= $m[1].'_';
		}
	}
	if ($className)
		$attrs .= 'class="'.$className.'" ';
	$retour = '<span id="'.$prefix.'">';
	for ($i=0;isset($infos[$prefix.$i]);$i++) {
		$getCoords = $infos[$prefix.$i];
		$retour .= '<img src="'.$url.'" alt="'.$l.'" data-t="'.$l.'" id="'.$prefix.$i.'"'.$attrs.'style="'.$styles.'position: absolute; left: '.preg_replace("#^(\d+),\d+#", "$1", $getCoords).'px; top: '.preg_replace("#\d+,(\d+)$#", "$1", $getCoords).'px; cursor: pointer;" onload="centerPos(this)" onclick="deplacer(event, this, false)" />';
	}
	return $retour.'<img src="'.$url.'" alt="'.$l.'" data-t="'.$l.'" id="'.$prefix.$i.'"'.$attrs.'style="'.$styles.'cursor: pointer;" onclick="deplacer(event,this,true);ajouter(this.dataset,parseInt(this.id.match(/\d+$/g))+1)" /></span>';
}
echo objet($infos,'o',null).' &nbsp; '.objet($infos,'a',null,'p','u').' '.objet($infos,'b',null,'q','v').' '.objet($infos,'c',null,'r','w').' '.objet($infos,'d',null,'s','x').' &nbsp; ';
$maxDecors = 0;
foreach ($decorTypes as $i=>$decorType) {
	if ($decorTypes[$i])
		$maxDecors = max($maxDecors,count($decorTypes[$i]));
}
for ($i=0;$i<$maxDecors;$i++)
	echo ' '.objet($infos,"t",array($map,$i));
echo '<br />
'.objet($infos,'e',null).' '.objet($infos,'f',null).' &nbsp; '.objet($infos,'g',null).' '.objet($infos,'h',null).' &nbsp; '.objet($infos,'i',null).' '.objet($infos,'j',null);
?>