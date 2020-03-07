<?php
include('getId.php');
include('language.php');
include('persos.php');
include('initdb.php');
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
		prevRate(currentRating);
		document.getElementById("perso-options-name").innerHTML = persoName;
		if (persoData.author)
			document.getElementById("perso-options-author").innerHTML = (language ? "By":"Par") + " " + persoData.author;
		else
			document.getElementById("perso-options-author").innerHTML = "";
	}
	else
		document.getElementById("perso-options").style.display = "none";
	document.getElementById("perso-options-feedback").style.display = "none";
}
var currentRating = 0;
function prevRate(rate) {
	for (var i=1;i<=rate;i++)
		document.getElementById("star"+i).src = "images/star1.png";
	for (var i=rate+1;i<=5;i++)
		document.getElementById("star"+i).src = "images/star0.png";
}
var sendingRate = false;
function resetRate() {
	prevRate(currentRating);
}
function sendRate(rate) {
	if (rate == currentRating)
		rate = 0;
	prevRate(rate);
	currentRating = rate;
	document.getElementById("myperso-"+persoId).dataset.rating = currentRating;
	if (sendingRate)
		return false;
	sendingRate = true;
	document.getElementById("perso-options-feedback").style.display = "none";
	o_xhr("ratePerso.php", "id="+ persoId +"&rating="+rate, function(res) {
		if (res == 1) {
			document.getElementById("perso-options-feedback").style.display = "block";
			sendingRate = false;
			return true;
		}
		return false;
	});
}
</script>
<title><?php echo $language ? 'Rate characters':'Noter les persos'; ?></title>
</head>
<body>
<?php
$getPsersos = mysql_query('SELECT c.*,h.acceleration,h.speed,h.handling,h.mass,h.rating FROM `mkchisto` h INNER JOIN `mkchars` c ON h.id=c.id AND c.author IS NOT NULL WHERE h.identifiant='.$identifiants[0].' AND h.identifiant2='.$identifiants[1].' AND h.identifiant3='.$identifiants[2].' AND h.identifiant4='.$identifiants[3].' ORDER BY date DESC');
$arePersos = mysql_numrows($getPsersos);
if ($arePersos) {
	?>
	<h1><?php echo $language ? 'Rate characters':'Noter les persos'; ?></h1>
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
	<div id="perso-options">
		<div id="perso-options-name" style="margin-bottom: 2px"></div>
		<div id="perso-options-author"></div>
		<div class="perso-options-stars" onmouseout="resetRate()"><?php
		for ($i=1;$i<=5;$i++) {
			?>
			<img src="images/star0.png" id="star<?php echo $i; ?>" onmouseover="prevRate(<?php echo $i; ?>)" onclick="sendRate(<?php echo $i; ?>)" alt="Star" />
			<?php
		}
		?></div>
		<div id="perso-options-feedback"><?php echo $language ? 'Thanks':'Merci'; ?></div>
	</div>
	</div>
	<?php
}
mysql_close();
?>
<p><a href="mariokart.php"><?php echo $language ? "Back to Mario Kart PC":"Retour à Mario Kart PC"; ?></a></p>
</body>
</html>