<?php
include('../includes/session.php');
include('../includes/language.php');
include('../includes/initdb.php');
include('../includes/getId.php');
require_once('../includes/utils-date.php');
require_once('../includes/getRights.php');
$isModerator = hasRight('moderator');
$creation = false;
$cup = false;
$mcup = false;
$type = '';
$cc = isset($_GET['cc']) ? $_GET['cc'] : 150;
$manage = isset($_GET['manage']);
$moderate = $isModerator && isset($_GET['moderate']);
$sManage = $manage || $moderate;
if (isset($_GET['circuit'])) {
	$cID = intval($_GET['circuit']);
	$creation = true;
	$simplified = true;
	$type = 'mkcircuits';
}
elseif (isset($_GET['draw'])) {
	$cID = intval($_GET['draw']);
	$creation = true;
	$simplified = false;
	$type = 'circuits';
}
elseif (isset($_GET['scup'])) {
	$cID = intval($_GET['scup']);
	$creation = true;
	$simplified = true;
	$cup = true;
	$type = 'mkcircuits';
}
elseif (isset($_GET['ccup'])) {
	$cID = intval($_GET['ccup']);
	$creation = true;
	$simplified = false;
	$cup = true;
	$type = 'circuits';
}
elseif (isset($_GET['mcup'])) {
	$cID = intval($_GET['mcup']);
	$creation = true;
	$cup = true;
	$mcup = true;
}
if (!$manage && isset($_GET['user'])) {
	$user = mysql_fetch_array(mysql_query('SELECT id,nom FROM mkjoueurs WHERE id="'.$_GET['user'].'"'));
	if (!$user)
		unset($user);
}
if (!$creation && isset($_GET['map']))
	mysql_query('DELETE n FROM `mknotifs` n INNER JOIN `mkrecords` r ON n.link=r.id WHERE n.identifiant='.$identifiants[0].' AND n.identifiant2='.$identifiants[1].' AND n.identifiant3='.$identifiants[2].' AND n.identifiant4='.$identifiants[3].' AND n.type="new_record" AND r.class="'.$cc.'" AND r.type="" AND r.circuit="'.$_GET['map'].'"');
if ($manage)
	$pIDs = $identifiants;
if ($creation) {
	if ($mcup) {
		$getMCup = mysql_fetch_array(mysql_query('SELECT mode FROM `mkmcups` WHERE id="'. $cID .'"'));
		$simplified = ($getMCup['mode']==0);
		$type = $simplified ? 'mkcircuits':'circuits';
		$nameCol = $language ? 'name_en' : 'name_fr';
		$getCircuits = mysql_query('SELECT c.id,IFNULL(s.'.$nameCol.',c.nom) AS name,p.id AS gid,IFNULL(s2.'.$nameCol.',p.nom) AS gname FROM mkmcups_tracks t INNER JOIN mkcups p ON p.id=t.cup INNER JOIN `'. $type .'` c ON c.id IN (p.circuit0,p.circuit1,p.circuit2,p.circuit3) LEFT JOIN `mktracksettings` s ON s.type="'. $type .'" AND s.circuit=c.id LEFT JOIN `mktracksettings` s2 ON s2.type="mkcups" AND s2.circuit=p.id WHERE t.mcup="'. $cID .'" ORDER BY t.ordering');
	}
	elseif ($cup) {
		$getCup = mysql_fetch_array(mysql_query('SELECT * FROM `mkcups` WHERE id="'. $cID .'"'));
		require_once('../includes/utils-cups.php');
		$getCircuits = getCreationDataQuery(array(
			'table' => $type,
			'select' => 'c.id,0 AS gid,"" AS gname',
			'where' => 'c.id IN ('. $getCup['circuit0'] .','. $getCup['circuit1'] .','. $getCup['circuit2'] .','. $getCup['circuit3'] .')'
		));
	}
	else {
		require_once('../includes/utils-cups.php');
		$getCircuits = getCreationDataQuery(array(
			'table' => $type,
			'select' => 'c.id,0 AS gid,"" AS gname',
			'where' => 'c.id="'. $cID .'"'
		));
	}
}
$pseudo = isset($_GET['pseudo']) ? $_GET['pseudo']:null;
?>
<!DOCTYPE html>
<html lang="<?php echo $language ? 'en':'fr'; ?>">
<head>
<title>Mario Kart PC</title>
<?php
include('../includes/heads.php');
?>
<link rel="stylesheet" type="text/css" href="styles/classement.css?reload=1" />
<link rel="stylesheet" type="text/css" href="styles/auto-complete.css" />

