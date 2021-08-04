<?php
include('session.php');
include('language.php');
include('initdb.php');
require_once('utils-date.php');
$creation = false;
$cup = false;
$mcup = false;
$type = '';
$cc = isset($_GET['cc']) ? $_GET['cc'] : 150;
$manage = isset($_GET['manage']);
if (isset($_GET['circuit'])) {
	$cID = $_GET['circuit'];
	$creation = true;
	$simplified = true;
	$type = 'mkcircuits';
}
elseif (isset($_GET['draw'])) {
	$cID = $_GET['draw'];
	$creation = true;
	$simplified = false;
	$type = 'circuits';
}
elseif (isset($_GET['scup'])) {
	$cID = $_GET['scup'];
	$creation = true;
	$simplified = true;
	$cup = true;
	$type = 'mkcircuits';
}
elseif (isset($_GET['ccup'])) {
	$cID = $_GET['ccup'];
	$creation = true;
	$simplified = false;
	$cup = true;
	$type = 'circuits';
}
elseif (isset($_GET['mcup'])) {
	$cID = $_GET['mcup'];
	$creation = true;
	$cup = true;
	$mcup = true;
}
if (!$manage && isset($_GET['user'])) {
	$user = mysql_fetch_array(mysql_query('SELECT id,nom FROM mkjoueurs WHERE id="'.$_GET['user'].'"'));
	if (!$user)
		unset($user);
}
if (!$creation && isset($_GET['map'])) {
	include('getId.php');
	mysql_query('DELETE n FROM `mknotifs` n INNER JOIN `mkrecords` r ON n.link=r.id WHERE n.identifiant='.$identifiants[0].' AND n.identifiant2='.$identifiants[1].' AND n.identifiant3='.$identifiants[2].' AND n.identifiant4='.$identifiants[3].' AND n.type="new_record" AND r.class="'.$cc.'" AND r.type="" AND r.circuit="'.$_GET['map'].'"');
}
if ($manage) {
	include('getId.php');
	$pIDs = $identifiants;
}
if ($creation) {
	if ($mcup) {
		$getMCup = mysql_fetch_array(mysql_query('SELECT mode FROM `mkmcups` WHERE id="'. $cID .'"'));
		$simplified = ($getMCup['mode']==0);
		$type = $simplified ? 'mkcircuits':'circuits';
		$getCircuits = mysql_query('SELECT c.id,c.nom,p.id AS gid,p.nom AS gname FROM mkmcups_tracks t INNER JOIN mkcups p ON p.id=t.cup INNER JOIN `'. $type .'` c ON c.id IN (p.circuit0,p.circuit1,p.circuit2,p.circuit3) WHERE t.mcup="'. $cID .'" ORDER BY t.ordering');
	}
	elseif ($cup) {
		$getCup = mysql_fetch_array(mysql_query('SELECT * FROM `mkcups` WHERE id="'. $cID .'"'));
		$getCircuits = mysql_query('(SELECT id,nom,0 AS gid,"" AS gname FROM `'. $type .'` WHERE id="'. $getCup['circuit0'] .'") UNION ALL (SELECT id,nom FROM `'. $type .'` WHERE id="'. $getCup['circuit1'] .'") UNION ALL (SELECT id,nom FROM `'. $type .'` WHERE id="'. $getCup['circuit2'] .'") UNION ALL (SELECT id,nom FROM `'. $type .'` WHERE id="'. $getCup['circuit3'] .'")');
	}
	else
		$getCircuits = mysql_query('SELECT id,nom,0 AS gid,"" AS gname FROM `'. $type .'` WHERE id="'. $cID .'"');
}
$pseudo = isset($_GET['pseudo']) ? $_GET['pseudo']:null;
?>
<!DOCTYPE html>
<html lang="<?php echo $language ? 'en':'fr'; ?>">
<head>
<title>Mario Kart PC</title>
<?php
include('heads.php');
?>
<link rel="stylesheet" type="text/css" href="styles/classement.css" />
<link rel="stylesheet" type="text/css" href="styles/auto-complete.css" />

