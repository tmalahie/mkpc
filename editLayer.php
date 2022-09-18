<?php
if (isset($_GET['id'])) {
	include('initdb.php');
	$layerId = $_GET['id'];
	if ($layer = mysql_fetch_array(mysql_query('SELECT l.bg,l.filename,b.identifiant FROM `mkbglayers` l INNER JOIN `mkbgs` b ON l.bg=b.id WHERE l.id="'. $layerId .'"'))) {
		include('language.php');
		include('getId.php');
		if ($layer['identifiant'] == $identifiants[0]) {
			include('utils-bgs.php');
			include('file-quotas.php');
			if (isset($_FILES['layer'])) {
                $upload = handle_bg_upload($_FILES['layer'], array(
                    'layer' => $_GET['id']
                ));
				if (isset($upload['error']))
					$error = $upload['error'];
                elseif (isset($upload['id'])) {
                    header('location: editBg.php?id='. $upload['id']);
                    mysql_close();
                    exit;
                }
			}
			elseif (isset($_POST['color'])) {
				$color = explode(',', $_POST['color']);
                $oldPath = get_layer_path($layer['filename']);

                $fileName = generate_layer_name($layerId, 'png');
                $newPath = get_layer_path($fileName);

                add_transparency($oldPath,$newPath, $color[0],$color[1],$color[2]);
				mysql_query('UPDATE `mkbglayers` SET filename="'.$fileName.'" WHERE id="'. $layerId .'"');
                @unlink($oldPath);

                header('location: editBg.php?id='. $layer['bg']);
                mysql_close();
                exit;
			}
            $spriteSrc = get_layer_path($layer['filename']);
			list($spriteW, $spriteH) = getimagesize($spriteSrc);
			?>
<!DOCTYPE html>
<html lang="<?php echo $language ? 'en':'fr'; ?>">
<head>
<meta charset="utf-8" />
<meta name="viewport" content="width=device-width, initial-scale=1">
<link rel="shortcut icon" type="image/x-icon" href="images/favicon.ico" />
<link rel="stylesheet" href="styles/editor.css" />
<link rel="stylesheet" href="styles/bg-editor.css" />
<?php
include('o_online.php');
?>
<script type="text/javascript">
var spriteSrc = "<?php echo $spriteSrc; ?>", spriteW = <?php echo $spriteW; ?>, spriteH = <?php echo $spriteH; ?>;
</script>
<script type="text/javascript" src="scripts/edit-sprite.js"></script>
<?php
$hasTransparency = has_transparency($spriteSrc);
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
            <input type="file" required="required" name="layer[]" />
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
		<a href="editBg.php?id=<?php echo $layer['bg']; ?>">&lt; <u><?php echo $language ? "Back to background editor":"Retour à l'édition de l'arrière-plan"; ?></u></a>
	</div>
</body>
</html>
		<?php
		}
	}
	mysql_close();
}
?>