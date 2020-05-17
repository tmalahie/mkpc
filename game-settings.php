<?php
include('language.php');
include('initdb.php');
$settings = array(
	'ld' => $language ? 'Don\'t display heavy elements (trees, decors)':'Désactiver l\'affichage des éléments lourds (arbres, décors)',
	'nogif' => $language ? 'Disable animation in gif-format tracks':'Désactiver les animations des circuits au format gif',
    'nomap' => $language ? 'Disable mini-map display':'Désactiver l\'affichage de la mini-map'
);
?>
<!DOCTYPE html>
<html lang="<?php echo $language ? 'en':'fr'; ?>">
<head>
<title><?php echo $language ? 'Game settings':'Paramètres de jeu'; ?> - Mario Kart PC</title>
<?php
include('heads.php');
?>
<link rel="stylesheet" type="text/css" href="styles/forum.css" />
<link rel="stylesheet" type="text/css" href="styles/profil.css" />
<link rel="stylesheet" type="text/css" href="styles/forms.css" />
<script type="text/javascript" src="scripts/topic.js"></script>
<?php
include('o_online.php');
?>
<style type="text/css">
#success-message {
    display: none;
    margin-bottom: 20px;
}
</style>
<script type="text/javascript">
var language = <?php echo +$language; ?>;
function initSettings() {
    var gameSettings = localStorage.getItem("settings");
    if (gameSettings) {
        gameSettings = JSON.parse(gameSettings);
        for (var setting in gameSettings) {
            if (gameSettings[setting] && document.getElementById(setting))
                document.getElementById(setting).checked = true;
        }
    }
}
function resetSettings() {
    if (confirm(language ? "Reset settings to default?":"Réinitiliser les paramètres à ceux par défaut ?")) {
        localStorage.removeItem("settings");
        var form = document.forms[0];
        for (var i=0;i<form.elements.length;i++) {
            var element = form.elements[i];
            var setting = element.name;
            if (element.checked)
                element.checked = false;
        }
        showSuccess();
    }
}
function processSettings(e) {
    e.preventDefault();
    var gameSettings = {};
    var form = document.forms[0];
    for (var i=0;i<form.elements.length;i++) {
        var element = form.elements[i];
        var setting = element.name;
        if (element.checked)
            gameSettings[setting] = 1;
    }
    localStorage.setItem("settings", JSON.stringify(gameSettings));
    showSuccess();
}
function showSuccess() {
    document.getElementById("success-message").style.display = "none";
    setTimeout(function() {
        document.getElementById("success-message").style.display = "block";
    }, 300);
}
</script>
</head>
<body onload="initSettings()">
<?php
include('header.php');
$page = 'forum';
include('menu.php');
?>
<main>
<form method="post" class="advanced-search" action="game-settings.php" onsubmit="processSettings(event)">
	<h1><?php echo $language ? 'Advanced game settings':'Paramètres de jeu avancés'; ?></h1>
	<div id="success-message" class="success"><?php echo $language ? 'Settings updated successfully':'Paramètres mis à jour avec succès'; ?></div>
	<table>
		<?php
		foreach ($settings as $type => $setting) {
			?>
			<tr>
				<td class="ligne">
					<input type="checkbox" name="<?php echo $type; ?>" id="<?php echo $type; ?>" />
				</td>
				<td>
					<label for="<?php echo $type; ?>">
					<?php echo $setting; ?>
					</label>
				</td>
			</tr>
			<?php
		}
		?>
		<tr>
			<td colspan="2">
                <a href="javascript:resetSettings()"><?php echo $language ? 'Reset settings':'Réinitialiser'; ?></a> &nbsp; 
				<input type="submit" class="action_button" value="<?php echo $language ? 'Submit':'Valider'; ?>" />
			</td>
		</tr>
	</table>
	<p class="forumButtons">
		<a href="mariokart.php"><?php echo $language ? 'Back to the game':'Retour au jeu'; ?></a><br />
		<a href="index.php"><?php echo $language ? 'Back to home':'Retour à l\'accueil'; ?></a>
	</p>
</form>
</main>
<?php
include('footer.php');
mysql_close();
?>
</body>
</html>