<?php
include('o_online.php');
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
#title a {
	position: absolute;
	right: 5px;
	top: 0;
	border: outset 2px #9c9;
}
#title a:active {
	border-style: inset;
}
@media screen and (max-width: 599px) {
	#title a {
		top: -3px;
		right: 0;
		padding: 2px 5px;
		font-size: 0.8em;
	}
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
</style>
</head>
<body>
<?php
include('header.php');
$page = 'game';
include('menu.php');
?>
<main>
<div id="title">
<h1><?php
	if ($manage)
		echo $language ? 'Manage my time trials records' : 'Gérer mes records en contre-la-montre';
	elseif (isset($user))
		echo $language ? 'Best time trial scores of '. $user['nom']:'Meilleurs scores contre-la-montre de '.$user['nom'];
	else
		echo $language ? 'Best scores time trial':'Meilleurs scores contre-la-montre';
?></h1>
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
</div>
<div><?php
if ($manage)
	echo $language ? 'Delete or rename here your time trial records':'Renommez ou supprimez ici vos records en contre-la-montre';
else
	echo $language ? 'You can see here all the records of the time trial mode in Mario Kart PC.':'Vous pouvez voir ici tous les records du mode contre-la-montre de Mario Kart PC.';
?>
<?php
if (!$manage) {
	if (!$creation) {
		?>
		<br /><?php echo $language ? 'The leaderbord is shown circuit by circuit, to see a global ranking, see <a href="classement.global.php">this page</a>.':'Les classements sont affichés circuit par circuit, pour voir un classement global, rendez-vous sur <a href="classement.global.php">cette page</a>.'; ?>
		<?php
	}
	echo '<br />';
	echo $language ? 'Note that those records have been reset after MKPC engine update. <a href="classement.old.php?'.$_SERVER['QUERY_STRING'].'">Click here</a> to see the old records.':'Notez que tous les records ont été réinitialisés avec la mise à jour du moteur de MKPC. <a href="classement.old.php?'.$_SERVER['QUERY_STRING'].'">Cliquez ici</a> pour voir les anciens temps.';
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
<form name="params" action="classement.php" onsubmit="displayResults();return false">
</form>
<p id="content"><strong style="font-size:1.4em"><?php echo $language ? 'Loading':'Chargement'; ?>...</strong></p>
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
include('footer.php');
?>
<script type="text/javascript" src="scripts/auto-complete.min.js"></script>
<script type="text/javascript" src="scripts/autocomplete-dummy.js"></script>
<script type="text/javascript">
var NB_RES = 20;
function Resultat(circuitId) {
	this.classement = new Array();
	this.circuit_id = circuitId;
}
var iCc = <?php echo intval($cc); ?>;
var autoSelectMap<?php
	if (isset($_GET['map']))
		echo ' = '. ($_GET['map']-1);
?>;
var circuitGroups = <?php
require_once('circuitEscape.php');
function escapeUtf8($str) {
	return addslashes(htmlspecialchars(escapeCircuitNames(utf8_encode($str))));
}
function dict_to_array(&$chunks) {
	$res = array();
	foreach ($chunks as $chunck)
		$res[] = $chunck;
	return $res;
}
if ($creation) {
	$groupsById = array();
	$circuitGroups = array();
	while ($getCircuit = mysql_fetch_array($getCircuits)) {
		if (!$getCircuit['nom'])
			$getCircuit['nom'] = $language ? 'Untitled':'Sans titre';
		$circuitGroups[$getCircuit['gid']][] = escapeUtf8($getCircuit['nom']);
		$cIDs[] = $getCircuit['id'];
		$groupsById[$getCircuit['gid']] = escapeUtf8($getCircuit['gname']);
	}
	echo json_encode(dict_to_array($circuitGroups));
}
else {
	include_once('circuitNames.php');
	$circuitGroups = array(
		array_slice($circuitNames,0,20),
		array_slice($circuitNames,20,20),
		array_slice($circuitNames,40,16)
	);
	echo json_encode($circuitGroups);
}
?>;
var circuits = [];
var groups = <?php
if ($creation)
	echo json_encode(dict_to_array($groupsById));
else
	echo '["SNES","GBA","DS"]';
?>;
for (var i=0;i<groups.length;i++)
	circuits = circuits.concat(circuitGroups[i]);
var sUser = <?php echo isset($user) ? $user['id']:0 ?>;
var sManage = <?php echo $manage ? 1:0 ?>;
var sFilteredData = (sUser || sManage);
var sPts = <?php echo +isset($_GET['pts']); ?>;
var language = <?php echo $language ? 1:0; ?>;
var classement = new Array();
for (var i=0;i<circuits.length;i++)
	classement[i] = new Resultat(i);
<?php
$joinBest = isset($_GET['date']) ? ' LEFT JOIN `mkrecords` r2 ON r.player=r2.player AND r.identifiant=r2.identifiant AND r.identifiant2=r2.identifiant2 AND r.identifiant3=r2.identifiant3 AND r.identifiant4=r2.identifiant4 AND r.class=r2.class AND r.circuit=r2.circuit AND r.type=r2.type AND r2.time<r.time AND r2.date<="'.$_GET['date'].'"':'';
$whereBest = isset($_GET['date']) ? ' AND r2.id IS NULL AND r.date<="'.$_GET['date'].'"':' AND r.best=1';
if (isset($user))
	$getResults = mysql_query('SELECT r.*,c.code,r.date,(r.player='.$user['id'].') AS shown FROM `mkrecords` r LEFT JOIN `mkprofiles` p ON r.player=p.id LEFT JOIN `mkcountries` c ON p.country=c.id'.$joinBest.' WHERE r.class="'. $cc .'" AND r.type="'. $type .'"'.$whereBest.' ORDER BY r.time');
else {
	if ($creation && empty($cIDs))
		$cIDs = array(0);
	$getResults = mysql_query('SELECT r.*,c.code,r.date'.(empty($pIDs)?'':',(r.identifiant="'.$pIDs[0].'" AND r.identifiant2="'.$pIDs[1].'" AND r.identifiant3="'.$pIDs[2].'" AND r.identifiant4="'.$pIDs[3].'") AS shown').' FROM `mkrecords` r LEFT JOIN `mkprofiles` p ON r.player=p.id LEFT JOIN `mkcountries` c ON p.country=c.id'.$joinBest.' WHERE r.class="'.$cc.'" AND r.type="'.$type.'"'.(empty($cIDs)?'':' AND r.circuit IN ('.implode(',',$cIDs).')').$whereBest.' ORDER BY r.time');
}
while ($result = mysql_fetch_array($getResults))
	echo 'classement['. ($creation ? array_search($result['circuit'],$cIDs):($result['circuit']-1)) .'].classement.push(["'.addslashes(htmlspecialchars($result['name'])).'","'.addslashes($result['perso']).'",'.$result['time'].','.$result['player'].','.'"'.$result['code'].'",'.'"'.pretty_dates_short($result['date'],array('shorter'=>true,'new'=>false)).'"'.(isset($result['shown']) ? ','.$result['shown']:'').''.($manage ? ','.$result['id']:'').']);';
?>
var jGroup = groups.length;
var iGroup = 0;
for (var i=circuits.length-1;i>=0;i--) {
	iGroup--;
	if (iGroup < 0) {
		jGroup--;
		iGroup += circuitGroups[jGroup].length;
	}
	if (!classement[i].classement.length || (sFilteredData && noShownData(classement[i].classement))) {
		circuitGroups[jGroup].splice(iGroup,1);
		if (!circuitGroups[jGroup].length) {
			circuitGroups.splice(jGroup,1);
			groups.splice(jGroup,1);
		}
		circuits.splice(i,1);
		classement.splice(i,1);
		if (autoSelectMap == i)
			autoSelectMap = undefined;
		else if (autoSelectMap > i)
			autoSelectMap--;
	}
}
var vw = Math.max(document.documentElement.clientWidth, window.innerWidth || 0);
var isMobile = (vw < 500);
function noShownData(iClassement) {
	for (var i=0;i<iClassement.length;i++) {
		if (iClassement[i][6])
			return false;
	}
	return true;
}
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
	require_once('persos.php');
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
function addResult(id, i) {
	var iJoueur = classement[id].classement[i];
	var oResult = document.createElement("tr");
	oResult.className = 'result';
	var oPlace = document.createElement("td");
	var inPlace = getPlace(id,i);
	var nPlaces = classement[id].classement.length;
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
		if (iJoueur[4]) {
			oPseudo.className = "recorder";
			pseudoTxt = '<img src="images/flags/'+iJoueur[4]+'.png" alt="'+iJoueur[4]+'" onerror="this.style.display=\'none\'" /> '+iJoueur[0];
		}
		else
			pseudoTxt = " "+iJoueur[0];
		if (iJoueur[3])
			oPseudo.innerHTML = '<a href="profil.php?id='+iJoueur[3]+'">'+pseudoTxt+'</a>';
		else
			oPseudo.innerHTML = pseudoTxt;
	}
	setNickHtml();
	oResult.appendChild(oPseudo);
	var oPerso = document.createElement("td");
	var oPersoDiv = document.createElement("div");
	var oPersoImg = document.createElement("img");
	oPersoImg.src = getSpriteSrc(iJoueur[1]);
	oPersoImg.setAttribute("alt", iJoueur[1]);
	oPersoImg.onload = spriteLoad;
	oPersoDiv.appendChild(oPersoImg);
	oPerso.appendChild(oPersoDiv);
	oResult.appendChild(oPerso);
	var oTemps = document.createElement("td");
	var getTime = iJoueur[2], mls = getTime%1000, sec = Math.floor(getTime/1000), min = Math.floor(sec/60);
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
		<?php
		if ($creation) {
			?>
		oDate.innerHTML = iJoueur[5];
			<?php
		}
		else {
			?>
			if (iJoueur[3]) {
				var aDate = document.createElement("a");
				aDate.href = "#null";
				aDate.title = language ? "History":"Historique";
				aDate.onclick = function() {
					window.open('recordHistory.php?player='+iJoueur[3]+'&map='+(iCircuit+1)+'&cc='+iCc,'gerer','scrollbars=1, resizable=1, width=500, height=400');
					return false;
				};
				aDate.innerHTML = iJoueur[5];
				oDate.appendChild(aDate);
			}
			else
				oDate.innerHTML = iJoueur[5];
			<?php
		}
		?>
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
				label: language ? "Change nick" : "Modifier pseudo",
				select: function() {
					var newName = prompt(language ? "Enter new nick:":"Entrer le nouveau pseudo :", iJoueur[0]);
					if (newName && newName !== iJoueur[0]) {
						o_xhr("editRecord.php", "id="+iJoueur[7]+"&name="+encodeURIComponent(newName), function(res) {
							if (res == 1) {
								iJoueur[0] = newName;
								setNickHtml();
								return true;
							}
							if (res == -1) {
								alert(language ? "This nick already exists, please choose another one":"Ce pseudo existe déjà, veuillez en choisir un autre");
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
						o_xhr("deleteRecord.php", "id="+iJoueur[7], function(res) {
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
	oPseudo.innerHTML = language ? "Nick":"Pseudo";
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
	if (n != undefined) {
		for (var i=0;i<n.length;i++)
			addResult(id, n[i]);
	}
	else {
		var debut = iResult.page*NB_RES, fin = Math.min(debut+NB_RES, iClassement.length);
		for (var i=debut;i<fin;i++)
			addResult(id, i);
		var oTableFooter = document.createElement("tr");
		var oPages = document.createElement("td");
		oPages.id = "page";
		oPages.setAttribute("colspan", 4+!isMobile+sPts);
		oPages.innerHTML = "Page :";
		if (document.getElementById("result"+ id).getElementsByTagName("tr").length == 1) {
			oPages.innerHTML = language ? "No record for this circuit yet":"Aucun record sur ce circuit pour l'instant";
			oPages.style.textAlign = "center";
			oPages.style.fontStyle = "italic";
		}
		else {
			var nbPages = Math.ceil(iResult.classement.length/NB_RES);
			for (var i=0;i<nbPages;i++)
				oPages.innerHTML += (i!=iPage) ? ' <a href="#circuit'+ id +'" onclick="changePage('+ id +", "+ i +');void(0)">'+ (i+1) +'</a>':' <span>'+ (i+1) +'</span>';
		}
		oTableFooter.appendChild(oPages);
		oTableResults.appendChild(oTableFooter);
	}
}
function displayResults() {
	var oContent = document.getElementById("content");
	removeElements(oContent);
	var cRace = oParams.map ? oParams.map.value:-1;
	var setToAll = (cRace == -1);
	var sPlayer = oParams.joueur.value.toLowerCase();
	var noPlayers = sPlayer;
	for (var i=0;i<circuits.length;i++) {
		if (setToAll || (cRace == i)) {
			var n = undefined;
			if (sPlayer) {
				n = [];
				var iClassement = classement[i].classement;
				for (var j=0;j<iClassement.length;j++) {
					if (iClassement[j][0].toLowerCase() == sPlayer) {
						n.push(j);
						noPlayers = false;
					}
				}
				if (!n.length)
					continue;
			}
			else if (sFilteredData) {
				n = [];
				var iClassement = classement[i].classement;
				for (var j=0;j<iClassement.length;j++) {
					if (iClassement[j][6]) {
						n.push(j);
						noPlayers = false;
						if (sPts)
							break;
					}
				}
				if (!n.length)
					continue;
			}
			else
				classement[i].page = 0;
			var circuitTitle = document.createElement("h2");
			circuitTitle.id = "circuit"+ i;
			circuitTitle.innerHTML = circuits[i];
			oContent.appendChild(circuitTitle);
			var tableResult = document.createElement("table");
			tableResult.id = "result"+ i;
			oContent.appendChild(tableResult);
			displayResult(i, n);
		}
	}
	if (noPlayers) {
		var oNoResults = document.createElement("strong");
		oNoResults.innerHTML = "Aucun résultat trouvé pour cette recherche.";
		oContent.appendChild(oNoResults);
	}
}
function changePage(id, nPage) {
	classement[id].page = nPage;
	displayResult(id);
}
window.onload = function() {
	oParams = document.forms["params"];
	
	var oParamsBlock = document.createElement("div");
	
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
			nbRecords += classement[i].classement.length;
		if (sFilteredData)
			cTous.innerHTML = language ? "All":"Tous";
		else
			cTous.innerHTML = (language ? "All":"Tous") +" ("+ nbRecords + " record"+ ((nbRecords>1) ? "s":"") +")";
		iCircuit.appendChild(cTous);
		var inc = 0;
		for (var j=0;j<groups.length;j++) {
			var optionGroup;
			if (groups.length > 1) {
				optionGroup = document.createElement("optgroup");
				optionGroup.setAttribute("label", groups[j]);
			}
			var circuitGroup = circuitGroups[j];
			for (var i=0;i<circuitGroup.length;i++) {
				var cCircuit = document.createElement("option");
				cCircuit.value = inc;
				var cRecords = classement[inc].classement.length;
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
	tJoueur.style.fontWeight = "bold";
	oParamsContent.appendChild(tJoueur);
	var iJoueur = document.createElement("input");
	iJoueur.type = "text";
	iJoueur.id = "joueur";
	iJoueur.name = "joueur";
	iJoueur.value = "<?php echo (isset($_GET['joueur']) ? $_GET['joueur']:null); ?>";
	iJoueur.onchange = displayResults;
	oParamsContent.appendChild(iJoueur);
	if (sFilteredData) {
		tJoueur.style.display = "none";
		iJoueur.style.display = "none";
	}
	
	oParamsBlock.appendChild(oParamsContent);
	
	var oParamsSubmit = document.createElement("div");
	
	oParamsBlock.appendChild(oParamsSubmit);
	oParams.appendChild(oParamsBlock);
	
	var joueurs = new Array();
	var lowerCaseJoueurs = new Array();
	for (var c=0;c<classement.length;c++) {
		var iClassement = classement[c].classement;
		for (var i=0;i<iClassement.length;i++) {
			var nJoueur = iClassement[i][0];
			if (lowerCaseJoueurs.indexOf(nJoueur.toLowerCase()) == -1) {
				joueurs.push(nJoueur);
				lowerCaseJoueurs.push(nJoueur.toLowerCase());
			}
		}
	}
	autocompleteDummy("#joueur", joueurs, {
		onSelect: function(event, term, item) {
			displayResults();
		}
	});
	
	displayResults();
};
</script>
</body>
</html>
<?php
mysql_close();
?>