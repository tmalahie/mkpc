<?php
if (isset($_GET['id'])) {
	include('../includes/initdb.php');
	$decorId = intval($_GET['id']);
	if ($decor = mysql_fetch_array(mysql_query('SELECT * FROM `mkdecors` WHERE id="'. $decorId .'"'))) {
		include('../includes/language.php');
		include('../includes/getId.php');
		require_once('../includes/collabUtils.php');
		$collabSuffix = '';
		if ($decor['identifiant'] == $identifiants[0]) {
			$hasReadGrants = true;
			$hasWriteGrants = true;
		}
		else {
			$collab = getCollabLinkFromQuery('mkdecors', $decor['extra_parent_id'] ?? $decorId);
			$hasReadGrants = isset($collab['rights']['view']);
			$hasWriteGrants = isset($collab['rights']['edit']);
			if ($collab) $collabSuffix = '&collab='. $collab['key'];
		}
		if ($hasWriteGrants) {
			include('../includes/utils-decors.php');
			include('../includes/file-quotas.php');
			$spriteSrcs = get_decor_srcs($decor);
			$type = 'decor';
			if (isset($_GET['map']))
				$type = 'map';
			switch ($type) {
			case 'decor' :
				$spriteSrc = $spriteSrcs['hd'];
				$spriteDir = $spriteSrcs['hdir'];
				$disableTransparency = $spriteSrcs['isurl'];
				break;
			default :
				$spriteSrc = $spriteSrcs[$type];
				$spriteDir = $spriteSrcs['ldir'];
				$disableTransparency = false;
				break;
			}
			if (isset($_FILES['sprites'])) {
				switch ($type) {
				case 'decor' :
					$upload = handle_decor_upload($decor['type'],get_basic_sprites_payload('sprites'),get_extra_sprites_payload('extraSprites'),$decor);
					if (isset($upload['id']))
						header('location: editDecor.php?id='. $upload['id'] . $collabSuffix);
					break;
				default :
					$upload = handle_decor_advanced($_FILES['sprites'],$decor,$type);
					if (isset($upload['id']))
						header('location: decorOptions.php?id='. $upload['id'] . $collabSuffix);
					break;
				}
				if (isset($upload['error']))
					$error = $upload['error'];
				else
					exit;
			}
			elseif (isset($_POST['color'])) {
				$color = explode(',', $_POST['color']);
				if ($disableTransparency) exit;
				$filehash = generate_decor_sprite_src($decor['id']);
				move_decor_sprite_imgs($spriteSrcs,$filehash);
				$newSrcs = decor_sprite_srcs($filehash);
				switch ($type) {
				case 'decor' :
					add_transparency($newSrcs['hdir'].$newSrcs['hd'],$newSrcs['hdir'].$newSrcs['hd'], $color[0],$color[1],$color[2]);
					clone_img_resource($newSrcs['hdir'].$newSrcs['hd'],$newSrcs['hdir'].$newSrcs['hd']);
					$spriteSizes = get_decor_sizes($decor);
					create_decor_sprite_thumbs($newSrcs,$spriteSizes);
					break;
				default :
					add_transparency($newSrcs['ldir'].$newSrcs[$type],$newSrcs['ldir'].$newSrcs[$type], $color[0],$color[1],$color[2]);
					clone_img_resource($newSrcs['ldir'].$newSrcs[$type],$newSrcs['ldir'].$newSrcs[$type]);
					break;
				}
				mysql_query('UPDATE `mkdecors` SET sprites="'.$filehash.'" WHERE id="'. $decorId .'"');
				$decor['sprites'] = $filehash;
				switch ($type) {
				case 'decor' :
					header('location: editDecor.php?id='. $decor['id'] . $collabSuffix);
					break;
				default :
					header('location: decorOptions.php?id='. $decor['id'] . $collabSuffix);
					break;
				}
				exit;
			}

			$hasTransparency = $disableTransparency || ($spriteSrc == $spriteSrcs['ld']) || has_transparency($spriteDir.$spriteSrc);
			if ($hasTransparency) {
				$spriteW = 0;
				$spriteH = 0;
			}
			else
				list($spriteW, $spriteH) = getimagesize($spriteDir.$spriteSrc);
			$minW = 64;
			if (($spriteW < $minW) && $spriteW) {
				$spriteH = round($spriteH*$minW/$spriteW);
				$spriteW = $minW;
			}
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
include('../includes/o_online.php');
?>
<script type="text/javascript" src="scripts/decor-editor.js?reload=1"></script>
<script type="text/javascript">
var spriteSrc = "<?php echo $spriteSrc; ?>", spriteW = <?php echo $spriteW; ?>, spriteH = <?php echo $spriteH; ?>;
</script>
<script type="text/javascript" src="scripts/edit-sprite.js"></script>
<title><?php echo $language ? 'Decor editor':'Éditeur de decors'; ?></title>
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
		switch ($type) {
		case 'decor':
			echo $language ? "Edit decor image":"Modifier l'image du décor";
			break;
		case 'map':
			echo $language ? "Edit minimap icon":"Modifier l'icone de la mini-map";
			break;
		}
		?></h2>
		<?php
		if (!$hasTransparency)
			echo '<h2><?php echo $language ? "New image:":"Nouvelle image :"; ?></h2>';
	}
	?>
	<div class="decors-list-container">
		<form method="post" class="decor-editor-form" action="" enctype="multipart/form-data">
		<?php
		if ($type === 'decor') {
			$isUrl = isset($decor['imgdata']['url']);
			?>
			<div class="editor-upload">
				<div class="editor-upload-tabs">
					<div class="editor-upload-tab<?php if (!$isUrl) echo ' editor-upload-tab-selected'; ?>">
						<?php echo $language ? 'Upload an image':'Uploader une image'; ?>
					</div><div class="editor-upload-tab<?php if ($isUrl) echo ' editor-upload-tab-selected'; ?>">
						<?php echo $language ? 'Paste image URL':'Coller l\'URL de l\'image'; ?>
					</div>
				</div>
				<div class="editor-upload-inputs">
					<div class="editor-upload-input<?php if (!$isUrl) echo ' editor-upload-input-selected'; ?>">
						<input type="file" accept="image/png,image/gif,image/jpeg" name="sprites"<?php if (!$isUrl) echo ' required="required"'; ?> />
					</div>
					<div class="editor-upload-input<?php if ($isUrl) echo ' editor-upload-input-selected'; ?>">
                        <input type="url" name="sprites-url" placeholder="https://mario.wiki.gallery/images/b/be/Warp_Pipe_SMB.png"<?php if ($isUrl) echo ' value="'. $decor['imgdata']['url'] .'" required="required"'; ?> />
					</div>
				</div>
			</div>
			<button type="submit"><?php echo $language ? 'Send':'Valider'; ?></button>
			<?php
		}
		else {
			?>
			<div>
				<input type="file" required="required" name="sprites" />
				<button type="submit"><?php echo $language ? 'Send':'Valider'; ?></button>
			</div>
			<?php
			if ($hasTransparency || ($spriteSrc !== $spriteSrcs['ld'])) {
				?>
				<div id="decor-current-img-preview">
				<?php echo $language ? 'Current image:':'Image actuelle :'; ?>&nbsp;<img src="<?php echo $spriteSrc; ?>" alt="Image" class="current-sprite" />
				<?php
				if ($spriteSrc != $spriteSrcs['ld'])
					echo '&nbsp;<a href="delDecorSprite.php?id='. $decorId .'&amp;'. $type . htmlspecialchars($collabSuffix) .'" onclick="return confirm(\''. ($language ? "Go back to original image?":"Revenir à l\'image d\'origine ?") .'\')">['. ($language ? 'Reset':'Réinitialiser') .']</a>';
				?>
				</div>
				<?php
			}
		}
		?>
		</form>
	</div>
	<?php
	if (!$hasTransparency) {
		?>
		<hr />
		<div class="decors-list-container" id="transparency-form-ctn">
			<h3><?php echo $language ? 'Transparent color:':'Couleur de transparence :'; ?></h3>
			<canvas id="select-transparency" width="<?php echo $spriteW; ?>" height="<?php echo $spriteH; ?>"></canvas>
			<form method="post" id="transparency-form" class="decor-editor-form" action="">
				<input type="hidden" name="color" />
				<div id="transparency-color-preview"></div>
				<button type="submit"><?php echo $language ? 'Send':'Valider'; ?></button>
			</form>
		</div><br />
		<?php
	}
	?>
	<div class="editor-navigation">
		<?php
		if ($type != 'decor') {
			?>
			<a href="decorOptions.php?id=<?php echo urlencode($_GET['id']) . htmlspecialchars($collabSuffix); ?>">&lt; <u><?php echo $language ? 'Back to advanced options':'Retour aux options avancées'; ?></u></a>
			<?php
		}
		?>
		<a href="editDecor.php?id=<?php echo urlencode($_GET['id']) . htmlspecialchars($collabSuffix); ?>">&lt; <u><?php echo $language ? "Back to decor editor":"Retour à l'édition du décor"; ?></u></a>
	</div>
	<script type="text/javascript">
		setupUploadTabs(document.querySelector('.editor-upload'));
	</script>
</body>
</html>
		<?php
		}
	}
	mysql_close();
}
?>