<?php
include('../includes/o_online.php');
?>
<style type="text/css">
main {
	text-align: center;
}
#titres td {
	width: 100px;
}
main td {
	width: auto;
}
main h1, h2 {
	text-align: center;
	text-decoration: underline;
	font-family: Verdana;
	color: #560000;
}
main select:hover, main input[type="text"]:hover {
	background-color: #F90;
}
main select:active, main input[type="text"]:active {
	background-color: #F60;
}
main table {
	background-color: #FC0;
	max-width: 600px;
}
main tr.result:nth-child(2n), main tr.result:nth-child(2n) a {
	color: #820;
}
main tr.result:nth-child(2n+1) {
	background-color: yellow;
}
main tr.result:nth-child(2n+1), main tr.result:nth-child(2n+1) a {
	color: #F60;
}
main tr.result:nth-child(2n) a:hover {
	color: #B50;
}
main tr.result:nth-child(2n+1) a:hover {
	color: #FA0;
}
main table div {
	margin-left: auto;
	margin-right: auto;
}
.pub {
	margin-top: 5px;
	margin-bottom: 2px;
}
.record-date {
	position: relative;
	font-size: 14px;
}
.ranking-modes-ctn {
	text-align: center;
	margin-bottom: 10px;
}
.ranking-modes-ctn > div {
	display: inline-flex;
	align-items: center;
}
.ranking-modes-ctn > div > span {
	font-weight: bold;
	margin-right: 6px;
}
#title {
	position: relative;
	width: 100%;
}
#title h1 {
	display: inline-block;
}
#title .action-btns {
	position: absolute;
	right: 5px;
	top: 0;
}
#title .action-btns a {
	border: outset 2px #9c9;
}
#title .action-btns a:active {
	border-style: inset;
}
#title .action-btns-sm {
	top: -3px;
	right: 0;
}
#title .action-btns-sm a {
	padding: 2px 5px;
	font-size: 0.8em;
}
@media screen and (max-width: 599px) {
	#title .action-btns {
		top: -3px;
		right: 0;
	}
	#title .action-btns a {
		padding: 2px 5px;
		font-size: 0.8em;
	}
}
#content:not(.firstload).loading {
	opacity: 0.5;
	pointer-events: none;
}
.editor-mask {
	position: fixed;
	z-index: 10;
	left: 0;
	top: 0;
	width: 100%;
	height: 100%;
}
.editor-mask-contextmenu {
	position: absolute;
	background-color: #FFB;
	text-align: left;
}
.editor-mask-contextmenu div {
	border: solid 1px #663;
	color: #742;
	font-size: 0.8em;
	padding: 0.1em 0.3em;
	cursor: pointer;
	white-space: nowrap;
}
.editor-mask-contextmenu div:hover {
	background-color: #FD9;
	color: #963;
}
.record-history {
	margin-top: 0.5em;
	margin-bottom: 0.5em;
	display: flex;
	align-items: center;
	justify-content: center;
	gap: 0.4em;
}
.record-history a {
	font-size: 1.1em;
}
.record-history img {
	height: 1em;
}
#reset_ranking {
    margin-top: 10px;
}
#reset_ranking.hide {
	display: none;
}
#reset_ranking button {
    background-color: #C66;
    color: white;
    display: inline-block;
    font-weight: bold;
    color: white;
    border-radius: 5px;
    cursor: pointer;
}
#reset_ranking button:hover {
    background-color: #D77;
}
</style>
</head>
<body>
<?php
include('../includes/header.php');
$page = 'game';
include('../includes/menu.php');
?>
<main>
<div id="title">
<h1><?php
	if ($manage)
		echo $language ? 'Manage my time trials records' : 'Gérer mes records en contre-la-montre';
	elseif ($moderate)
		echo $language ? 'Moderate time trials records' : 'Modérer les records en contre-la-montre';
	elseif (isset($user))
		echo $language ? 'Best time trial scores of '. $user['nom']:'Meilleurs scores contre-la-montre de '.$user['nom'];
	else
		echo $language ? 'Best scores time trial':'Meilleurs scores contre-la-montre';
?></h1>
<div class="action-btns<?php if ($isModerator && !$manage) echo ' action-btns-sm'; ?>">
<?php
if ($isModerator && !$manage) {
	?>
<a class="action_button" href="?<?php
	$get = $_GET;
	if ($moderate)
		unset($get['moderate']);
	else
		$get['moderate'] = 1;
	unset($get['manage']);
	echo http_build_query($get);
?>"><?php
if ($moderate)
	echo $language ? '&lt; Back to list':'&lt; Retour à la liste';
else
	echo $language ? 'Moderate records':'Modérer les records';
?></a>
	<?php
}
if (!$moderate) {
	?>
<a class="action_button" href="?<?php
	$get = $_GET;
	if ($manage)
		unset($get['manage']);
	else
		$get['manage'] = 1;
	echo http_build_query($get);
?>"><?php
if ($manage)
	echo $language ? '&lt; Back to list':'&lt; Retour à la liste';
else
	echo $language ? 'Manage my records':'Gérer mes records';
?></a>
	<?php
}
?>
</div>
</div>
<div><?php
if ($manage)
	echo $language ? 'Delete or rename here your time trial records':'Renommez ou supprimez ici vos records en contre-la-montre';
