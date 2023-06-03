<?php
include('../includes/language.php');
include('../includes/session.php');
include('../includes/tokens.php');
require_once('../includes/utils-circuits.php');
include('../includes/creations-params.php');
assign_token();
include('../includes/initdb.php');
require_once('../includes/getRights.php');
$isModerator = hasRight('moderator');
?>
<!DOCTYPE html>
<html lang="<?php echo $language ? 'en':'fr'; ?>">
<head>
<title><?php echo $language ? 'All shared circuits':'Tous les circuits partagés'; ?> - Mario Kart PC</title>
<?php
include('../includes/heads.php');
?>
<style type="text/css">
#form-search {
	text-align: center;
	margin-bottom: 10px;
}
#sort-tabs {
	display: table;
	margin-left: auto;
	margin-right: auto;
	margin-bottom: 6px;
	text-align: center;
	font-size: 0.9em;
}
#sort-tabs > * {
	display: table-cell;
	vertical-align: middle;
	border-top: solid 1px #820;
	border-bottom: solid 1px #820;
	padding: 6px 10px;
}
@media screen and (max-width: 500px) {
	#sort-tabs {
		font-size: 0.7em;
	}
	#sort-tabs > * {
		padding: 5px 8px;
	}
}
#sort-tabs > a {
	background-color: #FFE30C;
	color: #F60;
	text-decoration: none;
}
#sort-tabs > a:hover {
	background-color: #FFD816;
	color: #C30;
}
#sort-tabs > span {
	background-color: #FFC02C;
	color: #820;
	font-weight: bold;
}
#sort-tabs > *:first-child {
	border-left: solid 1px #820;
	border-top-left-radius: 5px;
	border-bottom-left-radius: 5px;
}
#sort-tabs > *:last-child {
	border-right: solid 1px #820;
	border-top-right-radius: 5px;
	border-bottom-right-radius: 5px;
}
.pub {
	width: 100%;
	overflow: hidden;
	text-align: center;
}
main {
	position: relative;
}
.pretty-link {
	color: #F90;
}
.pretty-link:hover {
	color: #FB0;
}
main select {
	background-color: #FC0;
	color: black;
	border: solid 1px maroon;
	padding: 2px;
}
main select:hover {
	background-color: #F90;
}
main select:active {
	background-color: #F60;
}
main input[type="text"] {
	background-color: #FFEE99;
	width: 100px;
	padding: 2px;
}
@media screen and (max-width: 500px) {
	main input[type="text"] {
		width: 60px;
	}
}
main input[type="text"].small {
	width: 50px;
}
main input[type="text"]:hover {
	background-color: #FFF3A9;
}
main input[type="text"]:focus {
	background-color: #FFF6CC;
}
main input[type="url"] {
	width: 200px;
	padding: 2px;
}
main form div {
	margin: 2px 0px;
}
h1, h2 {
	text-decoration: underline;
	font-family: Verdana;
	color: #560000;
}
@media screen and (min-width: 880px) {
	h2, .subbuttons {
		margin-left: 15%;
		margin-left: calc(50% - 350px);
	}
	.subbuttons {
		margin-right: 15%;
		margin-right: calc(50% - 350px);
	}
}
.hidden {
	height: <?php echo ($MAX_CIRCUITS*29+2); ?>px;
	overflow: hidden;
	text-align: center;
}
input.defiler {
	margin-left: 5px;
}
.defiler {
	position: relative;
    top: -4px;
}
a.defiler::before {
	content: "+";
	color: #FED;
	margin-right: 5px;
}
a.defiler {
	float: right;
	background-color: #F90;
}
a.defiler:hover {
	float: right;
	background-color: #FB0;
}
.liste {
	max-width: 851px;
	margin-left: auto;
	margin-right: auto;
	text-align: center;
	line-height: 0;
}
.liste > a {
	line-height: normal;
	position: relative;
	font-family: Helvetica, arial, sans-serif;
	text-align: center;
	width: 130px;
	height: 130px;
	margin: 8px 20px;
	display: inline-block;
}
.liste > a:hover {
	background-color: rgba(0,0,0,0.2);
}
@media screen and (max-width: 880px) {
	.liste > a {
		width: 110px;
		height: 110px;
	}
	.liste .circuit-name {
		font-size: 0.9em;
	}
	.hidden {
		height: <?php echo ($MAX_CIRCUITS*25+2); ?>px;
	}
}
.circuit-poster {
	background-position: center;
	background-size: cover;
	background-repeat: no-repeat;
}
.cup-poster {
	background-repeat: no-repeat;
	background-position: top left, top right, bottom left, bottom right;
	background-size: 50% 50%;
	background-size: calc(50% + 1px) calc(50% + 1px);
}
.liste .circuit-rate {
	display: inline-block;
	position: absolute;
	left: 0;
	bottom: 0;
	width: 100%;
	background-color: rgba(255,255,255, 0.6);
	opacity: 0.8;
}
.liste a:hover .circuit-rate {
	opacity: 1;
}
.circuit-star {
	width: 15px;
	position: relative;
}
.circuit-star > div {
	position: absolute;
	left: 0;
	top: 1px;
	overflow: hidden;
}
.circuit-name {
	display: none;
	position: absolute;
	left: 10%;
	top: 50%;
	width: 80%;
	-webkit-transform: translateY(-50%);
	-moz-transform: translateY(-50%);
	-o-transform: translateY(-50%);
	-ms-transform: translateY(-50%);
	transform: translateY(-50%);
	background-color: white;
	color: black;
	opacity: 0.8;
	padding-top: 2px;
	padding-bottom: 2px;
	word-wrap: break-word;
}
.circuit-author {
	margin-top: 1px;
	margin-bottom: 1px;
	font-size: 0.7em;
	opacity: 0.6;
}
.circuit-name small {
	font-size: 0.6em;
	position: relative;
	bottom: 0.15em;
}
.circuit-author img {
	height: 0.8em;
	position: relative;
	top: 1px;
}
.circuit-nbcomments {
	position: absolute;
	left: 0;
	top: 0;
	color: black;
	background-color: rgba(255,255,255, 0.6);
	padding: 2px 5px;
	border-bottom: solid 1px #AAA;
	border-right: solid 1px #AAA;
	border-bottom-right-radius: 3px;
	font-size: 12px;
}
.circuit-preview {
	display: none;
	position: absolute;
	right: 0;
	top: 0;
	background-color: rgba(255,255,255, 0.6);
	padding: 0px 5px;
	border-bottom: solid 1px #AAC;
	border-left: solid 1px #AAC;
	border-bottom-left-radius: 3px;
	cursor: zoom-in;
}
.circuit-preview:hover {
	background-color: rgba(230,230,230, 0.6);
}
.circuit-suppr {
	position: absolute;
	left: 40%;
	width: 20%;
	background-color: #FEE;
	opacity: 0.7;
	color: #F00;
}
.circuit-suppr:hover {
	opacity: 1;
}
.circuit-nbcomments img {
	height: 12px;
	position: relative;
	top: 2px;
}
.circuit-preview img {
	height: 12px;
}
.liste > a:hover .circuit-name {
	display: inline-block;
}
.liste > a:hover .circuit-preview {
	display: inline-block;
}
.liste .circuit-star {
	display: inline-block;
	padding-top: 1px;
}
.form-toggle-status {
	font-size: 0.8em;
	color: white;
	padding: 0.2em 0.5em;
	border-radius: 0.5em;
	position: relative;
	top: -0.05em;
}
.form-toggle-status-disabled {
	background-color: #EA8;
}
.form-toggle-status-enabled {
	background-color: #9D7;
}
</style>
<link rel="stylesheet" type="text/css" href="styles/creations.css" />

