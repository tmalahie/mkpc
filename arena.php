<?php
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
require_once('circuitPrefix.php');
if (isset($_GET['mid'])) { // Existing multicup
	$id = $_GET['mid'];
	$nid = $id;
	$isCup = true;
	$isMCup = true;
	if ($getMCup = mysql_fetch_array(mysql_query('SELECT * FROM `mkmcups` WHERE id="'. $id .'" AND mode=2'))) {
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
	$id = $_GET['cid'];
	$nid = $id;
	$isCup = true;
	if ($getCup = mysql_fetch_array(mysql_query('SELECT * FROM `mkcups` WHERE id="'. $id .'" AND mode=2'))) {
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
elseif (isset($_GET['id'])) {
	$id = $_GET['id'];
	$nid = $id;
	$trackIDs = array($id);
	$hthumbnail = 'https://mkpc.malahieude.net/mappreview.php?id='.$id;
	$cShared = true;
}
elseif (isset($_GET['cid0']) && isset($_GET['cid1']) && isset($_GET['cid2']) && isset($_GET['cid3'])) { // Cup being created
	$isCup = true;
	if (isset($_GET['nid'])) { // Cup being edited
		include('escape_all.php');
		$nid = $_GET['nid'];
		if ($getMain = mysql_fetch_array(mysql_query('SELECT nom,auteur,note,nbnotes,publication_date FROM `mkcups` WHERE id="'. $nid .'" AND mode=2 AND identifiant="'. $identifiants[0] .'" AND identifiant2="'. $identifiants[1] .'" AND identifiant3="'. $identifiants[2] .'" AND identifiant4="'. $identifiants[3] .'"'))) {
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
		if ($getMain = mysql_fetch_array(mysql_query('SELECT nom,auteur,note,nbnotes,publication_date FROM `mkmcups` WHERE id="'. $nid .'" AND mode=2 AND identifiant="'. $identifiants[0] .'" AND identifiant2="'. $identifiants[1] .'" AND identifiant3="'. $identifiants[2] .'" AND identifiant4="'. $identifiants[3] .'"'))) {
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
		$cupIDs[$i] = $_GET['mid'.$i];
	$cOptions = stripslashes($_GET['opt']);
	$edittingCircuit = true;
}
else { // Track being created
	if (isset($_GET['nid'])) { // Track being edited
		$nid = $_GET['nid'];
		if ($getMain = mysql_fetch_array(mysql_query('SELECT nom,auteur,note,nbnotes,publication_date FROM `mkcircuits` WHERE id="'. $nid .'" AND type AND identifiant="'. $identifiants[0] .'" AND identifiant2="'. $identifiants[1] .'" AND identifiant3="'. $identifiants[2] .'" AND identifiant4="'. $identifiants[3] .'"'))) {
			$infos['id'] = $nid;
			$cName = $getMain['nom'];
			$cPseudo = $getMain['auteur'];
			$cAuteur = $cPseudo;
			$pNote = $getMain['note'];
			$pNotes = $getMain['nbnotes'];
			$cDate = $getMain['publication_date'];
			addCircuitChallenges('mkcircuits', $nid,$cName, $clPayloadParams);
			$edittingCircuit = true;
		}
		else {
			mysql_close();
			exit;
		}
	}
	else
		$cPseudo = $_COOKIE['mkauteur'];
	for ($i=0;$i<36;$i++)
		$infos["p$i"] = (isset($_GET["p$i"])) ? $_GET["p$i"] : 11;
	for ($i=0;$i<8;$i++) {
		$infos["r$i"] = isset($_GET["r$i"]) ? $_GET["r$i"] : 0;
		$infos["s$i"] = isset($_GET["s$i"]) ? $_GET["s$i"] : 0;
	}
	$infos['map'] = (isset($_GET["map"])) ? $_GET["map"] : 1;
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
		$getAllCircuits = mysql_query('SELECT id,nom,circuit0,circuit1,circuit2,circuit3 FROM `mkcups` WHERE id IN ('. implode(',',$cupIDs) .') AND mode=2');
		while ($getCup = mysql_fetch_array($getAllCircuits)) {
			$cupTracks = array();
			for ($i=0;$i<4;$i++)
				$cupTracks[] = $getCup['circuit'.$i];
			$cupsTracks[$getCup['id']] = $cupTracks;
			$cupNamesById[$getCup['id']] = $getCup['nom'];
			addCircuitChallenges('mkcups', $getCup['id'],$getCup['nom'], $clPayloadParams, false);
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
		$getAllTracks = mysql_query('SELECT id,map,laps,nom,auteur,note,nbnotes,publication_date FROM `mkcircuits` WHERE id IN ('. implode(',',$trackIDs) .') AND type');
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
				$positions = mysql_query('SELECT * FROM `mkr` WHERE circuit="'.$trackID.'"');
				while ($position = mysql_fetch_array($positions)) {
					$infos['s'.$position['id']] = $position['s'];
					$infos['r'.$position['id']] = $position['r'];
				}
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
else
	$circuitsData = Array($infos);
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
<?php include('metas.php'); ?>

<?php include('c_mariokart.php'); ?>
<link rel="stylesheet" media="screen" type="text/css" href="styles/comments.css" />

<?php
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
var cupOpts = <?php echo $cOptions ? $cOptions:'{}'; ?>;
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
	include('persos.php');
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
			echo 'mode=2';
			foreach ($cupIDs as $i=>$cupID)
				echo '&cid'. $i .'='. $cupID;
			if (!empty($cOptions))
				echo '&opt='. urlencode($cOptions);
		}
		else {
			echo 'map='.$map.'&battle';
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
		?>&nom="+ getValue("cName") +"&auteur="+ getValue("cPseudo"), function(reponse) {
			if (reponse && !isNaN(reponse)) {
				document.getElementById("cSave").removeChild(document.getElementById("cTable"));
				var cP = document.createElement("p");
				cP.style.margin = "5px";
				cP.style.textAlign = "center";
				cP.innerHTML = '<?php
					if (isset($nid))
						echo $language ? 'The sharing of your '. ($isCup ? 'cup':'arena') .' has been updated.':'Le partage de votre '. ($isCup ? 'coupe':'arène') .' a &eacute;t&eacute; mis &agrave; jour.';
					else
						echo $language ? 'Your '. ($isCup ? 'cup':'arena') .' has just been added to the <a href="creations.php" target="_blank">list</a> !':'Votre '. ($isCup ? 'coupe':'arène') .' vient d\\\'&ecirc;tre ajout&eacute; &agrave; la <a href="creations.php" target="_blank">liste</a> !';
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
		xhr("<?php echo ($isMCup ? 'supprMCup':($isCup ? 'supprCup':'supprCreation')); ?>.php", "id=<?php echo $id; ?>", function(reponse) {
			if (reponse == 1) {
				document.getElementById("supprInfos").innerHTML = '<?php echo $language ? 'The '. ($isCup ? 'cup':'arena') .' has been successfully removed from the list.':($isCup ? 'La coupe':'L\\\'arène') .' a &eacute;t&eacute; retir&eacute;e de la liste avec succ&egrave;s.'; ?>';
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
		return escape(document.getElementById(name).value).replace(/\+/g, "%2B");
	}
	<?php
}
else {
	require_once('utils-ratings.php');
	$cNote = getMyRating($isMCup ? 'mkmcups':($isCup ? 'mkcups':'mkcircuits'), $id);
	?>
	var cNote = <?php echo $cNote ?>;
	var ratingParams = "id=<?php
		echo $id;
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
<td id="pQuality">&nbsp;</td>
<td id="vQuality">
</td>
<td rowspan="4" id="shareParams">
<?php
if ($canChange && !$isCup && !isset($infos['o0'])) {
	$message = $language ? 'Warning : your course doesn\'t contain objects !<br />Hard to fight with those conditions...'
	: 'Attention : votre arène ne contient aucun objet !<br />Difficile de se battre dans ces conditions...';
}
include('ip_banned.php');
if (isBanned())
   echo '&nbsp;';
   elseif ($canChange) {
	   $typeStr = $isCup ? ($isMCup ? ($language ? 'multicup':'la multicoupe'):($language ? 'cup':'la coupe')):($language ? 'arena':'l\'arène');
	   ?>
   <input type="button" id="changeRace" onclick="document.location.href='<?php echo ($isCup ? ($isMCup ? 'simplecups.php':'simplecup.php'):'arene.php') ?>'+document.location.search<?php if ($isCup) echo '+\'&battle\''; ?>" value="<?php echo ($language ? 'Edit '.$typeStr:'Modifier '.$typeStr); ?>" />
   <br /><br />
   
   <input type="button" id="shareRace" onclick="document.getElementById('cSave').style.display='block'" value="<?php echo ($language ? 'Share '.$typeStr:'Partager '.$typeStr); ?>"<?php if (isset($message)&&!isset($infoMsg)){echo ' disabled="disabled" class="cannotChange"';$cannotChange=true;} ?> /><?php
	   if (isset($_GET[$sid])) {
		   ?>
   <br /><br /><input type="button" id="supprRace" onclick="document.getElementById('confirmSuppr').style.display='block'" value="<?php echo ($language ? 'Delete sharing':'Supprimer partage'); ?>" />
		   <?php
	   }
   }
   else
	   printRatingView($language ? ('Rate this '.($isMCup?'multicup':($isCup?'cup':'arena')).'!'):('Notez '.($isMCup?'cette multicoupe':($isCup?'cette coupe':'cette arène'))).' !');
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
if (!isset($message) && isset($nid)) {
	if (!$isCup) {
		if ($cupOfCircuit = mysql_fetch_array(mysql_query('SELECT id FROM `mkcups` WHERE (circuit0="'. $nid .'" OR circuit1="'. $nid .'" OR circuit2="'. $nid .'" OR circuit3="'. $nid .'") AND mode=2 LIMIT 1'))) {
			$message = ($language ? 'This arena is part of a cup!<br /><a href="?cid='. $cupOfCircuit['id'] .'">Click here</a> to access it.':'Cette arène fait partie d\'une coupe !<br /><a href="?cid='. $cupOfCircuit['id'] .'">Cliquez ici</a> pour y acc&eacute;der.');
			$infoMsg = true;
		}
	}
	elseif (!$isMCup) {
		if ($cupOfCircuit = mysql_fetch_array(mysql_query('SELECT mcup FROM `mkmcups_tracks` WHERE cup="'. $nid .'"'))) {
			$message = ($language ? 'This cup is part of a multicup!<br /><a href="?mid='. $cupOfCircuit['mcup'] .'">Click here</a> to access it.':'Cette coupe fait partie d\'une multicoupe !<br /><a href="?mid='. $cupOfCircuit['mcup'] .'">Cliquez ici</a> pour y acc&eacute;der.');
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
	'Delete this '. ($isCup ? ($isMCup ? 'multicup':'cup'):'arena') .' sharing ?<br />
	'.($isCup ? ($isMCup ? 'The multicup':'The cup'):'The arena').' will be only removed from the list:<br />
	data will be recoverable.' :
	'Supprimer le partage de '. ($isCup ? ($isMCup ? 'cette multicoupe':'cette coupe'):'cette arène') .' ?<br />
	'.($isCup ? ($isMCup ? 'La multicoupe':'La coupe'):'L\'arène').' sera simplement retir&eacute;e de la liste :<br />
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
	<tr><td style="text-align: right"><label for="cName"><?php echo $language ? ($isCup ? ($isMCup ? 'Multicup':'Cup'):'Arena').' name':'Nom '.($isCup ? ($isMCup?'de la multicoupe':'de la coupe'):'de l\'arène'); ?> :</label></td><td><input type="text" name="cName" id="cName" value="<?php echo escapeUtf8($cName) ?>" /></td></tr>
	<tr><td colspan="2" id="cSubmit"><input type="button" value="<?php echo $language ? 'Cancel':'Annuler'; ?>" id="cAnnuler" onclick="document.getElementById('cSave').style.display='none'" /> &nbsp; <input type="submit" value="<?php echo $language ? 'Share':'Partager'; ?>" id="cEnregistrer" /></td></tr>
	</table>
	</form>
	<?php
}
?>
<?php
include('gameInitElts.php');
if (isset($nid)) {
	include('circuitUser.php');
	$circuitTable = $isMCup ? 'mkmcups' : ($isCup?'mkcups':'mkcircuits');
	?>
	<div id="comments-section"></div>
	<script type="text/javascript">
	var commentCircuit = <?php echo $nid; ?>, commentType = "<?php echo $circuitTable; ?>",
	circuitName = "<?php echo addSlashes(escapeUtf8($cName)) ?>", circuitAuthor = "<?php echo addSlashes(escapeUtf8($cAuteur)) ?>", circuitNote = <?php echo $pNote ?>, circuitNotes = <?php echo $pNotes ?>,
	circuitDate = "<?php echo formatDate($cDate); ?>";
	var circuitUser = <?php echo findCircuitUser($cAuteur,$nid,$circuitTable); ?>
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