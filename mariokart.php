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
    <title>Mario Kart PC</title>

<link rel="shortcut icon" type="image/x-icon" href="images/favicon.ico" />

<?php include('c_mariokart.php'); ?>

<?php
include('o_online.php');
?>
<script type="text/javascript">
var page = "MK";
var selectedPlayers = <?php echo (isset($_COOKIE['mkplayers']) ? $_COOKIE['mkplayers']:8); ?>;
var selectedTeams = <?php echo (isset($_COOKIE['mkteam']) ? $_COOKIE['mkteam']:0); ?>;
var selectedDifficulty = <?php echo (isset($_COOKIE['mkdifficulty']) ? $_COOKIE['mkdifficulty']:1); ?>;
var language = <?php echo ($language ? 'true':'false'); ?>;
var lCircuits = <?php
include_once('circuitNames.php');
echo json_encode($circuitNames);
?>;
var recorder = "<?php echo isset($_COOKIE['mkrecorder']) ? $_COOKIE['mkrecorder']:'' ?>";
var cp = <?php include('getPersos.php'); ?>;
var pUnlocked = <?php include('getLocks.php'); ?>;
var ptsGP = "<?php echo $mkSaves; ?>";
var isCup = false, isBattle = false, isSingle = false, complete = false, simplified = false;
var baseOptions = <?php include('getCourseOptions.php'); ?>;
var PERSOS_DIR = "<?php
	include('persos.php');
	echo PERSOS_DIR;
?>";
var NBCIRCUITS = <?php echo $nbVSCircuits; ?>;
</script>
<?php
mysql_close();
?>
<script type="text/javascript" src="mk/maps.php?reload=4"></script>
<?php include('mk/main.php') ?>
<script type="text/javascript">
document.addEventListener("DOMContentLoaded", MarioKart);
</script>
</head>
<body>
<div id="mariokartcontainer"></div>

<div id="virtualkeyboard"></div>

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
<div id="dMaps"></div>
<div id="scroller" width="100px" height="100px" style="width: 100px; height: 100px; overflow: hidden; position: absolute; visibility: hidden">
	<div style="position: absolute; left: 0; top: 0">
		<img class="aObjet" alt="." src="images/items/fauxobjet.gif" /><br />&nbsp;<br />
		<img class="aObjet" alt="." src="images/items/banane.gif" /><br />&nbsp;<br />
		<img class="aObjet" alt="." src="images/items/carapace.gif" /><br />&nbsp;<br />
		<img class="aObjet" alt="." src="images/items/bobomb.gif" /><br />&nbsp;<br />
		<img class="aObjet" alt="." src="images/items/carapacerouge.gif" /><br />&nbsp;<br />
		<img class="aObjet" alt="." src="images/items/carapacebleue.gif" /><br />&nbsp;<br />
		<img class="aObjet" alt="." src="images/items/champi.gif" /><br />&nbsp;<br />
		<img class="aObjet" alt="." src="images/items/megachampi.gif" /><br />&nbsp;<br />
		<img class="aObjet" alt="." src="images/items/etoile.gif" /><br />&nbsp;<br />
		<img class="aObjet" alt="." src="images/items/eclair.gif" /><br />&nbsp;<br />
		<img class="aObjet" alt="." src="images/items/billball.gif" /><br />&nbsp;<br />
		<img class="aObjet" alt="." src="images/items/fauxobjet.gif" />
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