<?php
include('../includes/o_online.php');
?>
<script type="text/javascript">
<?php
$managing = false;
if (isset($_GET['admin']) && $isModerator)
	$managing = true;
$tri = isset($_GET['tri']) ? intval($_GET['tri']):0;
$type = isset($_GET['type']) ? $_GET['type']:'';
$nom = isset($_GET['nom']) ? stripslashes($_GET['nom']):'';
$auteur = isset($_GET['auteur']) ? stripslashes($_GET['auteur']):'';
$prefix = isset($_GET['prefix']) ? stripslashes($_GET['prefix']):'';
$url = isset($_GET['url']) ? stripslashes($_GET['url']):'';
$noThumbnail = !empty($_GET['nothumbnail']);
$pids = null;
if (isset($_GET['user'])) {
	$user = $_GET['user'];
	if ($getProfile = mysql_fetch_array(mysql_query('SELECT identifiant,identifiant2,identifiant3,identifiant4 FROM `mkprofiles` WHERE id="'. $user .'"')))
		$pids = array($getProfile['identifiant'],$getProfile['identifiant2'],$getProfile['identifiant3'],$getProfile['identifiant4']);
}
else
	$user = '';
if ($managing && $url) {
    include('../includes/adminUtils.php');
	$circuitData = getCreationByUrl($url);
	if ($circuitData) {
		$aParams = array(
			'max_circuits' => 1,
			'type' => $circuitData['filter'],
			'id' => $circuitData['id']
		);
	}
	else {
		$aParams = array(
			'max_circuits' => 0,
			'type' => 0,
			'id' => -1
		);
	}
}
else {
	$aParams = array(
		'type' => $type,
		'tri' => $tri,
		'nom' => $nom,
		'auteur' => $auteur,
		'prefix' => $prefix,
		'pids' => $pids,
		'max_circuits' => $MAX_CIRCUITS,
	);
}
$pType = $aParams['type'];
$singleType = ($pType !== '');
if ($singleType) {
	if (isset($aCircuits[$pType])) {
		$aCircuits = array($aCircuits[$pType]);
		$weightsByType = array($weightsByType[$pType]);
	}
	else {
		$singleType = true;
		$pType = '';
		$aParams['type'] = $pType;
	}
}
if ($noThumbnail)
	$aParams['no_thumbnail'] = 1;
