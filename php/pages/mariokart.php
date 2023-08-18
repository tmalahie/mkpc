<?php
include('../includes/language.php');
include('../includes/initdb.php');
?>
<!DOCTYPE html>
<html lang="<?php echo $language ? 'en':'fr'; ?>">
   <head>
	<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
	<meta name="author" content="Timothé Malahieude" />
	<meta name="description" content="<?php echo $language ? 'Free online Mario Kart game' : 'Jeu de Mario Kart gratuit en ligne'; ?>" />
	<meta name="keywords" content="<?php echo $language ? 'Mario, Kart, PC, game, race, track, builder, multiplayer, online' : 'Mario, Kart, PC, jeu, course, éditeur, circuit, multijoueur'; ?>" />
	<meta name="viewport" content="width=device-width, user-scalable=no" />
	<meta name="thumbnail" content="https://mkpc.malahieude.net/images/screenshots/ssfr1.png" />
	<meta property="og:image" content="https://mkpc.malahieude.net/images/mkthumbnail.jpg" />
    <title>Mario Kart PC</title>

<link rel="shortcut icon" type="image/x-icon" href="images/favicon.ico" />

<?php include('../includes/c_mariokart.php'); ?>

<?php
include('../includes/o_online.php');
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
var dCircuits = <?php
$circuitNamesDetailed = array();
$inc = 0;
for ($i=0;$i<$nbSNESCircuits;$i++) {
	$circuitNamesDetailed[] = '<small>SNES</small> ' . $circuitNames[$inc];
	$inc++;
}
for ($i=0;$i<$nbGBACircuits;$i++) {
	$circuitNamesDetailed[] = '<small>GBA</small> ' . $circuitNames[$inc];
	$inc++;
}
for ($i=0;$i<$nbDSCircuits;$i++) {
	$circuitNamesDetailed[] = '<small>DS</small> ' . $circuitNames[$inc];
	$inc++;
}
for ($i=0;$i<$nbSNESArenas;$i++) {
	$circuitNamesDetailed[] = '<small>SNES</small> ' . $circuitNames[$inc];
	$inc++;
}
for ($i=0;$i<$nbGBAArenas;$i++) {
	$circuitNamesDetailed[] = '<small>GBA</small> ' . $circuitNames[$inc];
	$inc++;
}
for ($i=0;$i<$nbDSArenas;$i++) {
	$circuitNamesDetailed[] = '<small>DS</small> ' . $circuitNames[$inc];
	$inc++;
}
echo json_encode($circuitNamesDetailed);
?>;
var recorder = <?php echo json_encode(isset($_COOKIE['mkrecorder']) ? $_COOKIE['mkrecorder']:'') ?>;
var cp = <?php include('../includes/getPersos.php'); ?>;
var pUnlocked = <?php include('../includes/getLocks.php'); ?>;
var ptsGP = "<?php echo $mkSaves; ?>";
var isCup = false, isBattle = false, isSingle = false, complete = false, simplified = false;
var baseOptions = <?php include('../includes/getCourseOptions.php'); ?>;
var PERSOS_DIR = "<?php
	require_once('../includes/persos.php');
	echo PERSOS_DIR;
?>";
var NBCIRCUITS = <?php echo $nbVSCircuits; ?>;
</script>
<?php
mysql_close();
?>
<script type="text/javascript" src="scripts/maps.php?reload=1"></script>
<?php include('../includes/mk/main.php') ?>
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
	<td id="pSize">&nbsp;</td>
	<td id="vSize">
	</td>
	<td rowspan="4" id="commandes">&nbsp;</td>
	</tr>
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
	<tr><td id="pFps">
	&nbsp;
	</td>
	<td id="vFps">
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
<?php
include('../includes/gameInitElts.php');
?>
<div id="maps-list">
<?php
for ($i=1;$i<48;$i+=4)
	echo '<img src="images/selectors/select_map'.$i.'.png" alt="" />';
?>
</div>
<?php include('../includes/mk/description.php'); ?>
</body>
</html>