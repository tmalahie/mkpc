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
			if ($collab) $collabSuffix = '&amp;collab='. $collab['key'];
		}
		if ($hasReadGrants) {
			include('../includes/utils-decors.php');
			$spriteSrcs = decor_sprite_srcs($decor['sprites']);
			$spriteSizes = decor_sprite_sizes($decor['type'],'../../'.$spriteSrcs['hd']);
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
include('../includes/o_online.php');
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
		<a class="advanced-option option-map" href="<?php echo $hasWriteGrants ? 'decorSprite.php?id='.urlencode($_GET['id']).$collabSuffix : 'javascript:void(0)'; ?>&amp;map">
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
		<div class="advanced-option option-form">
			<h3 class="option-label"><?php echo $language ? 'Decor properties' : 'Propriétés du décor'; ?></h3>
			<form name="decor-options-form" class="decor-editor-form" method="post" action="decorBehavior.php">
				<div>
					<label class="option-form-group">
						<input type="hidden" name="hitbox" />
						<input type="checkbox" data-indeterminate="1" /> <?php echo $language ? 'Has hitbox':'A une hitbox'; ?>
						<a class="option-form-help" href="javascript:showHelp('<?php echo addslashes($language ? "If unchecked, you will pass through the decor if you run into it" : "Si désactivé, vous traverserez le décor si vous rentrez dedans"); ?>')">[?]</a>
						<a class="option-form-reset" href="#null" onclick="resetCheck(event)">[x]</a>
					</label>
					<label class="option-form-group">
						<input type="hidden" name="spin" />
						<input type="checkbox" data-indeterminate="1" /> <?php echo $language ? 'Collision damages':'Dégâts de collision'; ?>
						<a class="option-form-help" href="javascript:showHelp('<?php echo addslashes($language ? "If checked, the decor will make you spin when you hit it" : "Si activé, le décor vous fera tourner lorsque vous rentrez dedans"); ?>')">[?]</a>
						<a class="option-form-reset" href="#null" onclick="resetCheck(event)">[x]</a>
					</label>
					<label class="option-form-group">
						<input type="hidden" name="unbreaking" />
						<input type="checkbox" data-indeterminate="1" /> <?php echo $language ? 'Indestroyable':'Indestructible'; ?>
						<a class="option-form-help" href="javascript:showHelp('<?php echo addslashes($language ? "If checked, the decor cannot be destroyed when hit with a star/mega shroom item" : "Si activé, le décor ne peut pas être détruit avec un objet comme une étoile ou un méga champi"); ?>')">[?]</a>
						<a class="option-form-reset" href="#null" onclick="resetCheck(event)">[x]</a>
					</label>
					<div class="option-form-submit">
						<button type="submit"><?php echo $language ? 'Validate':'Valider'; ?></button>
					</div>
				</div>
			</form>
		</div>
	</div>
	<br />
	<div class="editor-navigation">
		<a href="editDecor.php?id=<?php echo urlencode($_GET['id']) . $collabSuffix; ?>">&lt; <u><?php echo $language ? "Back to decor editor":"Retour à l'édition du décor"; ?></u></a>
	</div>
	<script type="text/javascript">
	function showHelp(text) {
		alert(text);
	}
	var $checkboxInd = document.querySelectorAll("input[data-indeterminate]");
	for (var i=0;i<$checkboxInd.length;i++) {
		$checkboxInd[i].indeterminate = true;
		$checkboxInd[i].onclick = function() {
			var $div = this.parentNode;
			var $input = $div.querySelector('input[type="hidden"]');
			$input.value = this.checked ? 1 : 0;
			delete this.dataset.indeterminate;
		}
	}
	function resetCheck(e) {
		e.preventDefault();
		var $div = e.currentTarget.parentNode;
		var $input = $div.querySelector('input[type="hidden"]');
		var $checkbox = $div.querySelector('input[type="checkbox"]');
		$checkbox.indeterminate = true;
		$checkbox.dataset.indeterminate = "1";
		$input.value = "";
	}
	</script>
</body>
</html>
		<?php
		}
	}
	mysql_close();
}
?>