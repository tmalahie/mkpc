<?php
include('../includes/language.php');
include('../includes/session.php');
include('../includes/getId.php');
include('../includes/initdb.php');
include('../includes/perso-stats.php');
require_once('../includes/persos.php');
?>
<!DOCTYPE html>
<html lang="<?php echo $language ? 'en':'fr'; ?>">
<head>
<meta charset="utf-8" />
<meta name="viewport" content="width=device-width, initial-scale=1">
<link rel="shortcut icon" type="image/x-icon" href="images/favicon.ico" />
<link rel="stylesheet" href="styles/perso-editor.css?reload=1" />
<title><?php echo $language ? 'Character editor':'Éditeur de persos'; ?></title>
<?php
include('../includes/o_xhr.php');
?>
<script type="text/javascript">
var PERSO_DIR = "<?php echo PERSOS_DIR; ?>";
var language = <?php echo ($language ? 'true':'false'); ?>;
var persoStatsHandler;
function hoverPerso(e,list,id) {
	var div = document.getElementById(list+"persoctn-"+id);
	var img = document.getElementById(list+"perso-"+id);
	div.className = "perso-selector perso-animate";
	img.src = PERSO_DIR + div.dataset.sprites + ".png";
	document.getElementById("perso-info-name").innerHTML = div.dataset.name;
	if (div.dataset.author)
		document.getElementById("perso-info-author").innerHTML = (language ? "By":"Par") + " "+ "<strong>"+ div.dataset.author +"</strong>";
	else
		document.getElementById("perso-info-author").innerHTML = "";
	var oPersoRate = document.getElementById("perso-info-rating");
	oPersoRate.innerHTML = "";
	if (div.dataset.private && (list == "my"))
		document.getElementById("perso-info-nbrates").innerHTML = language ? "Non-shared character":"Perso non partagé";
	else {
		var note = div.dataset.rating*1, nbnotes = div.dataset.nbrates*1;
		for (var i=1;i<=note;i++) {
			var oEtoile = document.createElement("div");
			var eImg = document.createElement("img");
			eImg.src = "images/star1.png";
			eImg.alt = "star0";
			oEtoile.appendChild(eImg);
			oPersoRate.appendChild(oEtoile);
		}
		var rest = note-Math.floor(note);
		if (rest) {
			var w1 = (5+Math.round(15*rest));
			var oEtoile = document.createElement("div");
			var eImg = document.createElement("img");
			eImg.src = "images/star0.png";
			eImg.alt = "star1";
			oEtoile.appendChild(eImg);
			var oEtoile2 = document.createElement("div");
			oEtoile2.style.width = w1 +"px";
			var eImg2 = document.createElement("img");
			eImg2.src = "images/star1.png";
			eImg2.alt = "star0";
			oEtoile2.appendChild(eImg2);
			oEtoile.appendChild(oEtoile2);
			oPersoRate.appendChild(oEtoile);
		}
		for (var i=Math.ceil(note);i<5;i++) {
			var oEtoile = document.createElement("div");
			var eImg = document.createElement("img");
			eImg.src = "images/star0.png";
			eImg.alt = "star0";
			oEtoile.appendChild(eImg);
			oPersoRate.appendChild(oEtoile);
		}
		if (language)
			document.getElementById("perso-info-nbrates").innerHTML = nbnotes ? (Math.round(note*100)/100) +"/5 in "+ nbnotes +" rating"+ (nbnotes>1 ? "s":"") : "Unrated";
		else
			document.getElementById("perso-info-nbrates").innerHTML = nbnotes ? (Math.round(note*100)/100) +"/5 sur "+ nbnotes +" note"+ (nbnotes>1 ? "s":"") : "Non noté";
	}
	persoStatsHandler = setTimeout(function() {
		document.getElementById("perso-info").style.top = (e.target.getBoundingClientRect().bottom+10) +"px";
		document.getElementById("perso-info").style.display = "block";
	}, 200);
}
function houtPerso(list,id) {
	clearTimeout(persoStatsHandler);
	var div = document.getElementById(list+"persoctn-"+id);
	var img = document.getElementById(list+"perso-"+id);
	img.src = PERSO_DIR + div.dataset.sprites + "-ld.png";
	div.className = "perso-selector";
	document.getElementById("perso-info").style.display = "none";
}
var persoId = -1;
function selectPerso(list,id) {
	var div = document.getElementById(list+"persoctn-"+id);
	if ((list != "all" && list != "unlocked" && list != "collab") || div.dataset.mine) {
		if (window.opener) {
			window.opener.selectPerso(id);
			window.close();
		}
		else
			alert(language ? 'Unable to select the character, you probably closed the tab with the game':'Impossible de sélectionner le perso, l\'onglet du jeu a probablement été fermé');
	}
	else {
		persoId = id;
		var $persoStats = document.getElementById("perso-stats-mask");
		$persoStats.dataset.list = list;
		$persoStats.style.display = "block";
		restoreStats();
	}
}
function restoreStats() {
	var list = document.getElementById("perso-stats-mask").dataset.list;
	var div = document.getElementById(list+"persoctn-"+persoId);
	var persoData = div.dataset;
	document.getElementById("selectedpersostats").src = "images/sprites/uploads/"+ persoData.sprites + "-ld.png";
	document.getElementById("stats-template").selectedIndex = 0;
	for (var i=0;i<statTypes.length;i++) {
		var statType = statTypes[i];
		document.getElementById(statType).value = Math.round(statsGradient*(persoData[statType]-statsRange[statType].min)/(statsRange[statType].max-statsRange[statType].min));
	}
	updateCursors();
}
function confirmStats() {
	document.forms["perso-form"].style.visibility = "hidden";
	var list = document.getElementById("perso-stats-mask").dataset.list;
	document.getElementById("perso-stats-mask").onclick = undefined;
	var apiParams = "id="+persoId+"&list="+list;
	for (var i=0;i<statTypes.length;i++) {
		var statType = statTypes[i];
		apiParams += "&"+ statType +"="+ document.getElementById(statType).value;
	}
	xhr("selectPerso.php", apiParams, function(res) {
		if (res == 1) {
			selectPerso("my", persoId);
			return true;
		}
		return false;
	});
}
function goToEditor() {
	window.opener.document.location.href = "persoEditor.php";
	window.close();
}
function goToHistory() {
	window.opener.document.location.href = "persoHistory.php";
	window.close();
}
function goToLocked() {
	window.opener.document.location.href = "persoLocked.php";
	window.close();
}

