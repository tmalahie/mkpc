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
    <title>Metagame - Mario Kart PC</title>

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
var recorder = "";
var pUnlocked = [1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1];
var cp = <?php include('getPersosMeta.php') ?>;
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
<script type="text/javascript" src="mk/maps.php"></script>
<script type="text/javascript" src="scripts/mk.meta.js"></script>
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
	<td rowspan="4" style="text-align:center;padding-left:10px">
		<?php
		if ($isCommon) {
			echo $language ? 'Metagame - Common version':'Metagame - Version commune';
			echo '<br />';
			echo '<a href="mariokart.meta.php" style="color:white">'. ($language ? 'Back to personal version':'Retour à la version perso') .'</a>';
			$canBeAdmin = in_array($identifiants[0], array(1390635815,2963080980));
			if ($canBeAdmin) {
				echo '<br />';
				echo '<a href="metaStats.php?common" style="color:white">'. ($language ? 'Edit stats':'Modifier les stats') .'</a>';
			}
		}
		else {
			echo $language ? 'Metagame - Personnal version':'Metagame - Version personnelle';
			echo '<br />';
			echo '<a href="?common" style="color:white">'. ($language ? 'See common version':'Voir la version commune') .'</a>';
			$canBeAdmin = in_array($identifiants[0], array(1390635815,2963080980));
			echo '<br />';
			echo '<a href="metaStats.php" style="color:white">'. ($language ? 'Edit stats':'Modifier les stats') .'</a>';
		}
		?>
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
<?php
include('gameInitElts.php');
?>
<div id="maps-list" style="position: absolute; visibility: hidden">
<?php
for ($i=1;$i<48;$i+=4)
	echo '<img src="images/selectors/select_map'.$i.'.png" alt="" />';
?>
</div>
<?php include('mk/description.php'); ?>
</body>
</html>