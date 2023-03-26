<?php
include('initdb.php');
include('language.php');
require_once('utils-challenges.php');
include('creation-challenges.php');
$cAuteur = null;
$cupIDs = Array();
include('getId.php');
$cName = null;
$cPseudo = null;
$cAuteur = null;
$cDate = null;
$cShared = false;
$cEditting = false;
$pNote = 0;
$pNotes = 0;
getTrackPayloads(array(
	'sid' => 'i',
	'mode' => 1
));
$sid = ($isMCup ? 'mid' : ($isCup ? 'cid':'i'));
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
	echo '"'. ($circuit['nom'] ? addSlashes(escapeUtf8($circuit['nom'])) : "&nbsp;") .'"';
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
var page = "MA";
<?php include('getCupScore.php'); ?>
var PERSOS_DIR = "<?php
	require_once('persos.php');
	echo PERSOS_DIR;
?>";
var cShared = <?php echo $cShared ? 'true':'false'; ?>;
var isBattle = false;
var isCup = true;
var isSingle = <?php echo $isCup ? 'false':'true'; ?>;
var complete = true;
var simplified = false;
var nid = <?php echo isset($nid) ? $nid:'null'; ?>;
var edittingCircuit = <?php echo isset($edittingCircuit) ? 'true':'false'; ?>;
var NBCIRCUITS = <?php echo $NBCIRCUITS; ?>;
function listMaps() {
	return {<?php
	include('mk/map.php');
	?>};
}
<?php include('handleCupOptions.php'); ?>
</script>
<?php include('mk/main.php') ?>
<script type="text/javascript">
<?php
require_once('collabUtils.php');
$creationType = $isMCup ? 'mkmcups':($isCup ? 'mkcups':'circuits');
$collab = getCollabLinkFromQuery($creationType, $nid);
if (isset($nid)) {
	if (isset($creationData)) {
		$creator = ($creationData['identifiant'] == $identifiants[0]) && ($creationData['identifiant2'] == $identifiants[1]) && ($creationData['identifiant3'] == $identifiants[2]) && ($creationData['identifiant4'] == $identifiants[3]);
		$canChange = $creator || isset($collab['rights']['view']);
		$canShare = $creator || isset($collab['rights']['edit']);
	}
	else {
		$creator = false;
		$canChange = false;
		$canShare = false;
	}
}
else {
	$creator = true;
	$canChange = true;
	$canShare = true;
}
if ($canChange) {
	?>
	function saveRace() {
		document.getElementById("cAnnuler").disabled = true;
		document.getElementById("cAnnuler").className = "cannotChange";
		document.getElementById("cEnregistrer").disabled = true;
		document.getElementById("cEnregistrer").className = "cannotChange";
		xhr("<?php echo ($isMCup ? 'saveMCup' : ($isCup?'saveCup':'saveDraw')); ?>.php", "<?php
			if ($isCup) {
				echo 'mode=1';
				foreach ($cupIDs as $i=>$cupID)
					echo '&cid'. $i .'='. $cupID;
				if (!empty($cOptions))
					echo '&opt="+ encodeURIComponent(JSON.stringify(cupOpts)) +"';
				echo '&';
			}
			if (isset($nid)) echo 'id='.$nid;
			if ($clId) echo '&cl='.$clId;
			if ($collab) echo '&collab='.$collab['key'];
			if ($isCup)
				echo '"+getCollabQuery("'. ($isMCup ? 'mkcups':'circuits') .'", ['. implode(',',$cupIDs) .'])+"';
			?>&nom="+ getValue("cName") +"&auteur="+ getValue("cPseudo"), function(reponse) {
			if (reponse && !isNaN(reponse)) {
				document.getElementById("cSave").removeChild(document.getElementById("cTable"));
				var cP = document.createElement("p");
				cP.style.margin = "5px";
				cP.style.textAlign = "center";
				cP.innerHTML = '<?php
					if ($cShared)
						echo $language ? ($isCup ? 'Cup':'Circuit') .' updated successfully.':'Le partage de votre '. ($isCup ? 'coupe':'circuit') .' a été mis à jour.';
					else
						echo $language ? 'Your '. ($isCup ? 'cup':'circuit') .' has just been added to the <a href="creations.php" target="_blank">list</a>!':'Votre '. ($isCup ? 'coupe':'circuit') .' vient d\\\'être ajouté à la <a href="creations.php" target="_blank">liste</a> !';
				?><br /><br />';
				var cCont = document.createElement("input");
				cCont.type = "button";
				cCont.value = language ? "Continue":"Continuer";
				cCont.onclick = function() {
					<?php
					if ($isCup) {
						echo 'document.location.href = "?'.$sid.'="+ reponse';
						if ($collab) echo '+"&collab='.$collab['key'].'"';
						echo ';';
					}
					else
						echo 'location.reload();';
					?>
				};
				cP.appendChild(cCont);
				document.getElementById("cSave").appendChild(cP);
				<?php
				if ($isCup) {
					?>
					document.getElementById("changeRace").onclick = function() {
						document.location.href = "<?php echo $isMCup ? 'completecups.php?mid=':'completecup.php?cid='; ?>"+ reponse;
					};
					<?php
				}
				?>
				return true;
			}
			return false;
		});
	}
	<?php
	if ($cShared) {
		?>
	function supprRace() {
		document.getElementById("sAnnuler").disabled = true;
		document.getElementById("sAnnuler").className = "cannotChange";
		document.getElementById("sConfirmer").disabled = true;
		document.getElementById("sConfirmer").className = "cannotChange";
		xhr("<?php echo ($isMCup ? 'supprMCup':($isCup ? 'supprCup':'supprDraw')); ?>.php", "id=<?php
			echo $nid;
			if ($collab) echo '&collab='.$collab['key'];
		?>", function(reponse) {
			if (reponse == 1) {
				document.getElementById("supprInfos").innerHTML = '<?php echo $language ? 'The '. ($isCup ? 'cup':'circuit') .' has been successfully removed from the list.':($isCup ? 'La coupe':'Le circuit') .' a été retiré de la liste avec succès.'; ?>';
				document.getElementById("supprButtons").innerHTML = '';
				var cCont = document.createElement("input");
				cCont.type = "button";
				cCont.value = language ? "Continue" : "Continuer";
				cCont.onclick = function() {
					document.location.href = "?<?php
					if ($isMCup) {
						foreach ($cupIDs as $i => $cupID) {
							if ($i)
								echo '&';
							echo 'mid'. $i .'='. $cupIDs[$i];
						}
						if (!empty($cOptions))
							echo '&opt='. urlencode($cOptions);
						if ($clId) echo '&cl='.$clId;
					}
					elseif ($isCup) {
						for ($i=0;$i<4;$i++) {
							if ($i)
								echo '&';
							echo 'cid'. $i .'='. $cupIDs[$i];
						}
						if ($clId) echo '&cl='.$clId;
					}
					else
						echo 'i='.$nid;
					if ($collab) echo '&collab='.$collab['key'];
					?>";
				};
				document.getElementById("supprButtons").appendChild(cCont);
				return true;
			}
			return false;
		});
	}
		<?php
	}
	?>
	function getValue(name) {
		return encodeURIComponent(document.getElementById(name).value);
	}
