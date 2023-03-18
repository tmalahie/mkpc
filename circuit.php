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
if (isset($_GET['mid'])) { // Existing multicup
	$id = intval($_GET['mid']);
	$nid = $id;
	$isCup = true;
	$isMCup = true;
	if ($getMCup = mysql_fetch_array(mysql_query('SELECT * FROM `mkmcups` WHERE id="'. $id .'" AND mode=0'))) {
		$cName = $getMCup['nom'];
		$infos['name'] = $cName;
		$cPseudo = $getMCup['auteur'];
		$cAuteur = $cPseudo;
		$pNote = $getMCup['note'];
		$pNotes = $getMCup['nbnotes'];
		$cDate = $getMCup['publication_date'];
		$cOptions = $getMCup['options'];
		$getCups = mysql_query('SELECT cup FROM `mkmcups_tracks` WHERE mcup="'. $id .'" ORDER BY ordering');
		$cupIDs = array();
		while ($getCup = mysql_fetch_array($getCups))
			$cupIDs[] = $getCup['cup'];
		addCircuitChallenges('mkmcups', $nid,$cName, $clPayloadParams);
	}
}
elseif (isset($_GET['cid'])) { // Existing cup
	$id = intval($_GET['cid']);
	$nid = $id;
	$isCup = true;
	if ($getCup = mysql_fetch_array(mysql_query('SELECT * FROM `mkcups` WHERE id="'. $id .'" AND mode=0'))) {
		$cName = $getCup['nom'];
		$infos['name'] = $cName;
		$cPseudo = $getCup['auteur'];
		$cAuteur = $cPseudo;
		$pNote = $getCup['note'];
		$pNotes = $getCup['nbnotes'];
		$cDate = $getCup['publication_date'];
		for ($i=0;$i<4;$i++)
			$cupIDs[$i] = $getCup['circuit'. $i];
		$trackIDs = $cupIDs;
		addCircuitChallenges('mkcups', $nid,$cName, $clPayloadParams);
	}
}
elseif (isset($_GET['id'])) { // Existing track
	$id = intval($_GET['id']);
	$nid = $id;
	$trackIDs = array($id);
	$hthumbnail = 'https://mkpc.malahieude.net/mappreview.php?id='.$id;
	$cShared = true;
}
elseif (isset($_GET['cid0']) && isset($_GET['cid1']) && isset($_GET['cid2']) && isset($_GET['cid3'])) { // Cup being created
	$isCup = true;
	if (isset($_GET['nid'])) { // Cup being edited
		$nid = intval($_GET['nid']);
		if ($getMain = mysql_fetch_array(mysql_query('SELECT nom,auteur,note,nbnotes,publication_date FROM `mkcups` WHERE id="'. $nid .'" AND mode=0'))) {
			$cName = $getMain['nom'];
			$cPseudo = $getMain['auteur'];
			$cAuteur = $cPseudo;
			$pNote = $getMain['note'];
			$pNotes = $getMain['nbnotes'];
			$cDate = $getMain['publication_date'];
			addCircuitChallenges('mkcups', $nid,$cName, $clPayloadParams);
		}
	}
	else
		$cPseudo = isset($_COOKIE['mkauteur']) ? $_COOKIE['mkauteur']:null;
	for ($i=0;$i<4;$i++)
		$cupIDs[$i] = intval($_GET['cid'. $i]);
	$trackIDs = $cupIDs;
	$edittingCircuit = true;
}
elseif (isset($_GET['mid0'])) { // Multicups being created
	$isCup = true;
	$isMCup = true;
	if (isset($_GET['nid'])) { // Multicups being edited
		$nid = intval($_GET['nid']);
		if ($getMain = mysql_fetch_array(mysql_query('SELECT nom,auteur,note,nbnotes,publication_date FROM `mkmcups` WHERE id="'. $nid .'" AND mode=0'))) {
			$cName = $getMain['nom'];
			$cPseudo = $getMain['auteur'];
			$cAuteur = $cPseudo;
			$pNote = $getMain['note'];
			$pNotes = $getMain['nbnotes'];
			$cDate = $getMain['publication_date'];
			addCircuitChallenges('mkmcups', $nid,$cName, $clPayloadParams);
		}
	}
	else
		$cPseudo = isset($_COOKIE['mkauteur']) ? $_COOKIE['mkauteur']:null;
	for ($i=0;isset($_GET['mid'.$i])&&is_numeric($_GET['mid'.$i]);$i++)
		$cupIDs[$i] = intval($_GET['mid'.$i]);
	$cOptions = isset($_GET['opt']) ? json_decode(stripslashes($_GET['opt'])) : null;
	if ($cOptions) $cOptions = json_encode($cOptions);
	$edittingCircuit = true;
}
else { // Track being created
	if (isset($_GET['nid'])) { // Track being edited
		$nid = intval($_GET['nid']);
		require_once('collabUtils.php');
		$requireOwner = !hasCollabGrants('mkcircuits', $nid, $_GET['collab'], 'view');
		if ($getMain = mysql_fetch_array(mysql_query('SELECT nom,auteur,note,nbnotes,publication_date FROM `mkcircuits` WHERE id="'. $nid .'" AND !type'. ($requireOwner ? (' AND identifiant="'. $identifiants[0] .'" AND identifiant2="'. $identifiants[1] .'" AND identifiant3="'. $identifiants[2] .'" AND identifiant4="'. $identifiants[3] .'"') : '')))) {
			$infos['id'] = $nid;
			$cName = $getMain['nom'];
			$cPseudo = $getMain['auteur'];
			$cAuteur = $cPseudo;
			$pNote = $getMain['note'];
			$pNotes = $getMain['nbnotes'];
			$cDate = $getMain['publication_date'];
			addCircuitChallenges('mkcircuits', $nid,$cName, $clPayloadParams);
		}
		else {
			mysql_close();
			exit;
		}
	}
	else
		$cPseudo = isset($_COOKIE['mkauteur']) ? $_COOKIE['mkauteur']:null;
	for ($i=0;$i<36;$i++)
		$infos["p$i"] = (isset($_GET["p$i"])) ? intval($_GET["p$i"]) : 11;
	$infos['map'] = (isset($_GET["map"])) ? intval($_GET["map"]) : 1;
	$infos['laps'] = (isset($_GET["nl"])) ? intval($_GET["nl"]) : 3;
	$infos['name'] = '';
	for ($i=0;$i<$nbLettres;$i++) {
		$lettre = $lettres[$i];
		$prefixes = getLetterPrefixes($lettre,$infos['map']);
		for ($k=0;$k<$prefixes;$k++) {
			$prefix = getLetterPrefix($lettre,$k);
			for ($j=0;isset($_GET[$prefix.$j]);$j++)
				$infos[$prefix.$j] = $_GET[$prefix.$j];
		}
	}
	$edittingCircuit = true;
}
$cupNames = array();
if ($isMCup && !isset($trackIDs)) {
	$trackIDs = array();
	if (!empty($cupIDs)) {
		$cupsTracks = array();
		$cupNamesById = array();
		$getAllCircuits = mysql_query('SELECT id,nom,circuit0,circuit1,circuit2,circuit3 FROM `mkcups` WHERE id IN ('. implode(',',$cupIDs) .') AND mode=0');
		while ($getCup = mysql_fetch_array($getAllCircuits)) {
			$cupTracks = array();
			for ($i=0;$i<4;$i++)
				$cupTracks[] = $getCup['circuit'.$i];
			$cupsTracks[$getCup['id']] = $cupTracks;
			$cupNamesById[$getCup['id']] = $getCup['nom'];
			addCircuitChallenges('mkcups', $getCup['id'],$getCup['nom'], $clPayloadParams, false);
		}
		foreach ($cupIDs as $cupID) {
			if (isset($cupsTracks[$cupID])) {
				foreach ($cupsTracks[$cupID] as $cupTrack)
					$trackIDs[] = $cupTrack;
			}
			if (isset($cupNamesById[$cupID]))
				$cupNames[] = $cupNamesById[$cupID];
		}
	}
}
if (isset($trackIDs)) {
	foreach ($trackIDs as $i=>$trackID) {
		if (!is_numeric($trackID))
			$trackIDs[$i] = 0;
	}
	$circuitsData = array();
	if (!empty($trackIDs)) {
		$getAllTracks = mysql_query('SELECT id,map,laps,nom,auteur,note,nbnotes,publication_date FROM `mkcircuits` WHERE id IN ('. implode(',',$trackIDs) .') AND !type');
		$allTracks = array();
		while ($getMain = mysql_fetch_array($getAllTracks))
			$allTracks[$getMain['id']] = $getMain;
		foreach ($trackIDs as $trackID) {
			if (isset($allTracks[$trackID])) {
				$getMain = $allTracks[$trackID];
				$infos = array();
				$infos['id'] = $trackID;
				$infos['map'] = $getMain['map'];
				$infos['name'] = $getMain['nom'];
				$infos['laps'] = $getMain['laps'];
				$infos['note'] = $getMain['note'];
				$infos['nbnotes'] = $getMain['nbnotes'];
				$infos['auteur'] = $getMain['auteur'];
				$infos['publication_date'] = $getMain['publication_date'];
				$pieces = mysql_query('SELECT * FROM `mkp` WHERE circuit="'.$trackID.'"');
				while ($piece = mysql_fetch_array($pieces))
					$infos['p'.$piece['id']] = $piece['piece'];
				for ($j=0;$j<$nbLettres;$j++) {
					$lettre = $lettres[$j];
					$getInfos = mysql_query('SELECT * FROM `mk'.$lettre.'` WHERE circuit="'.$trackID.'"');
					$incs = array();
					while ($info=mysql_fetch_array($getInfos)) {
						$prefix = getLetterPrefixD($lettre,$info);
						if (!isset($incs[$prefix])) $incs[$prefix] = 0;
						$infos[$prefix.$incs[$prefix]] = $info['x'].','.$info['y'];
						$incs[$prefix]++;
					}
				}
				$circuitsData[] = $infos;
				addCircuitChallenges('mkcircuits', $trackID,$infos['name'], $clPayloadParams, !$isCup);
			}
		}
	}
	if (!$isCup && isset($circuitsData[0])) {
		$infos = $circuitsData[0];
		$cName = $infos['name'];
		$cPseudo = $infos['auteur'];
		$cAuteur = $cPseudo;
		$pNote = $infos['note'];
		$pNotes = $infos['nbnotes'];
		$cDate = $infos['publication_date'];
	}
}
elseif (!empty($infos))
	$circuitsData = Array($infos);
