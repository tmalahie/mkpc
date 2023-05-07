<?php
include('language.php');
include('initdb.php');
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="<?php echo $language ? 'en':'fr'; ?>" >
   <head>
	<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
	<meta name="author" content="Timothé Malahieude" />
	<meta name="description" content="Jeu de Mario Kart gratuit en ligne" />
	<meta name="keywords" content="Mario, Kart, PC, jeu, course, jeu gratuit, multijoueurs" />
	<meta name="viewport" content="width=device-width, user-scalable=no" />
	<meta name="thumbnail" content="https://mkpc.malahieude.net/images/screenshots/ssfr1.png" />
	<meta property="og:image" content="https://mkpc.malahieude.net/images/mkthumbnail.jpg" />
    <title>Mario Kart PC - <?php echo $language ? 'Old engine':'Ancien moteur'; ?></title>

<link rel="shortcut icon" type="image/x-icon" href="images/favicon.ico" />

<link rel="stylesheet" media="screen" type="text/css" href="styles/mariokart.css" />

<?php
include('o_online.php');
?>
<script type="text/javascript">
var page = "MK";
var selectedPlayers = <?php echo (isset($_COOKIE['mkplayers']) ? $_COOKIE['mkplayers']:8); ?>;
var selectedTeams = <?php echo (isset($_COOKIE['mkteam']) ? $_COOKIE['mkteam']:0); ?>;
var selectedDifficulty = <?php echo (isset($_COOKIE['mkdifficulty']) ? $_COOKIE['mkdifficulty']:1); ?>;
var language = <?php echo ($language ? 'true':'false'); ?>;
var sCircuits = ["Circuit Mario 1","Plaine Donut 1","Plage Koopa 1","Île Choco 1","Lac Vanille 1","Vallée Fantôme 1","Circuit Mario 2","Château de Bowser 1","Plaine Donut 2","Château de Bowser 2","Île Choco 2","Circuit Mario 3","Plage Koopa 2","Lac Vanille 2","Vallée Fantôme 2","Plaine Donut 3","Vallée Fantôme 3","Circuit Mario 4","Château de Bowser 3","Route Arc-en-Ciel","Circuit Peach","Plage Maskass","Bord du Fleuve","Château de Bowser I","Circuit Mario","Lac Boo","Pays Fromage","Château de Bowser II","Circuit Luigi","Jardin volant","Île Cheep-Cheep","Pays Crépuscule","Royaume Sorbet","Route Ruban","Désert Yoshi","Château de Bowser III","Bord du Lac","Jetée cassée","Château de Bowser IV","Route  Arc-en-Ciel","Arène Bataille 1","Arène Bataille 2","Arène Bataille 3","Arène Bataille 4","Arène Bataille  1","Arène Bataille  2","Arène Bataille  3","Arène Bataille  4"];
var lCircuits = language ? ["Mario Circuit 1","Donut Plains 1","Koopa Beach 1","Choco Island 1","Vanilla Lake 1","Ghost Valley 1","Mario Circuit 2","Bowser Castle 1","Donut Plains 2","Bowser Castle 2","Choco Island 2","Mario Circuit 3","Koopa Beach 2","Vanilla Lake 2","Ghost Valley 2","Donut Plains 3","Ghost Valley 3","Mario Circuit 4","Bowser Castle 3","Rainbow Road","Peach Circuit","Shy Guy Beach","Riverside Park","Bowser Castle I","Mario Circuit","Boo Lake","Cheese Land","Bowser Castle II","Luigi Circuit","Sky Garden","Cheep-Cheep Island","Sunset Wilds","Snow Land","Ribbon Road","Yoshi Desert","Bowser Castle III","Lakeside Park","Broken Pier","Bowser Castle IV","Rainbow  Road","Battle Course 1","Battle Course 2","Battle Course 3","Battle Course 4","Battle Course  1","Battle Course  2","Battle Course  3","Battle Course  4"]:sCircuits;
var recorder = "";
var cp = {"mario":[0.6,1,0.6],"luigi":[0.2,1.2,0.2],"peach":[0.2,1,1],"toad":[1,1,0.2],"yoshi":[0.6,1,0.6],"bowser":[1,0.9,1],"donkey-kong":[0.4,1,0.8],"daisy":[0.2,1,1],"waluigi":[0.8,1,0.4],"koopa":[0.4,1,0.8],"wario":[0.2,1.1,0.3],"maskass":[0.8,1,0.3],"birdo":[0.6,0.95,0.7],"roi_boo":[0.4,1,0.8],"frere_marto":[0.4,1.05,0.7],"bowser_jr":[0.9,0.95,0.7],"harmonie":[0.3,0.95,0.8],"diddy-kong":[0.4,1,0.8],"skelerex":[0.6,1,0.6],"funky-kong":[0.4,1,0.8],"toadette":[0.8,1,0.35]};
var pUnlocked = <?php include('getLocks.php'); ?>;
pUnlocked.splice(15,3);
var ptsGP = "<?php echo $mkSaves; ?>";
var isCup = false, isBattle = false, isSingle = false, complete = false, simplified = false;
var baseOptions = <?php include('getCourseOptions.php'); ?>;
var PERSOS_DIR = "<?php
	require_once('persos.php');
	echo PERSOS_DIR;