var unsavedData = <?php echo !empty($perso['name']) ? 'false':'true'; ?>;
var language = <?php echo ($language ? 'true':'false'); ?>;
var statTypes = ["<?php echo implode('","',array_keys($statsRange)); ?>"];
var statsGradient = <?php echo $statsGradient; ?>;
var statsRange = <?php echo json_encode($statsRange); ?>;
var cp = <?php echo json_encode($defaultPersosStats); ?>;
var pUnlocked = <?php include('../includes/getLocks.php'); ?>;
var P_ID = 0,
	P_UID = P_ID+1,
	P_NAME = P_UID+1,
	P_AUTHOR = P_NAME+1,
	P_MINE = P_AUTHOR+1,
	P_PRIVATE = P_MINE+1,
	P_STATS = P_PRIVATE+1,
	P_MAP = P_STATS+1,
	P_PODIUM = P_MAP+1,
	P_RATING = P_PODIUM+1,
	P_NBRATES = P_RATING+1,
	P_PLAYCOUNT = P_NBRATES+1;
function updatePersoList(listKey, from) {
	var persosList = persosLists[listKey];
	var listDiv = document.getElementById("persos-list-"+listKey);
	if (listDiv && !from)
		listDiv.innerHTML = "";

	var observer = window.IntersectionObserver ? new IntersectionObserver((entries, observer) => {
		entries.forEach((entry) => {
			if (entry.isIntersecting) {
				var oImg = entry.target;
				oImg.src = oImg.dataset.src;
				observer.unobserve(oImg);
			}
		});
	}, {
    	root: listDiv,
  	}) : null;

	for (var i=from||0;i<persosList.length;i++) {
		var persoData = persosList[i][0];
		var persoStats = persosList[i][1];
		var persoId = persoData[P_ID];
		if (!persoStats)
			persoStats = persoData[P_STATS];
		var oDiv = document.createElement("div");
		oDiv.className = "perso-selector";
		oDiv.id = listKey+"persoctn-"+persoId;
		if (!oDiv.dataset) oDiv.dataset = {};
		oDiv.dataset.id = persoId;
		oDiv.dataset.list = listKey;
		oDiv.dataset.sprites = persoData[P_UID];
		oDiv.dataset.name = persoData[P_NAME];
		if (listKey != "my" && !persoData[P_PRIVATE])
			oDiv.dataset.author = persoData[P_AUTHOR];
		if (persoData[P_MINE])
			oDiv.dataset.mine = "1";
		oDiv.dataset.acceleration = persoStats[0];
		oDiv.dataset.speed = persoStats[1];
		oDiv.dataset.handling = persoStats[2];
		oDiv.dataset.mass = persoStats[3];
		oDiv.dataset.map = persoData[P_MAP];
		oDiv.dataset.podium = persoData[P_PODIUM];
		if (persoData[P_PRIVATE])
			oDiv.dataset.private = persoData[P_PRIVATE];
		oDiv.dataset.rating = persoData[P_RATING];
		oDiv.dataset.nbrates = persoData[P_NBRATES];
		oDiv.onclick = persoClick;
		oDiv.onmouseover = persoHover;
		oDiv.onmouseout = persoHout;
		var oImg = document.createElement("img");
		oImg.id = listKey+"perso-"+persoId;
		oImg.alt = persoData["name"];
		if (observer) {
			oImg.src = "images/kart_placeholder.png";
			oImg.dataset.src = "images/sprites/uploads/"+ persoData[P_UID] + "-ld.png";
			observer.observe(oImg);
		}
		else
			oImg.src = "images/sprites/uploads/"+ persoData[P_UID] + "-ld.png";

		oDiv.appendChild(oImg);
		listDiv.appendChild(oDiv);
	}
}
function sortPersos(elt,sort) {
	document.getElementById("persos-currentsort").id = "";
	elt.id = "persos-currentsort";
	persosLists["all"] = [];
	updatePersoList("all");
	allPersoSort = sort;
	filterSearch();
}
function toggleSearch() {
	var searchForm = document.getElementById("persos-list-search");
	if (searchForm.style.display) {
		searchForm.style.display = "";
		searchForm.elements["perso-name"].value = "";
		searchForm.elements["perso-author"].value = "";
		filterSearch();
	}
	else {
		searchForm.style.display = "block";
		searchForm.elements["perso-name"].focus();
	}
}
<?php
$unlocked = array();
if ($id) {
	$unlockedPersos = mysql_query('SELECT c.* FROM `mkclrewarded` rw INNER JOIN `mkclrewards` r ON rw.reward=r.id INNER JOIN `mkchars` c ON r.charid=c.id WHERE rw.player='. $id .' ORDER BY rw.id DESC');
	while ($unlockedPerso = mysql_fetch_array($unlockedPersos))
		$unlocked[] = array(get_perso_payload($unlockedPerso),array(+$unlockedPerso['acceleration'],+$unlockedPerso['speed'],+$unlockedPerso['handling'],+$unlockedPerso['mass']));
}
?>
var persosLists = {
	"my":<?php
	$myPersos = mysql_query('SELECT * FROM `mkchars` WHERE identifiant='.$identifiants[0].' AND identifiant2='.$identifiants[1].' AND identifiant3='.$identifiants[2].' AND identifiant4='.$identifiants[3].' AND name!="" ORDER BY id DESC');
	$my = array();
	while ($myPerso = mysql_fetch_array($myPersos))
		$my[] = array(get_perso_payload($myPerso));
	echo json_encode($my);
	?>,
	"hist":<?php
	$histPersos = mysql_query('SELECT c.*,h.acceleration,h.speed,h.handling,h.mass FROM `mkchisto` h INNER JOIN `mkchars` c ON h.id=c.id AND (c.author IS NOT NULL OR h.list IN ("unlocked", "collab")) WHERE h.identifiant='.$identifiants[0].' AND h.identifiant2='.$identifiants[1].' AND h.identifiant3='.$identifiants[2].' AND h.identifiant4='.$identifiants[3].' ORDER BY date DESC, id DESC LIMIT 100');
	$hist = array();
	while ($histPerso = mysql_fetch_array($histPersos))
		$hist[] = array(get_perso_payload($histPerso),array(+$histPerso['acceleration'],+$histPerso['speed'],+$histPerso['handling'],+$histPerso['mass']));
	echo json_encode($hist);
	?>,
	"unlocked":<?php
	echo json_encode($unlocked);
	?>,
	"all":[]
};
function persoClick(e) {
	selectPerso(this.dataset.list,this.dataset.id);
}
function persoHover(e) {
	hoverPerso(e,this.dataset.list,this.dataset.id);
}
function persoHout(e) {
	houtPerso(this.dataset.list,this.dataset.id);
}
function filterSearch() {
	allPersoPage = 0;
	if (allPersoSearching)
		allPersoPostRefresh = true;
	else {
		document.getElementById("persos-list-all").scrollTop = 0;
		refreshPersoList();
	}
}
function handleListScroll(elt) {
	if (allPersoNext && !allPersoSearching) {
		if (elt.scrollTop >= (elt.scrollHeight - elt.offsetHeight)) {
			allPersoPage++;
			refreshPersoList();
		}
	}
}
var allPersoPage = 0;
var allPersoHandler = 0;
var allPersoNext = true;
var allPersoSort = "latest";
var allPersoSearching = false;
var allPersoPostRefresh = false;
function refreshPersoList() {
	var searchForm = document.forms["persos-list-search"];
	var nameSearch = searchForm.elements["perso-name"].value;
	var authorSearch = searchForm.elements["perso-author"].value;
	var apiParams = "page="+allPersoPage+"&sort="+allPersoSort;
	if (nameSearch)
		apiParams += "&name="+encodeURIComponent(nameSearch);
	if (authorSearch)
		apiParams += "&author="+encodeURIComponent(authorSearch);
	allPersoSearching = true;
	allPersoPostRefresh = false;
	var currentHandler = ++allPersoHandler;
	xhr("getPersosList.php", apiParams, function(res) {
		if (currentHandler !== allPersoHandler)
			return true;
		var data;
		try {
			data = JSON.parse(res);
		}
		catch (e) {
			return false;
		}
		var lastCount;
		if (allPersoPage) {
			lastCount = persosLists["all"].length;
			persosLists["all"] = persosLists["all"].concat(data.list);
		}
		else
			persosLists["all"] = data.list;
		updatePersoList("all",lastCount);
		allPersoSearching = false;
		allPersoNext = data.next;
		if (allPersoPostRefresh)
			refreshPersoList();
		return true;
	});
}
function showCollabLinkHelp() {
	alert(language ? "Enter the characters's collaboration link here.\nTo get this link, the character owner will simply need to select the character in the editor and click on \"Collaborate\"" : "Saisissez ici le lien de collaboration du perso.\nPour obtenir ce lien, le propriétaire du perso devra simplement sélectionner le perso dans l'éditeur et cliquer sur \"Collaborer\"");
}
function togglePersoCollab() {
	var $persoListCollab = document.getElementById("persos-collab-form");
	if ($persoListCollab.style.display)
		$persoListCollab.style.display = "";
	else {
		$persoListCollab.style.display = "block";
		$persoListCollab.elements["collab-link"].focus();
	}
}
function handlePersoCollabSubmit(e) {
	e.preventDefault();
	var $form = e.target;
	var url = $form.elements["collab-link"].value;
	var creationId, creationKey;
	try {
		var urlParams = new URLSearchParams(new URL(url).search);
		creationId = urlParams.get('id');
		creationKey = urlParams.get('collab');
	}
	catch (e) {
	}
	if (!creationKey) {
		alert(language ? "Invalid URL" : "URL invalide");
		return;
	}
	var $submitBtn = $form.querySelector('input[type="submit"]');
	$submitBtn.disabled = true;
	xhr("importCollabPerso.php", "id="+creationId+"&collab="+creationKey, function(res) {
		$submitBtn.disabled = false;
		if (!res) {
			alert(language ? "Invalid link" : "Lien invalide");
			return true;
		}
		res = JSON.parse(res);
		var persoData = [];
		persoData[P_ID] = res.id;
		persoData[P_UID] = res.sprites;
		persoData[P_NAME] = res.name;
		persoData[P_AUTHOR] = res.author;
		persoData[P_MAP] = res.map;
		persoData[P_PODIUM] = res.podium;
		var persoStats = [
			res.acceleration,
			res.speed,
			res.handling,
			res.mass
		];
		persosLists.collab = [[
			persoData,
			persoStats
		]];
		updatePersoList("collab");
		selectPerso("collab", res.id);

		return true;
	});
}
document.addEventListener("DOMContentLoaded", function() {
	for (var listKey in persosLists)
		updatePersoList(listKey);
	refreshPersoList();
});
</script>
<script type="text/javascript" src="scripts/perso-stats.js?reload=1"></script>
<script type="text/javascript">
var onUpdateCursors = updateCursors;
updateCursors = function() {
	if (persoId == -1)
		return;
	onUpdateCursors();
	var list = document.getElementById("perso-stats-mask").dataset.list;
	var div = document.getElementById(list+"persoctn-"+persoId);
	var persoData = div.dataset;
	var originalStats = true;
	for (var i=0;i<statTypes.length;i++) {
		var statType = statTypes[i];
		if (document.getElementById(statType).value != Math.round(statsGradient*(persoData[statType]-statsRange[statType].min)/(statsRange[statType].max-statsRange[statType].min))) {
			originalStats = false;
		}
	}
	document.getElementById("restorestats").style.display = originalStats ? "none":"block";
	if (originalStats)
		document.getElementById("stats-template").selectedIndex = 0;
};
</script>
</head>
<body>
	<h2><?php echo $language ? 'Chose a character from editor':'Choix d\'un perso à partir de l\'éditeur'; ?></h2>
	<div class="persos-list-container">
	<h3><?php echo $language ? 'Your characters':'Vos persos'; ?></h3>
	<?php
	if (mysql_numrows($myPersos)) {
		?>
		<div class="persos-list" id="persos-list-my"></div>
		<?php
	}
	else {
		echo '<div class="persos-list-empty">';
		echo $language ? 'You haven\'t created characters yet':'Vous n\'avez créé aucun perso pour l\'instant';
		echo '</div>';
	}
	?>
	<div class="persos-list-more">
		<strong style="color:#42E242">+</strong> <a href="persoEditor.php" target="_blank" onclick="goToEditor();return false"><?php echo $language ? "Go to characters editor":"Accéder à l'éditeur de persos"; ?></a>
	</div>
	</div>
	<?php
	if (mysql_numrows($histPersos)) {
	?>
	<div class="persos-list-container">
	<h3><?php echo $language ? 'History':'Historique'; ?></h3>
		<div class="persos-list" id="persos-list-hist"></div>
		<div class="persos-list-more">
			<span style="color:#E2F222"><?php echo urldecode('%E2%AD%90'); ?></span> <a href="persoHistory.php" target="_blank" onclick="goToHistory();return false"><?php echo $language ? "Rate characters":"Noter les persos"; ?></a>
		</div>
	</div>
		<?php
	}
	?>
	<?php
	if ($id) {
		?>
	<div class="persos-list-container">
	<h3><?php echo $language ? 'Unlocked characters':'Persos débloqués'; ?></h3>
		<?php
		if (mysql_numrows($unlockedPersos))
			echo '<div class="persos-list" id="persos-list-unlocked"></div>';
		else
			echo '<div class="persos-list-empty">'. ($language ? 'No unlocked character':'Aucun perso débloqué') .'</div>';
		?>
		<div class="persos-list-more">
			<span style="color:#d29740"><?php echo urldecode('%F0%9F%94%92'); ?></span> <a href="persoLocked.php" target="_blank" onclick="goToLocked();return false"><?php echo $language ? "See unlockable characters":"Voir les persos à débloquer"; ?></a>
		</div>
	</div>
		<?php
	}
	?>
	<div class="persos-list-container">
	<h3><?php echo $language ? 'All shared characters':'Tous les persos partagés'; ?></h3>
	<div class="persos-sort">
		<a href="#null" onclick="sortPersos(this,'latest');return false" id="persos-currentsort"><?php echo $language ? 'Latest':'Les&nbsp;plus&nbsp;récents'; ?></a>
		<a href="#null" onclick="sortPersos(this,'rating');return false"><?php echo $language ? 'Top&nbsp;rated':'Les&nbsp;mieux&nbsp;notés'; ?></a>
		<a href="#null" onclick="sortPersos(this,'playcount');return false"><?php echo $language ? 'Most&nbsp;played':'Les&nbsp;plus&nbsp;joués'; ?></a>
	</div>
	<div class="persos-list" id="persos-list-all" onscroll="handleListScroll(this)"></div>
	<div class="persos-list" id="persos-list-collab"></div>
	<div class="persos-list-more">
		<span style="color:#DBE;font-size:0.8em;display:inline-block;-webkit-transform: rotate(45deg);-moz-transform: rotate(45deg);-o-transform: rotate(45deg);transform: rotate(45deg);">&#9906;</span>
		<a href="#null" onclick="toggleSearch();return false"><?php echo $language ? "Search characters":"Rechercher un perso"; ?></a>
	</div>
	<form id="persos-list-search" name="persos-list-search">
		<div><?php echo $language ? 'Name:':'Nom :'; ?> <input type="text" name="perso-name" placeholder="<?php echo $language ? 'Baby Mario':'Bébé Mario'; ?>" oninput="filterSearch()" /></div>
		<div><?php echo $language ? 'Author:':'Auteur :'; ?> <input type="text" name="perso-author" placeholder="Wargor" oninput="filterSearch()" /></div>
	</form>
	<div class="persos-list-more persos-list-more-collab">
		<strong style="color:#42C2F2;font-size:0.7em"><?php echo urldecode('%F0%9F%94%97'); ?></strong> <a href="javascript:togglePersoCollab()"><?php echo $language ? "Import from collaboration link":"Importer via un lien de collaboration"; ?></a>
	</div>
	<form id="persos-collab-form" name="persos-collab-form" onsubmit="handlePersoCollabSubmit(event)">
		<label>
			<span><?php
			echo $language ? 'Link':'Lien';
			?><a href="javascript:showCollabLinkHelp()">[?]</a>:
			</span>
			<input type="url" name="collab-link" placeholder="<?php
			require_once('../includes/collabUtils.php');
			$collab = array(
				'type' => 'mkchars',
				'creation_id' => 42,
				'secret' => 'y-vf-erny_2401_pbasvezrq'
			);
			echo getCollabUrl($collab);
			?>" />
			<input type="submit" value="Ok" />
		</label>
	</form>
	</div>
	<div id="perso-info">
		<div id="perso-info-name"></div>
		<div id="perso-info-author"></div>
		<div id="perso-info-rating"></div>
		<div id="perso-info-nbrates"></div>
	</div>
	<div class="perso-mask" id="perso-stats-mask" data-list="all" onclick="document.getElementById('perso-stats-mask').style.display='none'">
		<form method="post" name="perso-form" class="perso-form" id="perso-customstats" onclick="event.stopPropagation()" onsubmit="confirmStats();return false">
			<a class="close-perso-popup" href="javascript:document.getElementById('perso-stats-mask').style.display='none';void(0)">&times;</a>
			<div class="perso-stats perso-customstats">
				<div id="selectcustomstats">
					<img id="selectedpersostats" alt="Preview" />
					<?php echo $language ? 'Select character stats':'Choisir les stats du perso'; ?>
				</div>
				<div id="statstemplate">
					<?php echo $language ? 'Retreive stats from another character:':'Reprendre les stats de:'; ?> <select id="stats-template">
						<option><?php echo $language ? 'Character':'Perso'; ?>...</option>
					</select>
				</div>
				<table>
					<tr>
						<td><label for="acceleration"><?php echo $language ? 'Acceleration:':'Accélération :'; ?></label></td>
						<td><input type="range" name="acceleration" id="acceleration" min="0" max="<?php echo $statsGradient; ?>" step="1" value="0" /></td>
					</tr>
					<tr>
						<td><label for="speed"><?php echo $language ? 'Max speed:':'Vitesse max :'; ?></label></td>
						<td><input type="range" name="speed" id="speed" min="0" max="<?php echo $statsGradient; ?>" step="1" value="0" /></td>
					</tr>
					<tr>
						<td><label for="handling"><?php echo $language ? 'Handling:':'Maniabilité :'; ?></label></td>
						<td><input type="range" name="handling" id="handling" min="0" max="<?php echo $statsGradient; ?>" step="1" value="0" /></td>
					</tr>
					<tr>
						<td><label for="mass"><?php echo $language ? 'Weight:':'Poids :'; ?></label></td>
						<td><input type="range" name="mass" id="mass" min="0" max="<?php echo $statsGradient; ?>" step="1" value="0" /></td>
					</tr>
				</table>
				<a id="restorestats" href="javascript:restoreStats()"><?php echo $language ? 'Restore original stats':'Rétablir les stats originales'; ?></a>
				<div id="statsinfo">
					<?php
					echo $language
						? "To avoid overskilled characters, stats may not be better than an existing MKPC character."
						: "Pour éviter les persos &quot;cheatés&quot;, les stats ne doivent pas être supérieures à un perso existant dans MKPC.";
					?>
				</div>
				<div id="statssubmit">
					<input id="perso-submit" type="submit" value="<?php echo $language ? 'Submit':'Valider'; ?>" />
				</div>
			</div>
		</form>
	</div>
	<div class="perso-bottom">
		<script async src="//pagead2.googlesyndication.com/pagead/js/adsbygoogle.js"></script>
		<!-- Mario Kart PC -->
		<ins class="adsbygoogle"
		     style="display:inline-block;width:468px;height:60px"
		     data-ad-client="ca-pub-1340724283777764"
		     data-ad-slot="6691323567"></ins>
		<script>
		(adsbygoogle = window.adsbygoogle || []).push({});
		</script>
	</div>
</body>
</html>
<?php
mysql_close();
?>