else {
	mysql_close();
	exit;
}
if ($isCup)
	$infos = Array();
$NBCIRCUITS = count($circuitsData);
if (!$NBCIRCUITS) {
	mysql_close();
	exit;
}
addClChallenges($nid, $clPayloadParams);
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
	return {<?php
	include('mk/circuit.php');
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
			echo 'mode=0';
			foreach ($cupIDs as $i=>$cupID)
				echo '&cid'. $i .'='. $cupID;
			if (!empty($cOptions))
				echo '&opt="+ encodeURIComponent(JSON.stringify(cupOpts)) +"';
		}
		else {
			echo 'map='.$infos['map'].'&nl='.$infos['laps'];
			for ($i=0;$i<36;$i++)
				echo '&p'.$i.'='.$infos['p'.$i];
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
						echo $language ? ($isCup ? 'Cup':'Circuit') .' updated successfully.':'Le partage de votre '. ($isCup ? 'coupe':'circuit') .' a été mis à jour.';
					else
						echo $language ? 'Your '. ($isCup ? 'cup':'circuit') .' has just been added to the <a href="creations.php" target="_blank">list</a>!':'Votre '. ($isCup ? 'coupe':'circuit') .' vient d\\\'être ajouté à la <a href="creations.php" target="_blank">liste</a> !';
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
					document.location.href = "<?php echo ($isCup ? ($isMCup ? 'simplecups':'simplecup'):'create'); ?>.php?<?php echo $sid; ?>="+ reponse;
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
				document.getElementById("supprInfos").innerHTML = '<?php echo $language ? ($isCup ? 'The cup':'The circuit').' has been successfully removed from the list.':($isCup ? 'La coupe':'Le circuit').' a été retiré'. ($isCup ? 'e':'') .' de la liste avec succès.'; ?>';
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
						echo 'map='.$infos['map'].'&nl='.$infos['laps'];
						for ($i=0;$i<36;$i++)
							echo '&p'.$i.'='.$infos['p'.$i];
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
if ($canChange && !$isCup) {
	if ($noStart)
		$message = $language ? 'Warning: your circuit doesn\'t have a start.<br />Quite annoying, we don\'t know where to begin.':'Attention : Votre circuit n\'a pas de départ !<br />C\'est ennuyeux, on ne sait pas par où commencer...';
	elseif ($twoStarts)
		$message = $language ? 'Warning: your circuit has more than one start.<br />It\'s hard to know which one to choose.':'Attention : votre circuit comporte plusieurs départs !<br />Difficile de savoir lequel choisir...';
	elseif ($bloqued)
		$message = $language ? 'Warning: your circuit has to make a loop.<br />Otherwise it\'s impossible to know how to make a lap...':'Attention : votre circuit doit former une boucle.<br />Impossible de faire un tour sinon...';
}
include('ip_banned.php');
if (isBanned())
   echo '&nbsp;';
elseif ($canChange) {
	$typeStr = $isCup ? ($isMCup ? ($language ? 'multicup':'la multicoupe'):($language ? 'cup':'la coupe')):($language ? 'circuit':'le circuit');
	?>
	<input type="button" id="changeRace"<?php if (!$creator) echo ' data-collab="1"'; ?> onclick="document.location.href='<?php echo ($isCup ? ($isMCup ? 'simplecups.php':'simplecup.php'):'create.php') ?>'+document.location.search" value="<?php echo ($language ? 'Edit '.$typeStr:'Modifier '.$typeStr); ?>" /><br /><?php
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
	<script type="text/javascript" src="scripts/comments.js"></script>
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