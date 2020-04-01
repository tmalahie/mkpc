<?php
$lettres = Array('a', 'b', 'c', 'd', 'e', 'f', 'g', 'h', 'i', 'j', 'o', 't');
$nbLettres = count($lettres);
$cupIDs = Array();
$infos = Array();
$isCup = false;
$isMCup = false;
include('getId.php');
include('initdb.php');
include('language.php');
require_once('utils-challenges.php');
mysql_set_charset('utf8');
include('creation-challenges.php');
if (isset($_GET['mid'])) { // Existing multicup
	$id = $_GET['mid'];
	$nid = $id;
	$isCup = true;
	$isMCup = true;
	if ($getMCup = mysql_fetch_array(mysql_query('SELECT * FROM `mkmcups` WHERE id="'. $id .'" AND mode=0'))) {
		$cName = $getMCup['nom'];
		$infos['name'] = $cName;
		$cPseudo = $getMCup['auteur'];
		$pNote = $getMCup['note'];
		$pNotes = $getMCup['nbnotes'];
		$cDate = $getMCup['publication_date'];
		$getCups = mysql_query('SELECT cup FROM `mkmcups_tracks` WHERE mcup="'. $id .'" ORDER BY ordering');
		$cupIDs = array();
		while ($getCup = mysql_fetch_array($getCups))
			$cupIDs[] = $getCup['cup'];
		addCircuitChallenges($challenges, 'mkmcups', $nid,$cName, $clPayloadParams);
	}
}
elseif (isset($_GET['cid'])) { // Existing cup
	$id = $_GET['cid'];
	$nid = $id;
	$isCup = true;
	if ($getCup = mysql_fetch_array(mysql_query('SELECT * FROM `mkcups` WHERE id="'. $id .'" AND mode=0'))) {
		$cName = $getCup['nom'];
		$infos['name'] = $cName;
		$cPseudo = $getCup['auteur'];
		$pNote = $getCup['note'];
		$pNotes = $getCup['nbnotes'];
		$cDate = $getCup['publication_date'];
		for ($i=0;$i<4;$i++)
			$cupIDs[$i] = $getCup['circuit'. $i];
		$trackIDs = $cupIDs;
		addCircuitChallenges($challenges, 'mkcups', $nid,$cName, $clPayloadParams);
	}
}
elseif (isset($_GET['id'])) { // Existing track
	include('escape_all.php');
	$id = $_GET['id'];
	$nid = $id;
	$trackIDs = array($id);
}
elseif (isset($_GET['cid0']) && isset($_GET['cid1']) && isset($_GET['cid2']) && isset($_GET['cid3'])) { // Cup being created
	$isCup = true;
	if (isset($_GET['nid'])) { // Cup being edited
		include('escape_all.php');
		$nid = $_GET['nid'];
		if ($getMain = mysql_fetch_array(mysql_query('SELECT nom,auteur,note,nbnotes,publication_date FROM `mkcups` WHERE id="'. $nid .'" AND mode=0 AND identifiant="'. $identifiants[0] .'" AND identifiant2="'. $identifiants[1] .'" AND identifiant3="'. $identifiants[2] .'" AND identifiant4="'. $identifiants[3] .'"'))) {
			$cName = $getMain['nom'];
			$cPseudo = $getMain['auteur'];
			$pNote = $getMain['note'];
			$pNotes = $getMain['nbnotes'];
			$cDate = $getMain['publication_date'];
			addCircuitChallenges($challenges, 'mkcups', $nid,$cName, $clPayloadParams);
		}
	}
	else
		$cPseudo = isset($_COOKIE['mkauteur']) ? $_COOKIE['mkauteur']:null;
	for ($i=0;$i<4;$i++)
		$cupIDs[$i] = $_GET['cid'. $i];
	$trackIDs = $cupIDs;
	$edittingCircuit = true;
}
elseif (isset($_GET['mid0'])) { // Multicups being created
	$isCup = true;
	$isMCup = true;
	if (isset($_GET['nid'])) { // Multicups being edited
		include('escape_all.php');
		$nid = $_GET['nid'];
		if ($getMain = mysql_fetch_array(mysql_query('SELECT nom,auteur,note,nbnotes,publication_date FROM `mkmcups` WHERE id="'. $nid .'" AND mode=0 AND identifiant="'. $identifiants[0] .'" AND identifiant2="'. $identifiants[1] .'" AND identifiant3="'. $identifiants[2] .'" AND identifiant4="'. $identifiants[3] .'"'))) {
			$cName = $getMain['nom'];
			$cPseudo = $getMain['auteur'];
			$pNote = $getMain['note'];
			$pNotes = $getMain['nbnotes'];
			$cDate = $getMain['publication_date'];
			addCircuitChallenges($challenges, 'mkmcups', $nid,$cName, $clPayloadParams);
		}
	}
	else
		$cPseudo = isset($_COOKIE['mkauteur']) ? $_COOKIE['mkauteur']:null;
	for ($i=0;isset($_GET['mid'.$i])&&is_numeric($_GET['mid'.$i]);$i++)
		$cupIDs[$i] = $_GET['mid'.$i];
	$edittingCircuit = true;
}
else { // Track being created
	if (isset($_GET['nid'])) { // Track being edited
		$nid = $_GET['nid'];
		if ($getMain = mysql_fetch_array(mysql_query('SELECT nom,auteur,note,nbnotes,publication_date FROM `mkcircuits` WHERE id="'. $nid .'" AND !type AND identifiant="'. $identifiants[0] .'" AND identifiant2="'. $identifiants[1] .'" AND identifiant3="'. $identifiants[2] .'" AND identifiant4="'. $identifiants[3] .'"'))) {
			$infos['id'] = $nid;
			$cName = $getMain['nom'];
			$cPseudo = $getMain['auteur'];
			$pNote = $getMain['note'];
			$pNotes = $getMain['nbnotes'];
			$cDate = $getMain['publication_date'];
			addCircuitChallenges($challenges, 'mkcircuits', $nid,$cName, $clPayloadParams);
			$edittingCircuit = true;
		}
		else {
			mysql_close();
			exit;
		}
	}
	else
		$cPseudo = isset($_COOKIE['mkauteur']) ? $_COOKIE['mkauteur']:null;
	for ($i=0;$i<36;$i++)
		$infos["p$i"] = (isset($_GET["p$i"])) ? $_GET["p$i"] : 11;
	$infos['map'] = (isset($_GET["map"])) ? $_GET["map"] : 1;
	$infos['laps'] = (isset($_GET["nl"])) ? $_GET["nl"] : 3;
	$infos['name'] = '';
	for ($i=0;$i<$nbLettres;$i++) {
		$lettre = $lettres[$i];
		for ($j=0;isset($_GET[$lettre.$j]);$j++)
			$infos[$lettre.$j] = $_GET[$lettre.$j];
	}
	$edittingCircuit = true;
}
$cupNames = array();
if ($isMCup && !isset($trackIDs)) {
	$trackIDs = array();
	if (!empty($cupIDs)) {
		$cupsTracks = array();
		$cupNamesById = array();
		$getAllCircuits = mysql_query('SELECT id,nom,circuit0,circuit1,circuit2,circuit3 FROM `mkcups` WHERE id IN ('. implode($cupIDs,',') .') AND mode=0');
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
					$getInfos = mysql_query('SELECT x,y FROM `mk'.$lettre.'` WHERE circuit="'.$trackID.'"');
					for ($k=0;$info=mysql_fetch_array($getInfos);$k++)
						$infos[$lettre.$k] = $info['x'].','.$info['y'];
				}
				$circuitsData[] = $infos;
				addCircuitChallenges($challenges, 'mkcircuits', $trackID,$infos['name'], $clPayloadParams, !$isCup);
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
	$circuitsData = Array($infos);
if ($isCup)
	$infos = Array();
$NBCIRCUITS = count($circuitsData);
if (!$NBCIRCUITS) {
	mysql_close();
	exit;
}
addClChallenges($challenges, $nid, $clPayloadParams);
$sid = ($isMCup ? 'mid' : ($isCup ? 'cid':'id'));
$getInfos = Array();
function escapeUtf8($str) {
	return preg_replace("/%u([0-9a-fA-F]{4})/", "&#x\\1;", htmlentities($str));
}
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="<?php echo $language ? 'en':'fr'; ?>">
   <head>
       <title><?php if ($cName){echo escapeUtf8($cName);if($cPseudo)echo ' ['. ($language ? 'By':'Par') .' '. escapeUtf8($cPseudo) .']';echo ' - ';} ?>Mario Kart PC</title>
<?php include('metas.php'); ?>

<link rel="stylesheet" media="screen" type="text/css" href="styles/mariokart.css?reload=1" />
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
	echo '"'. ($circuit['name'] ? addSlashes(escapeUtf8($circuit['name'])) : "&nbsp;") .'"';
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
var page = "CI";
<?php
if ($isCup) {
	if ($isMCup) {
		echo 'var cupScore = 0;';
		echo 'var ptsGP = "';
		$ptsGP = array();
		if (!empty($cupIDs)) {
			$getScores = mysql_query('SELECT cup,score FROM `mkwins` WHERE cup IN ('. implode($cupIDs,',') .') AND identifiant="'.$identifiants[0].'" AND identifiant2="'.$identifiants[1].'" AND identifiant3="'.$identifiants[2].'" AND identifiant4="'.$identifiants[3].'"');
			while ($getScore = mysql_fetch_array($getScores))
				$ptsGP[$getScore['cup']] = $getScore['score'];
			foreach ($cupIDs as $i => $cupID)
				echo (isset($ptsGP[$cupID]) ? $ptsGP[$cupID]:0);
		}
		echo '";';
	}
	else {
		$cupScore = 0;
		if ($nid) {
			if ($getScore = mysql_fetch_array(mysql_query('SELECT score FROM `mkwins` WHERE cup="'. $nid .'" AND identifiant="'.$identifiants[0].'" AND identifiant2="'.$identifiants[1].'" AND identifiant3="'.$identifiants[2].'" AND identifiant4="'.$identifiants[3].'"')))
				$cupScore = $getScore['score'];
		}
		echo 'var cupScore = '. $cupScore .';';
	}
}
?>
var PERSOS_DIR = "<?php
	include('persos.php');
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
var isds = <?php
	require_once('isDS.php');
	echo IS_DS;
?>;
function listMaps() {
	return {<?php
	include('mk/circuit.php');
	?>};
}
</script>
<?php include('mk/main.php') ?>
<script type="text/javascript">
<?php
$canChange = (!isset($nid) || mysql_numrows(mysql_query('SELECT * FROM `'.($isMCup ? 'mkmcups':($isCup?'mkcups':'mkcircuits')).'` WHERE id="'. $nid.'" AND identifiant='.$identifiants[0].' AND identifiant2='.$identifiants[1].' AND identifiant3='.$identifiants[2].' AND identifiant4='.$identifiants[3])));
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
		}
		else {
			echo 'map='.$infos['map'].'&nl='.$infos['laps'];
			for ($i=0;$i<36;$i++)
				echo '&p'.$i.'='.$infos['p'.$i];
			for ($i=0;$i<$nbLettres;$i++) {
				$l = $lettres[$i];
				for ($j=0;isset($infos[$l.$j]);$j++)
					echo '&'.$l.$j.'='.$infos[$l.$j];
			}
		}
		if (isset($nid)) echo '&id='.$nid;
		if ($clId) echo '&cl='.$clId;
		?>&nom="+ getValue("cName") +"&auteur="+ getValue("cPseudo"), function(reponse) {
			if (reponse && !isNaN(reponse)) {
				document.getElementById("cSave").removeChild(document.getElementById("cTable"));
				var cP = document.createElement("p");
				cP.style.margin = "5px";
				cP.style.textAlign = "center";
				cP.innerHTML = '<?php
					if (isset($nid))
						echo $language ? 'The sharing of your '. ($isCup ? 'cup':'circuit') .' have been updated.':'Le partage de votre '. ($isCup ? 'coupe':'circuit') .' a &eacute;t&eacute; mis &agrave; jour.';
					else
						echo $language ? 'Your '. ($isCup ? 'cup':'circuit') .' has just been added to the <a href="creations.php" target="_blank">list</a> !':'Votre '. ($isCup ? 'coupe':'circuit') .' vient d\\\'&ecirc;tre ajout&eacute; &agrave; la <a href="creations.php" target="_blank">liste</a> !';
				?><br /><br />';
				var cCont = document.createElement("input");
				cCont.type = "button";
				cCont.value = language ? "Continue":"Continuer";
				cCont.onclick = function() {
					document.location.href = "?<?php echo $sid; ?>="+ reponse;
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
		xhr("<?php echo ($isMCup ? 'supprMCup':($isCup ? 'supprCup':'supprCreation')); ?>.php", "id=<?php echo $id; ?>", function(reponse) {
			if (reponse == 1) {
				document.getElementById("supprInfos").innerHTML = '<?php echo $language ? 'The circuit has been successfully removed from the list.':'Le circuit a &eacute;t&eacute; retir&eacute; de la liste avec succ&egrave;s.'; ?>';
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
							for ($j=0;isset($infos[$l.$j]);$j++)
								echo '&'.$l.$j.'='.$infos[$l.$j];
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
		return escape(document.getElementById(name).value).replace(/\+/g, "%2B");
	}
	<?php
}
else {
$getNote = mysql_query('SELECT note FROM `'. ($isMCup ? 'ratings':($isCup ? 'mkavis':'mknotes')) .'` WHERE circuit="'. $id .'" AND identifiant='.$identifiants[0].' AND identifiant2='.$identifiants[1].' AND identifiant3='.$identifiants[2].' AND identifiant4='.$identifiants[3]);
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
	xhr("sendMark.php", "id=<?php echo $id ?>&note="+cNote<?php
		if ($isMCup)
			echo ' +"&mc=1"';
		elseif ($isCup)
			echo ' +"&cup=1"';
		?>, function(reponse) {
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
	if ($noStart)
		$message = $language ? 'Warning : Your circuit doesn\'t has a starting.<br />Quite annoying, we don\'t know where to begin.':'Attention : Votre circuit n\'a pas de d&eacute;part !<br />C\'est ennuyeux, on ne sait pas par o&ucirc; commencer...';
	elseif ($twoStarts)
		$message = $language ? 'Warning : your circuit have severals startings.<br />Hard to know which choosing.':'Attention : votre circuit comporte plusieurs d&eacute;parts !<br />Difficile de savoir lequel choisir...';
	elseif ($bloqued)
		$message = $language ? 'Warning : your circuit have to make a loop.<br />Otherwise it\'s impossible to know how to make a lap...':'Attention : votre circuit doit former une boucle.<br />Impossible de faire un tour sinon...';
}
include('ip_banned.php');
if (isBanned())
   echo '&nbsp;';
elseif ($canChange) {
	$typeStr = $isCup ? ($isMCup ? ($language ? 'multicup':'la multicoupe'):($language ? 'cup':'la coupe')):($language ? 'circuit':'le circuit');
	?>
<input type="button" id="changeRace" onclick="document.location.href='<?php echo ($isCup ? ($isMCup ? 'simplecups.php':'simplecup.php'):'create.php') ?>'+document.location.search" value="<?php echo ($language ? 'Edit '.$typeStr:'Modifier '.$typeStr); ?>" />
<br /><br />

<input type="button" id="shareRace" onclick="document.getElementById('cSave').style.display='block'" value="<?php echo ($language ? 'Share '.$typeStr:'Partager '.$typeStr); ?>"<?php if (isset($message)){echo ' disabled="disabled" class="cannotChange"';$cannotChange=true;} ?> /><?php
	if (isset($_GET[$sid])) {
		?>
<br /><br /><input type="button" id="supprRace" onclick="document.getElementById('confirmSuppr').style.display='block'" value="<?php echo ($language ? 'Delete sharing':'Supprimer partage'); ?>" />
		<?php
	}
}
else {
	?>
	<p id="markMsg"><?php echo $language ? ('Rate this '.($isMCup?'multicup':($isCup?'cup':'circuit'))):('Notez '.($isMCup?'cette multicoupe':($isCup?'cette coupe':'ce circuit'))) ?> !</p>
	<?php
	function addStar($i, $a, $apreciation) {
		echo '&nbsp;<img id="star'.$i.'" class="star" src="images/star'.$a.'.png" onclick="setMark('.$i.')" onmouseover="previewMark('.$i.')" onmouseout="updateMark()" title="'.htmlentities($apreciation).'" /> ';
	}
	$apreciations = $language ? Array('Very bad', 'Bad', 'Average', 'Good', 'Excellent'):Array('Très mauvais', 'Mauvais', 'Moyen', 'Bon', 'Excellent');
	for ($i=0;$i<=$cNote;$i++)
		addStar($i, 1, $apreciations[$i]);
	for ($i=$cNote+1;$i<5;$i++)
		addStar($i, 0, $apreciations[$i]);
		?><br />
		<input type="button" id="submitMark" value="<?php echo $language ? 'Submit':'Valider'; ?>" disabled="disabled" class="cannotChange" onclick="sendMark();" /></td>
		<?php
}
?></td></tr>
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
/*if (isset($nid)) {
	$message = $language ? 'New : a comment section for the circuit creations !':'Nouveau : une section commentaires pour les cr&eacute;ations de circuits !';
	$infoMsg = true;
}*/
if (!isset($message) && isset($nid)) {
	if (!$isCup) {
		if ($cupOfCircuit = mysql_fetch_array(mysql_query('SELECT id FROM `mkcups` WHERE (circuit0="'. $nid .'" OR circuit1="'. $nid .'" OR circuit2="'. $nid .'" OR circuit3="'. $nid .'") AND mode=0 LIMIT 1'))) {
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
	data will be recoverable.' :
	'Supprimer le partage de ce circuit ?<br />
	Le circuit sera simplement retir&eacute; de la liste :<br />
	les donn&eacute;es seront r&eacute;cup&eacute;rables.';
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
?>
<div id="dMaps"></div>
<div id="scroller" width="100px" height="100px" style="width: 100px; height: 100px; overflow: hidden; position: absolute; visibility: hidden">
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
if (isset($id)) {
	include('circuitUser.php');
	?>
	<div id="comments-section"></div>
	<script type="text/javascript">
	var commentCircuit = <?php echo $nid; ?>, commentType = "<?php echo $isMCup ? 'mkmcups' : ($isCup?'mkcups':'mkcircuits'); ?>",
	circuitName = "<?php echo addSlashes(escapeUtf8($cName)) ?>", circuitAuthor = "<?php echo addSlashes(escapeUtf8($cPseudo)) ?>", circuitNote = <?php echo $pNote ?>, circuitNotes = <?php echo $pNotes ?>,
	circuitDate = "<?php echo formatDate($cDate); ?>";
	var circuitUser = <?php echo findCircuitUser($cPseudo,$isCup?$circuitsData[0]['id']:$nid,'mkcircuits'); ?>;
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