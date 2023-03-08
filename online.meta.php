<?php
session_start();
$id = isset($_SESSION['mkid']) ? $_SESSION['mkid']:null;
include('language.php');
include('initdb.php');
if ($getPseudo = mysql_fetch_array(mysql_query('SELECT nom FROM `mkjoueurs` WHERE id="'. $id .'"')))
	$myPseudo = $getPseudo['nom'];
else
	$myPseudo = null;
if (isset($_COOKIE['mkp'])) {
	require_once('credentials.php');
	$myCredentials = credentials_decrypt($_COOKIE['mkp']);
	if (!$myPseudo) {
		if ($getPseudo = mysql_fetch_array(mysql_query('SELECT nom FROM `mkjoueurs` WHERE id="'. mysql_real_escape_string($myCredentials[0]) .'"')))
			$myPseudo = $getPseudo['nom'];
	}
	$myCode = $myCredentials[1];
}
if ($id && ($getBan=mysql_fetch_array(mysql_query('SELECT banned FROM `mkjoueurs` WHERE id="'.$id.'" AND banned')))) {
	include('getId.php');
	if ($getBan['banned'] == 1)
		include('ban_ip.php');
	echo 'Access denied';
	mysql_close();
	exit;
}
$isCup = false;
$isBattle = false;
$isSingle = false;
$isMCup = false;
if (isset($_GET['mid'])) {
	$isCup = true;
	$isMCup = true;
	$nid = intval($_GET['mid']);
}
elseif (isset($_GET['sid'])) {
	$isCup = true;
	$nid = intval($_GET['sid']);
	$complete = false;
}
elseif (isset($_GET['cid'])) {
	$isCup = true;
	$nid = intval($_GET['cid']);
	$complete = true;
}
elseif (isset($_GET['id'])) {
	$isCup = true;
	$nid = intval($_GET['id']);
	$complete = false;
	$isSingle = true;
}
elseif (isset($_GET['i'])) {
	$isCup = true;
	$nid = intval($_GET['i']);
	$complete = true;
	$isSingle = true;
}
if (isset($_GET['battle']))
	$isBattle = true;
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
if ($isCup)
	$NBCIRCUITS = ($isSingle?1:4);
else {
	include_once('circuitNames.php');
	$NBCIRCUITS = $nbVSCircuits;
}
if ($isCup) {
	$circuitsData = array();
	$cupIDs = array();
	$trackIDs = array();
	if ($isMCup) {
		$getMCup = mysql_fetch_array(mysql_query('SELECT nom,mode FROM `mkmcups` WHERE id="'. $nid .'"'));
		$complete = ($getMCup['mode'] == 1);
		$getTracks = mysql_query('SELECT c.* FROM `mkmcups_tracks` t INNER JOIN `mkcups` c ON t.cup=c.id WHERE t.mcup="'. $nid .'" ORDER BY t.ordering');
		$getCup = array('nom' => $getMCup['nom']);
		while ($getTrack = mysql_fetch_array($getTracks)) {
			$cupIDs[] = $getTrack['id'];
			for ($i=0;$i<4;$i++)
				$trackIDs[] = $getTrack['circuit'.$i];
		}
		$NBCIRCUITS = mysql_numrows($getTracks)*4;
	}
	elseif ($isSingle) {
		$getCup = mysql_fetch_array(mysql_query('SELECT nom FROM `'. ($complete ? ($isBattle?'arenes':'circuits'):'mkcircuits') .'` WHERE id="'. $nid .'"'));
		$trackIDs[] = $nid;
	}
	else {
		$getCup = mysql_fetch_array(mysql_query('SELECT nom,circuit0,circuit1,circuit2,circuit3 FROM `mkcups` WHERE id="'. $nid .'"'));
		$cupIDs[] = $nid;
		for ($i=0;$i<4;$i++)
			$trackIDs[] = $getCup['circuit'.$i];
	}
	if ($complete) {
		if (!empty($trackIDs)) {
			$table = $isBattle?'arenes':'circuits';
			$getCircuits = mysql_query('SELECT c.*,d.data FROM `'.$table.'` c LEFT JOIN `'.$table.'_data` d ON c.id=d.id WHERE c.id IN ('. implode(',',$trackIDs) .')');
			$allTracks = array();
			while ($getCircuit = mysql_fetch_array($getCircuits))
				$allTracks[$getCircuit['ID']] = $getCircuit;
			foreach ($trackIDs as $trackID) {
				if (isset($allTracks[$trackID]))
					$circuitsData[] = $allTracks[$trackID];
				else {
					mysql_close();
					exit;
				}
			}
		}
	}
	else {
		$getCircuits = mysql_query('SELECT id,map,laps,nom FROM `mkcircuits` WHERE id IN ('. implode(',',$trackIDs) .') AND ' . ($isBattle?'type':'!type'));
		$allTracks = array();
		while ($getCircuit = mysql_fetch_array($getCircuits))
			$allTracks[$getCircuit['id']] = $getCircuit;
		foreach ($trackIDs as $trackID) {
			if (isset($allTracks[$trackID]))
				$circuitsData[] = $allTracks[$trackID];
			else {
				mysql_close();
				exit;
			}
		}
		require_once('circuitPrefix.php');
		for ($i=0;$i<$NBCIRCUITS;$i++) {
			$circuit = &$circuitsData[$i];
			$pieces = mysql_query('SELECT * FROM `mkp` WHERE circuit="'.$circuit['id'].'"');
			while ($piece = mysql_fetch_array($pieces))
				$circuit['p'.$piece['id']] = $piece['piece'];
			for ($j=0;$j<$nbLettres;$j++) {
				$lettre = $lettres[$j];
				$getInfos = mysql_query('SELECT * FROM `mk'.$lettre.'` WHERE circuit="'.$circuit['id'].'"');
				$incs = array();
				while ($info=mysql_fetch_array($getInfos)) {
					$prefix = getLetterPrefixD($lettre,$info);
					if (!isset($incs[$prefix])) $incs[$prefix] = 0;
					$circuit[$prefix.$incs[$prefix]] = $info['x'].','.$info['y'];
					$incs[$prefix]++;
				}
			}
			if ($isBattle) {
				$getPos = mysql_query('SELECT * FROM `mkr` WHERE circuit="'.$circuit['id'].'"');
				while ($pos = mysql_fetch_array($getPos)) {
					$circuit['s'.$pos['id']] = $pos['s'];
					$circuit['r'.$pos['id']] = $pos['r'];
				}
			}
			unset($circuit);
		}
	}
}
$simplified = ($isCup && !$complete);
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
	   <title>Online Metagame - Mario Kart PC</title>
