<?php
$infos = Array();
require_once('circuitPrefix.php');
if (isset($_GET['id'])) {
	include('initdb.php');
	$id = $_GET['id'];
	if ($getMain = mysql_fetch_array(mysql_query('SELECT map,laps,nom,auteur,identifiant,identifiant2,identifiant3,identifiant4 FROM `mkcircuits` WHERE id="'. $id .'" AND !type'))) {
		include('getId.php');
		if ((($identifiants[0]==$getMain['identifiant'])&&($identifiants[1]==$getMain['identifiant2'])&&($identifiants[3]==$getMain['identifiant3'])&&($identifiants[3]==$getMain['identifiant4'])) || ($identifiants[0] == 1390635815)) {
			$map = $getMain['map'];
			$laps = $getMain['laps'];
			$cName = $getMain['nom'];
			$cPseudo = $getMain['auteur'];
			$pieces = mysql_query('SELECT * FROM `mkp` WHERE circuit="'.$id.'"');
			while ($piece = mysql_fetch_array($pieces))
				${'p'.$piece['id']} = $piece['piece'];
			for ($i=0;$i<$nbLettres;$i++) {
				$lettre = $lettres[$i];
				$getInfos = mysql_query('SELECT * FROM `mk'.$lettre.'` WHERE circuit="'.$id.'"');
				$incs = array();
				while ($info=mysql_fetch_array($getInfos)) {
					$prefix = getLetterPrefixD($lettre,$info);
					if (!isset($incs[$prefix])) $incs[$prefix] = 0;
					$infos[$prefix.$incs[$prefix]] = $info['x'].','.$info['y'];
					$incs[$prefix]++;
				}
			}
		}
	}
	mysql_close();
}
else {
	include('escape_all.php');
	if (isset($_GET['nid']))
		$id = $_GET['nid'];
	$pieces = Array(5,9,9,9,9,4,8,11,11,11,11,8,8,11,11,11,11,8,8,11,11,11,11,2,8,11,11,11,11,8,6,9,9,9,9,7);
	for ($i=0;$i<36;$i++)
		${"p$i"} = (isset($_GET["p$i"])) ? $_GET["p$i"] : $pieces[$i];
	unset($pieces);
	$map = (isset($_GET["map"])) ? $_GET["map"] : 1;
	$laps = (isset($_GET["nl"])) ? $_GET["nl"] : 3;
	for ($i=0;$i<$nbLettres;$i++) {
		$lettre = $lettres[$i];
		$prefixes = getLetterPrefixes($lettre,$map);
		for ($k=0;$k<$prefixes;$k++) {
			$prefix = getLetterPrefix($lettre,$k);
			for ($j=0;isset($_GET[$prefix.$j]);$j++)
				$infos[$prefix.$j] = $_GET[$prefix.$j];
		}
	}
}
$snes = ($map <= 13);
$gba = (($map > 8) && ($map <= 30)) || ($map >= 52);
include('language.php');
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="<?php echo $language ? 'en':'fr'; ?>" >
<head>
<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1" />
<link rel="shortcut icon" type="image/x-icon" href="images/favicon.ico" />

<?php
include('o_online.php');
?>
<title><?php echo $language ? 'Create a circuit Mario Kart':'Cr&eacute;er un circuit Mario Kart'; ?></title>
<link rel="stylesheet" type="text/css" href="styles/create.css" />
<style type="text/css">
#croisement {
	background-image: url('mapcreate.php?p0=11&p1=5&p2=4&p6=5&p7=10&p8=7&map=<?php echo $map ?>');
}
</style>
<script type="text/javascript">
var decorTypes = <?php echo json_encode($decorTypes); ?>;
</script>
<script type="text/javascript" src="scripts/create.js?reload=2"></script>
</head>
<body>
<div id="circuit">
<?php
for ($i=0;$i<36;$i++)
	echo '<img src="images/pieces/piececircuit'.$map.'_'. ${"p$i"} .'.png" alt="Piece Mario Kart" style="left: '. ($i%6)*100 .'px; top: '. floor($i/6)*100 .'px;" onmouseover="survol('.$i.')" onclick="change('.$i.')" ondblclick="c=11;appliquer()" />';
?>
</div>
<p id="pPieces" class="editor-section">
<?php
include('circuitObjects.php');
?>
<span id="deleteAllCtn">
	<input type="button" value="<?php echo $language ? 'Delete all':'Tout supprimer'; ?>" id="deleteAll" onclick="deleteAll('<?php echo $language ? 'Delete all pieces of this circuit ?':'Supprimer toutes les pi\xE8ces de ce circuit ?'; ?>')" />