<?php
}
else {
	require_once('utils-ratings.php');
	$cNote = getMyRating($creationType, $nid);
	?>
	var cNote = <?php echo $cNote ?>;
	var ratingParams = "id=<?php
		echo $nid;
		if ($isMCup)
			echo '&mc=1';
		elseif ($isCup)
			echo '&cup=1';
		else
			echo '&complete=1';
	?>";
	<?php
}
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
	function isCpOob() {
		global $circuitMainData, $nCps;
		$cps = $circuitMainData->sections;
		foreach ($cps as $cp) {
			if ($cp >= $nCps)
				return true;
		}
		return false;
	}
	if (!isset($circuitMainData->bgcolor))
		$message = $language ? 'Warning: You didn\'t specify any data for the circuit.<br />Go back to the editor before testing it.':'Attention : vous n\'avez pas encore spécifié les paramètres du circuit.<br />Revenez dans l\'éditeur avant de continuer.';
	elseif (($circuitMainData->startposition[0] == -1) && ($circuitMainData->startposition[1] == -1))
		$message = $language ? 'Warning: your circuit doesn\'t have a start.<br />Quite annoying, we don\'t know where to begin.':'Attention : Votre circuit n\'a pas de départ !<br />C\'est ennuyeux, on ne sait pas par où commencer...';
	elseif (empty($circuitPayload->aipoints[0]))
		$message = $language ? 'Warning, you didn\'t specify the CPUs route.<br />They could work so well...':'Attention : Vous n\'avez pas indiqué le trajet des ordis.<br />Ils risquent de marcher beaucoup moins bien...';
	elseif (!($nCps=count($circuitPayload->checkpoint)))
		$message = $language ? 'Warning, you didn\'t specify the checkpoints.<br />We can not find your way in the circuit!':'Attention : Vous n\'avez pas indiqué les checkpoints.<br />Impossible de vous repérer dans le circuit !';
	elseif (isset($circuitMainData->sections) && isCpOob())
		$message = $language ? 'Warning, one or several sections are mapped<br />to a non-existing checkpoint.<br />The circuit will likely be unfinishable...':'Attention, une ou plusieurs sections<br />sont associéesà un checkpoint inexistant.<br />Le circuit risque d\'être difficile à terminer...';
}
include('ip_banned.php');
if (isBanned())
	echo '&nbsp;';
