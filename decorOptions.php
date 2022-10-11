<?php
if (isset($_GET['id'])) {
	include('initdb.php');
	$decorId = $_GET['id'];
	if ($decor = mysql_fetch_array(mysql_query('SELECT * FROM `mkdecors` WHERE id="'. $decorId .'"'))) {
		include('language.php');
		include('getId.php');
		require_once('collabUtils.php');
		$collabSuffix = '';
		if ($decor['identifiant'] == $identifiants[0]) {
			$hasReadGrants = true;
			$hasWriteGrants = true;
		}
		else {
			$collab = getCollabLinkFromQuery('mkdecors', $decor['extra_parent_id'] ?? $decorId);
			$hasReadGrants = isset($collab['rights']['view']);
			$hasWriteGrants = isset($collab['rights']['edit']);
			if ($collab) $collabSuffix = '&amp;collab='. $collab['key'];
		}
		if ($hasReadGrants) {
			include('utils-decors.php');
			$spriteSrcs = decor_sprite_srcs($decor['sprites']);
			$spriteSizes = decor_sprite_sizes($decor['type'],$spriteSrcs['hd']);
			$originalSizes = decor_sprite_sizes($decor['type'],default_decor_sprite_src($decor['type']));
			$sizeRatio = $spriteSizes['hd']['w']/$originalSizes['hd']['w'];
			?>
<!DOCTYPE html>
<html lang="<?php echo $language ? 'en':'fr'; ?>">
<head>
<meta charset="utf-8" />
<meta name="viewport" content="width=device-width, initial-scale=1">
<link rel="shortcut icon" type="image/x-icon" href="images/favicon.ico" />
<link rel="stylesheet" href="styles/editor.css" />
<link rel="stylesheet" href="styles/decor-editor.css" />
<?php
include('o_online.php');
?>
<script type="text/javascript" src="scripts/edit-sprite.js"></script>
<title><?php echo $language ? 'Decor editor':'Éditeur de decors'; ?></title>
</head>
<body<?php if (!$hasWriteGrants) echo ' class="readonly"'; ?>>
	<?php
	if (isset($error)) {
		?>
		<p id="error"><?php echo $error; ?></p>
		<?php
	}
	else {
		?>
		<h2><?php echo $language ? "Advanced options":"Options avancées"; ?></h2>
		<?php
	}
	?>
	<div class="decors-list-container" id="advanced-option-ctn">
		<a class="advanced-option option-map" href="<?php echo $hasWriteGrants ? 'decorSprite.php?id='.$_GET['id'].$collabSuffix : 'javascript:void(0)'; ?>&amp;map">
			<div class="option-bg">
				<img src="images/maps/map3.png" alt="Map" />
				<img src="<?php echo $spriteSrcs['map']; ?>" alt="Decor" style="width:<?php echo round(12*$sizeRatio); ?>px" />
			</div>
			<div class="option-label"><?php
				if ($hasWriteGrants)
					echo $language ? "Edit minimap icon":"Modifier l'icone de la mini-map";
				else
					echo $language ? "Minimap icon":"Icone de la mini-map";
			?></div>
		</a>
	</div>
	<br />
	<div class="editor-navigation">
		<a href="editDecor.php?id=<?php echo $_GET['id'] . $collabSuffix; ?>">&lt; <u><?php echo $language ? "Back to decor editor":"Retour à l'édition du décor"; ?></u></a>
	</div>
</body>
</html>
		<?php
		}
	}
	mysql_close();
}
?>