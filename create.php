<?php
$lettres = Array('a', 'b', 'c', 'd', 'e', 'f', 'g', 'h', 'i', 'j', 'o', 't');
$nbLettres = count($lettres);
$infos = Array();
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
				$getInfos = mysql_query('SELECT x,y FROM `mk'.$lettre.'` WHERE circuit="'.$id.'"');
				for ($j=0;$info=mysql_fetch_array($getInfos);$j++)
					$infos[$lettre.$j] = $info['x'].','.$info['y'];
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
		for ($j=0;isset($_GET[$lettre.$j]);$j++)
			$infos[$lettre.$j] = $_GET[$lettre.$j];
	}
}
$snes = ($map <= 8);
$gba = ($map > 8) && ($map <= 25);
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
<style type="text/css">
body {
	margin-left: 650px;
	color: white;
	background-color: #030316;
	background-image: url('../images/editor/fond_stars.jpg');
}
a {
	color: white;
}
#circuit {
	position: absolute;
	left: 0px;
	top: 0px;
	width: 600px;
	height: 600px;
}
div img {
	position: absolute;
	width: 100px;
}
img {
	cursor: pointer;
}
#croisement {
	position: relative;
	text-align: left;
	margin: 10px 0 0 20px;
	width: 300px;
	height: 200px;
	overflow: hidden;
	padding: 0;
	text-align: center;
	background-image: url('mapcreate.php?p1=5&p2=4&p6=5&p7=10&p8=7&map=<?php echo $map ?>');
}
#croisement img {
	position: absolute;
	left: 40px;
	top: 40px;
	width: 215px;
	cursor: default;
}
#valider input {
	font-weight: bold;
	font-size: 20px;
	margin-left: 10px;
	cursor: pointer;
    background-color: #249;
    border-color: #57c;
    color: #abf;
}
#valider input:hover {
	background-color: #26a;
	border-color: #59B;
}
#valider a {
	font-size: 12pt;
	font-weight: bold;
}

#choose {
	display: none;
	position: fixed;
	z-index: 15;
	margin: 0;
	padding: 5px;
	background-color: #36F;
}
#barre {
	display: block;
	text-align: right;
	width: 99%;
	padding: 4px;
	background-color: #036;
	font-weight: bold;
	font-size: 16px;
	cursor: move;
}
#barre:hover {
	background-color: #339;
}
#barre:active {
	background-color: blue;
}
#barre a:hover {
	color: aqua;
}
.cPiece {
	border: solid 2px #038;
}
.editor-section {
	display: inline-block;
	padding: 5px 12px;
	background-color: rgba(76,70,94, 0.5);
	border-radius: 5px;
}
#pPiece img {
	z-index: 10;
}
#deleteAll {
	margin-left: 0;
}
#deleteAllCtn {
	display: block;
	margin-top: 8px;
	margin-bottom: 2px;
	text-align: center;
}
#advice {
	margin-top: 0;
	font-size: 18px;
}
#advice a {
	color: #CCF;
}
.adv-opt {
	display: inline-block;
}
.adv-opt:first-child {
	margin-right: 5px;
}
</style>
<script type="text/javascript" src="scripts/create.js?reload=3"></script>
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
function objet($infos,$l,$m,$n=null,$d=null) {
	global $snes,$gba;
	if (($n == null) || $snes)
		$n = $l;
	if (($d != null) && !$snes && !$gba)
		$n = $d;
	$retour = '<span id="'.$l.'">';
	for ($i=0;isset($infos[$l.$i]);$i++) {
		$getCoords = $infos[$l.$i];
		$retour .= '<img src="images/pieces/piececircuit_'.$n.$m.'.png" alt="'.$l.'" id="'.$l.$i.'" style="position: absolute; left: '.preg_replace("#^(\d+),\d+#", "$1", $getCoords).'px; top: '.preg_replace("#\d+,(\d+)$#", "$1", $getCoords).'px; cursor: pointer;" onload="centerPos(this)" onclick="deplacer(event, this, false)" />';
	}
	return $retour.'<img src="images/pieces/piececircuit_'.$n.$m.'.png" alt="'.$l.'" id="'.$l.$i.'" style="cursor: pointer;" onclick="deplacer(event,this,true);ajouter(this.alt,parseInt(this.id.match(/\d+$/g))+1)" /></span>';
}
echo objet($infos,'o',null).' &nbsp; '.objet($infos,'a',null,'p','u').' '.objet($infos,'b',null,'q','v').' '.objet($infos,'c',null,'r','w').' '.objet($infos,'d',null,'s','x').' &nbsp; '.objet($infos,"t",$map).'<br />
'.objet($infos,'e',null).' '.objet($infos,'f',null).' &nbsp; '.objet($infos,'g',null).' '.objet($infos,'h',null).' &nbsp; '.objet($infos,'i',null).' '.objet($infos,'j',null);
?>
<span id="deleteAllCtn">
	<input type="button" value="<?php echo $language ? 'Delete all':'Tout supprimer'; ?>" id="deleteAll" onclick="deleteAll('<?php echo $language ? 'Delete all pieces of this circuit ?':'Supprimer toutes les pi\xE8ces de ce circuit ?'; ?>')" />