elseif ($moderate)
	echo $language ? 'Delete or rename here the time trial records':'Renommez ou supprimez ici les records en contre-la-montre';
else
	echo $language ? 'You can see here all the records of the time trial mode in Mario Kart PC.':'Vous pouvez voir ici tous les records du mode contre-la-montre de Mario Kart PC.';
?>
<?php
if (!$manage) {
	if (!$creation) {
		?>
		<br /><?php echo $language ? 'The leaderboard is shown circuit by circuit, to see a global ranking, see <a href="classement.global.php">this page</a>.':'Les classements sont affichés circuit par circuit, pour voir un classement global, rendez-vous sur <a href="classement.global.php">cette page</a>.'; ?>
		<?php
	}
}
?>
</div>
<script async src="//pagead2.googlesyndication.com/pagead/js/adsbygoogle.js"></script>
<!-- Forum MKPC -->
<p class="pub"><ins class="adsbygoogle"
     style="display:inline-block;width:728px;height:90px"
     data-ad-client="ca-pub-1340724283777764"
     data-ad-slot="4919860724"></ins></p>
<script>
(adsbygoogle = window.adsbygoogle || []).push({});
</script>
<div class="ranking-modes-ctn">
<div>
<span><?php echo $language ? 'Class:':'Cylindrée :'; ?></span>
<div class="ranking-modes">
	<?php
	if ($cc == 150) {
		$get = $_GET;
		$get['cc'] = 200;
		?>
		<span>150cc</span><a href="classement.php?<?php echo http_build_query($get); ?>">200cc</a>
		<?php
	}
	else {
		$get = $_GET;
		unset($get['cc']);
		?>
		<a href="classement.php?<?php echo http_build_query($get); ?>">150cc</a><span>200cc</span>
		<?php
	}
	?>
</div>
</div>
</div>
<form name="params" action="classement.php" onsubmit="fetchResults();return false">
</form>
<p id="content" class="firstload"><strong style="font-size:1.4em"><?php echo $language ? 'Loading':'Chargement'; ?>...</strong></p>
<?php
if ($creation) {
	$groupsById = array();
	$circuitGroups = array();
	while ($getCircuit = mysql_fetch_array($getCircuits)) {
		if (!$getCircuit['name'])
			$getCircuit['name'] = $language ? 'Untitled':'Sans titre';
		$circuitGroups[$getCircuit['gid']][] = htmlEscapeCircuitNames($getCircuit['name']);
		$cIDs[] = $getCircuit['id'];
		$groupsById[$getCircuit['gid']] = addslashes($getCircuit['gname']);
	}
}
if ($creation && !$cup && !$sManage) {
	if (($getId = mysql_fetch_array(mysql_query('SELECT identifiant FROM `'. $type .'` WHERE id="'. $cID .'"'))) && ($getId['identifiant'] == $identifiants[0])) {
		?>
		<div id="reset_ranking" class="hide">
			<button class="action_button" onclick="resetRanking()"><?php echo $language ? 'Reset ranking':'Réinitialiser le classement'; ?></button>
		</div>
		<p></p>
		<script type="text/javascript">
		function resetRanking() {
			if (confirm("<?php echo $language ? 'Delete all time trials records? Caution, this action cannot be undone':'Effacer tous les records ? Attention, cette action est irréversible'; ?>")) {
				document.body.style.cursor = "progress";
				o_xhr("resetTimeTrials.php", "type=<?php echo $type; ?>&id=<?php echo $cID; ?>", function() {
					document.location.reload();
					return true;
				});
			}
		}
		</script>
		<?php
	}
}
?>
<p>
	<?php
	if (isset($user)) {
		?>
		<a href="profil.php?id=<?php echo $user['id']; ?>"><?php echo $language ? 'Back to '. $user['nom'] .'\'s profile':'Retour au profil de '.$user['nom']; ?></a><br />
		<?php
	}
	if ($creation) {
		if ($mcup) {
			?>
			<a href="<?php echo $simplified ? 'circuit.php?mid='.$cID : 'map.php?mid='.$cID; ?>"><?php echo $language ? 'Back to the multicup':'Retour à la multicoupe'; ?></a><br />
			<?php
		}
		elseif ($cup) {
			?>
			<a href="<?php echo $simplified ? 'circuit.php?cid='.$cID : 'map.php?cid='.$cID; ?>"><?php echo $language ? 'Back to the cup':'Retour à la coupe'; ?></a><br />
			<?php
		}
		else {
			?>
			<a href="<?php echo $simplified ? 'circuit.php?id='.$cID : 'map.php?i='.$cID; ?>"><?php echo $language ? 'Back to the circuit':'Retour au circuit'; ?></a><br />
			<?php
		}
	}
	?>
	<a href="index.php"><?php echo $language ? 'Back to Mario Kart PC':'Retour &agrave; Mario Kart PC'; ?></a>
