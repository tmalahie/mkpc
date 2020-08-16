<?php
$lettres = Array('a', 'b', 'c', 'd', 'e', 'f', 'g', 'h', 'i', 'j', 'o', 't');
$nbLettres = count($lettres);
$infos = Array();
include('getId.php');
include('initdb.php');
include('language.php');
require_once('utils-challenges.php');
mysql_set_charset('utf8');
include('creation-challenges.php');
require_once('circuitPrefix.php');
if (isset($_GET['id'])) {
	$id = $_GET['id'];
	$nid = $id;
	if ($getMain = mysql_fetch_array(mysql_query('SELECT map,nom,auteur,note,nbnotes,publication_date FROM `mkcircuits` WHERE id="'. $id .'" AND type'))) {
		$map = $getMain['map'];
		$cName = $getMain['nom'];
		$cPseudo = $getMain['auteur'];
		$cDate = $getMain['publication_date'];
		$pNote = $getMain['note'];
		$pNotes = $getMain['nbnotes'];
		$pieces = mysql_query('SELECT * FROM `mkp` WHERE circuit="'.$id.'"');
		while ($piece = mysql_fetch_array($pieces))
			$infos['p'.$piece['id']] = $piece['piece'];
		$positions = mysql_query('SELECT * FROM `mkr` WHERE circuit="'.$id.'"');
		while ($position = mysql_fetch_array($positions)) {
			$infos['s'.$position['id']] = $position['s'];
			$infos['r'.$position['id']] = $position['r'];
		}
		for ($j=0;$j<$nbLettres;$j++) {
			$lettre = $lettres[$j];
			$getInfos = mysql_query('SELECT * FROM `mk'.$lettre.'` WHERE circuit="'.$id.'"');
			$incs = array();
			while ($info=mysql_fetch_array($getInfos)) {
				$prefix = getLetterPrefixD($lettre,$info);
				if (!isset($incs[$prefix])) $incs[$prefix] = 0;
				$infos[$prefix.$incs[$prefix]] = $info['x'].','.$info['y'];
				$incs[$prefix]++;
			}
		}
		$infos['map'] = $map;
		addCircuitChallenges('mkcircuits', $nid,$cName, $clPayloadParams);
		$hthumbnail = 'https://mkpc.malahieude.net/mappreview.php?id='.$id;
	}
	else {
		mysql_close();
		exit;
	}
}
else {
	if (isset($_GET['nid'])) {
		$nid = $_GET['nid'];
		if ($getMain = mysql_fetch_array(mysql_query('SELECT nom,auteur,note,nbnotes,publication_date FROM `mkcircuits` WHERE id="'. $nid .'" AND type AND identifiant="'. $identifiants[0] .'" AND identifiant2="'. $identifiants[1] .'" AND identifiant3="'. $identifiants[2] .'" AND identifiant4="'. $identifiants[3] .'"'))) {
			$cName = $getMain['nom'];
			$cPseudo = $getMain['auteur'];
			$cDate = $getMain['publication_date'];
			$pNote = $getMain['note'];
			$pNotes = $getMain['nbnotes'];
			addCircuitChallenges('mkcircuits', $nid,$cName, $clPayloadParams);
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
	$map = (isset($_GET["map"])) ? $_GET["map"] : 1;
	$infos['map'] = $map;
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
require_once('circuitEscape.php');
function escapeUtf8($str) {
	return htmlentities(escapeCircuitNames($str));
}
$circuitsData = Array($infos);
$NBCIRCUITS = 1;
addClChallenges($nid, $clPayloadParams);
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
	echo '"'. ($circuit['nom'] ? addSlashes(escapeUtf8($circuit['nom'])) : "&nbsp;") .'"';
}
?>];
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
var isSingle = true;
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
$canChange = (!isset($nid) || mysql_numrows(mysql_query('SELECT * FROM `mkcircuits` WHERE id="'. $nid.'" AND identifiant='.$identifiants[0].' AND identifiant2='.$identifiants[1].' AND identifiant3='.$identifiants[2].' AND identifiant4='.$identifiants[3])));
if ($canChange) {
	?>
	function saveRace() {
		document.getElementById("cAnnuler").disabled = true;
		document.getElementById("cAnnuler").className = "cannotChange";
		document.getElementById("cEnregistrer").disabled = true;
		document.getElementById("cEnregistrer").className = "cannotChange";
		xhr("saveCreation.php", "<?php
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
						echo $language ? 'The sharing of your course have been updated.':'Le partage de votre ar&egrave;ne a &eacute;t&eacute; mis &agrave; jour.';
					else
						echo $language ? 'Your course has just been added to the <a href="creations.php" target="_blank">list</a> !':'Votre ar&egrave;ne vient d\\\'&ecirc;tre ajout&eacute;e &agrave; la <a href="creations.php" target="_blank">liste</a> !';
				?><br /><br />';
				var cCont = document.createElement("input");
				cCont.type = "button";
				cCont.value = language ? "Continue":"Continuer";
				cCont.onclick = function() {
					document.location.href = "?id="+ reponse;
				};
				cP.appendChild(cCont);
				document.getElementById("cSave").appendChild(cP);
				document.getElementById("changeRace").onclick = function() {
					document.location.href = "arene.php?id="+ reponse;
				};
				return true;
			}
			return false;
		});
	}
	<?php
	if (isset($_GET['id'])) {
		?>
	function supprRace() {
		document.getElementById("sAnnuler").disabled = true;
		document.getElementById("sAnnuler").className = "cannotChange";
		document.getElementById("sConfirmer").disabled = true;
		document.getElementById("sConfirmer").className = "cannotChange";
		xhr("supprArene.php", "id=<?php echo $id ?>", function(reponse) {
			if (reponse == 1) {
				document.getElementById("supprInfos").innerHTML = '<?php echo $language ? 'The course has been successfully removed from the list.':'L\\\'ar&egrave;ne a &eacute;t&eacute; retir&eacute;e de la liste avec succ&egrave;s.'; ?>';
				document.getElementById("supprButtons").innerHTML = '';
				var cCont = document.createElement("input");
				cCont.type = "button";
				cCont.value = language ? "Continue":"Continuer";
				cCont.onclick = function() {
					document.location.href = "?<?php
					echo 'map='.$map;
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
	$getNote = mysql_query('SELECT note FROM `mknotes` WHERE circuit="'. $id .'" AND identifiant='.$identifiants[0].' AND identifiant2='.$identifiants[1].' AND identifiant3='.$identifiants[2].' AND identifiant4='.$identifiants[3]);
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
		xhr("sendMark.php", "id=<?php echo $id ?>&note="+cNote, function(reponse) {
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
if ($canChange && !isset($infos['o0'])) {
	$message = $language ? 'Warning : your course doesn\'t contain objects !<br />Hard to fight with those conditions...'
	: 'Attention : votre ar&egrave;ne ne contient aucun objet !<br />Difficile de se battre dans ces conditions...';
}
include('ip_banned.php');
if (isBanned())
   echo '&nbsp;';
elseif ($canChange) {
	?>
	<input type="button" id="changeRace" onclick="document.location.href='arene.php'+document.location.search" value="<?php echo ($language ? 'Edit course':'Modifier l\'ar&egrave;ne'); ?>" /><br /><br /><?php
	if (!isset($_GET['id'])) {
		?>
	&nbsp;
		<?php
	}
	?>
	<input type="button" id="shareRace" onclick="document.getElementById('cSave').style.display='block'" value="<?php echo ($language ? 'Share course':'Partager l\'ar&egrave;ne'); ?>"<?php if (isset($message)){echo ' disabled="disabled" class="cannotChange"';$cannotChange=true;} ?> /><?php
	if (isset($_GET['id'])) {
		?>
	<br /><br /><input type="button" id="supprRace" onclick="document.getElementById('confirmSuppr').style.display='block'" value="<?php echo ($language ? 'Delete sharing':'Supprimer partage'); ?>" />
		<?php
	}
}
else {
	?>
	<p id="markMsg"><?php echo $language ? 'Rate this course':'Notez cette ar&egrave;ne'; ?> !</p>
	<?php
	function addStar($i, $a, $apreciation) {
		echo '&nbsp;<img id="star'.$i.'" class="star" src="images/star'.$a.'.png" onclick="setMark('.$i.')" onmouseover="previewMark('.$i.')" onmouseout="updateMark()" title="'.HTMLentities($apreciation).'" /> ';
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
?>
</td></tr>
<tr><td id="pSize">
</td>
<td id="vSize">
&nbsp;
</td></tr>
<tr><td id="pSound">
&nbsp;
</td>
<td id="vSound">
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
if (isset($message)) {
	?>
<div id="alerte"><p id="closeAlert"><a href="javascript:document.getElementById('alerte').style.display='none';void(0)">&times;</a></p>
<p><?php echo $message;
?></p></div>
	<?php
}
/*elseif (isset($nid)) {
	$message = $language ? 'New : a comment section for the circuit creations !':'Nouveau : une section commentaires pour les cr&eacute;ations de circuits !';
	?>
	<div id="alerte"><p id="closeAlert"><a href="javascript:document.getElementById('alerte').style.display='none';void(0)">&times;</a></p><p><?php echo $message; ?></p></div>
	<?php
}*/
?>
<div id="confirmSuppr">
<p id="supprInfos"><?php echo $language ?
	'Delete this course sharing ?<br />
	The course will be only removed from the list :<br />
	data will be retained.' :
	'Supprimer le partage de cette ar&egrave;ne ?<br />
	L\'ar&egrave;ne sera simplement retir&eacute;e de la liste :<br />
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
	<tr><td style="text-align: right"><label for="cName"><?php echo $language ? 'Course name':'Nom de l\'ar&egrave;ne'; ?> :</label></td><td><input type="text" name="cName" id="cName" value="<?php echo escapeUtf8($cName) ?>" /></td></tr>
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
	?>
	<div id="comments-section"></div>
	<script type="text/javascript">
	var commentCircuit = <?php echo $nid; ?>, commentType = "mkcircuits",
	circuitName = "<?php echo addSlashes(escapeUtf8($cName)) ?>", circuitAuthor = "<?php echo addSlashes(escapeUtf8($cPseudo)) ?>", circuitNote = <?php echo $pNote ?>, circuitNotes = <?php echo $pNotes ?>,
	circuitDate = "<?php echo formatDate($cDate); ?>";
	var circuitUser = <?php echo findCircuitUser($cPseudo,$nid,'mkcircuits'); ?>
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