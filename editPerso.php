<?php
if (isset($_GET['id'])) {
	$persoId = $_GET['id'];
	include('initdb.php');
	if ($perso = mysql_fetch_array(mysql_query('SELECT * FROM `mkchars` WHERE id="'. $persoId .'"'))) {
		include('getId.php');
        require_once('collabUtils.php');
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
			include('language.php');
			include('perso-stats.php');
			require_once('persos.php');
			if (isset($_POST['name']) && isset($_POST['speed']) && isset($_POST['acceleration']) && isset($_POST['handling']) && isset($_POST['mass']) && $hasWriteGrants) {
				$_POST['name'] = preg_replace('#<[^>]+>#', '', $_POST['name']);
				if (!$_POST['name'])
					$error = $language ? 'Please enter a name':'Veuillez entrer un nom';
				elseif ($_POST['acceleration'] < 0 || $_POST['acceleration'] > $statsGradient)
					$error = $language ? 'Please enter a valid acceleration value':'Veuillez entrer une valeur d\'accélération valide';
				elseif ($_POST['speed'] < 0 || $_POST['speed'] > $statsGradient)
					$error = $language ? 'Please enter a valid speed value':'Veuillez entrer une valeur de vitesse valide';
				elseif ($_POST['handling'] < 0 || $_POST['handling'] > $statsGradient)
					$error = $language ? 'Please enter a valid handling value':'Veuillez entrer une valeur de maniabilité valide';
				elseif ($_POST['mass'] < 0 || $_POST['mass'] > $statsGradient)
					$error = $language ? 'Please enter a valid weight value':'Veuillez entrer une valeur de poids valide';
				elseif (cheated())
					$error = $language ? 'Your character is overskilled, please lower its stats':'Votre perso est cheaté, veuillez diminuer les stats';
				else {
					$statPost = array();
					foreach ($statsRange as $stat => $range)
						$statPost[$stat] = $range['min'] + $_POST[$stat]*($range['max']-$range['min'])/$statsGradient;
					mysql_query('UPDATE `mkchars` SET name="'. $_POST['name'] .'",
						acceleration="'. $statPost['acceleration'] .'",
						speed="'. $statPost['speed'] .'",
						handling="'. $statPost['handling'] .'",
						mass="'. $statPost['mass'] .'"
					WHERE id="'. $_GET['id'] .'"');
					if ($collabSuffix)
						header('location: editPerso.php?id='.$_GET['id'] . $collabSuffix);
					else
						header('location: persoEditor.php?new='.$_GET['id']);
				}
			}
			$statShow = array();
			$oneStat = false;
			foreach ($statsRange as $stat => $range) {
				if ($perso[$stat])
					$oneStat = true;
				$statShow[$stat] = round(($perso[$stat]-$range['min'])*$statsGradient/($range['max']-$range['min']));
			}
			if (!$oneStat) {
				foreach ($statsRange as $stat => $range)
					$statShow[$stat] = $statsDefault;
			}
			?>
<!DOCTYPE html>
<html lang="<?php echo $language ? 'en':'fr'; ?>">
<head>
<meta charset="utf-8" />
<meta name="viewport" content="width=device-width, initial-scale=1">
<link rel="shortcut icon" type="image/x-icon" href="images/favicon.ico" />
<link rel="stylesheet" href="styles/perso-editor.css?reload=1" />
<?php
include('o_online.php');
?>
<script type="text/javascript">
var unsavedData = <?php echo $perso['name'] ? 'false':'true'; ?>;
var language = <?php echo ($language ? 'true':'false'); ?>;
var statTypes = ["<?php echo implode('","',array_keys($statsRange)); ?>"];
var statsGradient = <?php echo $statsGradient; ?>;
var statsRange = <?php echo json_encode($statsRange); ?>;
var cp = <?php echo json_encode($defaultPersosStats); ?>;
var pUnlocked = <?php include('getLocks.php'); ?>;
var readOnly = <?php echo $hasWriteGrants ? 0 : 1; ?>;
</script>
<script type="text/javascript" src="scripts/perso-stats.js?reload=1"></script>
<title><?php echo $language ? 'Character editor':'Éditeur de persos'; ?></title>
</head>
<body onbeforeunload="if(unsavedData)return language?'Your character is not finished and therefore will not be playable yet':'Votre perso n\'est pas encore terminé et ne sera donc pas jouable'">
	<?php
	if (isset($_GET['new'])) {
		?>
		<p id="success"><?php echo $language ? "Your character has been created":"Votre perso a été créé"; ?></p>
		<?php
	}
	elseif (isset($success)) {
		?>
		<p id="success"><?php echo $success; ?></p>
		<?php
	}
	elseif (isset($error)) {
		?>
		<p id="error"><?php echo $error; ?></p>
		<?php
	}
	elseif ($perso['name']) {
		?>
		<h1><?php echo $language ? "Edit a character":"Modifier un perso"; ?></h1>
		<?php
	}
	else {
		?>
		<h1><?php echo $language ? "New character":"Nouveau perso"; ?></h1>
		<?php
	}
	?>
	<div>
		<div class="perso-preview perso-animate"><img src="<?php echo PERSOS_DIR.$perso['sprites']; ?>.png" onload="this.parentNode.style.width=Math.round(this.naturalWidth/24)+'px';this.parentNode.style.height=this.naturalHeight+'px';this.style.width=this.naturalWidth+'px'" alt="perso" /></div>
		<?php
		if ($hasWriteGrants)
			echo '<a class="perso-editsprites" href="editSprite.php?id='. $_GET['id'] . htmlspecialchars($collabSuffix) .'" onclick="unsavedData=false">'. ($language ? "Edit image":"Modifier l'image") .'</a>';
		?>
	</div>
	<?php
	if (!$perso['name'])
		echo $language
			? '<p class="newpersoinfo">Final stage... You have now to indicate the properties of the character</p>'
			: '<p class="newpersoinfo">Encore un effort... Il vous faut maintenant indiquer les propriétés du perso</p>';
	else
		echo '<br />';
	?>
	<form method="post" name="perso-form" class="perso-form" action="editPerso.php?id=<?php echo $_GET['id'] . htmlspecialchars($collabSuffix); ?>" onsubmit="unsavedData=false">
		<label for="name"><?php echo $language ? 'Character name:':'Nom du perso :'; ?></label><input type="text" maxlength="30" required="required" name="name" id="name" placeholder="<?php echo $language ? 'Baby Mario':'Bébé Mario'; ?>" value="<?php echo htmlspecialchars($perso['name']); ?>"<?php if (!$hasWriteGrants) echo ' disabled="disabled"'; ?> />
		<br /><br />
		<fieldset class="perso-stats">
			<legend>&nbsp;<?php echo $language ? 'Character stats':'Stats du perso'; ?>&nbsp;</legend>
			<div id="statstemplate">
				<?php echo $language ? 'Retrieve stats from another character:':'Reprendre les stats de:'; ?> <select id="stats-template"<?php if (!$hasWriteGrants) echo ' disabled="disabled"'; ?>>
					<option><?php echo $language ? 'Character':'Perso'; ?>...</option>
				</select>
			</div>
			<table>
				<tr>
					<td><label for="acceleration"><?php echo $language ? 'Acceleration:':'Accélération :'; ?></label></td>
					<td><input type="range" name="acceleration" id="acceleration" min="0" max="<?php echo $statsGradient; ?>" step="1" value="<?php echo $statShow['acceleration']; ?>"<?php if (!$hasWriteGrants) echo ' disabled="disabled"'; ?> /></td>
				</tr>
				<tr>
					<td><label for="speed"><?php echo $language ? 'Max speed:':'Vitesse max :'; ?></label></td>
					<td><input type="range" name="speed" id="speed" min="0" max="<?php echo $statsGradient; ?>" step="1" value="<?php echo $statShow['speed']; ?>"<?php if (!$hasWriteGrants) echo ' disabled="disabled"'; ?> /></td>
				</tr>
				<tr>
					<td><label for="handling"><?php echo $language ? 'Handling:':'Maniabilité :'; ?></label></td>
					<td><input type="range" name="handling" id="handling" min="0" max="<?php echo $statsGradient; ?>" step="1" value="<?php echo $statShow['handling']; ?>"<?php if (!$hasWriteGrants) echo ' disabled="disabled"'; ?> /></td>
				</tr>
				<tr>
					<td><label for="mass"><?php echo $language ? 'Weight:':'Poids :'; ?></label></td>
					<td><input type="range" name="mass" id="mass" min="0" max="<?php echo $statsGradient; ?>" step="1" value="<?php echo $statShow['mass']; ?>"<?php if (!$hasWriteGrants) echo ' disabled="disabled"'; ?> /></td>
				</tr>
			</table>
			<div id="statsinfo">
				<?php
				echo $language
					? "To avoid overskilled characters, stats may not be better than an existing MKPC character."
					: "Pour éviter les persos &quot;cheatés&quot;, les stats ne doivent pas être supérieures à un perso existant dans MKPC.";
				?>
			</div>
		</fieldset>
		<br />
		<table class="advances-options">
			<tr>
				<td>
					<div class="advanced-options">
						<a href="persoOptions.php?id=<?php echo $_GET['id'] . htmlspecialchars($collabSuffix); ?>" onclick="unsavedData=false">
							<img src="images/advanced-options.png" alt="Avanced" /> <?php echo $language ? 'Advanced options':'Options avancées'; ?>
						</a>
					</div>
				</td>
				<td>
					<input type="submit" id="perso-submit" value="<?php echo $language ? 'Submit!':'Valider !'; ?>" />
				</td>
			</tr>
		</table>
		<p><a href="persoEditor.php"><?php echo $language ? "Back to character editor":"Retour à l'éditeur de persos"; ?></a></p>
	</form>
</body>
</html>
		<?php
		}
	}
	mysql_close();
}
?>