</p>
</main>
<?php
include('../includes/footer.php');
?>
<script type="text/javascript" src="scripts/auto-complete.min.js"></script>
<script type="text/javascript">
var NB_RES = 20;
function Resultat(circuitId) {
	this.classement = new Array();
	this.circuit_id = circuitId;
	this.page = 0;
}
var iCc = <?php echo intval($cc); ?>;
var autoSelectMap<?php
	if (isset($_GET['map']) && is_numeric($_GET['map']))
		echo ' = '. ($_GET['map']-1);
?>;
var baseCircuitGroups = <?php
require_once('../includes/circuitEscape.php');
if ($creation)
	echo json_encode(array_values($circuitGroups));
else {
	include_once('circuitNames.php');
	echo json_encode(array(
		array_slice($circuitNames,0,20),
		array_slice($circuitNames,20,20),
		array_slice($circuitNames,40,16)
	));
}
?>;
var circuitGroups = baseCircuitGroups;
var circuits = [];
var baseGroups = <?php
if ($creation)
	echo json_encode(array_values($groupsById));
else
	echo '["SNES","GBA","DS"]';
?>;
var groups = baseGroups;
var sUser = <?php echo isset($user) ? $user['id']:0 ?>;
var sManage = <?php echo $sManage ? 1:0 ?>;
var sModerate = <?php echo $moderate ? 1:0 ?>;
var sFilteredData = (sUser || sManage);
var sPts = <?php echo +isset($_GET['pts']); ?>;
var language = <?php echo $language ? 1:0; ?>;
var classement = new Array();
var vw = Math.max(document.documentElement.clientWidth, window.innerWidth || 0);
var isMobile = (vw < 500);
function getPlace(id,place) {
	var iCircuit = classement[id];
	var record = iCircuit.classement[place][2];
	while ((place >= 0) && (record == iCircuit.classement[place][2]))
		place--;
	return place+2;
}
function getScore(place,nPlaces) {
	if (place == 1)
		return 10;
	if (nPlaces == 2)
		return 0;
	var x = (place-2)/(nPlaces-2);
	return Math.round(8*Math.pow(1-x,4/3));
}
var PERSOS_DIR = "<?php
	require_once('../includes/persos.php');
	echo PERSOS_DIR;
?>";
if (!String.prototype.startsWith) {
	String.prototype.startsWith = function(searchString, position) {
		position = position || 0;
    	return this.indexOf(searchString, position) === position;
	};
}
function getSpriteSrc(playerName) {
	if (playerName.startsWith("cp-"))
		return PERSOS_DIR + playerName + ".png";
	return "images/sprites/sprite_" + playerName +".png";
}
function spriteLoad() {
	var w = this.naturalWidth, h = this.naturalHeight;
	if (w != 768 || h != 32) {
		var div = this.parentNode;
		// TODO: this works because 768 = 24*32, but it's a coincidence
		div.style.width = Math.round(w/h)+"px";
		this.style.left = -Math.round(6*w/h)+"px";
	}
}
var oParams;
var I_RANK = 0,
	I_NICK = I_RANK + 1,
	I_CHARACTER = I_NICK + 1,
	I_TIME = I_CHARACTER + 1,
	I_PLAYER = I_TIME + 1,
	I_COUNTRY = I_PLAYER + 1,
	I_DATE = I_COUNTRY + 1,
	I_ID = I_DATE + 1;
