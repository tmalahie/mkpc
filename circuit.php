<?php
$cupIDs = Array();
$infos = Array();
$isCup = false;
$isMCup = false;
include('getId.php');
include('initdb.php');
include('language.php');
require_once('utils-challenges.php');
include('creation-challenges.php');
require_once('circuitPrefix.php');
$cName = null;
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
require_once('circuitEscape.php');
function escapeUtf8($str) {
	return htmlentities(escapeCircuitNames($str));
}
?>
<!DOCTYPE HTML SYSTEM>
<html>
	<head>
		<title><?php if ($cName){echo escapeUtf8($cName);echo ' - ';} ?>Mario Kart PC</title>
<?php
include('metas.php');

include('c_mariokart.php');
include('c_collab.php');
include('c_comments.php');

include('o_online.php');
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
	echo '"'. ($circuit['name'] ? addSlashes(escapeUtf8($circuit['name'])) : "&nbsp;") .'"';
}
?>];
var cupIDs = <?php echo json_encode($cupIDs) ?>;
var cupOpts = <?php echo empty($cOptions) ? '{}':$cOptions; ?>;
<?php
if (!empty($cupNames)) {
	echo 'var cupNames = [';
	foreach ($cupNames as $i=>$cupName) {
		if ($i) echo ',';
		echo '"'.addSlashes(escapeUtf8($cupName)).'"';
	}
	echo '];';
}
?>
var cp = <?php include('getPersos.php'); ?>;
var pUnlocked = <?php include('getLocks.php'); ?>;
var baseOptions = <?php include('getCourseOptions.php'); ?>;
var page = "CI";
<?php include('getCupScore.php'); ?>
var PERSOS_DIR = "<?php
	require_once('persos.php');
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
<?php include('handleCupOptions.php'); ?>
</script>
<?php include('mk/main.php') ?>
<script type="text/javascript">
<?php
require_once('circuit-actions.php');
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
<table cellpadding="3" cellspacing="0" border="0" id="options">
<tr>
<td id="pSize">&nbsp;</td>
<td id="vSize">
</td>
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
?>
<div id="confirmSuppr">
<p id="supprInfos"><?php echo $language ?
	'Stop sharing this '. ($isCup ? ($isMCup ? 'multicup':'cup'):'circuit') .'?<br />
	'.($isCup ? ($isMCup ? 'The multicup':'The cup'):'The circuit').' will be only removed from the list:<br />
	data will be recoverable.' :
	'Supprimer le partage de '. ($isCup ? ($isMCup ? 'cette multicoupe':'cette coupe'):'ce circuit') .' ?<br />
	'.($isCup ? ($isMCup ? 'La multicoupe':'La coupe'):'Le circuit').' sera simplement retiré'.($isCup?'e':'').' de la liste :<br />
	les données seront récupérables.';
?></p>
<p id="supprButtons"><input type="button" value="<?php echo $language ? 'Cancel':'Annuler'; ?>" id="sAnnuler" onclick="document.getElementById('confirmSuppr').style.display='none'" /> &nbsp; <input type="button" value="<?php echo $language ? 'Delete':'Supprimer'; ?>" id="sConfirmer" onclick="supprRace()" /></p>
</div>
<?php
if (!isset($cannotChange)) {
	?>
	<form id="cSave" method="post" action="" onsubmit="saveRace();return false">
	<table id="cTable">
	<tr><td style="text-align: right"><label for="cPseudo"><?php echo $language ? 'Enter your nick':'Indiquez votre pseudo'; ?> :</label></td><td><input type="text" name="cPseudo" id="cPseudo" value="<?php echo escapeUtf8($cPseudo) ?>" /></td></tr>
	<tr><td style="text-align: right"><label for="cName"><?php echo $language ? ($isCup ? ($isMCup ? 'Multicup':'Cup'):'Circuit').' name':'Nom '.($isCup ? ($isMCup?'de la multicoupe':'de la coupe'):'du circuit'); ?> :</label></td><td><input type="text" name="cName" id="cName" value="<?php echo escapeUtf8($cName) ?>" /></td></tr>
	<tr><td colspan="2" id="cSubmit"><input type="button" value="<?php echo $language ? 'Cancel':'Annuler'; ?>" id="cAnnuler" onclick="document.getElementById('cSave').style.display='none'" /> &nbsp; <input type="submit" value="<?php echo $language ? 'Share':'Partager'; ?>" id="cEnregistrer" /></td></tr>
	</table>
	</form>
	<?php
}
include('gameInitElts.php');
?>
<?php
if (isset($nid)) {
	include('circuitUser.php');
	require_once('reactions.php');
	printReactionUI();
	$circuitTable = $isMCup ? 'mkmcups' : ($isCup?'mkcups':'mkcircuits');
	?>
	<div id="comments-section"></div>
	<script type="text/javascript">
	var commentCircuit = <?php echo $nid; ?>, commentType = "<?php echo $circuitTable; ?>",
	circuitName = "<?php echo addSlashes(escapeUtf8($cName)) ?>", circuitAuthor = "<?php echo addSlashes(escapeUtf8($cAuteur)) ?>", circuitNote = <?php echo $pNote ?>, circuitNotes = <?php echo $pNotes ?>,
	circuitDate = "<?php echo formatDate($cDate); ?>";
	var circuitUser = <?php echo findCircuitUser($cAuteur,$nid,$circuitTable); ?>;
	</script>
	<script type="text/javascript" src="scripts/comments.js?reload=1"></script>
	<script type="text/javascript" src="scripts/topic.js"></script>
	<?php
}
?>
<?php include('mk/description.php'); ?>
</body>
</html>
<?php
mysql_close();
?>