<?php
include('initdb.php');
include('language.php');
require_once('utils-challenges.php');
mysql_set_charset('utf8');
include('creation-challenges.php');
$cAuteur = null;
$cupIDs = Array();
include('getId.php');
if (isset($_GET['cid0']) && isset($_GET['cid1']) && isset($_GET['cid2']) && isset($_GET['cid3'])) { // Cup being created
	$isCup = true;
	if (isset($_GET['nid'])) { // Cup being edited
		$nid = $_GET['nid'];
		if ($getMain = mysql_fetch_array(mysql_query('SELECT nom,auteur,note,nbnotes,publication_date,identifiant,identifiant2,identifiant3,identifiant4 FROM `mkcups` WHERE id="'. $nid .'" AND mode=1'))) {
			$cName = $getMain['nom'];
			$cPseudo = $getMain['auteur'];
			$cAuteur = $cPseudo;
			$cDate = $getMain['publication_date'];
			$pNote = $getMain['note'];
			$pNotes = $getMain['nbnotes'];
			$creationData = $getMain;
			$cShared = true;
			addCircuitChallenges($challenges, 'mkcups', $nid,$cName, $clPayloadParams);
		}
	}
	else {
		$cPseudo = isset($_COOKIE['mkauteur']) ? $_COOKIE['mkauteur']:null;
		$cShared = false;
	}
	for ($c=0;$c<4;$c++)
		$cupIDs[$c] = $_GET['cid'. $c];
	$trackIDs = $cupIDs;
	$edittingCircuit = true;
}
elseif (isset($_GET['mid0'])) { // Multicups being created
	$isCup = true;
	$isMCup = true;
	if (isset($_GET['nid'])) { // Multicups being edited
		include('escape_all.php');
		$nid = $_GET['nid'];
		if ($getMain = mysql_fetch_array(mysql_query('SELECT nom,auteur,note,nbnotes,publication_date,identifiant,identifiant2,identifiant3,identifiant4 FROM `mkmcups` WHERE id="'. $nid .'" AND mode=1 AND identifiant="'. $identifiants[0] .'" AND identifiant2="'. $identifiants[1] .'" AND identifiant3="'. $identifiants[2] .'" AND identifiant4="'. $identifiants[3] .'"'))) {
			$cName = $getMain['nom'];
			$cPseudo = $getMain['auteur'];
			$cAuteur = $cPseudo;
			$pNote = $getMain['note'];
			$pNotes = $getMain['nbnotes'];
			$cDate = $getMain['publication_date'];
			$creationData = $getMain;
			addCircuitChallenges($challenges, 'mkmcups', $nid,$cName, $clPayloadParams);
		}
	}
	else
		$cPseudo = isset($_COOKIE['mkauteur']) ? $_COOKIE['mkauteur']:null;
	for ($i=0;isset($_GET['mid'.$i])&&is_numeric($_GET['mid'.$i]);$i++)
		$cupIDs[$i] = $_GET['mid'.$i];
	$edittingCircuit = true;
}
elseif (isset($_GET['mid'])) { // Existing multicup
	$id = $_GET['mid'];
	$nid = $id;
	$isCup = true;
	$isMCup = true;
	if ($getMCup = mysql_fetch_array(mysql_query('SELECT * FROM `mkmcups` WHERE id="'. $id .'" AND mode=1'))) {
		$cName = $getMCup['nom'];
		$cPseudo = $getMCup['auteur'];
		$cAuteur = $cPseudo;
		$cDate = $getMCup['publication_date'];
		$pNote = $getMCup['note'];
		$pNotes = $getMCup['nbnotes'];
		$creationData = $getMCup;
		$cShared = true;
		$getCups = mysql_query('SELECT cup FROM `mkmcups_tracks` WHERE mcup="'. $id .'" ORDER BY ordering');
		$cupIDs = array();
		while ($getCup = mysql_fetch_array($getCups))
			$cupIDs[] = $getCup['cup'];
		addCircuitChallenges($challenges, 'mkmcups', $nid,$cName, $clPayloadParams);
	}
}
elseif (isset($_GET['cid'])) { // Existing cup
	$nid = $_GET['cid'];
	$isCup = true;
	if ($getCup = mysql_fetch_array(mysql_query('SELECT * FROM `mkcups` WHERE id="'. $nid .'" AND mode=1'))) {
		$cName = $getCup['nom'];
		$cPseudo = $getCup['auteur'];
		$cAuteur = $cPseudo;
		$cDate = $getCup['publication_date'];
		$pNote = $getCup['note'];
		$pNotes = $getCup['nbnotes'];
		$creationData = $getCup;
		$cShared = true;
		for ($i=0;$i<4;$i++)
			$cupIDs[$i] = $getCup['circuit'. $i];
		$trackIDs = $cupIDs;
		addCircuitChallenges($challenges, 'mkcups', $nid,$cName, $clPayloadParams);
	}
}
else { // Existing track
	$isCup = false;
	$id = isset($_GET['i']) ? $_GET['i']:0;
	$nid = $id;
	if ($circuit = mysql_fetch_array(mysql_query('SELECT c.*,d.data,(nom IS NOT NULL) as shared FROM `circuits` c LEFT JOIN `circuits_data` d ON c.id=d.id WHERE c.id="'.$id.'"'))) {
		$cShared = $circuit['shared'];
		if ($cShared) {
			$cName = $circuit['nom'];
			$cPseudo = $circuit['auteur'];
		}
		else
			$cPseudo = $_COOKIE['mkauteur'];
		$cAuteur = $circuit['auteur'];
		$cDate = $circuit['publication_date'];
		$pNote = $circuit['note'];
		$pNotes = $circuit['nbnotes'];
		$creationData = $circuit;
		addCircuitChallenges($challenges, 'circuits', $nid,$circuit['nom'], $clPayloadParams);
	}
	else {
		mysql_close();
		exit;
	}
}
$cupNames = array();
if ($isMCup && !isset($trackIDs)) {
	$trackIDs = array();
	if (!empty($cupIDs)) {
		$cupsTracks = array();
		$cupNamesById = array();
		$getAllCircuits = mysql_query('SELECT id,nom,circuit0,circuit1,circuit2,circuit3 FROM `mkcups` WHERE id IN ('. implode($cupIDs,',') .') AND mode=1');
		while ($getCup = mysql_fetch_array($getAllCircuits)) {
			$cupTracks = array();
			for ($i=0;$i<4;$i++)
				$cupTracks[] = $getCup['circuit'.$i];
			$cupsTracks[$getCup['id']] = $cupTracks;
			$cupNamesById[$getCup['id']] = $getCup['nom'];
			addCircuitChallenges($challenges, 'mkcups', $getCup['id'],$getCup['nom'], $clPayloadParams, false);
		}
		foreach ($cupIDs as $cupID) {
			foreach ($cupsTracks[$cupID] as $cupTrack)
				$trackIDs[] = $cupTrack;
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
		$getAllTracks = mysql_query('SELECT c.*,d.data FROM `circuits` c LEFT JOIN `circuits_data` d ON c.id=d.id WHERE c.id IN ('. implode(',',$trackIDs) .')');
		$allTracks = array();
		while ($getMain = mysql_fetch_array($getAllTracks))
			$allTracks[$getMain['ID']] = $getMain;
		foreach ($trackIDs as $trackID) {
			if (isset($allTracks[$trackID])) {
				$getMain = $allTracks[$trackID];
				$circuitsData[] = $getMain;
				addCircuitChallenges($challenges, 'circuits', $getMain['ID'],$getMain['nom'], $clPayloadParams, !$isCup);
			}
		}
	}
	if (!$isCup && isset($circuitsData[0])) {
		$infos = $circuitsData[0];
		$cName = $infos['name'];
		$cPseudo = $infos['auteur'];
		$pNote = $infos['note'];
		$pNotes = $infos['nbnotes'];
		$cDate = $infos['publication_date'];
	}
}
else
	$circuitsData = Array($circuit);
function escapeUtf8($str) {
	return preg_replace("/%u([0-9a-fA-F]{4})/", "&#x\\1;", htmlentities($str));
}
$NBCIRCUITS = count($circuitsData);
if (!$NBCIRCUITS) {
	mysql_close();
	exit;
}
addClChallenges($challenges, $nid, $clPayloadParams);
$sid = ($isMCup ? 'mid' : ($isCup ? 'cid':'i'));
?>
<!DOCTYPE HTML SYSTEM>
<html>
   <head>
	   <title><?php if ($cName){echo escapeUtf8($cName);if($cAuteur)echo ' ['. ($language ? 'By':'Par') .' '. escapeUtf8($cAuteur) .']';echo ' - ';} ?>Mario Kart PC</title>
<?php include('metas.php'); ?>

<link rel="stylesheet" media="screen" type="text/css" href="styles/mariokart.css" />
<link rel="stylesheet" media="screen" type="text/css" href="styles/comments.css" />

<?php
include('o_online.php');
?>
<script type="text/javascript">
var selectedPlayers = <?php echo (isset($_COOKIE['mkplayers']) ? $_COOKIE['mkplayers']:8); ?>;
var selectedTeams = <?php echo (isset($_COOKIE['mkteam']) ? $_COOKIE['mkteam']:0); ?>;
var selectedDifficulty = <?php echo (isset($_COOKIE['mkdifficulty']) ? $_COOKIE['mkdifficulty']:1); ?>;
var challenges = <?php echo json_encode($challenges); ?>;
var clId = <?php echo json_encode($clId); ?>;
var language = <?php echo ($language ? 'true':'false'); ?>;
var recorder = "<?php echo isset($_COOKIE['mkrecorder']) ? $_COOKIE['mkrecorder']:'' ?>";
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
	include('persos.php');
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
var isds = <?php
	require_once('isDS.php');
	echo IS_DS;
?>;
function listMaps() {
	return {<?php
	include('mk/map.php');
	?>};
}
</script>
<?php include('mk/main.php') ?>
<script type="text/javascript">
<?php
$canChange = !isset($nid) || (isset($creationData) && ($creationData['identifiant'] == $identifiants[0]) && ($creationData['identifiant2'] == $identifiants[1]) && ($creationData['identifiant3'] == $identifiants[2]) && ($creationData['identifiant4'] == $identifiants[3]));
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
				echo '&';
			}
			if (isset($nid)) echo 'id='.$nid.'&';
			if ($clId) echo 'cl='.$clId.'&';
			?>nom="+ getValue("cName") +"&auteur="+ getValue("cPseudo"), function(reponse) {
			if (reponse && !isNaN(reponse)) {
				document.getElementById("cSave").removeChild(document.getElementById("cTable"));
				var cP = document.createElement("p");
				cP.style.margin = "5px";
				cP.style.textAlign = "center";
				cP.innerHTML = '<?php
					if ($cShared)
						echo $language ? 'The sharing of your '. ($isCup ? 'cup':'circuit') .' have been updated.':'Le partage de votre '. ($isCup ? 'coupe':'circuit') .' a &eacute;t&eacute; mis &agrave; jour.';
					else
						echo $language ? 'Your '. ($isCup ? 'cup':'circuit') .' has just been added to the <a href="creations.php" target="_blank">list</a> !':'Votre '. ($isCup ? 'coupe':'circuit') .' vient d\\\'&ecirc;tre ajout&eacute; &agrave; la <a href="creations.php" target="_blank">liste</a> !';
				?><br /><br />';
				var cCont = document.createElement("input");
				cCont.type = "button";
				cCont.value = language ? "Continue":"Continuer";
				cCont.onclick = function() {
					<?php
					if ($isCup)
						echo 'document.location.href = "?'.$sid.'="+ reponse;';
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
		xhr("<?php echo ($isMCup ? 'supprMCup':($isCup ? 'supprCup':'supprDraw')); ?>.php", "id=<?php echo $nid; ?>", function(reponse) {
			if (reponse == 1) {
				document.getElementById("supprInfos").innerHTML = '<?php echo $language ? 'The circuit has been successfully removed from the list.':'Le circuit a &eacute;t&eacute; retir&eacute; de la liste avec succ&egrave;s.'; ?>';
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
		return escape(document.getElementById(name).value).replace(/\+/g, "%2B");
	}
<?php
}
else {
	$getNote = mysql_query('SELECT note FROM `'. ($isMCup ? 'ratings':($isCup ? 'mkavis':'notes')) .'` WHERE circuit="'. $nid .'" AND identifiant='.$identifiants[0].' AND identifiant2='.$identifiants[1].' AND identifiant3='.$identifiants[2].' AND identifiant4='.$identifiants[3]);
	if ($note = mysql_fetch_array($getNote))
		$cNote = $note['note'];
	else
		$cNote = -1;
	?>
	var cNote = <?php echo $cNote; ?>, aNote = cNote;
	function previewMark(note) {
		for (i=0;i<=note;i++)
			document.getElementById("star"+ i).src = "images/star1.png";
		for (i=note+1;i<5;i++)
			document.getElementById("star"+ i).src = "images/star0.png";
	}
	function updateMark() {
		previewMark(cNote);
	}
	function setMark(nNote) {
		cNote = (cNote != nNote) ? nNote:-1;
		if (cNote != aNote) {
			document.getElementById("submitMark").disabled = false;
			document.getElementById("submitMark").className = "";
		}
		else {
			document.getElementById("submitMark").disabled = true;
			document.getElementById("submitMark").className = "cannotChange";
		}
		previewMark(cNote);
	}
	function sendMark() {
		document.getElementById("markMsg").innerHTML = "<?php echo $language ? 'Sending...':'Envoi en cours...'; ?>";
		document.getElementById("submitMark").disabled = true;
		document.getElementById("submitMark").className = "cannotChange";
		xhr("sendMark.php", "id=<?php echo $nid ?>&note="+cNote +"<?php
			if ($isMCup)
				echo '&mc=1';
			elseif ($isCup)
				echo '&cup=1';
			else
				echo '&complete=1';
			?>", function(reponse) {
			if (reponse == 1) {
				aNote = cNote;
				document.getElementById("markMsg").innerHTML = (aNote!=-1) ? "<?php echo $language ? 'Thanks for your vote':'Merci de votre vote'; ?>":"<?php echo $language ? 'Vote removed successfully':'Vote supprim&eacute; avec succ&egrave;s'; ?>";
				return true;
			}
			return false;
		});
	}
	<?php
}
?>
</script>
<script src="scripts/jquery.min.js"></script>
<script type="text/javascript">$(document).ready(MarioKart);</script>
</head>
<body>
<div id="mariokartcontainer"></div>

<div id="virtualkeyboard"></div>

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
	if (null === $circuitMainData->bgcolor)
		$message = $language ? 'Warning : You didn\'t specify any data for the circuit.<br />Go back to the editor before testing it.':'Attention : vous n\'avez pas encore spécifié les paramètres du circuit.<br />Revenez dans l\'éditeur avant de continuer.';
	elseif (($circuitMainData->startposition[0] == -1) && ($circuitMainData->startposition[1] == -1))
		$message = $language ? 'Warning : Your circuit doesn\'t has a starting.<br />Quite annoying, we don\'t know where to begin.':'Attention : Votre circuit n\'a pas de d&eacute;part !<br />C\'est ennuyeux, on ne sait pas par o&ucirc; commencer...';
	elseif (empty($circuitPayload->aipoints[0]))
		$message = $language ? 'Warning, you didn\'t specify the CPUs route.<br />They could work so well...':'Attention : Vous n\'avez pas indiqu&eacute; le trajet des ordis.<br />Ils risquent de marcher beaucoup moins bien...';
	elseif (!($nCps=count($circuitPayload->checkpoint)))
		$message = $language ? 'Warning, you didn\'t specify the checkpoints.<br />We can not find your way in the circuit !':'Attention : Vous n\'avez pas indiqu&eacute; les checkpoints.<br />Impossible de vous rep&eacute;rer dans le circuit !';
	elseif (isset($circuitMainData->sections) && isCpOob())
		$message = $language ? 'Warning, one or several sections are mapped<br />to a non-existing checkpoint.<br />The circuit will likely be unfinishable...':'Attention, une ou plusieurs sections<br />sont associéesà un checkpoint inexistant.<br />Le circuit risque d\'&ecirc;tre difficile à terminer...';
}
include('ip_banned.php');
if (isBanned())
   echo '&nbsp;';
elseif ($canChange) {
	$typeStr = $isCup ? ($isMCup ? ($language ? 'multicup':'la multicoupe'):($language ? 'cup':'la coupe')):($language ? 'circuit':'le circuit');
	?>
	<input type="button" id="changeRace" onclick="document.location.href=<?php echo ($isCup ? "'". ($isMCup ? "completecups.php":"completecup.php") ."'+document.location.search":"'draw.php?i=$nid'") ?>" value="<?php echo ($language ? 'Edit '.$typeStr:'Modifier '.$typeStr); ?>" /><br /><br /><?php
	if (!$cShared) {
		?>
	&nbsp;
		<?php
	}
	?>
	<input type="button" id="shareRace" onclick="document.getElementById('cSave').style.display='block'" value="<?php echo ($language ? 'Share '.$typeStr:'Partager '.$typeStr); ?>"<?php if (isset($message)){echo ' disabled="disabled" class="cannotChange"';$cannotChange=true;} ?> /><?php
	if ($cShared) {
		?>
	<br /><br /><input type="button" id="supprRace" onclick="document.getElementById('confirmSuppr').style.display='block'" value="<?php echo ($language ? 'Delete sharing':'Supprimer partage'); ?>" />
		<?php
	}
}
else {
	?>
	<p id="markMsg"><?php echo $language ? ('Rate this '.($isMCup?'multicup':($isCup?'cup':'circuit'))):('Notez '.($isMCup?'cette multicoupe':($isCup?'cette coupe':'ce circuit'))) ?> !</p>
	<?php
	$apreciations = $language ? Array('Very bad', 'Bad', 'Average', 'Good', 'Excellent'):Array('Très mauvais', 'Mauvais', 'Moyen', 'Bon', 'Excellent');
	function addStar($i, $a, $apreciation) {
		echo '&nbsp;<img id="star'.$i.'" class="star" src="images/star'.$a.'.png" onclick="setMark('.$i.')" onmouseover="previewMark('.$i.')" onmouseout="updateMark()" title="'.HTMLentities($apreciation).'" /> ';
	}
	for ($i=0;$i<=$cNote;$i++)
		addStar($i, 1, $apreciations[$i]);
	for ($i=$cNote+1;$i<5;$i++)
		addStar($i, 0, $apreciations[$i]);
	?><br />
	<input type="button" id="submitMark" value="<?php echo $language ? 'Submit':'Valider'; ?>" disabled="disabled" class="cannotChange" onclick="sendMark();" /></td>
	<?php
}
?>
</td></tr>
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
/*if ($cShared) {
	$message = $language ? 'New : a comment section for the circuit creations !':'Nouveau : une section commentaires pour les cr&eacute;ations de circuits !';
	$infoMsg = true;
}*/
if (!isset($message) && isset($nid)) {
	if (!$isCup) {
		if ($cupOfCircuit = mysql_fetch_array(mysql_query('SELECT id FROM `mkcups` WHERE (circuit0="'. $nid .'" OR circuit1="'. $nid .'" OR circuit2="'. $nid .'" OR circuit3="'. $nid .'") AND mode=1 LIMIT 1'))) {
			$message = ($language ? 'This circuit is part of a cup !<br /><a href="?cid='. $cupOfCircuit['id'] .'">Click here</a> to access it.':'Ce circuit fait partie d\'une coupe !<br /><a href="?cid='. $cupOfCircuit['id'] .'">Cliquez ici</a> pour y acc&eacute;der.');
			$infoMsg = true;
		}
	}
	elseif (!$isMCup) {
		if ($cupOfCircuit = mysql_fetch_array(mysql_query('SELECT mcup FROM `mkmcups_tracks` WHERE cup="'. $nid .'"'))) {
			$message = ($language ? 'This cup is part of a multicups !<br /><a href="?mid='. $cupOfCircuit['mcup'] .'">Click here</a> to access it.':'Cette coupe fait partie d\'une multicoupe !<br /><a href="?mid='. $cupOfCircuit['mcup'] .'">Cliquez ici</a> pour y acc&eacute;der.');
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
	'Delete this circuit sharing ?<br />
	The circuit will be only removed from the list :<br />
	data will be retained.' :
	'Supprimer le partage de ce circuit ?<br />
	Le circuit sera simplement retir&eacute; de la liste :<br />
	les donn&eacute;es seront conserv&eacute;es.';
?></p>
<p id="supprButtons"><input type="button" value="<?php echo $language ? 'Cancel':'Annuler'; ?>" id="sAnnuler" onclick="document.getElementById('confirmSuppr').style.display='none'" /> &nbsp; <input type="button" value="<?php echo $language ? 'Delete':'Supprimer'; ?>" id="sConfirmer" onclick="supprRace()" /></p>
</div>
<?php
if (!isset($cannotChange)) {
	?>
	<form id="cSave" method="post" action="" onsubmit="saveRace();return false">
	<table id="cTable">
	<tr><td style="text-align: right"><label for="cPseudo"><?php echo $language ? 'Enter your nick':'Indiquez votre pseudo'; ?> :</label></td><td><input type="text" name="cPseudo" id="cPseudo" value="<?php echo escapeUtf8($cPseudo) ?>" /></td></tr>
	<tr><td style="text-align: right"><label for="cName"><?php echo $language ? 'Circuit name':'Nom du circuit'; ?> :</label></td><td><input type="text" name="cName" id="cName" value="<?php echo escapeUtf8($cName) ?>" /></td></tr>
	<tr><td colspan="2" id="cSubmit"><input type="button" value="<?php echo $language ? 'Cancel':'Annuler'; ?>" id="cAnnuler" onclick="document.getElementById('cSave').style.display='none'" /> &nbsp; <input type="submit" value="<?php echo $language ? 'Share':'Partager'; ?>" id="cEnregistrer" /></td></tr>
	</table>
	</form>
	<?php
}
?>
<table id="objet0" border="1" cellpadding="0" cellspacing="0">
<tr>	<td id="roulette0" valign="middle"></td>	</tr>
</table>
<table id="objet1" border="1" cellpadding="0" cellspacing="0">
<tr>	<td id="roulette1" valign="middle"></td>	</tr>
</table>
<div id="dMaps"></div>
<p id="infoPlace0"></p>
<div id="lakitu0"><div></div></div>
<div id="drift0">
	<img alt="." src="images/drift.png" class="driftimg" />
</div>
<div id="scroller0" width="100px" height="100px" style="width: 100px; height: 100px; overflow: hidden; position: absolute; visibility: hidden">
	<div style="position: absolute; left: 0; top: 0">
		<img class="aObjet" alt="." src="images/items/fauxobjet.gif" /><br />&nbsp;<br />
		<img class="aObjet" alt="." src="images/items/banane.gif" /><br />&nbsp;<br />
		<img class="aObjet" alt="." src="images/items/carapace.gif" /><br />&nbsp;<br />
		<img class="aObjet" alt="." src="images/items/bobomb.gif" /><br />&nbsp;<br />
		<img class="aObjet" alt="." src="images/items/carapacerouge.gif" /><br />&nbsp;<br />
		<img class="aObjet" alt="." src="images/items/carapacebleue.gif" /><br />&nbsp;<br />
		<img class="aObjet" alt="." src="images/items/champi.gif" /><br />&nbsp;<br />
		<img class="aObjet" alt="." src="images/items/megachampi.gif" /><br />&nbsp;<br />
		<img class="aObjet" alt="." src="images/items/etoile.gif" /><br />&nbsp;<br />
		<img class="aObjet" alt="." src="images/items/eclair.gif" /><br />&nbsp;<br />
		<img class="aObjet" alt="." src="images/items/billball.gif" /><br />&nbsp;<br />
		<img class="aObjet" alt="." src="images/items/fauxobjet.gif" />
	</div>
</div>
<?php
if ($cShared) {
	include('circuitUser.php');
	?>
	<div id="comments-section"></div>
	<script type="text/javascript">
	var commentCircuit = <?php echo $nid; ?>, commentType = "<?php echo $isMCup ? 'mkmcups' : ($isCup?'mkcups':'circuits'); ?>",
	circuitName = "<?php echo addSlashes(escapeUtf8($cName)) ?>", circuitAuthor = "<?php echo addSlashes(escapeUtf8($cAuteur)) ?>", circuitNote = <?php echo $pNote ?>, circuitNotes = <?php echo $pNotes ?>,
	circuitDate = "<?php echo formatDate($cDate); ?>";
	var circuitUser = <?php echo findCircuitUser($cAuteur,$isCup?$circuitsData[0]['ID']:$nid,'circuits'); ?>;
	</script>
	<script type="text/javascript" src="scripts/comments.js"></script>
	<?php
}
?>
<?php include('mk/description.php'); ?>
</body>
</html>
<?php
mysql_close();
?>