$nbByType = countTracksByType($aCircuits,$aParams);
$creationsList = listCreations(1,$nbByType,$weightsByType,$aCircuits,$aParams);
$nbCreations = array_sum($nbByType);
echo 'var lCircuits=[';
printCircuits($creationsList);
echo '];';
echo 'var nCircuits='.$nbCreations.';';
mysql_close();
?>
</script>
</head>
<body>
<?php
include('../includes/header.php');
$page = 'game';
include('../includes/initdb.php');
include('../includes/menu.php');
?>
<main>
	<h1><?php
		if ($pids) {
			$username = mysql_fetch_array(mysql_query('SELECT nom FROM `mkjoueurs` WHERE id="'. $user .'"'));
			echo $language ? ($username['nom']."'s creations"):'Liste des créations de '. $username['nom'];
		}
		else
			echo $language ? 'Creations list of Mario Kart PC':'Liste des créations Mario Kart PC';
	?> (<?php echo $nbCreations; ?>)</h1>
	<p><?php
	if (!$pids) {
		echo $language ?
		'Welcome to the list of circuits and courses shared by the Mario Kart PC community !<br />
		You too can share your circuit creations by clicking on &quot;Share circuit&quot; at the bottom-left of the circuit page.' :
		'Bienvenue dans la liste des circuits et arènes partagés par la communauté de Mario Kart PC !<br />
		Vous aussi, partagez les circuits que vous créez en cliquant sur &quot;Partager le circuit&quot; en bas à gauche de la page du circuit.';
	}
	?></p>
	<form method="get" action="creations.php" id="form-search">
		<div id="sort-tabs">
			<?php
			$sortTabs = $language ? Array('Latest', 'Top rated', 'Trending'):Array('Les plus récents', 'Les mieux notés', 'Tendances');
			foreach ($sortTabs as $i => $sortTab) {
				if ($i == $tri)
					echo '<span>'.$sortTab.'</span>';
				else {
					$getParams = $_GET;
					$getParams['tri'] = $i;
					echo '<a href="?'. http_build_query($getParams) .'" onclick="selectTab('.$i.');return false">'.$sortTab.'</a>';
				}
			}
				
			?>
		</div>
		<div><strong><?php echo $language ? 'Creation type':'Type de création'; ?></strong> :
		<select name="type" onchange="this.form.submit()">
		<?php
		$types = $language
		 ? Array('Complete mode  - multicups',	'Quick mode - multicups',		'Complete mode - cups',	'Quick mode - cups',		'Complete mode - circuits',	'Quick mode - circuits',	'Complete mode - arenas','Quick mode - arenas',	'Complete mode - battle cups','Quick mode - battle cups',	'Complete mode - battle multicups','Quick mode - battle multicups')
		 : Array('Mode complet  - multicoupes',	'Mode simplifié - multicoupes',	'Mode complet - coupes',	'Mode simplifié - coupes',	'Mode complet - circuits',	'Mode simplifié - circuits','Mode complet - arènes','Mode simplifié - arènes','Mode complet - coupes bataille','Mode simplifié - coupes bataille','Mode complet - multicoupes bataille','Mode simplifié - multicoupes bataille');
		echo '<option value=""'. (($type === '') ? ' selected="selected"':'') .'">'. ($language ? 'All creations':'Toutes les créations') . ($singleType ? '':' ('. $nbCreations .')') .'</option>';
		foreach ($types as $i=>$iType)
			echo '<option value="'. $i .'"'. ((strval($i) === $type) ? ' selected="selected"':'') .'>'. $iType . ($singleType ? '':' ('.$nbByType[$i].')') .'</option>';
		?>
		</select></div>
		<div><strong><?php echo $language ? 'Search':'Recherche'; ?></strong> :
			<?php
			if (isset($_GET['admin']))
				echo '<input type="hidden" name="admin" value="1" />';
			if ($noThumbnail)
				echo '<input type="hidden" name="nothumbnail" value="1" />';
			?>
			<input type="hidden" name="user" value="<?php echo htmlspecialchars($user); ?>" />
			<input type="hidden" name="tri" id="tri" value="<?php echo htmlspecialchars($tri); ?>" />
			<input type="text" name="nom" placeholder="<?php echo $language ? 'Name':'Nom'; ?>" value="<?php echo htmlspecialchars($nom); ?>" />
			<input type="text" name="auteur" placeholder="<?php echo $language ? 'Author':'Auteur'; ?>" value="<?php echo htmlspecialchars($auteur); ?>" />
			<input type="text" name="prefix" class="small" placeholder="<?php echo $language ? 'Prefix':'Préfixe'; ?>" value="<?php echo htmlspecialchars($prefix); ?>" />
			<input type="submit" value="Ok" class="action_button" />
		</div>
		<?php
		if ($managing) {
			?>
			<div>
				<label>
					<?php echo $language ? '<em>OR</em> &nbsp;<strong>Track URL</strong>':'<em>OU</em> &nbsp;<strong>URL circuit</strong>'; ?> :
					<input type="url" name="url" placeholder="https://mkpc.malahieude.net/map.php?i=42" value="<?php echo htmlspecialchars($url); ?>" />
				</label>
				<input type="submit" value="Ok" class="action_button" />
			</div>
			<?php
		}
		if ($isModerator) {
			?>
			<div class="form-toggle">
				<strong>
					<?php echo $language ? 'Moderation actions':'Actions de modération'; ?>
				</strong>
				<?php
				if ($managing) {
					echo '<span class="form-toggle-status form-toggle-status-enabled">';
					echo $language ? 'Enabled' : 'Activé';
					echo '</span>';
					?>
					<a class="pretty-link" href="creations.php?<?php
						$get = $_GET;
						unset($get['admin']);
						echo http_build_query($get);
					?>">[<?php echo $language ? 'Disable':'Désactiver'; ?>]</a>
					<?php
				}
				else {
					echo '<span class="form-toggle-status form-toggle-status-disabled">';
					echo $language ? 'Disabled' : 'Désactivé';
					echo '</span>';
					?>
					<a class="pretty-link" href="creations.php?<?php
						$get = $_GET;
						$get['admin'] = 1;
						echo http_build_query($get);
					?>">[<?php echo $language ? 'Enable':'Activer'; ?>]</a>
					<?php
				}
				?>
			</div>
			<?php
		}
		?>
		<div class="form-toggle">
			<strong>
				<?php echo $language ? 'Custom thumbnails':'Miniatures custom'; ?>
			</strong>
			<?php
			if ($noThumbnail) {
				echo '<span class="form-toggle-status form-toggle-status-disabled">';
				echo $language ? 'Disabled' : 'Désactivé';
				echo '</span>';
				?>
				<a class="pretty-link" href="creations.php?<?php
					$get = $_GET;
					unset($get['nothumbnail']);
					echo http_build_query($get);
				?>">[<?php echo $language ? 'Enable':'Activer'; ?>]</a>
				<?php
			}
			else {
				echo '<span class="form-toggle-status form-toggle-status-enabled">';
				echo $language ? 'Enabled' : 'Activé';
				echo '</span>';
				?>
				<a class="pretty-link" href="creations.php?<?php
					$get = $_GET;
					$get['nothumbnail'] = 1;
					echo http_build_query($get);
				?>">[<?php echo $language ? 'Disable':'Désactiver'; ?>]</a>
				<?php
			}
			?>
		</div>
	</form>
	<script async src="//pagead2.googlesyndication.com/pagead/js/adsbygoogle.js"></script>
	<!-- Forum MKPC -->
	<div class="pub"><ins class="adsbygoogle"
	     style="display:inline-block;width:728px;height:90px"
	     data-ad-client="ca-pub-1340724283777764"
	     data-ad-slot="4919860724"></ins></div>
	<script>
	(adsbygoogle = window.adsbygoogle || []).push({});
	</script>
	<div id="cTracks" class="hidden">
	<div class="liste" id="liste">
	</div>
	</div>
	<p class="subbuttons">
	<input type="button" id="defiler" class="defiler action_button" value="<?php echo $language ? 'More':'Plus'; ?>" onclick="defile()" /> &nbsp; 
	<input type="button" id="masquer" class="defiler action_button" value="<?php echo $language ? 'Less':'Moins'; ?>" style="visibility: hidden" onclick="masque()" /> &nbsp; 
	<input type="button" id="reduire" class="defiler action_button" value="<?php echo $language ? 'Minimize':'Réduire'; ?>" style="visibility: hidden" onclick="reduceAll()" />
	</p>

	<p>
		<a class="pretty-link" href="javascript:scrollTo(0,0)"><?php echo $language ? 'Back to top':'Retour haut de page'; ?></a> - 
		<a class="pretty-link" href="index.php"><?php echo $language ? 'Back to Mario Kart PC':'Retour à Mario Kart PC'; ?></a>
	</p>
