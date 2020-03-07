<?php
include('session.php');
include('initdb.php');
require_once('getRights.php');
if (!hasRight('moderator')) {
	echo 'Access denied';
	mysql_close();
	exit;
}
include('language.php');
include('persos.php');
?>
<!DOCTYPE html>
<html lang="<?php echo $language ? 'en':'fr'; ?>">
<head>
<meta charset="utf-8" />
<meta name="viewport" content="width=device-width, initial-scale=1">
<link rel="shortcut icon" type="image/x-icon" href="images/favicon.ico" />
<link rel="stylesheet" href="styles/perso-editor.css" />
<?php
include('o_online.php');
?>
<script type="text/javascript">
var persoId = -1;
var author = "<?php echo htmlspecialchars($_COOKIE['mkauteur']); ?>";
var language = <?php echo ($language ? 'true':'false'); ?>;
function selectPerso(id) {
	if (persoId != -1)
		document.getElementById("myperso-"+persoId).className = "";
	if (id == persoId)
		persoId = -1;
	else
		persoId = id;
	if (persoId != -1) {
		document.getElementById("myperso-"+persoId).className = "perso-selected";
		document.getElementById("perso-options").style.display = "inline-block";
		var persoData = document.getElementById("myperso-"+persoId).dataset;
		var persoName = persoData.name;
		currentRating = persoData.rating*1;
		document.getElementById("perso-options-name").innerHTML = persoName;
		if (persoData.author)
			document.getElementById("perso-options-author").innerHTML = (language ? "By":"Par") + " " + persoData.author;
		else
			document.getElementById("perso-options-author").innerHTML = "";
	}
	else
		document.getElementById("perso-options").style.display = "none";
}
function delPerso() {
	if (confirm(language ? "Delete character?":"Supprimer le perso ?")) {
		o_xhr("deleteShare.php", "id="+persoId, function(res) {
			if (res == 1) {
				document.getElementById("myperso-"+persoId).style.display = "none";
				document.getElementById("perso-options").style.display = "none";
				persoId = -1;
				return true;
			}
			return false;
		});
	}
}
</script>
<title><?php echo $language ? 'Rate characters':'Noter les persos'; ?></title>
</head>
<body>
<?php
$getPsersos = mysql_query('SELECT * FROM `mkchars` WHERE author IS NOT NULL ORDER BY publication_date DESC, id DESC');
$arePersos = mysql_numrows($getPsersos);
if ($arePersos) {
	?>
	<h1><?php echo $language ? 'Delete character shares':'Supprimer des partages de persos'; ?></h1>
	<div class="mypersos">
	<div class="mypersos-list">
	<?php
	while ($perso = mysql_fetch_array($getPsersos)) {
		$spriteSrcs = get_sprite_srcs($perso['sprites']);
		?>
		<img src="<?php echo $spriteSrcs['ld']; ?>" alt="<?php echo htmlspecialchars($perso['name']); ?>" id="myperso-<?php echo $perso['id']; ?>" onclick="selectPerso(<?php echo $perso['id']; ?>)" data-name="<?php echo htmlspecialchars($perso['name']); ?>" data-rating="<?php echo htmlspecialchars($perso['rating']); ?>" data-author="<?php echo htmlspecialchars($perso['author']); ?>" />
		<?php
	}
	?>
	</div>
	</div>
	<div id="perso-options" style="min-width: 150px">
		<div id="perso-options-name" style="margin-bottom: 2px"></div>
		<div id="perso-options-author"></div>
		<div class="perso-options-delete">
			<button class="suppr-perso" onclick="javascript:delPerso()"><?php echo $language ? "Delete":"Supprimer"; ?></button>
		</div>
	</div>
	</div>
	<?php
}
mysql_close();
?>
<p><a href="index.php"><?php echo $language ? "Back to Mario Kart PC":"Retour à Mario Kart PC"; ?></a></p>
</body>
</html>