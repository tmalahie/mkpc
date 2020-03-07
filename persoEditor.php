<?php
include('getId.php');
include('language.php');
session_start();
include('tokens.php');
assign_token();
include('persos.php');
include('file-quotas.php');
include('initdb.php');
if (isset($_FILES['sprites'])) {
	$upload = handle_upload($_FILES['sprites']);
	if (isset($upload['id']))
		header('location: editPerso.php?id='. $upload['id'] .'&new');
	if (isset($upload['error']))
		$error = $upload['error'];
}
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
		var persoName = document.getElementById("myperso-"+persoId).dataset.name;
		if (persoName)
			document.getElementById("perso-options-name").innerHTML = persoName;
		else
			document.getElementById("perso-options-name").innerHTML = "<em>"+ (language ? "Being created":"En cours de création") +"</em>";
	}
	else
		document.getElementById("perso-options").style.display = "none";
}
function editPerso() {
	document.location.href = "editPerso.php?id="+persoId;
}
function delPerso() {
	if (confirm(language ? "Delete character?":"Supprimer le perso ?"))
		document.location.href = "delPerso.php?id="+persoId +"&token=<?php echo $_SESSION['csrf']; ?>";
}
function sharePerso() {
	document.getElementById("perso-share-mask").style.display = "block";
	var form = document.forms["perso-share-form"];
	form.elements["id"].value = persoId;
	var persoVal = document.getElementById("myperso-"+persoId).dataset.author;
	form.elements["pseudo"].value = (persoVal == undefined) ? author:persoVal;
	form.elements["pseudo"].focus();
	if (persoVal != undefined) {
		document.getElementById("delete-share-link").href = "unsharePerso.php?id="+persoId;
		document.getElementById("delete-share-link").style.display = "block";
	}
	else
		document.getElementById("delete-share-link").style.display = "none";
	setTimeout(function() {
		form.elements["pseudo"].selectionStart = form.elements["pseudo"].selectionEnd = form.elements["pseudo"].value.length;
	}, 1);
}
function sharePersoWithId(id) {
	selectPerso(id);
	sharePerso();
}
function toggleHelp() {
	document.getElementById("perso-instructions").style.display = (document.getElementById("perso-instructions").style.display =="block") ? "none":"block";
}
</script>
<title><?php echo $language ? 'Character editor':'Éditeur de persos'; ?></title>
</head>
<body>
<?php
if (isset($error))
	echo '<p id="error">'. $error .'</p>';
if (isset($_GET['new'])) {
	if ($isShared = mysql_fetch_array(mysql_query('SELECT author FROM mkchars WHERE id="'. $_GET['new'] .'"'))) {
		echo '<p id="success">'
			. ($language ? 'Your character has been saved.':'Votre perso a été enregistré.')
			. ((null==$isShared['author']) ? ' <a href="javascript:sharePersoWithId('. $_GET['new'] .')">'. ($language ? 'Share':'Partager') .'</a>':'')
			. '</p>';
	}
}
elseif (isset($_GET['shared'])) {
	if ($getPerso = mysql_fetch_array(mysql_query('SELECT name FROM `mkchars` WHERE id="'. $_GET['shared'] .'"'))) {
		?>
	<p id="success"><?php echo $language ? "Your character <strong>". $getPerso['name'] ."</strong> has been shared successfully":"Votre perso <strong>". $getPerso['name'] ."</strong> a été partagé avec succès"; ?></p>
		<?php
	}
}
elseif (isset($_GET['unshared'])) {
	if ($getPerso = mysql_fetch_array(mysql_query('SELECT name FROM `mkchars` WHERE id="'. $_GET['unshared'] .'"'))) {
		?>
	<p id="success"><?php echo $language ? "Your character <strong>". $getPerso['name'] ."</strong> has been unshared successfully":"Le partage de <strong>". $getPerso['name'] ."</strong> a été annulé"; ?></p>
		<?php
	}
}
?>
<p class="description">
	<?php
	if ($language) {
		?>
		Welcome to the character editor!<br />
		With this mode, you can create your own characters and play with them in every mode of the game!<br />
		<!--You can also share your creations in order for other members to benefit from it!-->
		<?php
	}
	else {
		?>
		Bienvenue dans l'editeur de persos !<br />
		Grâce à ce mode, vous pourrez créer vos propres personnages et jouer avec dans tous les modes du jeu !<br />
		<!--Vous pouvez également partager vos créations afin que les autres membres puissent en profiter !-->
		<?php
	}
	?>
