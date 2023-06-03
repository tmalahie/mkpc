<?php
$cupIDs = Array();
$infos = Array();
$isCup = false;
$isMCup = false;
include('../includes/getId.php');
include('../includes/initdb.php');
include('../includes/language.php');
require_once('../includes/utils-challenges.php');
require_once('../includes/utils-cups.php');
include('../includes/creation-challenges.php');
$cName = null;
$cName0 = null;
$cPrefix = null;
$cPseudo = null;
$cAuteur = null;
$cDate = null;
$cShared = false;
$pNote = 0;
$pNotes = 0;
getTrackPayloads(array(
	'sid' => 'id',
	'mode' => 0
));
$sid = ($isMCup ? 'mid' : ($isCup ? 'cid':'id'));
$getInfos = Array();
?>
<!DOCTYPE HTML SYSTEM>
<html>
	<head>
		<title><?php if ($cName){echo htmlEscapeCircuitNames($cName);echo ' - ';} ?>Mario Kart PC</title>
<?php
include('../includes/metas.php');

include('../includes/c_mariokart.php');
include('../includes/c_collab.php');
include('../includes/c_comments.php');

include('../includes/o_online.php');
?>
<script type="text/javascript">
var selectedPlayers = <?php echo (isset($_COOKIE['mkplayers']) ? $_COOKIE['mkplayers']:8); ?>;
var selectedTeams = <?php echo (isset($_COOKIE['mkteam']) ? $_COOKIE['mkteam']:0); ?>;
var selectedDifficulty = <?php echo (isset($_COOKIE['mkdifficulty']) ? $_COOKIE['mkdifficulty']:1); ?>;
var challenges = <?php echo json_encode($challenges); ?>;
var clRewards = <?php echo json_encode($clRewards); ?>;
var clId = <?php echo json_encode($clId); ?>;
var language = <?php echo ($language ? 'true':'false'); ?>;
var recorder = <?php echo json_encode(isset($_COOKIE['mkrecorder']) ? $_COOKIE['mkrecorder']:'') ?>;
var lCircuits = [<?php
for ($i=0;$i<$NBCIRCUITS;$i++) {
	if ($i)
		echo ',';
	$circuit = $circuitsData[$i];
	echo '"'. ($circuit['name'] ? addSlashes(htmlEscapeCircuitNames($circuit['name'])) : "&nbsp;") .'"';
}
?>];
<?php
if (!empty($dCircuits))
	echo 'var dCircuits = '. json_encode($dCircuits) .';';
if (!empty($cupPayloads))
	echo 'var cupPayloads = '. json_encode($cupPayloads) .';';
?>
var cupOpts = <?php echo empty($cOptions) ? '{}':$cOptions; ?>;
<?php
if (!empty($cupNames)) {
	echo 'var cupNames = [';
	foreach ($cupNames as $i=>$cupName) {
		if ($i) echo ',';
		echo '"'.addSlashes(htmlEscapeCircuitNames($cupName)).'"';
	}
	echo '];';
}
?>
var cp = <?php include('../includes/getPersos.php'); ?>;
var pUnlocked = <?php include('../includes/getLocks.php'); ?>;
var baseOptions = <?php include('../includes/getCourseOptions.php'); ?>;
var page = "CI";
<?php include('../includes/getCupScore.php'); ?>
var PERSOS_DIR = "<?php
	require_once('../includes/persos.php');
	echo PERSOS_DIR;
