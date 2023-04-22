<?php
include('session.php');
include('language.php');
include('initdb.php');
if ($getPseudo = mysql_fetch_array(mysql_query('SELECT nom FROM `mkjoueurs` WHERE id="'. $id .'"')))
	$myPseudo = $getPseudo['nom'];
else
	$myPseudo = null;
if ($id && ($getBan=mysql_fetch_array(mysql_query('SELECT banned FROM `mkjoueurs` WHERE id="'.$id.'" AND banned')))) {
	include('getId.php');
	if ($getBan['banned'] == 1)
		include('ban_ip.php');
	echo $language ? 'You have been banned from the online mode' : 'Vous avez été banni du mode en ligne';
	mysql_close();
	exit;
}
require_once('getRights.php');
$isSingle = false;
if (isset($_GET['mid']))
	$trackPayloadParams = array();
elseif (isset($_GET['sid'])) {
	$_GET['cid'] = $_GET['sid'];
	$trackPayloadParams = array();
}
elseif (isset($_GET['cid'])) {
	$trackPayloadParams = array();
}
elseif (isset($_GET['id'])) {
	$isSingle = true;
	$trackPayloadParams = array(
		'sid' => 'id'
	);
}
elseif (isset($_GET['i'])) {
	$isSingle = true;
	$trackPayloadParams = array(
		'sid' => 'i'
	);
}
$isCup = false;
$isMCup = false;
$isBattle = isset($_GET['battle']);
$isCustom = isset($trackPayloadParams);
if ($isCustom) {
	require_once('utils-cups.php');
	$trackPayloadParams['online'] = true;
	$mId = $id;
	getTrackPayloads($trackPayloadParams);
	$id = $mId;
}
if (isset($_GET['key'])) {
	$privateLink = intval($_GET['key']);
	if ($privateLinkData = mysql_fetch_array(mysql_query('SELECT * FROM `mkprivgame` WHERE id="'.$privateLink.'"'))) {
		if ($id)
			mysql_query('UPDATE `mkprivgame` SET last_used_date=NULL WHERE id="'.$privateLink.'"');
	}
	else {
		echo 'Private link is invalid or has expired';
		mysql_close();
		exit;
	}
}
if ($isCustom) {
	if (!$NBCIRCUITS) {
		mysql_close();
		exit;
	}
	$complete = ($circuitsData[0]['mode'] % 2);
	$simplified = 1 - $complete;
}
if (!$isCustom) {
	include_once('circuitNames.php');
	$NBCIRCUITS = $nbVSCircuits;
	$complete = false;
	$simplified = false;
}
$delNotif = true;
if (isset($privateLink)) {
	$delNotif = false;
	if ($getOptions = mysql_fetch_array(mysql_query('SELECT rules,public FROM `mkgameoptions` WHERE id="'.$privateLink.'"'))) {
		$linkOptions = json_decode($getOptions['rules']);
		if ($getOptions['public']) {
			$linkOptions->public = 1;
			$delNotif = true;
		}
	}
}
if ($id && $delNotif)
	mysql_query('DELETE FROM `mknotifs` WHERE user="'. $id .'" AND type="currently_online"');
if (isset($_SESSION['mklink'])) {
	if (isset($privateLink) && ($privateLink == $_SESSION['mklink']))
		$linkAccepted = true;
	unset($_SESSION['mklink']);
}
require_once('circuitEscape.php');
function escapeUtf8($str) {
	return htmlspecialchars(escapeCircuitNames($str));
}
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="<?php echo $language ? 'en':'fr'; ?>">
   <head>
	   <title><?php
	   	if ($isCustom) {
	   		if ($cName)
	   			echo escapeUtf8($cName) . ' - ';
	   		echo $language ? 'Online '.($isBattle?'battle':'race'):($isBattle?'Bataille':'Course') .' en ligne';
	   	}
	   	elseif ($isBattle)
	   		echo $language ? 'Online battle Mario Kart PC':'Bataille en ligne Mario Kart PC';
	   	else
	   		echo $language ? 'Online race Mario Kart PC':'Course en ligne Mario Kart PC';
	   ?></title>
<?php include('metas.php'); ?>
<?php
if (isset($privateLink)) {
	?>
<meta name="robots" content="noindex" />
	<?php
}
?>

<?php include('c_mariokart.php'); ?>
<link rel="stylesheet" media="screen" type="text/css" href="styles/mk-online.css?reload=1" />

