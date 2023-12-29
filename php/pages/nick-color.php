<?php
include('../includes/getId.php');
include('../includes/language.php');
include('../includes/session.php');
include('../includes/initdb.php');
if ($getProfile = mysql_fetch_array(mysql_query('SELECT j.nom,p.nick_color FROM `mkjoueurs` j INNER JOIN `mkprofiles` p ON j.id=p.id WHERE j.id="'. $id .'"'))) {
	if (isset($_POST['message'])) {
		$nickColor = $_POST['message'];
		$nickUntagged = preg_replace('#\[color=(\#[a-f0-9]{3}(?:[a-f0-9]{3})?|[a-z]+)\](.*)\[/color\]#isU', '$2', $nickColor);
		if ($nickUntagged == $getProfile['nom']) {
			mysql_query('UPDATE mkprofiles SET nick_color="'. $nickColor .'" WHERE id="'. $id .'"');
			$getProfile['nick_color'] = $nickColor;
			$success = $language ? 'Color updated successfully':'Couleur mise à jour avec succès';
		}
	}
	?>
<!DOCTYPE html>
<html lang="<?php echo $language ? 'en':'fr'; ?>">
<head>
<title><?php echo $language ? 'Edit username color':'Modifier couleur du pseudo'; ?> - Mario Kart PC</title>
<?php
include('../includes/heads.php');
?>
<link rel="stylesheet" type="text/css" href="styles/forum.css" />
<link rel="stylesheet" type="text/css" href="styles/profil.css" />
<link rel="stylesheet" type="text/css" href="styles/forms.css" />
<style type="text/css">
.nick-edit-ctn .mInput textarea {
	height: 130px;
}
@media screen and (min-width: 600px) {
	#nMessage {
		width: 360px;
	}
	.nick-edit-ctn {
		display: inline-block;
	}
	.nick-editor {
		display: flex;
		align-items: center;
		justify-content: center;
	}
	.nick-edit-ctn {
		margin-right: 30px;
	}
	.nick-edit-preview {
		display: inline-block;
	}
	#fMessages {
		display: block;
	}
}
.nick-editor h2 {
	font-size: 15px;
	margin: 0;
	font-weight: bold;
}
.nick-edit-preview h2 {
	background-color: #FFC028;
	color: #820;
	border: solid 1px #820;
	padding: 4px 0px;
	border-bottom: none;
}
.fMessage {
	margin-top: 0;
}
h1 {
	margin-top: 10px;
	margin-bottom: 15px;
}
@media screen and (min-width: 600px) and (max-width: 700px) {
	.nick-edit-ctn {
		margin-right: 5px;
	}
}
@media screen and (max-width: 600px) {
	#nMessage {
		width: 100%;
		max-width: 360px;
	}
	.topicPages, .fMessage {
		width: 100%;
	}
	.nick-edit-preview {
		display: block;
		max-width: 400px;
		margin-top: 20px;
		margin-left: auto;
		margin-right: auto;
	}
	#fMessages {
		display: block;
		margin-left: auto;
		margin-right: auto;
	}
}
#fMessages .mContent {
	display: none;
}
main #nMessage select {
	margin-left: 25px;
}
main #nMessage input[type="submit"] {
	margin-left: 25px;
	width: auto;
	padding-left: 15px;
	padding-right: 15px;
}
main #nMessage td {
	padding: 2px;
}
#nick-submit:disabled {
	opacity: 0.5;
	cursor: default;
}
#nick-submit:disabled:hover {
	background-color: #FF9010;
}
.nick-color-explain {
	margin: 0px 10px 20px 10px;
	text-align: justify;
}
.success {
	text-align: center;
	margin-bottom: 10px;
}
</style>
<script type="text/javascript" src="scripts/topic.js"></script>
<?php
include('../includes/o_online.php');
?>
</head>
<body>
<?php
include('../includes/header.php');
$page = 'forum';
include('../includes/menu.php');
?>
<main>
<h1><?php echo $language ? 'Edit username color':'Modifier la couleur du pseudo'; ?></h1>
<?php
if (isset($success))
	echo '<div class="success">'. $success .'</div>';