?>";
var isBattle = false;
var isCup = true;
var isSingle = <?php echo $isCup ? 'false':'true'; ?>;
var complete = false;
var simplified = true;
var nid = <?php echo isset($nid) ? $nid:'null'; ?>;
var edittingCircuit = <?php echo isset($edittingCircuit) ? 'true':'false'; ?>;
var NBCIRCUITS = <?php echo $NBCIRCUITS; ?>;
function listMaps() {
	return {<?php printCircuitsData(); ?>};
}
<?php include('../includes/handleCupOptions.php'); ?>
</script>
<?php include('../includes/mk/main.php') ?>
<script type="text/javascript">
<?php
require_once('../includes/circuit-actions.php');
includeShareLib();
?>
</script>
<script type="text/javascript" src="scripts/ratings.js"></script>
<script src="scripts/jquery.min.js"></script>
<script type="text/javascript">$(document).ready(MarioKart);</script>
</head>
<body>
<div id="mariokartcontainer"></div>

<div id="virtualkeyboard"></div>

<form name="modes" method="get" action="#null" onsubmit="return false">
<div id="options-ctn">
<table cellpadding="3" cellspacing="0" border="0" id="options"<?php if ($ctActions) echo ' class="ct-actions"'; ?>>
<tr>
<td id="pSize">&nbsp;</td>
<td id="vSize">
</td>
<td rowspan="4" id="commandes">&nbsp;</td>
<td rowspan="4" id="shareParams">
<?php
if ($canChange && !$isCup) {
	if ($noStart)
		$message = $language ? 'Warning: your circuit doesn\'t have a start.<br />Quite annoying, we don\'t know where to begin.':'Attention : Votre circuit n\'a pas de départ !<br />C\'est ennuyeux, on ne sait pas par où commencer...';
	elseif ($twoStarts)
		$message = $language ? 'Warning: your circuit has more than one start.<br />It\'s hard to know which one to choose.':'Attention : votre circuit comporte plusieurs départs !<br />Difficile de savoir lequel choisir...';
	elseif ($bloqued)
		$message = $language ? 'Warning: your circuit has to make a loop.<br />Otherwise it\'s impossible to know how to make a lap...':'Attention : votre circuit doit former une boucle.<br />Impossible de faire un tour sinon...';
}
printCircuitActions();
?>
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
if (!isset($message) && isset($nid)) {
	if (!$isCup) {
		if ($cupOfCircuit = mysql_fetch_array(mysql_query('SELECT id FROM `mkcups` WHERE (circuit0="'. $nid .'" OR circuit1="'. $nid .'" OR circuit2="'. $nid .'" OR circuit3="'. $nid .'") AND mode=0 LIMIT 1'))) {
			$message = ($language ? 'This circuit is part of a cup!<br /><a href="?cid='. $cupOfCircuit['id'] .'">Click here</a> to access it.':'Ce circuit fait partie d\'une coupe !<br /><a href="?cid='. $cupOfCircuit['id'] .'">Cliquez ici</a> pour y accéder.');
			$infoMsg = true;
		}
	}
	elseif (!$isMCup) {
		if ($cupOfCircuit = mysql_fetch_array(mysql_query('SELECT mcup FROM `mkmcups_tracks` WHERE cup="'. $nid .'"'))) {
			$message = ($language ? 'This cup is part of a multicup!<br /><a href="?mid='. $cupOfCircuit['mcup'] .'">Click here</a> to access it.':'Cette coupe fait partie d\'une multicoupe !<br /><a href="?mid='. $cupOfCircuit['mcup'] .'">Cliquez ici</a> pour y accéder.');
			$infoMsg = true;
		}
	}
}
if (isset($message)) {
	?>
<div id="alerte"<?php if (isset($infoMsg)) echo ' class="alerte-info"'; ?>><p id="closeAlert"><a href="javascript:document.getElementById('alerte').style.display='none';void(0)">&times;</a></p>
<p><?php echo $message;
?></p></div>
	<?php
}
printCircuitShareUI();
include('../includes/gameInitElts.php');
?>
<?php
if (isset($nid)) {
	require_once('../includes/reactions.php');
	printReactionUI();
	?>
	<div id="comments-section"></div>
	<?php
	include('../includes/o_comments.php');
}
?>
<?php include('../includes/mk/description.php'); ?>
</body>
</html>
<?php
mysql_close();
?>