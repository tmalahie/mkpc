<?php
if (isset($_GET['id'])) {
	include('initdb.php');
	$layerId = intval($_GET['id']);
	if ($layer = mysql_fetch_array(mysql_query('SELECT l.bg,l.filename,l.url,b.identifiant FROM `mkbglayers` l INNER JOIN `mkbgs` b ON l.bg=b.id WHERE l.id="'. $layerId .'"'))) {
		include('language.php');
		include('getId.php');
		require_once('collabUtils.php');
		if (($layer['identifiant'] == $identifiants[0]) || hasCollabGrants('mkbgs', $layer['bg'], $_GET['collab'], 'edit')) {
			include('utils-bgs.php');
			include('file-quotas.php');
			$collabSuffix = isset($_GET['collab']) ? '&collab='.urlencode($_GET['collab']) : '';
			if (isset($_FILES['layer'])) {
				$url = isset($_POST['url']) ? $_POST['url'] : '';
				if ($url === '')
					$layerFile = $_FILES['layer'];
				else
					$layerFile = url_to_file_payload($url);
				$upload = handle_bg_upload(array($layerFile), array(
					'layer' => $_GET['id'],
					'identifiant' => $layer['identifiant']
				));
				if (isset($upload['error']))
					$error = $upload['error'];
				elseif (isset($upload['id'])) {
					header('location: editBg.php?id='. $upload['id'] . $collabSuffix);
					mysql_close();
					exit;
				}
			}
			elseif (isset($_POST['color']) && ($layer['filename'] !== '')) {
				$color = explode(',', $_POST['color']);
				$oldPath = get_layer_path($layer['filename']);

				$fileName = generate_layer_name($layerId, 'png');
				$newPath = get_layer_path($fileName);

				add_transparency('../../'.$oldPath,'../../'.$newPath, $color[0],$color[1],$color[2]);
				mysql_query('UPDATE `mkbglayers` SET filename="'.$fileName.'" WHERE id="'. $layerId .'"');
				@unlink('../../'.$oldPath);

				header('location: editBg.php?id='. $layer['bg'] . $collabSuffix);
				mysql_close();
				exit;
			}
			if ($layer['filename'] !== '') {
				$spriteSrc = get_layer_path($layer['filename']);
				list($spriteW, $spriteH) = getimagesize('../../'.$spriteSrc);
			}
			?>
<!DOCTYPE html>
<html lang="<?php echo $language ? 'en':'fr'; ?>">
<head>
<meta charset="utf-8" />
<meta name="viewport" content="width=device-width, initial-scale=1">
<link rel="shortcut icon" type="image/x-icon" href="images/favicon.ico" />
<link rel="stylesheet" href="styles/editor.css" />
<link rel="stylesheet" href="styles/bg-editor.css" />
<script type="text/javascript" src="scripts/bg-editor.js"></script>
<?php
include('o_online.php');
?>
<?php
if (isset($spriteSrc)) {
	?>
<script type="text/javascript">
<?php
echo "var spriteSrc = \"$spriteSrc\", spriteW = $spriteW, spriteH = $spriteH;";
?>
</script>
	<?php
}
?>
<script type="text/javascript" src="scripts/edit-sprite.js"></script>
<?php
$hasTransparency = !isset($spriteSrc) || has_transparency('../../'.$spriteSrc);
?>
<title><?php echo $language ? 'Background editor':'Éditeur d\'arrière-plans'; ?></title>
</head>
<body>
	<?php
	if (isset($error)) {
		?>
		<p id="error"><?php echo $error; ?></p>
		<?php
	}
	else {
		?>
		<h2><?php
		echo $language ? "Edit layer image":"Modifier un calque";
		?></h2>
		<?php
		if (!$hasTransparency)
			echo '<h2><?php echo $language ? "New image:":"Nouvelle image :"; ?></h2>';
	}
	?>
	<div class="bgs-list-container">
		<form method="post" class="bg-editor-form" action="" enctype="multipart/form-data">
			<div class="editor-upload">
				<div class="editor-upload-tabs">
					<div class="editor-upload-tab<?php if ($layer['filename'] !== '') echo ' editor-upload-tab-selected'; ?>">
						<?php echo $language ? 'Upload an image':'Uploader une image'; ?>
					</div><div class="editor-upload-tab<?php if ($layer['url'] !== '') echo ' editor-upload-tab-selected'; ?>">
						<?php echo $language ? 'Paste image URL':'Coller l\'URL de l\'image'; ?>
					</div>
				</div>
				<div class="editor-upload-inputs">
					<div class="editor-upload-input<?php if ($layer['filename'] !== '') echo ' editor-upload-input-selected'; ?>">
						<input type="file" accept="image/png,image/gif,image/jpeg" name="layer"<?php if ($layer['filename'] !== '') echo ' required="required"'; ?> />
					</div>
					<div class="editor-upload-input<?php if ($layer['url'] !== '') echo ' editor-upload-input-selected'; ?>">
						<input type="url" name="url" placeholder="https://tcrf.net/images/c/c0/SMK_UnusedChocoBGPalette.png"<?php if ($layer['url'] !== '') echo ' value="'. $layer['url'] .'" required="required"'; ?> />
					</div>
				</div>
			</div>
			<button type="submit"><?php echo $language ? 'Send':'Valider'; ?></button>
		</form>
	</div>
	<?php
	if (!$hasTransparency) {
		?>
		<hr />
		<div class="bgs-list-container" id="transparency-form-ctn">
			<h3><?php echo $language ? 'Transparent color:':'Couleur de transparence :'; ?></h3>
			<canvas id="select-transparency" width="<?php echo $spriteW; ?>" height="<?php echo $spriteH; ?>"></canvas>
			<form method="post" id="transparency-form" class="bg-editor-form" action="">
				<input type="hidden" name="color" />
				<div id="transparency-color-preview"></div>
				<button type="submit"><?php echo $language ? 'Send':'Valider'; ?></button>
			</form>
		</div><br />
		<?php
	}
	?>
	<div class="editor-navigation">
		<a href="editBg.php?id=<?php echo $layer['bg'] . htmlspecialchars($collabSuffix); ?>">&lt; <u><?php echo $language ? "Back to background editor":"Retour à l'édition de l'arrière-plan"; ?></u></a>
	</div>
	<script type="text/javascript">
		setupUploadTabs(document.querySelector(".editor-upload"));
	</script>
</body>
</html>
		<?php
		}
	}
	mysql_close();
}
?>