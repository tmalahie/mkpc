<?php
if (isset($_GET['id'])) {
	include('initdb.php');
	$persoId = $_GET['id'];
	if ($perso = mysql_fetch_array(mysql_query('SELECT * FROM `mkchars` WHERE id="'. $persoId .'"'))) {
		include('language.php');
		include('getId.php');
		if (($perso['identifiant'] == $identifiants[0]) && ($perso['identifiant2'] == $identifiants[1]) && ($perso['identifiant3'] == $identifiants[2]) && ($perso['identifiant4'] == $identifiants[3])) {
			include('persos.php');
			include('file-quotas.php');
			$spriteSrcs = get_sprite_srcs($perso['sprites']);
			$type = 'perso';
			if (isset($_GET['map']))
				$type = 'map';
			elseif (isset($_GET['podium']))
				$type = 'podium';
			switch ($type) {
			case 'perso' :
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
				case 'perso' :
					$upload = handle_upload($_FILES['sprites'],$perso);
					if (isset($upload['id']))
						header('location: editPerso.php?id='. $upload['id']);
					break;
				default :
					$upload = handle_advanced($_FILES['sprites'],$perso,$type);
					if (isset($upload['id']))
						header('location: persoOptions.php?id='. $upload['id']);
					break;
				}
				if (isset($upload['error']))
					$error = $upload['error'];
			}
			elseif (isset($_POST['color'])) {
				$color = explode(',', $_POST['color']);
				$oldSrcs = get_sprite_srcs($perso['sprites']);
				$filehash = generate_sprite_src($perso['id']);
				move_sprite_imgs($oldSrcs,$filehash);
				$newSrcs = get_sprite_srcs($filehash);
				switch ($type) {
				case 'perso' :
					add_transparency($newSrcs['hd'],$newSrcs['hd'], $color[0],$color[1],$color[2]);
					clone_img_resource($newSrcs['hd'],$newSrcs['hd']);
					create_sprite_thumbs($newSrcs);
					break;
				default :
					add_transparency($newSrcs[$type],$newSrcs[$type], $color[0],$color[1],$color[2]);
					clone_img_resource($newSrcs[$type],$newSrcs[$type]);
					break;
				}
				update_sprite_src($perso['sprites'],$filehash);
				$perso['sprites'] = $filehash;
				switch ($type) {
				case 'perso' :
					header('location: editPerso.php?id='. $perso['id']);
					break;
				default :
					header('location: persoOptions.php?id='. $perso['id']);
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
<link rel="stylesheet" href="styles/perso-editor.css" />
<?php
include('o_online.php');
?>
<script type="text/javascript">
var spriteSrc = "<?php echo $spriteSrc; ?>", spriteW = <?php echo $spriteW; ?>, spriteH = <?php echo $spriteH; ?>;
window.onload = function() {
	var canvas = document.getElementById("select-transparency");
	if (canvas) {
		var ctx = canvas.getContext("2d");
		ctx.imageSmoothingEnabled = false;
		var img = document.createElement("img");
		img.src = spriteSrc;
		img.onload = function() {
			ctx.drawImage(img, 0,0, img.naturalWidth,img.naturalHeight, 0,0, spriteW,spriteH);
			canvas.onclick = function(e) {
				var x = e.pageX - this.offsetLeft; 
				var y = e.pageY - this.offsetTop;
    			var color = ctx.getImageData(x, y, 1, 1).data;
    			color = color[0]+","+color[1]+","+color[2];
    			var form = document.getElementById("transparency-form");
    			form.color.value = color;
    			document.getElementById("transparency-color-preview").style.backgroundColor = "rgb("+color+")";
    			form.style.display = "block";
			};
		};
	}
}
</script>
<?php
$hasTransparency = ($spriteSrc == $spriteSrcs['ld']) || has_transparency($spriteSrc);
?>
<title><?php echo $language ? 'Character editor':'Éditeur de persos'; ?></title>
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
		<h1><?php
		switch ($type) {
		case 'perso':
			echo $language ? "Edit character image":"Modifier l'image du perso";
			break;
		case 'map':
			echo $language ? "Edit minimap icon":"Modifier l'icone de la mini-map";
			break;
		case 'podium':
			echo $language ? "Edit Grand Prix image":"Modifier l'image de fin de Grand Prix";
			break;
		}
		?></h1>
		<?php
		if (!$hasTransparency)
			echo '<h2><?php echo $language ? "New image:":"Nouvelle image :"; ?></h2>';
	}
	?>
	<form method="post" action="" enctype="multipart/form-data">
		<p>
			<input type="file" required="required" name="sprites" />
			<input type="submit" value="<?php echo $language ? 'Send':'Valider'; ?>" />
		</p>
	</form>
	<?php
	if ($type != 'perso' && ($hasTransparency || ($spriteSrc != $spriteSrcs['ld']))) {
		?>
		<?php echo $language ? 'Current image:':'Image actuelle :'; ?> <img src="<?php echo $spriteSrc; ?>" alt="Image" class="current-sprite" />
		<?php
		if ($spriteSrc != $spriteSrcs['ld'])
			echo '&nbsp;<a href="delSprite.php?id='. $persoId .'&amp;'. $type .'" onclick="return confirm(\''. ($language ? "Go back to original image?":"Revenir à l\'image d\'origine ?") .'\')">['. ($language ? 'Reset':'Réinitialiser') .']</a>';
		?>
		<?php
	}
	?>
	<?php
	if (!$hasTransparency) {
		?>
		<hr />
		<h2><?php echo $language ? 'Transparent color:':'Couleur de transparence :'; ?></h2>
		<canvas id="select-transparency" width="<?php echo $spriteW; ?>" height="<?php echo $spriteH; ?>"></canvas>
		<form method="post" id="transparency-form" action="">
			<input type="hidden" name="color" />
			<div id="transparency-color-preview"></div>
			<input type="submit" value="<?php echo $language ? 'Send':'Valider'; ?>" />
		</form>
		<?php
	}
	?>
	<p>
		<?php
		if ($type != 'perso') {
			?>
			<a href="persoOptions.php?id=<?php echo $_GET['id']; ?>"><?php echo $language ? 'Back to advanced options':'Retour aux options avancées'; ?></a><br />
			<?php
		}
		?>
		<a href="editPerso.php?id=<?php echo $_GET['id']; ?>"><?php echo $language ? "Back to character editor":"Retour à l'édition du perso"; ?></a>
	</p>
</body>
</html>
		<?php
		}
	}
	mysql_close();
}
?>