function addResult(id, i) {
	var iJoueur = classement[id].classement[i];
	var oResult = document.createElement("tr");
	oResult.className = 'result';
	var oPlace = document.createElement("td");
	var inPlace = iJoueur[I_RANK];
	var nPlaces = classement[id].count;
	var nScore = getScore(inPlace,nPlaces);
	var iCircuit = classement[id].circuit_id;
	var sPlace;
	<?php
	if ($language) {
		?>
	var centaines = inPlace%100;
	if ((centaines >= 10) && (centaines < 20))
		sPlace = "th";
	else {
		switch (inPlace%10) {
		case 1 :
			sPlace = "st";
			break;
		case 2 :
			sPlace = "nd";
			break;
		case 3 :
			sPlace = "rd";
			break;
		default :
			sPlace = "th";
		}
	}
		<?php
	}
	else
		echo 'sPlace = ((inPlace>1) ? "e":"er");';
	?>
	oPlace.innerHTML = inPlace +"<sup>"+ sPlace +"</sup>";
	oResult.appendChild(oPlace);
	var oPseudo = document.createElement("td");
	function setNickHtml() {
		var pseudoTxt;
		if (iJoueur[I_COUNTRY]) {
			oPseudo.className = "recorder";
			pseudoTxt = '<img src="images/flags/'+iJoueur[I_COUNTRY]+'.png" alt="'+iJoueur[I_COUNTRY]+'" onerror="this.style.display=\'none\'" /> '+iJoueur[I_NICK];
		}
		else
			pseudoTxt = " "+iJoueur[I_NICK];
		if (iJoueur[I_PLAYER])
			oPseudo.innerHTML = '<a href="profil.php?id='+iJoueur[I_PLAYER]+'">'+pseudoTxt+'</a>';
		else
			oPseudo.innerHTML = pseudoTxt;
	}
	setNickHtml();
	oResult.appendChild(oPseudo);
	var oPerso = document.createElement("td");
	var oPersoDiv = document.createElement("div");
	var oPersoImg = document.createElement("img");
	oPersoImg.src = getSpriteSrc(iJoueur[I_CHARACTER]);
	oPersoImg.setAttribute("alt", iJoueur[I_CHARACTER]);
	oPersoImg.onload = spriteLoad;
	oPersoDiv.appendChild(oPersoImg);
	oPerso.appendChild(oPersoDiv);
	oResult.appendChild(oPerso);
	var oTemps = document.createElement("td");
	var getTime = iJoueur[I_TIME], mls = getTime%1000, sec = Math.floor(getTime/1000), min = Math.floor(sec/60);
	sec -= min*60;
	if (sec < 10)
		sec = "0"+ sec;
	if (mls < 10)
		mls = "00"+ mls;
	else if (mls < 100)
		mls = "0"+ mls;
	oTemps.innerHTML = min +":"+ sec +":"+ mls;
	oResult.appendChild(oTemps);
	if (!isMobile) {
		var oDate = document.createElement("td");
		oDate.className = "record-date";
		if (iJoueur[I_PLAYER]) {
			var aDate = document.createElement("a");
			aDate.href = "#null";
			aDate.title = language ? "History":"Historique";
			aDate.onclick = function() {
				window.open('recordHistory.php?player='+iJoueur[I_PLAYER]+'&map='+classement[id].id+'&cc='+iCc<?php
				if ($type) echo "+'&type=$type'";
				?>,'gerer','scrollbars=1, resizable=1, width=500, height=400');
				return false;
			};
			aDate.innerHTML = iJoueur[I_DATE];
			oDate.appendChild(aDate);
		}
		else
			oDate.innerHTML = iJoueur[I_DATE];
		oResult.appendChild(oDate);
	}
	if (sPts) {
		var oPts = document.createElement("td");
		oPts.innerHTML = nScore;
		oResult.appendChild(oPts);
	}
	if (sManage) {
		var oManage = document.createElement("td");
		var oManageLink = document.createElement("a");
		oManageLink.href = "#null";
		oManageLink.innerHTML = "⋮";
		oManageLink.onclick = function(e) {
			e.preventDefault();
			var items = [{
				label: language ? "Change username" : "Modifier pseudo",
				select: function() {
					var newName = prompt(language ? "Enter new username:":"Entrer le nouveau pseudo :", iJoueur[I_NICK]);
					if (newName && newName !== iJoueur[I_NICK]) {
						o_xhr("editRecord.php", "id="+iJoueur[I_ID]+"&name="+encodeURIComponent(newName), function(res) {
							if (res == 1) {
								iJoueur[I_NICK] = newName;
								setNickHtml();
								return true;
							}
							if (res == -1) {
								alert(language ? "This username already exists, please choose another one":"Ce pseudo existe déjà, veuillez en choisir un autre");
								return true;
							}
							if (res < 0) {
								alert(language ? "An unknown error occurred, please try again later":"Une erreur est survenue, veuillez réessayer ultérieurement");
								return true;
							}
							return false;
						});
					}
				}
			}, {
				label: language ? "Delete" : "Supprimer",
				select: function(){
					if (confirm(language ? "Remove this record? This operation cannot be undone" : "Supprimer ce record ? Cette opération est irréversible")) {
						o_xhr("deleteRecord.php", "id="+iJoueur[I_ID], function(res) {
							if (res == 1) {
								document.getElementById("result"+ id).removeChild(oResult);
								return true;
							}
							if (res < 0) {
								alert(language ? "An unknown error occurred, please try again later":"Une erreur est survenue, veuillez réessayer ultérieurement");
								return true;
							}
							return false;
						});
					}
				}
			}];
			createContextMenu({
				event: e,
				items: items
			});
			return false;
		}
		oManage.appendChild(oManageLink);
		oResult.appendChild(oManage);
	}
	document.getElementById("result"+ id).appendChild(oResult);
}
function createContextMenu(options) {
	var e = options.event;
	var items = options.items;
	var $mask = document.createElement("div");
	$mask.className = "editor-mask";
	function closeMask() {
		document.removeEventListener("keydown", hideOnEscape);
		document.body.removeChild($mask);
		if (options.onclose)
			options.onclose();
	}
	function hideOnEscape(e) {
		switch (e.keyCode) {
		case 27:
			closeMask();
			break;
		}
	}
	document.body.appendChild($mask);
	var oContextMenu = document.createElement("div");
	for (let item of items) {
		oContextMenu.style.position = "absoulte";
		oContextMenu.className = "editor-mask-contextmenu";
		var oContextMenuItem = document.createElement("div");
		oContextMenuItem.innerHTML = item.label;
		oContextMenuItem.onclick = function() {
			item.select();
			closeMask();
		}
		oContextMenuItem.oncontextmenu = function() {
			item.select();
			closeMask();
			return false;
		}
		oContextMenu.appendChild(oContextMenuItem);
	}
	oContextMenu.onclick = function(e) {
		e.stopPropagation();
	};
	oContextMenu.style.visibility = "hidden";
	$mask.appendChild(oContextMenu);
	oContextMenu.style.left = Math.min(e.clientX, (window.innerWidth||screen.width)-oContextMenu.scrollWidth-20) +"px";
	oContextMenu.style.top = e.clientY +"px";
	oContextMenu.style.visibility = "";
	document.addEventListener("keydown", hideOnEscape);
	$mask.onclick = closeMask;
}
function removeElements(elmt) {
	var oChilds = elmt.childNodes;
	while (oChilds.length) {
		removeElements(oChilds[0]);
		elmt.removeChild(oChilds[0]);
	}
	elmt.innerHTML = "";
}
function displayResult(id, n) {
	var oTableResults = document.getElementById("result"+ id);
	removeElements(oTableResults);
	var iResult = classement[id], iPage = iResult.page, iClassement = iResult.classement;
	var tableHeader = document.createElement("tr");
	tableHeader.id = "titres";
	var oPlace = document.createElement("td");
	oPlace.innerHTML = language ? "Rank":"Place";
	oPlace.style.width = "20px";
	tableHeader.appendChild(oPlace);
	var oPseudo = document.createElement("td");
	oPseudo.innerHTML = language ? "Username":"Pseudo";
	tableHeader.appendChild(oPseudo);
	var oPerso = document.createElement("td");
	oPerso.innerHTML = language ? "Char.":"Perso";
	oPerso.style.width = "20px";
	tableHeader.appendChild(oPerso);
	var oTemps = document.createElement("td");
	oTemps.innerHTML = language ? "Time":"Temps";
	tableHeader.appendChild(oTemps);
	if (!isMobile) {
		var oDate = document.createElement("td");
		oDate.style.width = "55px";
		oDate.innerHTML = language ? "Date":"Date";
		tableHeader.appendChild(oDate);
	}
	if (sPts) {
		var oPts = document.createElement("td");
		oPts.style.width = "10px";
		oPts.innerHTML = "Pts";
		tableHeader.appendChild(oPts);
	}
	if (sManage) {
		var oPts = document.createElement("td");
		oPts.style.width = "10px";
		oPts.innerHTML = "Action";
		tableHeader.appendChild(oPts);
	}
	oTableResults.appendChild(tableHeader);

	for (var i=0;i<iClassement.length;i++)
		addResult(id, i);
	if (iResult.paginated && (iClassement.length < iResult.count)) {
		var oTableFooter = document.createElement("tr");
		var oPages = document.createElement("td");
		oPages.id = "page";
		oPages.setAttribute("colspan", 4+!isMobile+sPts+sFilteredData);
		oPages.innerHTML = "Page :";
		if (document.getElementById("result"+ id).getElementsByTagName("tr").length == 1) {
			oPages.innerHTML = language ? "No record for this circuit yet":"Aucun record sur ce circuit pour l'instant";
			oPages.style.textAlign = "center";
			oPages.style.fontStyle = "italic";
		}
		else {
			var nbPages = Math.ceil(iResult.count/NB_RES);
			var paging = makePaging(iPage, nbPages);
			for (var i=0;i<paging.length;i++) {
				if (i)
					oPages.innerHTML += ' ...&nbsp;';
				for (var j=0;j<paging[i].length;j++) {
					var p = paging[i][j];
					oPages.innerHTML += (p!=iPage) ? ' <a href="#circuit'+ id +'" onclick="changePage('+ id +", "+ p +');void(0)">'+ (p+1) +'</a>':' <span>'+ (p+1) +'</span>';
				}
			}
		}
		oTableFooter.appendChild(oPages);
		oTableResults.appendChild(oTableFooter);
	}
}
function makePaging(cPage, nbPages, intervalle=3) {
	if (nbPages <= (intervalle*2+2)) {
		var block = [];
		for (var i=0;i<nbPages;i++)
			block.push(i);
		return [block];
	}
	var res = [];
	var block = [];
	var debut = cPage-intervalle;
	if (debut <= 0)
		debut = 0;
	else {
		block.push(0);
		if (debut != 1) {
			res.push(block);
			block = [];
		}
	}
	var fin = debut + intervalle*2 + 1;
	if (fin > nbPages) {
		fin = nbPages;
		debut = fin-intervalle*2-1;
	}
	for (var i=debut;i<fin;i++)
		block.push(i);
	
	if (fin < nbPages) {
		if (fin != (nbPages-1)) {
			res.push(block);
			block = [];
		}
		block.push(nbPages-1);
		res.push(block);
	}
	else
		res.push(block);
	return res;
}
function displayResults() {
	var oContent = document.getElementById("content");
	removeElements(oContent);
	var cRace = oParams.map ? oParams.map.value:-1;
	var setToAll = (cRace == -1);
	var noPlayers = !sFilteredData;
	for (var i=0;i<circuits.length;i++) {
		if (setToAll || (cRace == i)) {
			var n = undefined;
			if (classement[i].classement.length)
				noPlayers = false;
			else
				continue;
			var circuitTitle = document.createElement("h2");
			circuitTitle.id = "circuit"+ i;
			circuitTitle.innerHTML = circuits[i];
			oContent.appendChild(circuitTitle);
			var tableResult = document.createElement("table");
			tableResult.id = "result"+ i;
			oContent.appendChild(tableResult);
			var circuitHistoryContainer = document.createElement("div");
			circuitHistoryContainer.className = "record-history";
			var circuitHistoryIcon = document.createElement("img");
			circuitHistoryIcon.src = "images/cups/cup1.png";
			circuitHistoryIcon.alt = "cup icon";
			circuitHistoryContainer.appendChild(circuitHistoryIcon);
			var circuitHistory = document.createElement("a");
			circuitHistory.href = "#null";
			circuitHistory.dataset.circuit = classement[i].id;
			circuitHistory.onclick = function() {
				window.open('wrHistory.php?map='+this.dataset.circuit+'&cc='+iCc<?php
				if ($type) echo "+'&type=$type'";
				?>,'gerer','scrollbars=1, resizable=1, width=600, height=500');
				return false;
			};
			circuitHistory.innerHTML = language ? "Track world record history":"Historique records du circuit";
			circuitHistoryContainer.appendChild(circuitHistory);
			var circuitHistoryIcon = document.createElement("img");
			circuitHistoryIcon.src = "images/cups/cup1.png";
			circuitHistoryIcon.alt = "cup icon";
			circuitHistoryContainer.appendChild(circuitHistoryIcon);
			oContent.appendChild(circuitHistoryContainer);
			displayResult(i, n);
		}
	}
	if (noPlayers) {
		var oNoResults = document.createElement("strong");
		oNoResults.innerHTML = language ? "No result found for this search" : "Aucun résultat trouvé pour cette recherche.";
		oContent.appendChild(oNoResults);
	}
	else {
		var $resetRanking = document.getElementById("reset_ranking");
		if ($resetRanking)
			$resetRanking.classList.remove("hide");
	}
}
var oParamsBlock;
var fetchingResults = false;
function changePage(id, nPage) {
	if (fetchingResults)
		return;
	var oTableResults = document.getElementById("result"+ id);
	oTableResults.classList.add("loading");
	classement[id].page = nPage;
	var nCircuit = classement[id].id;
	var nPlayer = oParams.joueur.value;
	<?php
	$apiParams = array('cc' => $cc);
	$baseParams = array();
	if ($manage)
		$apiParams['manage'] = 1;
	if ($moderate)
		$apiParams['moderate'] = 1;
	$paginated = false;
	if (isset($user)) {
		$apiParams['user'] = $user['id'];
		if (isset($_GET['pts']))
			$baseParams['count'] = 1;
	}
	elseif (!$manage) {
		$baseParams['count'] = 1;
		$paginated = true;
	}
	if (isset($cIDs))
		$apiParams['cIDs'] = implode(',', $cIDs);
	if ($type)
		$apiParams['type'] = $type;
	?>
	o_xhr("getTtRanking.php", <?php
		echo '"'.http_build_query($apiParams).'&page="+ nPage +"&cIDs="+ nCircuit + (nPlayer ? "&name="+ nPlayer:"")';
	?>, function(res) {
		fetchingResults = false;
		oTableResults.classList.remove("loading");
		res = JSON.parse(res);
		classement[id].classement = res[0].list;
		displayResult(id);
		return true;
	});
}
function fetchResults() {
	if (fetchingResults)
		return;
	fetchingResults = true;
	var oContent = document.getElementById("content");
	oContent.classList.add("loading");
	var nPlayer = oParams.joueur ? oParams.joueur.value : "";
	o_xhr("getTtRanking.php", <?php
		$apiParams = array_merge($apiParams, $baseParams);
		echo '"'.http_build_query($apiParams).'" + (nPlayer ? "&name="+ nPlayer:"")';
	?>, function(res) {
		fetchingResults = false;
		oContent.className = "";
		res = JSON.parse(res);
		circuits.length = 0;
		circuitGroups = baseCircuitGroups.map(function(baseCircuitGroup) {
			return baseCircuitGroup.slice();
		});
		groups = baseGroups.slice();
		for (var i=0;i<groups.length;i++)
			circuits = circuits.concat(circuitGroups[i]);
		for (var i=0;i<circuits.length;i++)
			classement[i] = new Resultat(i);
		for (var i=0;i<res.length;i++) {
			var iRanking = res[i];
			classement[i].id = iRanking.id;
			classement[i].classement = iRanking.list;
			if (iRanking.count === undefined)
				classement[i].count = iRanking.list.length;
			else
				classement[i].count = iRanking.count;
			<?php
			if ($paginated) echo 'if (!nPlayer) classement[i].paginated = true;';
			?>
		}
		
		if (!oParamsBlock) {
			oParamsBlock = document.createElement("div");
			
			var oParamsContent = document.createElement("div");
			<?php
			if (!$creation || $cup) {
				?>
				var tCircuit = document.createElement("span");
				tCircuit.innerHTML = language ? 'See circuit: ':'Voir circuit : ';
				tCircuit.style.fontWeight = "bold";
				oParamsContent.appendChild(tCircuit);
				var iCircuit = document.createElement("select");
				iCircuit.name = "map";
				var cTous = document.createElement("option");
				cTous.value = -1;
				var nbRecords = 0;
				for (var i=0;i<circuits.length;i++)
					nbRecords += classement[i].count;
				if (sFilteredData)
					cTous.innerHTML = language ? "All":"Tous";
				else
					cTous.innerHTML = (language ? "All":"Tous") +" ("+ nbRecords + " record"+ ((nbRecords>1) ? "s":"") +")";
				iCircuit.appendChild(cTous);
				var inc = 0;
				var enabledGroups = groups.map(function(group,j) {
					var circuitGroup = circuitGroups[j];
					var res = false;
					circuitGroup.forEach(function(circuit) {
						if (classement[inc++].count)
							res = true;
					});
					return res;
				});
				var multipleGroups = enabledGroups.reduce(function(a,b) {
					return a+b;
				}, 0) > 1;
				inc = 0;
				for (var j=0;j<groups.length;j++) {
					var circuitGroup = circuitGroups[j];
					if (!enabledGroups[j]) {
						inc += circuitGroup.length;
						continue;
					}
					var optionGroup;
					if (multipleGroups) {
						optionGroup = document.createElement("optgroup");
						optionGroup.setAttribute("label", groups[j]);
					}
					for (var i=0;i<circuitGroup.length;i++) {
						var cRecords = classement[inc].count;
						if (!cRecords) {
							inc++;
							continue;
						}
						var cCircuit = document.createElement("option");
						cCircuit.value = inc;
						if (sFilteredData)
							cCircuit.innerHTML = circuitGroup[i];
						else
							cCircuit.innerHTML = circuitGroup[i] +" ("+ cRecords +" record"+ ((cRecords>1) ? "s":"") +")";
						if (optionGroup)
							optionGroup.appendChild(cCircuit);
						else
							iCircuit.appendChild(cCircuit);
						inc++;
					}
					if (optionGroup)
						iCircuit.appendChild(optionGroup);
				}
				if (autoSelectMap != undefined)
					iCircuit.value = autoSelectMap;
				iCircuit.onchange = displayResults;
				oParamsContent.appendChild(iCircuit);
				
				oParamsContent.appendChild(document.createElement("br"));
				<?php
			}
			?>
			
			var tJoueur = document.createElement("span");
			tJoueur.innerHTML = language ? "See player:":"Voir joueur :";
			tJoueur.innerHTML += "&nbsp;";
			tJoueur.style.fontWeight = "bold";
			oParamsContent.appendChild(tJoueur);
			var iJoueur = document.createElement("input");
			iJoueur.type = "text";
			iJoueur.id = "joueur";
			iJoueur.name = "joueur";
			iJoueur.onchange = fetchResults;
			oParamsContent.appendChild(iJoueur);
			if (sFilteredData) {
				tJoueur.style.display = "none";
				iJoueur.style.display = "none";
			}
			
			oParamsBlock.appendChild(oParamsContent);
			
			var oParamsSubmit = document.createElement("div");
			
			oParamsBlock.appendChild(oParamsSubmit);
			oParams.appendChild(oParamsBlock);
		}
		
		var autoHandler = 0;
		new autoComplete({
			selector: "#joueur",
			minChars: 1,
			source: function(term, suggest) {
				var cHandler = ++autoHandler;
				o_xhr('matchingRecords.php', 'prefix='+encodeURIComponent(term)+'&type=<?php echo $type; ?>&cc=<?php echo $cc; ?><?php if (isset($cIDs)) echo '&cIDs='. implode(',', $cIDs) ?>', function(res) {
					if (cHandler == autoHandler)
						suggest(JSON.parse(res));
					return true;
				});
			},
			onSelect: function() {
				fetchResults();
			}
		});
		
		displayResults();

		return true;
	});
}
document.addEventListener("DOMContentLoaded", function() {
	oParams = document.forms["params"];
	fetchResults();
})
</script>
</body>
</html>
<?php
mysql_close();
?>