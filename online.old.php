<?php
session_start();
$id = isset($_SESSION['mkid']) ? $_SESSION['mkid']:null;
include('language.php');
include('initdb.php');
mysql_set_charset('utf8');
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
	$myCode = isset($myCredentials[1]) ? $myCredentials[1] : null;
}
else
	$myCode = null;
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
	$nid = $_GET['mid'];
}
elseif (isset($_GET['sid'])) {
	$isCup = true;
	$nid = $_GET['sid'];
	$complete = false;
}
elseif (isset($_GET['cid'])) {
	$isCup = true;
	$nid = $_GET['cid'];
	$complete = true;
}
elseif (isset($_GET['id'])) {
	$isCup = true;
	$nid = $_GET['id'];
	$complete = false;
	$isSingle = true;
}
elseif (isset($_GET['i'])) {
	$isCup = true;
	$nid = $_GET['i'];
	$complete = true;
	$isSingle = true;
}
else {
	$complete = false;
}
if (isset($_GET['battle']))
	$isBattle = true;
$privateLink = 2949102931;
if ($isCup)
	$NBCIRCUITS = ($isSingle?1:4);
else {
    $circuitNames = array("Circuit Mario 1","Plaine Donut 1","Plage Koopa 1","Île Choco 1","Lac Vanille 1","Vallée Fantôme 1","Circuit Mario 2","Château de Bowser 1","Plaine Donut 2","Château de Bowser 2","Île Choco 2","Circuit Mario 3","Plage Koopa 2","Lac Vanille 2","Vallée Fantôme 2","Plaine Donut 3","Vallée Fantôme 3","Circuit Mario 4","Château de Bowser 3","Route Arc-en-Ciel","Circuit Peach","Plage Maskass","Bord du Fleuve","Château de Bowser I","Circuit Mario","Lac Boo","Pays Fromage","Château de Bowser II","Circuit Luigi","Jardin volant","Île Cheep-Cheep","Pays Crépuscule","Royaume Sorbet","Route Ruban","Désert Yoshi","Château de Bowser III","Bord du Lac","Jetée cassée","Château de Bowser IV","Route  Arc-en-Ciel","Arène Bataille 1","Arène Bataille 2","Arène Bataille 3","Arène Bataille 4","Arène Bataille  1","Arène Bataille  2","Arène Bataille  3","Arène Bataille  4");
    $nbVSCircuits = 40;
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
		$lettres = Array('a', 'b', 'c', 'd', 'e', 'f', 'g', 'h', 'i', 'j', 'o', 't');
		$nbLettres = count($lettres);
		for ($i=0;$i<$NBCIRCUITS;$i++) {
			$circuit = &$circuitsData[$i];
			$pieces = mysql_query('SELECT * FROM `mkp` WHERE circuit="'.$circuit['id'].'"');
			while ($piece = mysql_fetch_array($pieces))
				$circuit['p'.$piece['id']] = $piece['piece'];
			for ($j=0;$j<$nbLettres;$j++) {
				$lettre = $lettres[$j];
				$getInfos = mysql_query('SELECT x,y FROM `mk'.$lettre.'` WHERE circuit="'. $circuit['id'] .'"');
				for ($k=0;$info=mysql_fetch_array($getInfos);$k++)
					$circuit[$lettre.$k] = $info['x'].','.$info['y'];
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
	   <title><?php
	   	if ($isCup) {
	   		if ($getCup['nom'])
	   			echo escapeUtf8($getCup['nom']) . ' - ';
	   		echo $language ? 'Online '.($isBattle?'battle':'race'):($isBattle?'Bataille':'Course') .' en ligne';
	   	}
	   	elseif ($isBattle)
	   		echo $language ? 'Online battle - Old engine':'Bataille en ligne - Ancien moteur';
	   	else
	   		echo $language ? 'Online race - Old engine':'Course en ligne - Ancien moteur';
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
<script type="text/javascript" src="mk/maps.old.js"></script>
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
var cp = {"mario":[0.6,1,0.6],"luigi":[0.2,1.2,0.2],"peach":[0.2,1,1],"toad":[1,1,0.2],"yoshi":[0.6,1,0.6],"bowser":[1,0.9,1],"donkey-kong":[0.4,1,0.8],"daisy":[0.2,1,1],"waluigi":[0.8,1,0.4],"koopa":[0.4,1,0.8],"wario":[0.2,1.1,0.3],"maskass":[0.8,1,0.3],"birdo":[0.6,0.95,0.7],"roi_boo":[0.4,1,0.8],"frere_marto":[0.4,1.05,0.7],"bowser_jr":[0.9,0.95,0.7],"harmonie":[0.3,0.95,0.8],"diddy-kong":[0.4,1,0.8],"skelerex":[0.6,1,0.6],"funky-kong":[0.4,1,0.8],"toadette":[0.8,1,0.35]};
var pUnlocked = <?php include('getLocks.php'); ?>;
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
<script type="text/javascript" src="scripts/mk.v41.old.js"></script>
<script type="text/javascript">document.addEventListener("DOMContentLoaded", MarioKart);</script>
</head>
<body>
<div id="mariokartcontainer"></div>

<div id="virtualkeyboard"></div>

<p id="waitrace" class="wait"><?php echo $language ? 'There are <strong id="racecountdown">30</strong> second(s) left to choose the next race':'Il vous reste <span id="racecountdown">30</span> seconde(s) pour choisir la prochaine course'; ?></p>
<p id="waitteam" class="wait"><?php echo $language ? 'There are <strong id="teamcountdown">10</strong> second(s) left to choose the teams':'Il vous reste <span id="teamcountdown">10</span> seconde(s) pour choisir les équipes'; ?></p>
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
<td rowspan="4" id="commandes">
<?php
if ($language) {
	?>
<strong>Move</strong> : Arrows<br />
<strong>Use object</strong> : Spacebar<br />
<strong><em>OR</em></strong> : Left click<br />
<strong>Jump/drift</strong> : Ctrl<br />
<?php if ($isBattle) echo '<strong>Gonfler un ballon</strong> : Maj<br />'; ?>
<strong>Rear/Front view</strong> : X<br />
<strong>Quit</strong> : Escape</td>
	<?php
}
else {
	?>
<strong>Se diriger</strong> : Fl&egrave;ches directionnelles<br />
<strong>Utiliser un objet</strong> : Barre d'espace<br />
<strong><em>OU</em></strong> : Clic gauche<br />
<strong>Sauter/déraper</strong> : Ctrl<br />
<?php if ($isBattle) echo '<strong>'. ($language ? 'Inflate a balloon':'Gonfler un ballon') .'</strong> : '. ($language ? 'Shift':'Maj') .'<br />'; ?>
<strong>Vue arri&egrave;re/avant</strong> : X<br />
<strong>Quitter</strong> : &Eacute;chap</td>
	<?php
}
?></tr>
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
<div id="dMaps"></div>
<p id="infoPlace0"></p>
<div id="lakitu0"><div></div></div>
<div id="drift0">
	<img alt="." src="images/drift.png" class="driftimg" />
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
<?php include('mk/description.php'); ?>
</body>
</html>
<?php
mysql_close();
?>