</span>
</p>
<form method="get" action="circuit.php">
<div class="editor-section adv-opt">
Type : <select name="map" onchange="changeMap(this.value);this.blur()">
<optgroup label="SNES">
<?php
$circuits = $language ? Array('Mario Circuit', 'Donut Plains', 'Koopa Beach', 'Choco Island', 'Vanilla Lake', 'Ghost Valley', 'Bowser Castle', 'Rainbow Road', 'Mario Circuit', 'Lakeside Park', 'Cheep-Cheep Island', 'Cheese Land', 'Sky Garden', 'Snow Land', 'Sunset Wilds', 'Boo Lake', 'Ribbon Road', 'Yoshi Desert', 'Bowser Castle', 'Rainbow Road', 'Figure 8 Circuit', 'Yoshi Falls'):Array('Circuit Mario', 'Plaine Donut', 'Plage Koopa', '&Icirc;le Choco', 'Lac Vanille', 'Vall&eacute;e Fant&ocirc;me', 'Ch&acirc;teau de Bowser', 'Route Arc-en-Ciel', 'Circuit Mario', 'Bord du Lac', '&Icirc;le Cheep-Cheep', 'Pays Fromage', 'Jardin Volant', 'Royaume Sorbet', 'Pays Cr&eacute;puscule', 'Lac Boo', 'Route Ruban', 'D&eacute;sert Yoshi', 'Ch&acirc;teau de Bowser', 'Route Arc-en-Ciel', 'Circuit en 8', 'Cascade Yoshi');
for ($i=1;$i<=8;$i++)
	echo '<option value="'.$i.'" '. ($map!=$i ? null : 'selected="selected"') .'>'.$circuits[($i-1)].'</option>';
?>
</optgroup>
<optgroup label="GBA">
<?php
for ($i=14;$i<=25;$i++)
	echo '<option value="'.$i.'" '. ($map!=$i ? null : 'selected="selected"') .'>'.$circuits[($i-6)].'</option>';
?>
</optgroup>
<optgroup label="DS">
<?php
for ($i=31;$i<=32;$i++)
	echo '<option value="'.$i.'" '. ($map!=$i ? null : 'selected="selected"') .'>'.$circuits[($i-11)].'</option>';
?>
</optgroup>
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
	for ($i=0;isset($infos[$l.$i]);$i++)
		echo '<input type="hidden" name="'.$l.$i.'" value="'.$infos[$l.$i].'" />';
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
	<a href="mariokart.php"><?php echo $language ? 'Back to Mario Kart PC':'Retour &agrave; Mario Kart PC'; ?></a>
</div>
</body>
</html>