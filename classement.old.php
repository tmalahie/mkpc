<?php
include('session.php');
include('language.php');
include('initdb.php');
$creation = false;
$cup = false;
$mcup = false;
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
if (isset($_GET['user'])) {
	$user = mysql_fetch_array(mysql_query('SELECT id,nom FROM mkjoueurs WHERE id="'.$_GET['user'].'"'));
	if (!$user)
		unset($user);
}
if ($creation) {
	if ($mcup) {
		$getMCup = mysql_fetch_array(mysql_query('SELECT mode FROM `mkmcups` WHERE id="'. $cID .'"'));
		$simplified = ($getMCup['mode']==0);
		$type = $simplified ? 'mkcircuits':'circuits';
		$getCircuits = mysql_query('SELECT c.id,c.nom FROM mkmcups_tracks t INNER JOIN mkcups p ON p.id=t.cup INNER JOIN `'. $type .'` c ON c.id IN (p.circuit0,p.circuit1,p.circuit2,p.circuit3) WHERE t.mcup="'. $cID .'" ORDER BY t.ordering');
	}
	elseif ($cup) {
		$getCup = mysql_fetch_array(mysql_query('SELECT * FROM `mkcups` WHERE id="'. $cID .'"'));
		$getCircuits = mysql_query('(SELECT id,nom FROM `'. $type .'` WHERE id="'. $getCup['circuit0'] .'") UNION ALL (SELECT id,nom FROM `'. $type .'` WHERE id="'. $getCup['circuit1'] .'") UNION ALL (SELECT id,nom FROM `'. $type .'` WHERE id="'. $getCup['circuit2'] .'") UNION ALL (SELECT id,nom FROM `'. $type .'` WHERE id="'. $getCup['circuit3'] .'")');
	}
	else
		$getCircuits = mysql_query('SELECT id,nom FROM `'. $type .'` WHERE id="'. $cID .'"');
}
$pseudo = isset($_GET['pseudo']) ? $_GET['pseudo']:null;
?>
<!DOCTYPE html>
<html lang="<?php echo $language ? 'en':'fr'; ?>">
<head>
<title><?php echo $language ? 'Time trial ranking':'Classement contre-la-montre'; ?> - Mario Kart PC</title>
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
main h1, h2 {
	text-align: center;
	text-decoration: underline;
	font-family: Verdana;
	color: #560000;
}
main select, main input[type="text"] {
	background-color: #FC0;
	color: black;
	border: solid 1px #A0300A;
	padding: 2px;
}
main select:hover, main input[type="text"]:hover {
	background-color: #F90;
}
main select:active, main input[type="text"]:active {
	background-color: #F60;
}
main table {
	text-align: center;
	position: relative;
	left: 50%;
	transform: translateX(-50%);
	border: double 4px black;
	background-color: #FC0;
	font-weight: bold;
	color: black;
}
main td {
	border: solid 1px black;
	margin: 2px;
	padding: 5px;
	width: 100px;
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
	margin-left: 30px;
	width: 24px;
	height: 24px;
	overflow: hidden;
}
main table div img {
	height: 100%;
	position: relative;
	left: -144px;
}
#titres {
	background-color: #F90;
	font-size: 12px;
	padding: 2px;
}
#page {
	text-align: left;
	background-color: #F90;
	color: #800;
}
#page span {
	color: #800;
}
#page a {
	color: yellow;
	text-decoration: none;
	font-weight: bold;
}
#page a:hover {
	color: #FC0;
}
main a {
	color: #F90;
}
main a:hover {
	color: #FC0;
}
.pub {
	margin-top: 5px;
	margin-bottom: 2px;
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
<h1><?php
	if (isset($user))
		echo $language ? 'Best time trial scores of '. $user['nom']:'Meilleurs scores contre-la-montre de '.$user['nom'];
	else
		echo $language ? 'Best scores time trial':'Meilleurs scores contre-la-montre';
?></h1>
<div><?php echo $language ? 'Here are all the records of the time trial mode in Mario Kart PC <strong>before the engine update</strong>. <a href="topic.php?topic=5048">Click here</a> to learn more':'Ici se trouvent les records du mode contre-la-montre de MKPC <strong>avant la mise à jour du moteur</strong>. <a href="topic.php?topic=5048">Cliquez ici</a> pour en savoir plus'; ?></div>
<script async src="//pagead2.googlesyndication.com/pagead/js/adsbygoogle.js"></script>
<!-- Forum MKPC -->
<p class="pub"><ins class="adsbygoogle"
     style="display:inline-block;width:728px;height:90px"
     data-ad-client="ca-pub-1340724283777764"
     data-ad-slot="4919860724"></ins></p>
<script>
(adsbygoogle = window.adsbygoogle || []).push({});
</script>
<form name="params" action="classement.php" onsubmit="displayResults();return false">
</form>
<p id="content"><strong style="font-size:1.4em"><?php echo $language ? 'Loading':'Chargement'; ?>...</strong></p>
<p>
	<a href="classement.php?<?php echo $_SERVER['QUERY_STRING']; ?>"><?php echo $language ? 'Back to current records':'Retour aux record actuels'; ?></a><br />
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
function Resultat() {
	this.classement = new Array();
}
var circuits = <?php
require_once('circuitEscape.php');
function escapeUtf8($str) {
	return addslashes(htmlspecialchars(escapeCircuitNames($str)));
}
if ($creation) {
	echo '[';
	$v = '';
	$cNames = array();
	while ($getCircuit = mysql_fetch_array($getCircuits)) {
		echo $v;
		$v = ',';
		if (!$getCircuit['nom'])
			$getCircuit['nom'] = $language ? 'Untitled':'Sans titre';
		while (in_array($getCircuit['nom'], $cNames))
			$getCircuit['nom'] .= ' [2]';
		$cNames[] = $getCircuit['nom'];
		echo '"'.escapeUtf8($getCircuit['nom']).'"';
		$cIDs[] = $getCircuit['id'];
	}
	echo ']';
}
else
	echo '["Circuit Mario 1","Plaine Donut 1","Plage Koopa 1","Île Choco 1","Lac Vanille 1","Vallée Fantôme 1","Circuit Mario 2","Château de Bowser 1","Plaine Donut 2","Château de Bowser 2","Île Choco 2","Circuit Mario 3","Plage Koopa 2","Lac Vanille 2","Vallée Fantôme 2","Plaine Donut 3","Vallée Fantôme 3","Circuit Mario 4","Château de Bowser 3","Route Arc-en-Ciel","Circuit Peach","Plage Maskass","Bord du Fleuve","Château de Bowser I","Circuit Mario","Lac Boo","Pays Fromage","Château de Bowser II","Circuit Luigi","Jardin volant","Île Cheep-Cheep","Pays Crépuscule","Royaume Sorbet","Route Ruban","Désert Yoshi","Château de Bowser III","Bord du Lac","Jetée cassée","Château de Bowser IV","Route  Arc-en-Ciel"]'; ?>,
lMaps = <?php echo ($language&&!$creation) ? '["Mario Circuit 1","Donut Plains 1","Koopa Beach 1","Choco Island 1","Vanilla Lake 1","Ghost Valley 1","Mario Circuit 2","Bowser Castle 1","Donut Plains 2","Bowser Castle 2","Choco Island 2","Mario Circuit 3","Koopa Beach 2","Vanilla Lake 2","Ghost Valley 2","Donut Plains 3","Ghost Valley 3","Mario Circuit 4","Bowser Castle 3","Rainbow Road","Peach Circuit","Shy Guy Beach","Riverside Park","Bowser Castle I","Mario Circuit","Boo Lake","Cheese Land","Bowser Castle II","Luigi Circuit","Sky Garden","Cheep-Cheep Island","Sunset Wilds","Snow Land","Ribbon Road","Yoshi Desert","Bowser Castle III","Lakeside Park","Broken Pier","Bowser Castle IV","Rainbow  Road"]':'circuits'; ?>;
var sUser = <?php echo isset($user) ? $user['id']:0 ?>;
var classement = new Array();
for (var i=0;i<circuits.length;i++)
	classement[circuits[i]] = new Resultat();
<?php
if ($creation) {
	if (!empty($cIDs))
		$getResults = mysql_query('SELECT r.*,c.code FROM `mkrecords_bkp` r LEFT JOIN `mkprofiles` p ON r.player=p.id LEFT JOIN `mkcountries` c ON p.country=c.id WHERE r.circuit IN ('. implode(',',$cIDs) .') AND r.type="'. $type .'" ORDER BY r.temps');
	else
		$getResults = null;
}
elseif (isset($user))
	$getResults = mysql_query('SELECT r.*,c.code,(r.player='.$user['id'].') AS shown FROM `records_bkp` r LEFT JOIN `mkprofiles` p ON r.player=p.id LEFT JOIN `mkcountries` c ON p.country=c.id ORDER BY r.temps');
else
	$getResults = mysql_query('SELECT r.*,c.code FROM `records_bkp` r LEFT JOIN `mkprofiles` p ON r.player=p.id LEFT JOIN `mkcountries` c ON p.country=c.id ORDER BY r.temps');
while ($result = mysql_fetch_array($getResults))
	echo 'classement['. ($creation ? 'circuits['. array_search($result['circuit'],$cIDs) .']':'"'. $result['circuit'] .'"') .'].classement.push(["'.addslashes($result['nom']).'","'.addslashes($result['perso']).'",'.$result['temps'].','.$result['player'].','.'"'.$result['code'].'"'.(isset($result['shown']) ? ','.$result['shown']:'').']);';
?>
for (var i=circuits.length-1;i>=0;i--) {
	var circuit = circuits[i];
	if (!classement[circuit].classement.length || (sUser && noShownData(classement[circuit].classement))) {
		circuits.splice(i,1);
		if (lMaps != circuits)
			lMaps.splice(i,1);
		delete classement[circuit];
	}
}
function noShownData(classement) {
	for (var i=0;i<classement.length;i++) {
		if (classement[i][5])
			return false;
	}
	return true;
}
function getPlace(id,place) {
	var iCircuit = classement[circuits[id]];
	var record = iCircuit.classement[place][2];
	while ((place >= 0) && (record == iCircuit.classement[place][2]))
		place--;
	return place+2;
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
var oParams;
function addResult(id, i) {
	var iJoueur = classement[circuits[id]].classement[i];
	var oResult = document.createElement("tr");
	oResult.className = 'result';
	var oPlace = document.createElement("td");
	var inPlace = getPlace(id,i);
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
	var pseudoTxt;
	if (iJoueur[4]) {
		oPseudo.className = "recorder";
		pseudoTxt = '<img src="images/flags/'+iJoueur[4]+'.png" alt="'+iJoueur[4]+'" onerror="this.style.display=\'none\'" /> '+iJoueur[0];
	}
	else
		pseudoTxt = iJoueur[4]+" "+iJoueur[0];
	if (iJoueur[3])
		oPseudo.innerHTML = '<a href="profil.php?id='+iJoueur[3]+'">'+pseudoTxt+'</a>';
	else
		oPseudo.innerHTML = pseudoTxt;
	oResult.appendChild(oPseudo);
	var oPerso = document.createElement("td");
	var oPersoDiv = document.createElement("div");
	var oPersoImg = document.createElement("img");
	oPersoImg.src = getSpriteSrc(iJoueur[1]);
	oPersoImg.setAttribute("alt", iJoueur[1]);
	oPersoDiv.appendChild(oPersoImg);
	oPerso.appendChild(oPersoDiv);
	oResult.appendChild(oPerso);
	var oTemps = document.createElement("td");
	var getTime = iJoueur[2]/1000*67, sec = Math.floor(getTime), mls = Math.round((getTime-sec)*1000), min = Math.floor(sec/60);
	sec -= min*60;
	if (sec < 10)
		sec = "0"+ sec;
	if (mls < 10)
		mls = "00"+ mls;
	else if (mls < 100)
		mls = "0"+ mls;
	oTemps.innerHTML = min +":"+ sec +":"+ mls;
	oResult.appendChild(oTemps);
	document.getElementById("result"+ id).appendChild(oResult);
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
	var iResult = classement[circuits[id]], iPage = iResult.page, iClassement = iResult.classement;
	var tableHeader = document.createElement("tr");
	tableHeader.id = "titres";
	var oPlace = document.createElement("td");
	oPlace.innerHTML = "Place";
	tableHeader.appendChild(oPlace);
	var oPseudo = document.createElement("td");
	oPseudo.innerHTML = "<?php echo $language ? 'Nick':'Pseudo'; ?>";
	tableHeader.appendChild(oPseudo);
	var oPerso = document.createElement("td");
	oPerso.innerHTML = "<?php echo $language ? 'Character':'Personnage'; ?>";
	tableHeader.appendChild(oPerso);
	var oTemps = document.createElement("td");
	oTemps.innerHTML = "<?php echo $language ? 'Time':'Temps'; ?>";
	tableHeader.appendChild(oTemps);
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
		oPages.setAttribute("colspan", 4);
		oPages.innerHTML = "Page :";
		if (document.getElementById("result"+ id).getElementsByTagName("tr").length == 1) {
			oPages.innerHTML = "<?php echo $language ? 'No record for this circuit yet':'Aucun record sur ce circuit pour l\'instant'; ?>";
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
	var cRace = oParams.map ? oParams.map.value:circuits[0];
	var setToAll = (cRace == "All");
	var sPlayer = oParams.joueur.value.toLowerCase();
	var noPlayers = sPlayer;
	for (var i=0;i<circuits.length;i++) {
		if (setToAll || (cRace == circuits[i])) {
			var n = undefined;
			if (sPlayer) {
				n = [];
				var iClassement = classement[circuits[i]].classement;
				for (var j=0;j<iClassement.length;j++) {
					if (iClassement[j][0].toLowerCase() == sPlayer) {
						n.push(j);
						noPlayers = false;
					}
				}
				if (!n.length)
					continue;
			}
			else if (sUser) {
				n = [];
				var iClassement = classement[circuits[i]].classement;
				for (var j=0;j<iClassement.length;j++) {
					if (iClassement[j][5]) {
						n.push(j);
						noPlayers = false;
					}
				}
				if (!n.length)
					continue;
			}
			else
				classement[circuits[i]].page = 0;
			var circuitTitle = document.createElement("h2");
			circuitTitle.id = "circuit"+ i;
			circuitTitle.innerHTML = lMaps[i];
			oContent.appendChild(circuitTitle);
			var tableResult = document.createElement("table");
			tableResult.id = "result"+ i;
			oContent.appendChild(tableResult);
			displayResult(i, n);
		}
	}
	if (noPlayers) {
		var oNoResults = document.createElement("strong");
		oNoResults.innerHTML = "<?php echo $language ? "No result found for this search" : "Aucun résultat trouvé pour cette recherche."; ?>";
		oContent.appendChild(oNoResults);
	}
}
function changePage(id, nPage) {
	classement[circuits[id]].page = nPage;
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
		tCircuit.innerHTML = "<?php echo $language ? 'See':'Voir'; ?> circuit : ";
		tCircuit.style.fontWeight = "bold";
		oParamsContent.appendChild(tCircuit);
		var iCircuit = document.createElement("select");
		iCircuit.name = "map";
		var cTous = document.createElement("option");
		cTous.value = "All";
		var nbRecords = 0;
		for (var i=0;i<circuits.length;i++)
			nbRecords += classement[circuits[i]].classement.length;
		if (sUser)
			cTous.innerHTML = "<?php echo $language ? 'All':'Tous'; ?>";
		else
			cTous.innerHTML = "<?php echo $language ? 'All':'Tous'; ?> ("+ nbRecords + " record"+ ((nbRecords>1) ? "s":"") +")";
		iCircuit.appendChild(cTous);
		for (var i=0;i<circuits.length;i++) {
			var cCircuit = document.createElement("option");
			cCircuit.value = circuits[i];
			var cRecords = classement[circuits[i]].classement.length;
			if (sUser)
				cCircuit.innerHTML = lMaps[i];
			else
				cCircuit.innerHTML = lMaps[i] +" ("+ cRecords +" record"+ ((cRecords>1) ? "s":"") +")";
			iCircuit.appendChild(cCircuit);
		}
		<?php
		if (isset($_GET['map']))
			echo 'iCircuit.value = "'.htmlspecialchars($_GET['map']).'";';
		?>
		iCircuit.onchange = displayResults;
		oParamsContent.appendChild(iCircuit);
		
		oParamsContent.appendChild(document.createElement("br"));
		<?php
	}
	?>
	
	var tJoueur = document.createElement("span");
	tJoueur.innerHTML = "<?php echo $language ? 'See player':'Voir joueur'; ?> : ";
	tJoueur.style.fontWeight = "bold";
	oParamsContent.appendChild(tJoueur);
	var iJoueur = document.createElement("input");
	iJoueur.type = "text";
	iJoueur.id = "joueur";
	iJoueur.name = "joueur";
	iJoueur.value = "<?php echo (isset($_GET['joueur']) ? $_GET['joueur']:null); ?>";
	iJoueur.onchange = displayResults;
	oParamsContent.appendChild(iJoueur);
	if (sUser) {
		tJoueur.style.display = "none";
		iJoueur.style.display = "none";
	}
	
	oParamsBlock.appendChild(oParamsContent);
	
	var oParamsSubmit = document.createElement("div");
	
	oParamsBlock.appendChild(oParamsSubmit);
	oParams.appendChild(oParamsBlock);
	
	var joueurs = new Array();
	var lowerCaseJoueurs = new Array();
	for (circuit in classement) {
		var iClassement = classement[circuit].classement;
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