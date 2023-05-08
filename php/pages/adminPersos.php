<?php
include('../includes/session.php');
include('../includes/initdb.php');
require_once('../includes/getRights.php');
if (!hasRight('moderator')) {
	echo 'Access denied';
	mysql_close();
	exit;
}
include('../includes/language.php');
require_once('../includes/persos.php');
?>
<!DOCTYPE html>
<html lang="<?= P_("html language", "en") ?>">
<head>
<meta charset="utf-8" />
<meta name="viewport" content="width=device-width, initial-scale=1">
<link rel="shortcut icon" type="image/x-icon" href="images/favicon.ico" />
<link rel="stylesheet" href="styles/perso-editor.css?reload=1" />
<?php
include('../includes/o_online.php');
?>
<style type="text/css">
.perso-selector {
	display: inline-block;
	border-style: solid;
	border-width: 5px;
	border-color: transparent;
}
.perso-selector:hover {
	background-color: #90AADD;
	border-color: #90AADD;
}
.perso-selector.perso-selected {
	background-color: #BDE;
	border-color: #BDE;
}
#perso-options {
	margin-bottom: 10px;
}
#persos-list-search {
	display: block;
}
</style>
<script type="text/javascript">
var persoIds = [];
var author = "<?php if (isset($_COOKIE['mkauteur'])) echo htmlspecialchars($_COOKIE['mkauteur']); ?>";
var language = <?php echo ($language ? 'true':'false'); ?>;
function previewPerso(id) {
	var $myPerso = document.getElementById("myperso-"+id);
	$myPerso.classList.add("perso-animate");
	var $myPersoImg = $myPerso.querySelector("img");
	$myPersoImg.dataset.osrc = $myPersoImg.src;
	$myPersoImg.src = $myPerso.dataset.sprites;
}
function unprevPerso(id) {
	var $myPerso = document.getElementById("myperso-"+id);
	$myPerso.classList.remove("perso-animate");
	var $myPersoImg = $myPerso.querySelector("img");
	$myPersoImg.src = $myPersoImg.dataset.osrc;
	$myPersoImg.dataset.osrc = "";
}
function selectPerso(id) {
	var pid = persoIds.indexOf(id);
	if (pid != -1) {
		document.getElementById("myperso-"+id).classList.remove("perso-selected");
		persoIds.splice(pid,1);
	}
	else {
		document.getElementById("myperso-"+id).classList.add("perso-selected");
		persoIds.push(id);
	}
	var nbPersos = 0;
	for (var pid in persoIds) {
		if (lastPersoId != id)
			lastPersoId = pid;
		nbPersos++;
	}
	if (persoIds.length) {
		var lastPersoId = persoIds[persoIds.length-1];
		document.getElementById("perso-options").style.display = "inline-block";
		var persoData = document.getElementById("myperso-"+lastPersoId).dataset;
		var persoName = persoData.name;
		document.getElementById("perso-options-name").innerHTML = persoName;
		if (persoData.author)
			document.getElementById("perso-options-author").innerHTML = (language ? "By":"Par") + " " + persoData.author;
		else
			document.getElementById("perso-options-author").innerHTML = "";
	}
	else
		document.getElementById("perso-options").style.display = "none";
	document.getElementById("perso-del-nb").innerHTML = persoIds.length;
}
function delPerso() {
	if (confirm(language ? "Confirm deletion of "+ persoIds.length +" characters?":"Confirmer la suppression de "+ persoIds.length +" persos ?")) {
		o_xhr("deleteShare.php", "ids="+persoIds.join(","), function(res) {
			if (res == 1) {
				var persoList = document.querySelector(".mypersos-list");
				for (var i=0;i<persoIds.length;i++)
					persoList.removeChild(document.getElementById("myperso-"+persoIds[i]));
				document.getElementById("perso-options").style.display = "none";
				persoIds.length = 0;
				return true;
			}
			return false;
		});
	}
}
function lemmatize(s) {
	return s.toLowerCase();
}
function matchesSearch(needle,haystack) {
	return lemmatize(haystack).indexOf(lemmatize(needle)) != -1;
}
function filterSearch() {
	var searchForm = document.forms["persos-list-search"];
	var nameSearch = searchForm.elements["perso-name"].value;
	var authorSearch = searchForm.elements["perso-author"].value;
	var persosDiv = document.querySelectorAll(".mypersos-list > div");
	for (var i=0;i<persosDiv.length;i++) {
		var persoDiv = persosDiv[i];
		if (matchesSearch(nameSearch,persoDiv.dataset.name) && matchesSearch(authorSearch,persoDiv.dataset.author))
				persoDiv.style.display = "";
			else
				persoDiv.style.display = "none";
	}
}
document.addEventListener("DOMContentLoaded", function() {
	var observer = window.IntersectionObserver ? new IntersectionObserver((entries, observer) => {
		entries.forEach((entry) => {
			if (entry.isIntersecting) {
				var oImg = entry.target;
				oImg.src = oImg.dataset.src;
				observer.unobserve(oImg);
			}
		});
	}) : null;
	var listImgs = document.querySelectorAll(".mypersos-list > div > img");
	for (var i=0;i<listImgs.length;i++)
		observer.observe(listImgs[i]);
});
</script>
<title><?= _('Manage characters') ?></title>
</head>
<body>
<?php
$getPsersos = mysql_query('SELECT * FROM `mkchars` WHERE author IS NOT NULL ORDER BY publication_date DESC, id DESC');
$arePersos = mysql_numrows($getPsersos);
if ($arePersos) {
	?>
	<h1><?= _('Delete character shares') ?></h1>
	<div id="perso-options" style="min-width: 150px">
		<div id="perso-options-name" style="margin-bottom: 2px"></div>
		<div id="perso-options-author"></div>
		<div class="perso-options-delete">
			<button class="suppr-perso" onclick="javascript:delPerso()"><?= F_('Delete {htmlCharacterNb}', htmlCharacterNb: '[<span id="perso-del-nb">0</span>]') ?></button>
		</div>
	</div>
	<form id="persos-list-search" name="persos-list-search">
		<div><?= _('Name:') ?> <input type="text" name="perso-name" placeholder="<?= _('Baby Mario') ?>" oninput="filterSearch()" /></div>
		<div><?= _('Author:') ?> <input type="text" name="perso-author" placeholder="Wargor" oninput="filterSearch()" /></div>
	</form>
	<div class="mypersos-list">
	<?php
	while ($perso = mysql_fetch_array($getPsersos)) {
		$spriteSrcs = get_sprite_srcs($perso['sprites']);
		?>
		<div id="myperso-<?php echo $perso['id']; ?>" class="perso-selector" onmouseover="previewPerso(<?php echo $perso['id']; ?>)" onmouseout="unprevPerso(<?php echo $perso['id']; ?>)" onclick="selectPerso(<?php echo $perso['id']; ?>)" data-name="<?php echo htmlspecialchars($perso['name']); ?>" data-author="<?php echo htmlspecialchars($perso['author']); ?>" data-sprites="<?php echo $spriteSrcs['hd']; ?>">
			<img src="images/kart_placeholder.png" data-src="<?php echo $spriteSrcs['ld']; ?>" alt="<?php echo htmlspecialchars($perso['name']); ?>" />
		</div>
		<?php
	}
	?>
	</div>
	</div>
	<?php
}
mysql_close();
?>
<p><a href="index.php"><?= _("Back to Mario Kart PC") ?></a></p>
</body>
</html>