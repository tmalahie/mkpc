<?php
$cupIDs = Array();
$infos = Array();
$isCup = false;
$isMCup = false;
include('getId.php');
include('initdb.php');
include('language.php');
require_once('utils-challenges.php');
require_once('utils-cups.php');
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
	'mode' => 2
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
var challenges = <?php echo json_encode($challenges); ?>;
var clRewards = <?php echo json_encode($clRewards); ?>;
var clId = <?php echo json_encode($clId); ?>;
var language = <?php echo ($language ? 'true':'false'); ?>;
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
var page = "AR";
var PERSOS_DIR = "<?php
	require_once('persos.php');
	echo PERSOS_DIR;
?>";
var isBattle = true;
var isCup = true;
var isSingle = <?php echo $isCup ? 'false':'true'; ?>;
var complete = false;
var simplified = true;
var nid = <?php echo isset($nid) ? $nid:'null'; ?>;
var edittingCircuit = <?php echo isset($edittingCircuit) ? 'true':'false'; ?>;
var NBCIRCUITS = 0;
function listMaps() {
	return {<?php
	include('mk/arena.php');
	?>};
}
<?php include('handleCupOptions.php'); ?>
</script>
<?php include('mk/main.php') ?>
<script type="text/javascript">
<?php
require_once('collabUtils.php');
$creationType = $isMCup ? 'mkmcups':($isCup ? 'mkcups':'mkcircuits');
$collab = getCollabLinkFromQuery($creationType, $nid);
if (isset($nid)) {
	$creator = mysql_numrows(mysql_query('SELECT * FROM `'.$creationType.'` WHERE id="'. $nid.'" AND identifiant='.$identifiants[0].' AND identifiant2='.$identifiants[1].' AND identifiant3='.$identifiants[2].' AND identifiant4='.$identifiants[3]));
	$canChange = $creator || isset($collab['rights']['view']);
	$canShare = $creator || isset($collab['rights']['edit']);
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
		xhr("<?php echo ($isMCup ? 'saveMCup' : ($isCup?'saveCup':'saveCreation')); ?>.php", "<?php
		if ($isCup) {
			echo 'mode=2';
			foreach ($cupIDs as $i=>$cupID)
				echo '&cid'. $i .'='. $cupID;
			if (!empty($cOptions))
				echo '&opt="+ encodeURIComponent(JSON.stringify(cupOpts)) +"';
		}
		else {
			echo 'map='.$infos['map'].'&battle';
			for ($i=0;$i<36;$i++)
				echo '&p'.$i.'='.$infos['p'.$i];
			for ($i=0;$i<8;$i++)
				echo '&r'.$i.'='.$infos['r'.$i].'&s'.$i.'='.$infos['s'.$i];
			for ($i=0;$i<$nbLettres;$i++) {
				$l = $lettres[$i];
				$prefixes = getLetterPrefixes($l,$infos['map']);
				for ($k=0;$k<$prefixes;$k++) {
					$prefix = getLetterPrefix($l,$k);
					for ($j=0;isset($infos[$prefix.$j]);$j++)
						echo '&'.$prefix.$j.'='.$infos[$prefix.$j];
				}
			}
		}
		if (isset($nid)) echo '&id='.$nid;
		if ($clId) echo '&cl='.$clId;
		if ($collab) echo '&collab='.$collab['key'];
		if ($isCup)
			echo '"+getCollabQuery("'. ($isMCup ? 'mkcups':'mkcircuits') .'", ['. implode(',',$cupIDs) .'])+"';
		?>&nom="+ getValue("cName") +"&auteur="+ getValue("cPseudo"), function(reponse) {
			if (reponse && !isNaN(reponse)) {
				document.getElementById("cSave").removeChild(document.getElementById("cTable"));
				var cP = document.createElement("p");
				cP.style.margin = "5px";
				cP.style.textAlign = "center";
				cP.innerHTML = '<?php
					if (isset($nid))
						echo $language ? ($isCup ? 'Cup':'Arena') .' updated successfully.':'Le partage de votre '. ($isCup ? 'coupe':'arène') .' a été mis à jour.';
					else
						echo $language ? 'Your '. ($isCup ? 'cup':'arena') .' has just been added to the <a href="creations.php" target="_blank">list</a>!':'Votre '. ($isCup ? 'coupe':'arène') .' vient d\\\'être ajoutée à la <a href="creations.php" target="_blank">liste</a> !';
				?><br /><br />';
				var cCont = document.createElement("input");
				cCont.type = "button";
				cCont.value = language ? "Continue":"Continuer";
				cCont.onclick = function() {
					document.location.href = "?<?php echo $sid; ?>="+ reponse<?php
					if ($collab) echo '+"&collab='.$collab['key'].'"';
					?>;
				};
				cP.appendChild(cCont);
				document.getElementById("cSave").appendChild(cP);
				document.getElementById("changeRace").onclick = function() {
					document.location.href = "<?php echo ($isCup ? ($isMCup ? 'simplecups':'simplecup'):'create'); ?>.php?<?php echo $sid; ?>="+ reponse +"<?php echo $isCup ? '&battle':''; ?>";
				};
				return true;
			}
			return false;
		});
	}
	<?php
	if (isset($sid)) {
		?>
	function supprRace() {
		document.getElementById("sAnnuler").disabled = true;
		document.getElementById("sAnnuler").className = "cannotChange";
		document.getElementById("sConfirmer").disabled = true;
		document.getElementById("sConfirmer").className = "cannotChange";
		xhr("<?php echo ($isMCup ? 'supprMCup':($isCup ? 'supprCup':'supprCreation')); ?>.php", "id=<?php
			echo $nid;
			if ($collab) echo '&collab='.$collab['key'];
		?>", function(reponse) {
			if (reponse == 1) {
				document.getElementById("supprInfos").innerHTML = '<?php echo $language ? 'The '. ($isCup ? 'cup':'arena') .' has been successfully removed from the list.':($isCup ? 'La coupe':'L\\\'arène') .' a été retirée de la liste avec succès.'; ?>';
				document.getElementById("supprButtons").innerHTML = '';
				var cCont = document.createElement("input");
				cCont.type = "button";
				cCont.value = language ? "Continue":"Continuer";
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
					}
					elseif ($isCup) {
						for ($i=0;$i<4;$i++) {
							if ($i)
								echo '&';
							echo 'cid'. $i .'='. $cupIDs[$i];
						}
					}
					else {
						echo 'map='.$infos['map'];
						for ($i=0;$i<36;$i++)
							echo '&p'.$i.'='.$infos['p'.$i];
						for ($i=0;$i<8;$i++)
							echo '&r'.$i.'='.$infos['r'.$i].'&s'.$i.'='.$infos['s'.$i];
						for ($i=0;$i<$nbLettres;$i++) {
							$l = $lettres[$i];
							$prefixes = getLetterPrefixes($l,$infos['map']);
							for ($k=0;$k<$prefixes;$k++) {
								$prefix = getLetterPrefix($l,$k);
								for ($j=0;isset($infos[$prefix.$j]);$j++)
									echo '&'.$prefix.$j.'='.$infos[$prefix.$j];
							}
						}
					}
					if ($clId) echo '&cl='.$clId;
					?>";
				};
				document.getElementById("supprButtons").appendChild(cCont);
				document.getElementById("changeRace").disabled = true;
				document.getElementById("shareRace").disabled = true;
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
if ($canChange && !$isCup && !isset($infos['o0'])) {
	$message = $language ? 'Warning: your course doesn\'t contain any items!<br />Hard to fight with those conditions...'
	: 'Attention : votre arène ne contient aucun objet !<br />Difficile de se battre dans ces conditions...';
}
include('ip_banned.php');
if (isBanned())
   echo '&nbsp;';
elseif ($canChange) {
	$typeStr = $isCup ? ($isMCup ? ($language ? 'multicup':'la multicoupe'):($language ? 'cup':'la coupe')):($language ? 'arena':'l\'arène');
	?>
	<input type="button" id="changeRace"<?php if (!$creator) echo ' data-collab="1"'; ?> onclick="document.location.href='<?php echo ($isCup ? ($isMCup ? 'simplecups.php':'simplecup.php'):'arene.php') ?>'+document.location.search<?php if ($isCup) echo '+\'&battle\''; ?>" value="<?php echo ($language ? 'Edit '.$typeStr:'Modifier '.$typeStr); ?>" /><br /><?php
	if ($creator && isset($nid) && !isset($_GET['nid'])) {
		?>
		<br class="br-small" />
		<input type="button" id="linkRace" onclick="showTrackCollabPopup('<?php echo $creationType ?>', <?php echo $nid; ?>)" value="<?php echo ($language ? 'Collaborate...':'Collaborer...'); ?>" /><br /><br />
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
	if ($nid)
		echo $language ? 'Edit sharing':'Modifier partage';
	else
		echo $language ? "Share $typeStr":"Partager $typeStr";
	?>"<?php if (isset($message)&&!isset($infoMsg)){echo ' disabled="disabled" class="cannotChange"';$cannotChange=true;} ?> /><?php
		if (isset($_GET[$sid])) {
			?>
		<br /><br class="br-small" /><input type="button" id="supprRace" onclick="document.getElementById('confirmSuppr').style.display='block'" value="<?php echo ($language ? 'Delete sharing':'Supprimer partage'); ?>" />
			<?php
		}
	}
}
else
	printRatingView($language ? ('Rate this '.($isMCup?'multicup':($isCup?'cup':'course')).'!'):('Notez '.($isMCup?'cette multicoupe':($isCup?'cette coupe':'cette arène'))).' !');
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
		if ($cupOfCircuit = mysql_fetch_array(mysql_query('SELECT id FROM `mkcups` WHERE (circuit0="'. $nid .'" OR circuit1="'. $nid .'" OR circuit2="'. $nid .'" OR circuit3="'. $nid .'") AND mode=2 LIMIT 1'))) {
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
<div id="alerte"<?php if (isset($infoMsg)) echo ' class="alerte-info"'; ?>><p id="closeAlert"><a href="javascript:document.getElementById('alerte').style.display='none';void(0)">&times;</a></p>
<p><?php echo $message;
?></p></div>
	<?php
}
?>
<div id="confirmSuppr">
<p id="supprInfos"><?php echo $language ?
	'Stop sharing this '. ($isCup ? ($isMCup ? 'multicup':'cup'):'arena') .'?<br />
	'.($isCup ? ($isMCup ? 'The multicup':'The cup'):'The arena').' will be only removed from the list:<br />
	data will be recoverable.' :
	'Supprimer le partage de '. ($isCup ? ($isMCup ? 'cette multicoupe':'cette coupe'):'cette arène') .' ?<br />
	'.($isCup ? ($isMCup ? 'La multicoupe':'La coupe'):'L\'arène').' sera simplement retirée de la liste :<br />
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
	<tr><td style="text-align: right"><label for="cName"><?php echo $language ? ($isCup ? ($isMCup ? 'Multicup':'Cup'):'Arena').' name':'Nom '.($isCup ? ($isMCup?'de la multicoupe':'de la coupe'):'de l\'arène'); ?> :</label></td><td><input type="text" name="cName" id="cName" value="<?php echo escapeUtf8($cName) ?>" /></td></tr>
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