<?php
include('initdb.php');
include('language.php');
require_once('utils-challenges.php');
include('creation-challenges.php');
$id = isset($_GET['i']) ? intval($_GET['i']) : 0;
if ($arene = mysql_fetch_array(mysql_query('SELECT a.*,(a.nom IS NOT NULL) as shared,d.data FROM `arenes` a LEFT JOIN `arenes_data` d ON a.id=d.id WHERE a.id="'.$id.'"'))) {
	$cShared = $arene['shared'];
	if ($arene['nom'] != null)
		$cName = $arene['nom'];
	else
		$cName = '';
	if ($arene['auteur'] != null)
		$cPseudo = $arene['auteur'];
	else
		$cPseudo = isset($_COOKIE['mkauteur']) ? $_COOKIE['mkauteur']:null;
	$cDate = $arene['publication_date'];
	$pNote = $arene['note'];
	$pNotes = $arene['nbnotes'];
	require_once('circuitEscape.php');
	function escapeUtf8($str) {
		return htmlentities(escapeCircuitNames($str));
	}
	include('getId.php');
	addCircuitChallenges('arenes', $id,$arene['nom'], $clPayloadParams);
	$circuitsData = Array($arene);
	$NBCIRCUITS = 1;
	addClChallenges($id, $clPayloadParams);
	$hthumbnail = 'https://mkpc.malahieude.net/coursepreview.php?id='.$id;
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
	echo '"'. ($circuit['nom'] ? addSlashes(escapeUtf8($circuit['nom'])) : "&nbsp;") .'"';
}
?>];
var cp = <?php include('getPersos.php'); ?>;
var pUnlocked = <?php include('getLocks.php'); ?>;
var baseOptions = <?php include('getCourseOptions.php'); ?>;
var page = "BA";
var PERSOS_DIR = "<?php
	require_once('persos.php');
	echo PERSOS_DIR;
