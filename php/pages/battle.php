<?php
include('../includes/initdb.php');
include('../includes/language.php');
require_once('../includes/utils-challenges.php');
require_once('../includes/utils-cups.php');
include('../includes/creation-challenges.php');
$cAuteur = null;
$cupIDs = Array();
include('../includes/getId.php');
$cName = null;
$cName0 = null;
$cPrefix = null;
$cPseudo = null;
$cAuteur = null;
$cDate = null;
$cShared = false;
$cEditting = false;
$pNote = 0;
$pNotes = 0;
getTrackPayloads(array(
	'sid' => 'i',
	'mode' => 3
));
$sid = ($isMCup ? 'mid' : ($isCup ? 'cid':'i'));
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
var challenges = <?php echo json_encode($challenges); ?>;
var clRewards = <?php echo json_encode($clRewards); ?>;
var clId = <?php echo json_encode($clId); ?>;
var language = <?php echo ($language ? 'true':'false'); ?>;
var lCircuits = [<?php
for ($i=0;$i<$NBCIRCUITS;$i++) {
	if ($i)
		echo ',';
	$circuit = $circuitsData[$i];
	echo '"'. ($circuit['nom'] ? addSlashes(htmlEscapeCircuitNames($circuit['nom'])) : "&nbsp;") .'"';
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
var page = "BA";
var PERSOS_DIR = "<?php
	require_once('../includes/persos.php');
	echo PERSOS_DIR;
?>";
var cShared = <?php echo $cShared ? 'true':'false'; ?>;
var isBattle = true;
var isCup = true;
var isSingle = <?php echo $isCup ? 'false':'true'; ?>;
var complete = true;
var simplified = false;
var nid = <?php echo isset($nid) ? $nid:'null'; ?>;
var edittingCircuit = <?php echo isset($edittingCircuit) ? 'true':'false'; ?>;
var NBCIRCUITS = 0;
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
	if (!isset($circuitMainData->bgcolor))
		$message = $language ? 'Warning: You didn\'t specify any data for the circuit.<br />Go back to the editor before testing it.':'Attention : vous n\'avez pas encore spécifié les paramètres du circuit.<br />Revenez dans l\'éditeur avant de continuer.';
	elseif (count($circuitMainData->startposition) < 8)
		$message = $language ? 'Warning: You did not indicate all the start positions.<br />Quite annoying, we don\'t know where to begin.':'Attention : Vous n\'avez pas indiqué toutes les positions de départ !<br />C\'est ennuyeux, on ne sait pas par où commencer...';
	elseif (empty($circuitPayload->arme))
		$message = $language ? 'Warning: your course doesn\'t contain any items!<br />Hard to fight with those conditions...' :'Attention : votre arène ne contient aucun objet !<br />Difficile de se battre dans ces conditions...';
	elseif (empty($circuitPayload->aipoints))
		$message = $language ? 'Warning: you have not indicated the trajectory of CPUs. They<br />may not know where to go...' :'Attention : vous n\'avez pas indiqué<br />la trajectoire des ordis. Ils risque de ne pas<br />savoir où aller...';
	elseif (!$circuitPayload->aipoints[count($circuitPayload->aipoints)-1][0])
		$message = $language ? 'Warning: you have not connected the dots<br />indicating the trajectory of CPUs. They<br />may be stuck in the same place ...' :'Attention : vous n\'avez pas relié les points indiquant<br />la trajectoire des ordis. Ils risque de rester<br />bloqués au même endroit...';
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
		if ($cupOfCircuit = mysql_fetch_array(mysql_query('SELECT id FROM `mkcups` WHERE (circuit0="'. $nid .'" OR circuit1="'. $nid .'" OR circuit2="'. $nid .'" OR circuit3="'. $nid .'") AND mode=3 LIMIT 1'))) {
			$message = ($language ? 'This arena is part of a cup!<br /><a href="?cid='. $cupOfCircuit['id'] .'">Click here</a> to access it.':'Cette arène fait partie d\'une coupe !<br /><a href="?cid='. $cupOfCircuit['id'] .'">Cliquez ici</a> pour y accéder.');
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
	<div id="alerte"<?php if (isset($infoMsg)) echo ' class="alerte-info"'; ?>><p id="closeAlert"><a href="javascript:document.getElementById('alerte').style.display='none';void(0)">&times;</a></p><p><?php echo $message; ?></p></div>
	<?php
}
printCircuitShareUI();
include('../includes/gameInitElts.php');
?>
<?php
if ($cShared) {
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