</main>
<?php
include('../includes/footer.php');
?>
<script type="text/javascript">

var wStep = 169, hStep = 146, dhStep = 10;
try {
	if (window.matchMedia('(max-width: 800px)').matches) {
		wStep = 150;
		hStep = 126;
	}
}
catch (e) {
}
var pageRace = 1;
var idsRace = 0;
var CIRCUITS_INC = <?php echo $MAX_CIRCUITS ?>, CIRCUITS0, initHeight;
function defile() {
	document.getElementById("masquer").style.visibility = "visible";
	document.getElementById("defiler").disabled = true;
	document.getElementById("masquer").disabled = true;
	document.getElementById("reduire").style.visibility = "visible";
	var cHeight = document.getElementById("cTracks").offsetHeight;
	var mHeight = Math.min(cHeight+hStep, document.getElementById("cTracks").scrollHeight);
	nbsRace = Math.min(nbsRace+CIRCUITS0, nCircuits);
	if (nbsRace <= lCircuits.length)
		progressDefile(cHeight, mHeight, dhStep);
	else {
		<?php
		$nextCircuitsParams = array(
			'type' => $type,
			'tri' => $tri,
			'nom' => $nom,
			'auteur' => $auteur,
			'prefix' => $prefix,
			'user' => $user
		);
		if ($noThumbnail)
			$nextCircuitsParams['nothumbnail'] = 1;
		foreach ($nbByType as $i=>$nb)
			$nextCircuitsParams['nb'.$i] = $nb;
		?>
		pageRace++;
		o_xhr("next-circuits.php", "<?php echo http_build_query($nextCircuitsParams); ?>&page="+pageRace, function(res) {
			if (res != "") {
				var data = JSON.parse(res);
				var nextCircuits = data.circuits;
				for (var i=0;i<nextCircuits.length;i++)
					lCircuits.push(nextCircuits[i]);
				for (var i=0;i<nextCircuits.length;i++)
					addRace();
				iconDelayDt = 50;
				loadCircuitImgs();
				mHeight = Math.min(cHeight+hStep, document.getElementById("cTracks").scrollHeight);
				progressDefile(cHeight, mHeight, dhStep);
				return true;
			}
			return false;
		});
	}
}
function progressDefile(height, until, tStep) {
	height += tStep;
	if (height >= until) {
		height = until;
		document.getElementById("defiler").disabled = false;
		document.getElementById("masquer").disabled = false;
		document.getElementById("reduire").disabled = false;
		if (nbsRace >= nCircuits)
			document.getElementById("defiler").style.visibility = "hidden";
	}
	else
		setTimeout(function(){progressDefile(height,until,tStep)}, 50);
	document.getElementById("cTracks").style.height = height +"px";
}
function masque() {
	nbsRace -= CIRCUITS0;
	document.getElementById("defiler").style.visibility = "visible";
	document.getElementById("defiler").disabled = true;
	document.getElementById("masquer").disabled = true;
	document.getElementById("reduire").disabled = true;
	var cHeight = document.getElementById("cTracks").offsetHeight;
	progressMasque(cHeight, cHeight-hStep, dhStep);
}