<?php include('metas.php'); ?>
<?php
if (isset($privateLink)) {
	?>
<meta name="robots" content="noindex" />
	<?php
}
?>

<?php include('c_mariokart.php'); ?>
<style type="text/css">
.wait {
	position: absolute;
	text-align: center;
	color: #DDD;
	z-index: 20001;
	visibility: hidden;
}
@media (max-width: 850px) {
	.online-chat {
		display: none;
	}
}
</style>

<?php
if (!$isCup) {
	?>
<script type="text/javascript" src="mk/maps.php"></script>
	<?php
}
?>
<script type="text/javascript">
var language = <?php echo ($language ? 'true':'false'); ?>;
var course = "<?php echo $isBattle ? 'BB':'VS'; ?>";
<?php
if ($isCup) {
	?>
var lCircuits = [<?php
for ($i=0;$i<$NBCIRCUITS;$i++) {
	if ($i)
		echo ',';
	$circuit = $circuitsData[$i];
	echo '"'. ($circuit['nom'] ? addSlashes(escapeUtf8($circuit['nom'])) : "&nbsp;") .'"';
}
?>];
var cupIDs = <?php echo json_encode($cupIDs) ?>;
	<?php
}
elseif ($isBattle) {
	?>
var lCircuits = <?php echo json_encode(array_splice($circuitNames,$nbVSCircuits)); ?>;
	<?php
}
else {
	?>
var lCircuits = <?php echo json_encode(array_splice($circuitNames,0,$nbVSCircuits)); ?>;
	<?php
}
?>
var cp = <?php include('getPersosMeta.php') ?>;
var pUnlocked = [1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1];
var baseOptions = <?php include('getCourseOptions.php'); ?>;
var page = "OL";
var PERSOS_DIR = "<?php
	require_once('persos.php');
	echo PERSOS_DIR;
?>";
var mId = <?php echo $id ? $id:'null'; ?>;
var mPseudo = "<?php echo $myPseudo; ?>", mCode = "<?php echo $myCode; ?>";
var isSingle = <?php echo $isSingle ? 'true':'false'; ?>;
var isBattle = <?php echo $isBattle ? 'true':'false'; ?>;
var isCup = <?php echo $isCup ? 'true':'false'; ?>;
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
		if ($isCup) {
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
	$params[] = '"meta"';
	echo implode(',',$params);
	?>]
};
var NBCIRCUITS = <?php echo $isBattle ? 0:$NBCIRCUITS; ?>;
<?php
if ($isCup) {
	?>
function listMaps() {
	return {
	<?php
	if ($complete) {
		$aID = $id;
		if ($isBattle)
			include('mk/battle.php');
		else
			include('mk/map.php');
		$id = $aID;
	}
	else {
		if ($isBattle)
			include('mk/arena.php');
		else
			include('mk/circuit.php');
	}
	?>
	};
}
	<?php
}
else {
	?>
	var aListMaps = listMaps;
	listMaps = function() {
		<?php
		if ($isBattle)
			echo 'var a='.($NBCIRCUITS+1).',n=8;';
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
?>
</script>
<script type="text/javascript" src="mk/maps.php"></script>
<script type="text/javascript" src="scripts/mk.meta.js"></script>
<script type="text/javascript">document.addEventListener("DOMContentLoaded", MarioKart);</script>
</head>
<body>
<div id="mariokartcontainer">
	<p id="waitrace" class="wait"><?php echo $language ? 'There are <strong id="racecountdown">30</strong> second(s) left to choose the next race':'Il vous reste <span id="racecountdown">30</span> seconde(s) pour choisir la prochaine course'; ?></p>
	<p id="waitteam" class="wait"><?php echo $language ? 'There are <strong id="teamcountdown">10</strong> second(s) left to choose the teams':'Il vous reste <span id="teamcountdown">10</span> seconde(s) pour choisir les équipes'; ?></p>
</div>

<div id="virtualkeyboard"></div>

<form name="modes" method="get" action="#null" onsubmit="return false">
<div id="options-ctn">
<table cellpadding="3" cellspacing="0" border="0" id="options">
<tr>
<td id="pSize">&nbsp;</td>
<td id="vSize">
</td>
<td rowspan="4" style="text-align:center;padding-left:10px">
    <?php
    if ($isCommon) {
        echo $language ? 'Metagame - Common version':'Metagame - Version commune';
        echo '<br />';
        echo '<a href="online.meta.php" style="color:white">'. ($language ? 'Back to personal version':'Retour à la version perso') .'</a>';
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
<?php include('mk/description.php'); ?>
</body>
</html>
<?php
mysql_close();
?>