<?php
if (!$isCustom) {
	?>
<script type="text/javascript" src="mk/maps.php?reload=2"></script>
	<?php
}
?>
<script type="text/javascript">
var language = <?php echo ($language ? 'true':'false'); ?>;
var course = "<?php echo $isBattle ? 'BB':'VS'; ?>";
<?php
if ($isCustom) {
	?>
var lCircuits = [<?php
for ($i=0;$i<$NBCIRCUITS;$i++) {
	if ($i)
		echo ',';
	$circuit = $circuitsData[$i];
	echo '"'. ($circuit['name'] ? addSlashes(escapeUtf8($circuit['name'])) : "&nbsp;") .'"';
}
?>];
<?php
if ($isCup) {
	$dCircuits = array();
	$isCircuitPrefix = false;
	for ($i=0;$i<$NBCIRCUITS;$i++) {
		$circuit = $circuitsData[$i];
		if ($circuit['prefix']) {
			$isCircuitPrefix = true;
			$dCircuits[] = '<small>'. htmlspecialchars($circuit['prefix']) .'</small> ' . escapeUtf8($circuit['name']);
		}
		else
			$dCircuits[] = escapeUtf8($circuit['name']);
	}
	if ($isCircuitPrefix)
		echo 'var dCircuits = '. json_encode($dCircuits) .';';
}
if (!empty($cupPayloads))
	echo 'var cupPayloads = '. json_encode($cupPayloads) .';';
?>
var cupOpts = <?php echo empty($cOptions) ? '{}':$cOptions; ?>;
	<?php
}
elseif ($isBattle) {
	?>
var lCircuits = <?php echo json_encode(array_slice($circuitNames,$nbVSCircuits)); ?>;
var dCircuits = <?php
$circuitNamesDetailed = array();
$inc = $nbVSCircuits;
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
	<?php
}
else {
	?>
var lCircuits = <?php echo json_encode(array_slice($circuitNames,0,$nbVSCircuits)); ?>;
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
echo json_encode($circuitNamesDetailed);
?>;
	<?php
}
?>
var cp = <?php include('getPersos.php'); ?>;
var pUnlocked = <?php include('getLocks.php'); ?>;
var baseOptions = <?php include('getCourseOptions.php'); ?>;
var page = "OL";
var PERSOS_DIR = "<?php
	require_once('persos.php');
	echo PERSOS_DIR;
?>";
var mId = <?php echo $id ? $id:'null'; ?>;
var mPseudo = "<?php echo $myPseudo; ?>", mCode = "";
var mIsModerator = <?php echo hasRight('moderator') ? 1:0; ?>;
var isSingle = <?php echo $isSingle ? 'true':'false'; ?>;
var isBattle = <?php echo $isBattle ? 'true':'false'; ?>;
var isCup = <?php echo $isCustom ? 'true':'false'; ?>;
var complete = <?php echo $complete ? 'true':'false'; ?>;
var simplified = <?php echo $simplified ? 'true':'false'; ?>;
var nid = <?php echo isset($nid) ? $nid:'null'; ?>;
var shareLink = {
	key: <?php echo isset($privateLink) ? "'$privateLink'":'null'; ?>,
	player: <?php echo isset($privateLinkData) ? $privateLinkData['player']:'null'; ?>,
	options: <?php echo isset($linkOptions) ? json_encode($linkOptions):'null'; ?>,
	url:"<?php echo (isset($_SERVER['HTTPS'])?'https':'http') . '://' . $_SERVER['HTTP_HOST'] . parse_url($_SERVER["REQUEST_URI"], PHP_URL_PATH); ?>",
	accepted: <?php echo isset($linkAccepted) ? 'true':'false'; ?>,
	params: [<?php
	$params = array();
	if ($isMCup)
		$params[] = '"mid='.$nid.'"';
	else {
		if ($isCustom) {
			if ($isSingle) {
				if ($complete)
					$params[] = '"i='.$nid.'"';
				else
					$params[] = '"id='.$nid.'"';
			}
			else {
				if ($complete)
					$params[] = '"cid='.$nid.'"';
				else
					$params[] = '"sid='.$nid.'"';
			}
		}
	}
	if ($isBattle)
		$params[] = '"battle"';
	echo implode(',',$params);
	?>]
};
var NBCIRCUITS = <?php echo $isBattle ? 0:$NBCIRCUITS; ?>;
<?php
if ($isCustom) {
	?>
function listMaps() {
	return {<?php printCircuitsData(); ?>};
}
	<?php
}
else {
	?>
	var aListMaps = listMaps;
	listMaps = function() {
		<?php
		if ($isBattle)
			echo 'var a='.($NBCIRCUITS+1).',n=12;';
		else
			echo 'var a=1,n='.$NBCIRCUITS.';';
		?>
		var aMaps = aListMaps();
		var res = {};
		for (var i=0;i<n;i++)
			res["map"+(i+a)] = aMaps["map"+(i+a)];
		return res;
	}
	<?php
}
include('handleCupOptions.php');
?>
</script>
<?php include('mk/main.php') ?>
<script type="text/javascript">document.addEventListener("DOMContentLoaded", MarioKart);</script>
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
<td rowspan="4" id="commandes">
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
include('gameInitElts.php');
?>
<script type="text/javascript" src="scripts/simplepeer.min.js"></script>
<script type="text/javascript">var rtcService, cPlayerPeers = {};</script>
<script type="text/javascript" src="scripts/mk-online.js"></script>
<?php include('mk/description.php'); ?>
</body>
</html>
<?php
mysql_close();
?>