?>";
var NBCIRCUITS = 40;
</script>
<?php
mysql_close();
?>
<script type="text/javascript" src="mk/maps.old.js"></script>
<script type="text/javascript" src="scripts/mk.v42.oldengine.js"></script>
<script type="text/javascript">
document.addEventListener("DOMContentLoaded", MarioKart);
</script>
</head>
<body>
<div id="mariokartcontainer"></div>

<div id="virtualkeyboard"></div>

<p id="temps0"></p>
<p id="compteur0"></p>
<table id="infos0" cellspacing="1" cellpadding="0" style="visibility: hidden">
<tr><td></td></tr></table>

<form name="modes" method="get" action="#null" onsubmit="return false">
	<div id="options-ctn">
	<table cellpadding="3" cellspacing="0" border="0" id="options">
	<tr>
	<td id="pQuality">&nbsp;</td>
	<td id="vQuality">
	</td>
	<td rowspan="4" id="commandes">&nbsp;</td>
	</tr>
	<tr><td id="pSize">

	</td>
	<td id="vSize">
	&nbsp;
	</td></tr>
	<tr><td id="pMusic">
	&nbsp;
	</td>
	<td id="vMusic">
	&nbsp;
	</td></tr>
	<tr><td id="pSfx">
	&nbsp;
	</td>
	<td id="vSfx">
	&nbsp;
	</td></tr>
	</table>
</div>
<div id="vPub"><script async src="//pagead2.googlesyndication.com/pagead/js/adsbygoogle.js"></script>
<!-- Mario Kart PC -->
<ins class="adsbygoogle"
     style="display:inline-block;width:468px;height:60px"
     data-ad-client="ca-pub-1340724283777764"
     data-ad-slot="6691323567"></ins>
<script>
(adsbygoogle = window.adsbygoogle || []).push({});
</script></div>
</form>
<table id="objet0" border="1" cellpadding="0" cellspacing="0">
<tr>	<td id="roulette0" valign="middle"></td>	</tr>
</table>
<table id="objet1" border="1" cellpadding="0" cellspacing="0">
<tr>	<td id="roulette1" valign="middle"></td>	</tr>
</table>
<div id="dMaps"></div>
<p id="infoPlace0"></p>
<div id="lakitu0" class="pixelated"><div></div></div>
<div id="drift0">
	<img alt="." src="images/drift.png" class="driftimg pixelated" />
</div>
<div id="scroller0" width="100px" height="100px" style="width: 100px; height: 100px; overflow: hidden; position: absolute; visibility: hidden">
	<div style="position: absolute; left: 0; top: 0">
		<img class="aObjet" alt="." src="images/items/fauxobjet.png" /><br />&nbsp;<br />
		<img class="aObjet" alt="." src="images/items/banane.png" /><br />&nbsp;<br />
		<img class="aObjet" alt="." src="images/items/carapace.png" /><br />&nbsp;<br />
		<img class="aObjet" alt="." src="images/items/bobomb.png" /><br />&nbsp;<br />
		<img class="aObjet" alt="." src="images/items/carapacerouge.png" /><br />&nbsp;<br />
		<img class="aObjet" alt="." src="images/items/carapacebleue.png" /><br />&nbsp;<br />
		<img class="aObjet" alt="." src="images/items/champi.png" /><br />&nbsp;<br />
		<img class="aObjet" alt="." src="images/items/megachampi.png" /><br />&nbsp;<br />
		<img class="aObjet" alt="." src="images/items/etoile.png" /><br />&nbsp;<br />
		<img class="aObjet" alt="." src="images/items/eclair.png" /><br />&nbsp;<br />
		<img class="aObjet" alt="." src="images/items/billball.png" /><br />&nbsp;<br />
		<img class="aObjet" alt="." src="images/items/fauxobjet.png" />
	</div>
</div>
<div id="maps-list" style="position: absolute; visibility: hidden">
<?php
for ($i=1;$i<48;$i+=4)
	echo '<img src="images/selectors/select_map'.$i.'.png" alt="" />';
?>
</div>
<?php include('mk/description.php'); ?>
</body>
</html>