</span>
</p>
<form method="get" action="circuit.php">
<div class="editor-section adv-opt">
Type : <select name="map" onchange="changeMap(this.value);this.blur()">
<?php
$circuitGroups = $language ? Array(
	'SNES' => Array(
		1 => 'Mario Circuit',
		2 => 'Donut Plains',
		3 => 'Koopa Beach',
		4 => 'Choco Island',
		5 => 'Vanilla Lake',
		6 => 'Ghost Valley',
		7 => 'Bowser\'s Castle',
		8 => 'Rainbow Road'
	), 
	'GBA' => Array(
		14 => 'Mario Circuit',
		52 => 'Shy Guy Beach',
		15 => 'Lakeside Park',
		16 => 'Cheep-Cheep Island',
		17 => 'Cheese Land',
		18 => 'Sky Garden',
		19 => 'Snow Land',
		20 => 'Sunset Wilds',
		21 => 'Boo Lake',
		22 => 'Ribbon Road',
		23 => 'Yoshi Desert',
		24 => 'Bowser Castle',
		25 => 'Rainbow Road'
	), 
	'DS' => Array(
		31 => 'Figure 8 Circuit',
		32 => 'Yoshi Falls',
		33 => 'Cheep-Cheep Beach',
		34 => 'Luigi\'s Mansion',
		35 => 'Desert Hills',
		36 => 'Delfino Square',
		37 => 'Waluigi Pinball',
		38 => 'Shroom Ridge',
		39 => 'DK Pass',
		40 => 'Tick-Tock Clock',
		41 => 'Mario Circuit',
		42 => 'Airship Fortress',
		43 => 'Wario Stadium',
		44 => 'Peach Gardens',
		45 => 'Bowser\'s Castle',
		46 => 'Rainbow Road'
	)
) : Array(
	'SNES' => Array(
		1 => 'Circuit Mario',
		2 => 'Plaine Donut',
		3 => 'Plage Koopa',
		4 => 'Île Choco',
		5 => 'Lac Vanille',
		6 => 'Vallée Fantôme',
		7 => 'Château de Bowser',
		8 => 'Route Arc-en-Ciel'
	),
	'GBA' => Array(
		14 => 'Circuit Mario',
		52 => 'Plage Maskass',
		15 => 'Bord du Lac',
		16 => 'Île Cheep-Cheep',
		17 => 'Pays Fromage',
		18 => 'Jardin Volant',
		19 => 'Royaume Sorbet',
		20 => 'Pays Crépuscule',
		21 => 'Lac Boo',
		22 => 'Route Ruban',
		23 => 'Désert Yoshi',
		24 => 'Château de Bowser',
		25 => 'Route Arc-en-Ciel'
	),
	'DS' => Array(
		31 => 'Circuit en 8',
		32 => 'Cascade Yoshi',
		33 => 'Plage Cheep-Cheep',
		34 => 'Manoir de Luigi',
		35 => 'Désert du Soleil',
		36 => 'Quartier Delfino',
		37 => 'Flipper Waluigi',
		38 => 'Corniche Champignon',
		39 => 'Alpes DK',
		40 => 'Horloge Tic-Tac',
		41 => 'Circuit Mario',
		42 => 'Bateau Volant',
		43 => 'Stade Wario',
		44 => 'Jardin Peach',
		45 => 'Château de Bowser',
		46 => 'Route Arc-en-Ciel'
	)
);

foreach ($circuitGroups as $platform => $circuitGroup) {
	echo '<optgroup label="'.$platform.'">';
	foreach ($circuitGroup as $circuitId => $name)
		echo '<option value="'.$circuitId.'"'.($circuitId==$map?' selected="selected"':'').'>'.$name.'</option>';
	echo '</optgroup>';
}
?>
</select>
</div>
<div class="editor-section adv-opt">
<?php
if (isset($_GET['cl']))
	echo '<input type="hidden" name="cl" value="'. htmlspecialchars($_GET['cl']) .'" />';
echo ($language ? 'Nb laps:':'Nb tours :'); ?> <select name="nl"><?php
for ($i=1;$i<10;$i++)
	echo '<option value="'.$i.'" '. ($laps!=$i ? null : ' selected="selected"') .'>'.$i.'</option>';
?></select>
</div>
<p id="pieces">
<?php
if (isset($id))
	echo '<input type="hidden" name="nid" value="'.$id.'" />';
for ($i=0;$i<36;$i++)
echo '<input type="hidden" name="p'.$i.'" value="'.${"p$i"}.'" />';
foreach ($lettres as $l) {
	$prefixes = getLetterPrefixes($l,$map);
	for ($k=0;$k<$prefixes;$k++) {
		$prefix = getLetterPrefix($l,$k);
		for ($i=0;isset($infos[$prefix.$i]);$i++)
			echo '<input type="hidden" name="'.$prefix.$i.'" value="'.$infos[$prefix.$i].'" />';
	}
}
?>
</p>
<p id="valider"><input type="submit" value="&nbsp; <?php echo $language ? 'Create circuit':'Cr&eacute;er circuit' ?> &nbsp;" />
<?php
if ($language) {
	?>
	<p>&nbsp;</p>
	<?php
}
else {
	?>
	<p id="advice">
		Lien utile pour d&eacute;marrer : <a href="topic.php?topic=739" target="_blank">Conseils pour cr&eacute;er un circuit/ar&egrave;ne</a>	
	</p>
	<?php
}
?>
<div id="crossing-warning">
	<?php echo $language ? 'Warning ! For a crossing, you have to go straight, like on this image':'Attention ! Lors d\'un croisement, vous devez aller tout droit, comme sur cette image' ?> :</caption>
	<p id="croisement">
		<img src="images/mktoutdroit.png" alt="Croisement" />
	</p>
</div>
</form>
<p id="choose" style="left: 650px; top: 150px;">
<span id="barre" onmousedown="pos(event)" onmouseup="document.body.onmousemove=undefined"><a href="javascript:fermer()" title="<?php echo $language ? 'Close (Escape)':'Fermer (&Eacute;chap)' ?>"><?php echo $language ? 'Close':'Fermer' ?></a></span>
<?php
for($i=0;$i<12;$i++)
	echo '<img class="cPiece" src="images/pieces/piececircuit'.$map.'_'.$i.'.png" alt="Piece '.$i.'" onmouseover="apercu('.$i.')" onmouseout="disappear()" onclick="appliquer('.$i.')" />'.(($i+1)%4 ? ' ' : '<br />');
?>
</p>
<div class="editor-navigation">
	<a href="arene.php"><?php echo $language ? 'Create a battle course':'Cr&eacute;er une ar&egrave;ne bataille'; ?></a><br />
	<a href="index.php"><?php echo $language ? 'Back to Mario Kart PC':'Retour &agrave; Mario Kart PC'; ?></a>
</div>
</body>
</html>