function progressMasque(height, until, tStep) {
	height -= tStep;
	if (height <= until) {
		height = until;
		document.getElementById("defiler").disabled = false;
		document.getElementById("masquer").disabled = false;
		document.getElementById("reduire").disabled = false;
		if (nbsRace <= CIRCUITS_INC)
			document.getElementById("masquer").style.visibility = "hidden";
		if (nbsRace <= CIRCUITS0)
			document.getElementById("reduire").style.visibility = "hidden";
	}
	else
		setTimeout(function(){progressMasque(height,until,tStep)}, 50);
	document.getElementById("cTracks").style.height = height +"px";
}
function reduceAll() {
	nbsRace = CIRCUITS0;
	document.getElementById("defiler").style.visibility = "visible";
	document.getElementById("defiler").disabled = true;
	document.getElementById("masquer").disabled = true;
	document.getElementById("reduire").disabled = true;
	document.getElementById("masquer").style.visibility = "hidden";
	document.getElementById("reduire").style.visibility = "hidden";
	progressMasque(document.getElementById("cTracks").offsetHeight, initHeight, Math.round(lCircuits.length*1.65));
}
function replaceAll(str, toRemove, toAdd) {
	return str.split(toRemove).join(toAdd);
}
function HTMLentities(str) {
	return replaceAll(replaceAll(replaceAll(replaceAll(str,"&","&amp;"),'>',"&gt;"),'<',"&lt;"),'"',"&quot;");
}
function addRace() {
	var circuit = lCircuits[idsRace];
	var id = circuit.id, auteur = circuit.author, note = circuit.note, nbnotes = circuit.nbnotes, nbcomments = circuit.nbcomments, type = circuit.category;
	var lNom = circuit.name ? circuit.name:"<?php echo ($language?'Untitled':'Sans titre'); ?>";
	var lPrefix = circuit.prefix;
	var oLink = document.createElement("a");
	oLink.href = circuit.href;
	oLink.className = (circuit.cicon.indexOf(",")!=-1) ? "cup-poster":"circuit-poster";
	if (circuit.icon) {
		var bgSrcs = [];
		for (var i=0;i<circuit.icon.length;i++)
			bgSrcs.push("url('images/creation_icons/"+circuit.icon[i]+"')");
		oLink.style.backgroundImage = bgSrcs.join(",");
	}
	else
		oLink.dataset.cicon = circuit.cicon;

	if (lNom || auteur) {
		if (lNom) {
			var oCircuitName = document.createElement("div");
			oCircuitName.className = "circuit-name";
			var oCircuitTitle = document.createElement("div");
			oCircuitTitle.className = "circuit-title";
			var nLimit = 30;
			if (lPrefix && (lNom.length+lPrefix.length < nLimit)) {
				oCircuitTitle.innerHTML = '<small>'+HTMLentities(lPrefix)+'</small> ' + HTMLentities(lNom);
			}
			else if (lNom.length < nLimit)
				oCircuitTitle.innerHTML = HTMLentities(lNom);
			else {
				oCircuitTitle.innerHTML = HTMLentities(lNom.substring(0,nLimit-3)+"...");
				oLink.title = lNom;
			}
			oCircuitName.appendChild(oCircuitTitle);
		}
		if (auteur) {
			var oCircuitAuthor = document.createElement("div");
			oCircuitAuthor.className = "circuit-author";
			nLimit = 15;
			var authorContent;
			if (auteur.length < nLimit)
				authorContent = HTMLentities(auteur);
			else
				authorContent = HTMLentities(auteur.substring(0,nLimit-3)+"...");
			oCircuitAuthor.innerHTML = '<img src="images/user.png" alt="Author" /> '+ authorContent;
			oCircuitName.appendChild(oCircuitAuthor);
		}
		oLink.appendChild(oCircuitName);
	}

	var oCircuitRate = document.createElement("div");
	oCircuitRate.className = "circuit-rate";
	oCircuitRate.title = nbnotes ? (Math.round(note*100)/100) +"/5 <?php echo $language ? 'on':'sur'; ?> "+ nbnotes +" <?php echo $language ? 'rating':'note'; ?>"+ (nbnotes>1 ? "s":"") : "<?php echo $language ? 'Unrated':'Non noté'; ?>";
	for (var i=1;i<=note;i++) {
		var oEtoile = document.createElement("div");
		oEtoile.className = "circuit-star";
		var eImg = document.createElement("img");
		eImg.src = "images/ministar1.png";
		eImg.alt = "star0";
		oEtoile.appendChild(eImg);
		oCircuitRate.appendChild(oEtoile);
	}
	var rest = note-Math.floor(note);
	if (rest) {
		var w1 = (3+Math.round(9*rest));
		var oEtoile = document.createElement("div");
		oEtoile.className = "circuit-star";
		var eImg = document.createElement("img");
		eImg.src = "images/ministar0.png";
		eImg.alt = "star1";
		oEtoile.appendChild(eImg);
		var oEtoile2 = document.createElement("div");
		oEtoile2.style.width = w1 +"px";
		var eImg2 = document.createElement("img");
		eImg2.src = "images/ministar1.png";
		eImg2.alt = "star0";
		oEtoile2.appendChild(eImg2);
		oEtoile.appendChild(oEtoile2);
		oCircuitRate.appendChild(oEtoile);
		note++;
	}
	for (var i=note;i<5;i++) {
		var oEtoile = document.createElement("div");
		oEtoile.className = "circuit-star";
		var eImg = document.createElement("img");
		eImg.src = "images/ministar0.png";
		eImg.alt = "star0";
		oEtoile.appendChild(eImg);
		oCircuitRate.appendChild(oEtoile);
	}
	oLink.appendChild(oCircuitRate);

	var oCircuitComments = document.createElement("div");
	oCircuitComments.className = "circuit-nbcomments";
	oCircuitComments.title = nbcomments ? (nbcomments +" <?php echo $language ? 'comment':'commentaire'; ?>"+ ((nbcomments>1) ? "s":"")):"<?php echo $language ? 'No comments':'Aucun commentaire'; ?>";
	var cImg = document.createElement("img");
	cImg.src = "images/comments.png";
	cImg.alt = "Comments";
	oCircuitComments.appendChild(cImg);
	var oCommentsNb = document.createElement("span");
	oCommentsNb.innerHTML = " " + nbcomments;
	oCircuitComments.appendChild(oCommentsNb);
	oLink.appendChild(oCircuitComments);

	var oCircuitPreview = document.createElement("div");
	oCircuitPreview.className = "circuit-preview";
	oCircuitPreview.title = "<?php echo ($language?'Preview':'Aperçu'); ?>";
	oCircuitPreview.onclick = function(e) {
		apercu(circuit.srcs);
		return false;
	};
	var pImg = document.createElement("img");
	pImg.src = "images/preview.png";
	pImg.alt = "preview";
	oCircuitPreview.appendChild(pImg);
	<?php
	if ($managing) {
		?>
	var oCircuitSuppr = document.createElement("div");
	oCircuitSuppr.className = "circuit-suppr";
	oCircuitSuppr.title = "Supprimer le circuit";
	oCircuitSuppr.onclick = function(e) {
		if (confirm("Supprimer ce circuit ?")) {
			var supprUrl;
			var supprData;
			switch (type) {
			case 0 :
			case 1 :
			case 10 :
			case 11 :
				supprUrl = "supprMCup.php";
				supprData = "id="+ id;
				break;
			case 2 :
			case 3 :
			case 8 :
			case 9 :
				supprUrl = "supprCup.php";
				supprData = "id="+ id;
				break;
			case 4 :
				supprUrl = "suppr.php?i="+ id +"&token=<?php echo $_SESSION['csrf']; ?>";
				break;
			case 5 :
			case 7 :
				supprUrl = "supprCreation.php";
				supprData = "id="+ id;
				break;
			case 6 :
				supprUrl = "clear.php?i="+ id +"&token=<?php echo $_SESSION['csrf']; ?>";
				break;
			}
			o_xhr(supprUrl, supprData, function(res) {
				if (res != "") {
					oLink.style.visibility = "hidden";
					return true;
				}
				return false;
			});
		}
		return false;
	};
	oCircuitSuppr.innerHTML = "&times;";
	oLink.appendChild(oCircuitSuppr);
		<?php
	}
	?>
	oLink.appendChild(oCircuitPreview);
	document.getElementById("liste").appendChild(oLink);
	idsRace++;
}
function addRaces() {
	for (var i=0;i<lCircuits.length;i++)
		addRace();
	if (nCircuits <= CIRCUITS_INC) {
		var tableCtn = document.getElementById("cTracks");
		if (nCircuits)
			tableCtn.style.height = tableCtn.children[0].scrollHeight +"px";
		else {
			tableCtn.style.height = "auto";
			tableCtn.innerHTML = "<?php echo $language ? 'No result for this search':'Aucun résultat pour cette recherche'; ?>";
		}
		document.getElementById("defiler").style.visibility = "hidden";
	}
	iconDelayDt = 30;
	loadCircuitImgs();
}
document.addEventListener("DOMContentLoaded", function() {
	var liste0 = document.getElementById("liste")
	var liste0W = liste0.scrollWidth;
	liste0.style.width = liste0W + "px";
	
	var circuitsPerRow = Math.floor(liste0W/wStep);
	var nbRows0 = 12;
	initHeight = nbRows0*hStep;
	CIRCUITS0 = nbRows0*circuitsPerRow;

	var circuitsPerScroll = Math.floor(CIRCUITS0/circuitsPerRow);
	hStep *= circuitsPerScroll;
	dhStep *= circuitsPerScroll;
	
	nbsRace = Math.min(lCircuits.length, CIRCUITS0);
	addRaces();
});
function getScrollLeft() {
	var doc = document.documentElement;
	return (window.pageXOffset || doc.scrollLeft) - (doc.clientLeft || 0);
}
function getScrollTop() {
	var doc = document.documentElement;
	return (window.pageYOffset || doc.scrollTop) - (doc.clientTop || 0);
}
function selectTab(i) {
	document.forms[0].elements["tri"].value = i;
	document.forms[0].submit();
}

var loadingMsg = "<?php echo $language ? 'Loading':'Chargement'; ?>";
</script>
<script type="text/javascript" src="scripts/creations.js"></script>
<script type="text/javascript" src="scripts/posticons.js"></script>
</body>
</html>