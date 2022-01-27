<?php
if (isset($_GET['id'])) {
	include('initdb.php');
	$decorId = $_GET['id'];
	if ($decor = mysql_fetch_array(mysql_query('SELECT * FROM `mkdecors` WHERE id="'. $decorId .'"'))) {
		include('language.php');
		include('getId.php');
		if ($decor['identifiant'] == $identifiants[0]) {
			include('utils-decors.php');
			include('file-quotas.php');
			$spriteSrcs = decor_sprite_srcs($decor['sprites']);
			$type = 'decor';
			if (isset($_GET['map']))
				$type = 'map';
			switch ($type) {
			case 'decor' :
				$spriteSrc = $spriteSrcs['hd'];
				break;
			default :
				$spriteSrc = $spriteSrcs[$type];
				break;
			}
			list($spriteW, $spriteH) = getimagesize($spriteSrc);
			$minW = 64;
			if ($spriteW < $minW) {
				$spriteH = round($spriteH*$minW/$spriteW);
				$spriteW = $minW;
			}
			if (isset($_FILES['sprites'])) {
				switch ($type) {
				case 'decor' :
					$upload = handle_decor_upload($decor['type'],$_FILES['sprites'],get_extra_sprites_payload('extraSprites'),$decor);
					if (isset($upload['id']))
						header('location: editDecor.php?id='. $upload['id']);
					break;
				default :
					$upload = handle_decor_advanced($_FILES['sprites'],$decor,$type);
					if (isset($upload['id']))
						header('location: decorOptions.php?id='. $upload['id']);
					break;
				}
				if (isset($upload['error']))
					$error = $upload['error'];
			}
			elseif (isset($_POST['color'])) {
				$color = explode(',', $_POST['color']);
				$oldSrcs = decor_sprite_srcs($decor['sprites']);
				$filehash = generate_decor_sprite_src($decor['id']);
				move_decor_sprite_imgs($oldSrcs,$filehash);
				$newSrcs = decor_sprite_srcs($filehash);
				switch ($type) {
				case 'decor' :
					add_transparency($newSrcs['hd'],$newSrcs['hd'], $color[0],$color[1],$color[2]);
					clone_img_resource($newSrcs['hd'],$newSrcs['hd']);
                    $spriteSizes = decor_sprite_sizes($decor['type'],$newSrcs['hd']);
					create_decor_sprite_thumbs($newSrcs,$spriteSizes);
					break;
				default :
					add_transparency($newSrcs[$type],$newSrcs[$type], $color[0],$color[1],$color[2]);
					clone_img_resource($newSrcs[$type],$newSrcs[$type]);
					break;
				}
				mysql_query('UPDATE `mkdecors` SET sprites="'.$filehash.'" WHERE id="'. $decorId .'"');
				$decor['sprites'] = $filehash;
				switch ($type) {
				case 'decor' :
					header('location: editDecor.php?id='. $decor['id']);
					break;
				default :
					header('location: decorOptions.php?id='. $decor['id']);
					break;
				}
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
include('o_online.php');
?>
<script type="text/javascript">
var spriteSrc = "<?php echo $spriteSrc; ?>", spriteW = <?php echo $spriteW; ?>, spriteH = <?php echo $spriteH; ?>;
</script>
<script type="text/javascript" src="scripts/edit-sprite.js"></script>
<?php
$hasTransparency = ($spriteSrc == $spriteSrcs['ld']) || has_transparency($spriteSrc);
?>
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
            <input type="file" required="required" name="sprites" />
            <button type="submit"><?php echo $language ? 'Send':'Valider'; ?></button>
        </form>
		<?php
		if ($type != 'decor' && ($hasTransparency || ($spriteSrc != $spriteSrcs['ld']))) {
			?>
			<div id="decor-current-img-preview">
			<?php echo $language ? 'Current image:':'Image actuelle :'; ?>&nbsp;<img src="<?php echo $spriteSrc; ?>" alt="Image" class="current-sprite" />
			<?php
			if ($spriteSrc != $spriteSrcs['ld'])
				echo '&nbsp;<a href="delDecorSprite.php?id='. $decorId .'&amp;'. $type .'" onclick="return confirm(\''. ($language ? "Go back to original image?":"Revenir à l\'image d\'origine ?") .'\')">['. ($language ? 'Reset':'Réinitialiser') .']</a>';
			?>
			</div>
			<?php
		}
		?>
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
            <a href="decorOptions.php?id=<?php echo $_GET['id']; ?>">&lt; <u><?php echo $language ? 'Back to advanced options':'Retour aux options avancées'; ?></u></a>
			<?php
		}
		?>
		<a href="editDecor.php?id=<?php echo $_GET['id']; ?>">&lt; <u><?php echo $language ? "Back to decor editor":"Retour à l'édition du décor"; ?></u></a>
	</div>
</body>
</html>
		<?php
		}
	}
	mysql_close();
}
?>