?>";
var cShared = <?php echo $cShared ? 'true':'false'; ?>;
var isBattle = true;
var isCup = true;
var isSingle = true;
var complete = true;
var simplified = false;
var nid = <?php echo isset($id) ? $id:'null'; ?>;
var NBCIRCUITS = 0;
function listMaps() {
	return {<?php
	include('mk/battle.php');
	?>};
}
</script>
<?php include('mk/main.php') ?>
<script type="text/javascript">
<?php
require_once('collabUtils.php');
$collab = getCollabLinkFromQuery('arenes', $id);
if ($arene) {
	$creator = (($arene['identifiant'] == $identifiants[0]) && ($arene['identifiant2'] == $identifiants[1]) && ($arene['identifiant3'] == $identifiants[2]) && ($arene['identifiant4'] == $identifiants[3]));
	$canChange = $creator || isset($collab['rights']['view']);
	$canShare = $creator || isset($collab['rights']['edit']);
}
else {
	$creator = false;
	$canChange = false;
	$canShare = false;
}
if ($canChange) {
	$shared = mysql_numrows(mysql_query('SELECT * FROM `arenes` WHERE id="'.$id.'" AND nom IS NOT NULL'));
	?>
	function saveRace() {
		document.getElementById("cAnnuler").disabled = true;
		document.getElementById("cAnnuler").className = "cannotChange";
		document.getElementById("cEnregistrer").disabled = true;
		document.getElementById("cEnregistrer").className = "cannotChange";
		xhr("saveBattle.php", "id=<?php
			echo $id;
			if ($clId) echo '&cl='. $clId;
			if ($collab) echo '&collab='.$collab['key'];
		?>&nom="+ getValue("cName") +"&auteur="+ getValue("cPseudo"), function(reponse) {
			if (reponse == 1) {
				document.getElementById("cSave").removeChild(document.getElementById("cTable"));
				var cP = document.createElement("p");
				cP.style.margin = "5px";
				cP.style.textAlign = "center";
				cP.innerHTML = '<?php
					if ($shared)
						echo $language ? 'The sharing of your course has been updated.':'Le partage de votre ar&egrave;ne a &eacute;t&eacute; mis &agrave; jour.';
					else
						echo $language ? 'Your course has just been added to the <a href="creations.php" target="_blank">list</a> !':'Votre ar&egrave;ne vient d\\\'&ecirc;tre ajout&eacute; &agrave; la <a href="creations.php" target="_blank">liste</a> !';
				?><br /><br />';
				var cCont = document.createElement("input");
				cCont.type = "button";
				cCont.value = language ? "Continue":"Continuer";
				cCont.onclick = function() {
					location.reload();
				};
				cP.appendChild(cCont);
				document.getElementById("cSave").appendChild(cP);
				return true;
			}
			return true;
		});
	}
	<?php
	if ($shared) {
		?>
	function supprRace() {
		document.getElementById("sAnnuler").disabled = true;
		document.getElementById("sAnnuler").className = "cannotChange";
		document.getElementById("sConfirmer").disabled = true;
		document.getElementById("sConfirmer").className = "cannotChange";
		xhr("supprBattle.php", "id=<?php
			echo $id;
			if ($collab) echo '&collab='.$collab['key'];
		?>", function(reponse) {
			if (reponse == 1) {
				document.getElementById("supprInfos").innerHTML = '<?php echo $language ? 'The course has been successfully removed from the list.':'L\\\'ar&egrave;ne a &eacute;t&eacute; retir&eacute;e de la liste avec succ&egrave;s.'; ?>';
				document.getElementById("supprButtons").innerHTML = '';
				var cCont = document.createElement("input");
				cCont.type = "button";
				cCont.value = language ? "Continue" : "Continuer";
				cCont.onclick = function() {
					document.location.href = "?i=<?php
						echo $id;
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
	$shared = false;
	require_once('utils-ratings.php');
	$cNote = getMyRating('arenes', $id);
	?>
	var cNote = <?php echo $cNote ?>;
	var ratingParams = "id=<?php echo $id ?>&complete=2";
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
/*if ($cShared)
	$message = $language ? 'New : a comment section for the circuit creations !':'Nouveau : une section commentaires pour les cr&eacute;ations de circuits !';*/
if ($canChange) {
	if (!isset($circuitMainData->bgcolor))
		$message = $language ? 'Warning: You didn\'t specify any data for the circuit.<br />Go back to the editor before testing it.':'Attention : vous n\'avez pas encore spécifié les paramètres du circuit.<br />Revenez dans l\'éditeur avant de continuer.';
	elseif (count($circuitMainData->startposition) < 8)
		$message = $language ? 'Warning: You did not indicate all the start positions.<br />Quite annoying, we don\'t know where to begin.':'Attention : Vous n\'avez pas indiqu&eacute; toutes les positions de d&eacute;part !<br />C\'est ennuyeux, on ne sait pas par o&ucirc; commencer...';
	elseif (empty($circuitPayload->arme))
		$message = $language ? 'Warning: your course doesn\'t contain objects !<br />Hard to fight with those conditions...' :'Attention : votre ar&egrave;ne ne contient aucun objet !<br />Difficile de se battre dans ces conditions...';
	elseif (empty($circuitPayload->aipoints))
		$message = $language ? 'Warning: you have not indicated the trajectory of CPUs. They<br />may not know where to go...' :'Attention : vous n\'avez pas indiqu&eacute;<br />la trajectoire des ordis. Ils risque de ne pas<br />savoir o&ucirc; aller...';
	elseif (!$circuitPayload->aipoints[count($circuitPayload->aipoints)-1][0])
		$message = $language ? 'Warning: you have not connected the dots<br />indicating the trajectory of CPUs. They<br />may be stuck in the same place ...' :'Attention : vous n\'avez pas reli&eacute; les points indiquant<br />la trajectoire des ordis. Ils risque de rester<br />bloqu&eacute;s au m&ecirc;me endroit...';
}
include('ip_banned.php');
if (isBanned())
  echo '&nbsp;';
elseif ($canChange) {
	?>
	<input type="button" id="changeRace"<?php if (!$creator) echo ' data-collab="1"'; ?> onclick="document.location.href='course.php?i=<?php
		echo $id;
		if ($collab) echo '&collab='. $collab['key'];
	?>'" value="<?php echo ($language ? 'Edit course':'Modifier l\'ar&egrave;ne'); ?>" /><br /><?php
	if ($creator) {
		?>
		<br class="br-small" />
		<input type="button" id="linkRace" onclick="showCollabPopup('arenes', <?php echo $id; ?>)" value="<?php echo ($language ? 'Collaborate...':'Collaborer...'); ?>" /><br /><br />
		<?php
	}
	else {
		?>
		<br />
		<?php
	}
	if (!$shared) {
		?>
	&nbsp;
		<?php
	}
	if ($canShare) {
		?>
	<input type="button" id="shareRace" onclick="document.getElementById('cSave').style.display='block'" value="<?php
	if ($shared)
		echo $language ? 'Edit sharing':'Modifier partage';
	else
		echo $language ? 'Share course':'Partager l\'ar&egrave;ne';
	?>"<?php if (isset($message)){echo ' disabled="disabled" class="cannotChange"';$cannotChange=true;} ?> /><?php
		if ($shared) {
			?>
	<br /><br /><input type="button" id="supprRace" onclick="document.getElementById('confirmSuppr').style.display='block'" value="<?php echo ($language ? 'Delete sharing':'Supprimer partage'); ?>" />
			<?php
		}
	}
}
else
	printRatingView($language ? 'Rate this course!':'Notez cette ar&egrave;ne !');
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
if (isset($message)) {
	?>
	<div id="alerte"><p id="closeAlert"><a href="javascript:document.getElementById('alerte').style.display='none';void(0)">&times;</a></p><p><?php echo $message; ?></p></div>
	<?php
}
?>
<div id="confirmSuppr">
<p id="supprInfos"><?php echo $language ?
	'Delete this course sharing ?<br />
	The course will be only removed from the list :<br />
	data will be retained.' :
	'Supprimer le partage de cette ar&egrave;ne ?<br />
	L\'ar&egrave;ne sera simplement retir&eacute;e de la liste :<br />
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
	<tr><td style="text-align: right"><label for="cName"><?php echo $language ? 'Course name':'Nom de l\'ar&egrave;ne'; ?> :</label></td><td><input type="text" name="cName" id="cName" value="<?php echo escapeUtf8($cName) ?>" /></td></tr>
	<tr><td colspan="2" id="cSubmit"><input type="button" value="<?php echo $language ? 'Cancel':'Annuler'; ?>" id="cAnnuler" onclick="document.getElementById('cSave').style.display='none'" /> &nbsp; <input type="submit" value="<?php echo $language ? 'Share':'Partager'; ?>" id="cEnregistrer" /></td></tr>
	</table>
	</form>
	<?php
}
?>
<?php
require_once('collabUtils.php');
include('gameInitElts.php');
if ($cShared) {
	include('circuitUser.php');
	require_once('reactions.php');
	printReactionUI();
	?>
	<div id="comments-section"></div>
	<script type="text/javascript">
	var commentCircuit = <?php echo $id; ?>, commentType = "arenes",
	circuitName = "<?php echo addSlashes(escapeUtf8($cName)) ?>", circuitAuthor = "<?php echo addSlashes(escapeUtf8($arene['auteur'])) ?>", circuitNote = <?php echo $pNote ?>, circuitNotes = <?php echo $pNotes ?>,
	circuitDate = "<?php echo formatDate($cDate); ?>";
	var circuitUser = <?php echo findCircuitUser($arene['auteur'],$id,'arenes'); ?>;
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
}
mysql_close();
?>