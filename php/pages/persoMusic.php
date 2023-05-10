<?php
if (isset($_GET['id'])) {
	include('../includes/initdb.php');
	$persoId = intval($_GET['id']);
	if ($perso = mysql_fetch_array(mysql_query('SELECT * FROM `mkchars` WHERE id="'. $persoId .'"'))) {
		include('../includes/language.php');
		include('../includes/getId.php');
        require_once('../includes/collabUtils.php');
        $collabSuffix = '';
        if (($perso['identifiant'] == $identifiants[0]) && ($perso['identifiant2'] == $identifiants[1]) && ($perso['identifiant3'] == $identifiants[2]) && ($perso['identifiant4'] == $identifiants[3])) {
            $hasReadGrants = true;
            $hasWriteGrants = true;
        }
        else {
            $collab = getCollabLinkFromQuery('mkchars', $persoId);
            $hasReadGrants = isset($collab['rights']['view']);
            $hasWriteGrants = isset($collab['rights']['edit']);
            if ($collab) $collabSuffix = '&collab='. $collab['key'];
        }
        if ($hasReadGrants) {
			if (isset($_POST['type']) && $hasWriteGrants) {
				switch ($_POST['type']) {
				case 'original':
					$perso = $_POST['perso'];
					break;
				case 'youtube':
					$perso = $_POST['youtube'];
					break;
				default:
					$perso = null;
				}
				if ($perso) {
					mysql_query('UPDATE `mkchars` SET music="'. $perso .'" WHERE id="'. $persoId .'"');
					header('location: persoOptions.php?id='. $persoId . $collabSuffix);
				}
			}
			require_once('../includes/persos.php');
			$persoMusic = get_perso_music($perso);
			ob_start();
			include('../includes/getPersos.php');
			$persos = json_decode(ob_get_clean());
			ob_start();
			include('../includes/getLocks.php');
			$unlocked = json_decode(ob_get_clean());
			$isOriginal = isset($persos->$persoMusic);
			?>
<!DOCTYPE html>
<html lang="<?php echo $language ? 'en':'fr'; ?>">
<head>
<meta charset="utf-8" />
<meta name="viewport" content="width=device-width, initial-scale=1">
<link rel="shortcut icon" type="image/x-icon" href="images/favicon.ico" />
<link rel="stylesheet" href="styles/perso-editor.css?reload=1" />
<style type="text/css">
h2 {
	margin-top: 0px;
	margin-bottom: 10px;
}
h2 input {
	-ms-transform: scale(1.6);
	-moz-transform: scale(1.6);
	-webkit-transform: scale(1.6);
	-o-transform: scale(1.6);
	transform: scale(1.6);
	position: relative;
	bottom: 0.12em;
	margin-right: 0.1em;
}
label {
	display: inline-block;
}
input[type="submit"] {
	padding: 2px 25px;
}
embed,#ytplayer {
	position: absolute;
	left: -10000px;
	top: -10000px;
	width: 0;
	height: 0;
}
</style>
<script type="text/javascript">
function selectPerso(elt) {
	document.forms["perso-selector"].elements["type"].value = "original";
	originalSelect();
	var oldPerso = document.forms["perso-selector"].elements["perso"].value;
	var newPerso = elt.dataset.perso;
	if (oldPerso == newPerso)
		return;
	document.forms["perso-selector"].elements["perso"].value = newPerso;
	selectedDiv = document.getElementById("perso-selected");
	if (selectedDiv)
		selectedDiv.id = "";
	elt.id = "perso-selected";
	jouerOriginal();
}
function removeMusic() {
	if (oMusic) {
		document.body.removeChild(oMusic);
		oMusic = null;
	}
}
function youtubeSelect() {
	document.getElementById("original-perso-selector").className = "";
	document.getElementById("youtube-perso-selector").className = "perso-selector-enabled";
	removeMusic();
}
function originalSelect() {
	document.getElementById("original-perso-selector").className = "perso-selector-enabled";
	document.getElementById("youtube-perso-selector").className = "";
	removeMusic();
}
function youtube_parser(url) {
	var regExp = /.*(?:youtu.be\/|v\/|u\/\w\/|embed\/|watch\?v=)([^#\&\?]*).*/;
	var match = url.match(regExp);
	return (match&&match[1].length==11)? match[1] : false;
}
var oMusic;
function jouerOriginal() {
	removeMusic();
	var persoUid = document.forms["perso-selector"].elements["perso"].value;
	if (persoUid) {
		oMusic = document.createElement("embed");
		oMusic.src = "musics/endings/ending_"+ persoUid +".mp3";
		oMusic.setAttribute("loop", true);
		document.body.appendChild(oMusic);
	}
}
function jouerToiTuyaux() {
	removeMusic();
	var ytId = youtube_parser(document.forms["perso-selector"].elements["youtube"].value);
	if (ytId) {
		oMusic = document.createElement("iframe");
		oMusic.id = "ytplayer";
		oMusic.src = "https://www.youtube.com/embed/"+ ytId +"?allow=autoplay&autoplay=1";
		oMusic.setAttribute("allow", "autoplay");
		document.body.appendChild(oMusic);
	}
}
</script>
<?php
include('../includes/o_online.php');
?>
<title><?php echo $language ? 'Character editor':'Éditeur de persos'; ?></title>
</head>
<body<?php if (!$hasWriteGrants) echo ' class="readonly"'; ?>>
	<h1><?php echo $language ? "Edit end race music":"Modifier la musique de fin de course"; ?></h1>
	<form method="post" name="perso-selector">
	<h2><label><input type="radio" name="type" value="original" onclick="originalSelect()"<?php if ($isOriginal) echo ' checked="checked"'; if (!$hasWriteGrants) echo ' disabled="disabled"'; ?>  /> <?php echo $language ? "From existing character:":"À partir d'un perso existant :"; ?></label></h2>
	<input type="hidden" name="perso" value="<?php echo $persoMusic; ?>" />
	<div id="original-perso-selector"<?php if ($isOriginal) echo ' class="perso-selector-enabled"'; ?>>
		<?php
		$i = 0;
		foreach ($persos as $perso => $ignored) {
			if ($unlocked[$i]) {
				?><div data-perso="<?php echo $perso; ?>"<?php if ($hasWriteGrants) echo ' onclick="selectPerso(this)"'; if ($persoMusic==$perso) echo ' id="perso-selected"' ?>>
					<div><img src="images/sprites/sprite_<?php echo $perso; ?>.png" alt="<?php echo $perso; ?>" onload="this.style.left=-Math.round(this.naturalWidth*11/24+((this.naturalWidth/24)-32)/2)+'px';this.style.top=-Math.round((this.naturalHeight-32)/2)+'px'" /></div>
				</div><?php
			}
			$i++;
		}
		?>
	</div>
	<h2><label><input type="radio" name="type" value="youtube" onclick="youtubeSelect()"<?php if (!$isOriginal) echo ' checked="checked"'; if (!$hasWriteGrants) echo ' disabled="disabled"'; ?> /> <?php echo $language ? "From Youtube:":"À partir de Youtube :"; ?></label></h2>
	<div id="youtube-perso-selector"<?php if (!$isOriginal) echo ' class="perso-selector-enabled"'; ?>>
		<input type="text" name="youtube" value="<?php if (!$isOriginal) echo htmlspecialchars($persoMusic); ?>"<?php if (!$hasWriteGrants) echo ' disabled="disabled"'; ?> placeholder="https://www.youtube.com/watch?v=g5VNjnmdY5I" onfocus="this.form.elements['type'].value='youtube';youtubeSelect();var that=this;setTimeout(function(){that.select()},1);" onchange="jouerToiTuyaux()" />
	</div>
	<p>
	<input type="submit" value="<?php echo $language ? 'Select the music':'Sélectionner la musique'; ?>"<?php if (!$hasWriteGrants) echo ' disabled="disabled"'; ?> />
	</p>
	</form>
	<p>
		<a href="persoOptions.php?id=<?php echo urlencode($_GET['id']) . htmlspecialchars($collabSuffix); ?>"><?php echo $language ? 'Back to advanced options':'Retour aux options avancées'; ?></a><br />
		<a href="editPerso.php?id=<?php echo urlencode($_GET['id']) . htmlspecialchars($collabSuffix); ?>"><?php echo $language ? "Back to character editor":"Retour à l'édition du perso"; ?></a>
	</p>
</body>
</html>
		<?php
		}
	}
	mysql_close();
}
?>