?>
<p class="nick-color-explain">
	<?php
	if ($language) {
		?>
	This page allows you to change your username color as it appears on the forum.<br />
	For that, it's simple: insert the colors of your choice in the field below
	using the beacon bbCode <span style="font-family:Monospace">[color]</span>, as you would in a message on the forum.<br />
	Example:
		<?php
	}
	else {
		?>
	Cette page vous permet de modifier la couleur du pseudo tel qu'il apparait sur le forum.<br />
	Pour cela, c'est simple : insérez les couleurs de votre choix dans le champ ci-dessous
	en utilisant la balise bbCode <span style="font-family:Monospace">[color]</span>, comme vous feriez dans un message sur le forum.<br />
	Exemple : 
		<?php
	}
	?><span style="font-family:Monospace"><?php
	$nick = $getProfile['nom'];
	$nickColor = $getProfile['nick_color'];
	$middle = round(strlen($nick)/2);
	$nick1 = substr($nick, 0,$middle);
	$nick2 = substr($nick, $middle);
	echo '[color=blue]'.$nick1.'[/color][color=red]'.$nick2.'[/color]';
	?></span>
	<?php echo $language ? 'gives':'donne'; ?> <?php
	echo '<span style="color:blue">'.$nick1.'</span><span style="color:red">'.$nick2.'</span>';
	?>
</p>
<form method="post" class="advanced-search" action="nick-color.php">
	<div class="nick-editor">
		<div class="nick-edit-ctn">
			<h2>&nbsp;</h2>
			<table id="nMessage">
				<tr><td class="mInput">
					<textarea name="message" oninput="previewNick()"><?php echo htmlspecialchars($nickColor); ?></textarea>
				</td></tr>
				<tr><td class="mLabel">
					<?php
					$colors = Array("black", "maroon", "green", "olive", "navy", "purple", "#F60", "teal", "gray", "red", "blue", "fuchsia", "dodgerblue");
					$colorNames = $language ? Array("Black",	"Maroon",				"Dark green",			"Light brown",	"Navy",			"Purple",	 "Orange",	"Blue-green",	"Gray",	"Red",	"Blue",	"Fuchsia",	"Blue sky")
							  				: Array("Noir",	"Rouge fonc&eacute;",	"Vert fonc&eacute;",	"Marron clair",	"Bleu marine",	"Violet",	 "Orange",	"Bleu-vert",	"Gris",	"Rouge",	"Bleu",	"Fuchsia",	"Bleu ciel");
					echo '<select onchange="insertColorTag(this.value);this.selectedIndex=0">';
					echo '<option selected="selected">'. ($language ? 'Color':'Couleur') .'</option>';
					foreach ($colors as $i=>$color)
						echo '<option value="'. $color .'" style="color: '.$color.'">'. $colorNames[$i] .'</option>';
					echo '<option value="custom_picker" style="color: #D31">'. ($language ? 'Other':'Autre') .'...</option>';
					echo '</select>';
					?><input type="color" id="bbColPicker" tabindex=-1 onchange="insertColorTag(this.value)" />
					<input type="submit" id="nick-submit" value="<?php echo $language ? 'Submit':'Valider'; ?>" />
				</td></tr>
			</table>
		</div>
		<div class="nick-edit-preview">
			<h2><?php echo $language ? 'Preview:':'Aperçu :'; ?></h2>
			<?php
			include('../includes/preview-msg.php');
			?>
		</div>
	</div>
</form>
<p class="forumButtons">
	<a href="profil.php?id=<?php echo $id; ?>"><?php echo $language ? 'Back to your profile':'Retour à votre profil'; ?></a><br />
	<a href="forum.php"><?php echo $language ? 'Back to the forum':'Retour au forum'; ?></a>
</p>
</main>
<script type="text/javascript" src="scripts/msg.php"></script>
<script type="text/javascript">
var actualNick = "<?php echo $nick; ?>";
function insertColorTag(c) {
	insertCustomTag("color",c);
	previewNick();
}
function previewNick() {
	var nickTag = document.getElementsByClassName("mPseudo")[0].getElementsByTagName("strong")[0];
	var content = document.forms[0].message.value;
	var colorRe = /\[color=(\#[a-f0-9]{3}(?:[a-f0-9]{3})?|[a-z]+)\]([\s\S]*?)\[\/color\]/gi;
	content = content.replace(colorRe, '<span style="color: $1">$2</span>');
	nickTag.innerHTML = content;
	var content = document.forms[0].message.value;
	content = content.replace(colorRe, '$2');
	document.getElementById("nick-submit").disabled = (content != actualNick);
}
</script>
<?php
include('../includes/footer.php');
mysql_close();
}
?>
</body>
</html>