elseif ($canChange) {
	$typeStr = $isCup ? ($isMCup ? ($language ? 'multicup':'la multicoupe'):($language ? 'cup':'la coupe')):($language ? 'circuit':'le circuit');
	?>
	<input type="button" id="changeRace"<?php if (!$creator) echo ' data-collab="1"'; ?> onclick="document.location.href='<?php
		echo ($isCup ? ($isMCup ? 'completecups.php':'completecup.php'):'draw.php'); ?>'+document.location.search<?php
	?>" value="<?php echo ($language ? 'Edit '.$typeStr:'Modifier '.$typeStr); ?>" /><br />
	<?php
	if ($creator && isset($nid) && !isset($_GET['nid'])) {
		?>
		<br class="br-small" />
		<input type="button" id="linkRace" onclick="showTrackCollabPopup('<?php echo $creationType; ?>', <?php echo $nid; ?>)" value="<?php echo ($language ? 'Collaborate...':'Collaborer...'); ?>" /><br /><br />
		<?php
	}
	else {
		?>
		<br />
		<?php
	}
	if ($canShare) {
		?>
	<input type="button" id="shareRace" onclick="document.getElementById('cSave').style.display='block'" value="<?php
	if ($cShared)
		echo $language ? 'Edit sharing':'Modifier partage';
	else
		echo $language ? "Share $typeStr":"Partager $typeStr";
	?>"<?php if (isset($message)&&!isset($infoMsg)){echo ' disabled="disabled" class="cannotChange"';$cannotChange=true;} ?> /><?php
		if ($cShared && !$cEditting) {
			?>
	<br /><br class="br-small" /><input type="button" id="supprRace" onclick="document.getElementById('confirmSuppr').style.display='block'" value="<?php echo ($language ? 'Delete sharing':'Supprimer partage'); ?>" />
			<?php
		}
	}
}
else
	printRatingView($language ? ('Rate this '.($isMCup?'multicup':($isCup?'cup':'circuit')).'!'):('Notez '.($isMCup?'cette multicoupe':($isCup?'cette coupe':'ce circuit'))).' !');
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
		if ($cupOfCircuit = mysql_fetch_array(mysql_query('SELECT id FROM `mkcups` WHERE (circuit0="'. $nid .'" OR circuit1="'. $nid .'" OR circuit2="'. $nid .'" OR circuit3="'. $nid .'") AND mode=1 LIMIT 1'))) {
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
	<div id="alerte"<?php if (isset($infoMsg)) echo ' class="alerte-info"'; ?>><p id="closeAlert"><a href="javascript:document.getElementById('alerte').style.display='none';void(0)">&times;</a></p><p><?php echo $message; ?></p></div>
	<?php
}
?>
<div id="confirmSuppr">
<p id="supprInfos"><?php echo $language ?
	'Stop sharing this circuit?<br />
	The circuit will be only removed from the list:<br />
	data will be retained.' :
	'Supprimer le partage de ce circuit ?<br />
	Le circuit sera simplement retiré de la liste :<br />
	les données seront conservées.';
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
if ($cShared) {
	include('circuitUser.php');
	require_once('reactions.php');
	printReactionUI();
	$circuitTable = $isMCup ? 'mkmcups' : ($isCup?'mkcups':'circuits');
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