</p>
<?php
$getPsersos = mysql_query('SELECT * FROM `mkchars` WHERE identifiant='.$identifiants[0].' AND identifiant2='.$identifiants[1].' AND identifiant3='.$identifiants[2].' AND identifiant4='.$identifiants[3].' ORDER BY id DESC');
$arePersos = mysql_numrows($getPsersos);
if ($arePersos) {
	?>
	<h1><?php echo $language ? 'Your characters':'Vos persos'; ?></h1>
	<div class="mypersos">
	<div class="mypersos-list">
	<?php
	while ($perso = mysql_fetch_array($getPsersos)) {
		$spriteSrcs = get_sprite_srcs($perso['sprites']);
		?>
		<img src="<?php echo $spriteSrcs['ld']; ?>" alt="<?php echo htmlspecialchars($perso['name']); ?>" id="myperso-<?php echo $perso['id']; ?>" onclick="selectPerso(<?php echo $perso['id']; ?>)" data-name="<?php echo htmlspecialchars($perso['name']); ?>"<?php echo (null===$perso['author'] ? '':' data-author="'. htmlspecialchars($perso['author']) .'"'); ?> />
		<?php
	}
	?>
	</div>
	<?php
	$poids = file_total_size();
	echo '<div class="mypersos-size">'. ($language ? 'You use '.filesize_str($poids).' out of '.filesize_str(MAX_FILE_SIZE).' ('. filesize_percent($poids) .')' : 'Vous utilisez '.filesize_str($poids).' sur '.filesize_str(MAX_FILE_SIZE).' ('.filesize_percent($poids).')') .'</div>';
	?>
	</div>
	<div id="perso-options">
		<div id="perso-options-name"></div>
		<button class="edit-perso" onclick="javascript:editPerso()"><?php echo $language ? "Edit":"Modifier"; ?></button>
		<button class="suppr-perso" onclick="javascript:delPerso()"><?php echo $language ? "Delete":"Supprimer"; ?></button>
		<button class="share-perso" onclick="javascript:sharePerso()"><?php echo $language ? "Share":"Partage"; ?>...</button>
	</div>
	</div>
	<?php
}
?>
<div id="perso-share-mask" class="perso-mask" onclick="document.getElementById('perso-share-mask').style.display='none'">
	<div id="perso-share-popup" onclick="event.stopPropagation()">
		<a class="close-perso-popup" href="javascript:document.getElementById('perso-share-mask').style.display='none';void(0)">&times;</a>
		<h2><?php echo $language ? 'Share character':'Partager le perso'; ?></h2>
		<form method="post" name="perso-share-form" action="sharePerso.php">
			<input type="hidden" name="id" />
			<?php echo ($language ? 'Your nick:':'Votre pseudo :') ?>
			<input type="text" name="pseudo" maxlength="30" />
			<input type="submit" value="Ok" />
		</form>
		<a id="delete-share-link" href="unsharePerso.php"><?php echo $language ? 'Delete share':'Supprimer partage'; ?></a>
	</div>
</div>
<h1><?php
echo $language ? 'New character':'Nouveau perso';
if ($arePersos) {
	?>
	<a class="sprite-help" href="javascript:toggleHelp()">[<?php echo $language ? "Show help":"Afficher l'aide"; ?>]</a>
	<?php
}
?></h1>
<div id="perso-instructions"<?php if ($arePersos) echo ' class="instructions-unshown"'; ?>>
	<?php
	if ($language) {
		?>
		<p class="description">
			Characters from MKPC use the principle of
			<a href="https://en.wikipedia.org/wiki/Sprite_(computer_graphics)" target="_blank">sprites</a>, which means that a
			character is modeled by a set of images (names sprites), each image corresponding to an angle of view.<br />
		</p>
		<p class="description">
			For example, Mario is modeled by the following image:<br />
			<img src="images/sprites/sprite_mario.png" alt="Mario Sprite" /><br />
			And Yoshi by this one:<br />
			<img src="images/sprites/sprite_yoshi.png" alt="Yoshi Sprite" /><br />
		</p>
		<p class="description">
			Thus, to create a new perso, &quot;Simply&quot; create an image (via a drawing software) with the different sprites aligned,
			as in the examples above.<br />
			Make sure you keep the right dimensions:
			<strong>32&times;32px</strong> for each sprite, or <strong>768&times;32px</strong> in total.
		</p>
		<p class="description">
			When your image is ready, send it in the form below.
		</p>
		<?php
	}
	else {
		?>
		<p class="description">
			Les persos MKPC utilisent le principe des
			<a href="https://fr.wikipedia.org/wiki/Sprite_(jeu_vid%C3%A9o)" target="_blank">sprites</a>, c'est-à-dire qu'un
			personnage est modélisé par un ensemble d'images (appelées sprites), chaque image correspondant à un angle de vue.<br />
		</p>
		<p class="description">
			Par exemple, Mario est modélisé par l'image suivante :<br />
			<img src="images/sprites/sprite_mario.png" alt="Sprite Mario" /><br />
			Et Yoshi par cette image :<br />
			<img src="images/sprites/sprite_yoshi.png" alt="Sprite Yoshi" /><br />
		</p>
		<p class="description">
			Ainsi, pour créer un nouveau perso, il vous &quot;suffit&quot; de créer une image (via un logiciel de dessin) avec les différents sprites alignés,
			comme sur les exemples ci-dessus.<br />
			Attention à garder les bonnes dimensions :
			<strong>32&times;32px</strong> pour chaque sprite, soit une image de <strong>768&times;32px</strong> au total.
		</p>
		<p class="description">
			Lorsque votre image est prête, envoyez-la simplement dans le formulaire ci-dessous.
		</p>
		<?php
	}
	mysql_close();
	?>
</div>
<form method="post" action="persoEditor.php" enctype="multipart/form-data">
	<p>
		<input type="file" required="required" name="sprites" />
		<input type="submit" value="<?php echo $language ? 'Send !':'Valider !'; ?>" />
	</p>
</form>
<p><a href="mariokart.php"><?php echo $language ? "Back to Mario Kart PC":"Retour à Mario Kart PC"; ?></a></p>
</body>
</html>