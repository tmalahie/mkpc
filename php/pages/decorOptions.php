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
			$spriteSrcs = get_decor_srcs($decor);
			$spriteSizes = get_decor_sizes($decor);
			$originalDecorType = $decor['type'];
			if (isset($CUSTOM_DECOR_TYPES[$originalDecorType]['linked_sprite']))
				$originalDecorType = $CUSTOM_DECOR_TYPES[$originalDecorType]['linked_sprite'];
			$originalSizes = decor_sprite_sizes($decor['type'],default_decor_sprite_src($originalDecorType));
			$sizeRatio = $spriteSizes['hd']['w']/$originalSizes['hd']['w'];
			?>
<!DOCTYPE html>
<html lang="<?php echo $language ? 'en':'fr'; ?>">
<head>
<meta charset="utf-8" />
<meta name="viewport" content="width=device-width, initial-scale=1">
<link rel="shortcut icon" type="image/x-icon" href="images/favicon.ico" />
<link rel="stylesheet" href="styles/editor.css?reload=1" />
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
		<?php
		if (!$decor['extra_parent_id'] && !decor_is_asset($decor['type']) && empty($CUSTOM_DECOR_TYPES[$decor['type']]['no_options']) && empty($CUSTOM_DECOR_TYPES[$decor['type']]['linked_sprite'])) {
			?>
		<div class="advanced-option option-form">
			<h3 class="option-label"><?php echo $language ? 'Decor properties' : 'Propriétés du décor'; ?></h3>
			<form name="decor-options-form" class="decor-editor-form" method="post" action="decorProperties.php?id=<?php echo $decorId; ?>">
				<div class="option-form-props">
					<?php
    				$decorOptions = array(
						'hitbox' => array(
							'label' => $language ? 'Has hitbox':'A une hitbox',
							'help' => $language ? "If unchecked, you will pass through the decor if you run into it" : "Si désactivé, vous traverserez le décor si vous rentrez dedans",
							"default" => "1"
						),
						'spin' => array(
							'label' => $language ? 'Collision damage':'Dégâts de collision',
							'help' => $language ? "If checked, the decor will make you spin when you hit it" : "Si activé, le décor vous fera tourner lorsque vous rentrez dedans"
						),
						'unbreaking' => array(
							'label' => $language ? 'Unbreaking':'Indestructible',
							'help' => $language ? "If checked, the decor cannot be destroyed when hit with a star/mega shroom item" : "Si activé, le décor ne peut pas être détruit avec un objet comme une étoile ou un méga champi"
						),
						'breaking' => array(
							'label' => $language ? 'Fragile':'Fragile',
							'help' => $language ? "If checked, the decor will be destroyed when bumping into it, like for crates" : "Si activé, le décor sera détruit lorsque vous rentrez dedans, comme pour les caisses"
						)
					);
					$decorOptionsValue = $decor['options'] ? json_decode($decor['options']) : new \stdClass();
					foreach ($decorOptions as $option => $optionData) {
						$decorOptionValue = isset($decorOptionsValue->$option) ? $decorOptionsValue->$option : null;
						?>
						<label class="option-form-group">
							<input type="hidden" name="<?php echo $option; ?>" value="<?php echo $decorOptionValue; ?>" />
							<input type="checkbox"<?php 
								if ($decorOptionValue === null) {
									echo ' data-indeterminate="1"';
									if (!empty($optionData['default']))
										echo ' checked="checked"';
								}
								elseif ($decorOptionValue)
									echo ' checked="checked"';
							?> /> <?php echo $optionData['label']; ?>
							<a class="option-form-help" href="javascript:showHelp('<?php echo addslashes($optionData['help']); ?>')">[?]</a>
							<a class="option-form-reset" href="#null" onclick="resetCheck(event)">[x]</a>
						</label>
						<?php
					}
					?>
					<div class="option-form-items">
						<?php
						$selectedItems = isset($decorOptionsValue->items) ? $decorOptionsValue->items : null;
						?>
						<label>
							<input type="checkbox" id="items-cb" onclick="handleItemsChange()"<?php
							if ($selectedItems !== null)
								echo ' checked="checked"';
							?> />
							<input type="hidden" name="items" onclick="handleItemsChange()" />
							<?php echo $language ? 'Items can destroy decor' : 'Les objets peuvent détruire le décor'; ?>
						</label>
						<div class="option-form-items-types">
						<?php
						$itemTypes = array('carapace', 'carapace-rouge', 'bobomb', 'carapace-bleue', 'champi', 'etoile', 'megachampi', 'billball');
						$itemSrcs = array('bobomb' => 'bob-omb');
						foreach ($itemTypes as $key) {
							$itemOptionSelected = ($selectedItems === null) || in_array($key,$selectedItems);
							$itemSrc = isset($itemSrcs[$key]) ? $itemSrcs[$key] : $key;
							?>
							<input type="button" data-key="<?php echo $key; ?>"<?php if ($itemOptionSelected) echo 'data-selected="1"'; ?> style="background-image:url('images/map_icons/<?php echo $itemSrc; ?>.png')" onclick="selectItem(this)" />
							<?php
						}
						?>
						</div>
					</div>
					<div class="option-form-submit">
						<button type="submit"><?php echo $language ? 'Validate':'Valider'; ?></button>
						<a class="option-form-reset-all" href="javascript:resetOptions()"><?php echo $language ? 'Reset all':'Réinitialiser'; ?></a>
					</div>
				</div>
			</form>
		</div>
			<?php
		}
		?>
	</div>
	<br />
	<div class="editor-navigation">
		<a href="editDecor.php?id=<?php echo urlencode($_GET['id']) . $collabSuffix; ?>">&lt; <u><?php echo $language ? "Back to decor editor":"Retour à l'édition du décor"; ?></u></a>
	</div>
	<script type="text/javascript">
	function showHelp(text) {
		alert(text);
	}
	function handleFormChange() {
		var $form = document.forms['decor-options-form'];
		var $unbreaking = $form.querySelector('input[name="unbreaking"]');
		var $unbreakingCb = $unbreaking.parentNode.querySelector('input[type="checkbox"]');
		var $breaking = $form.querySelector('input[name="breaking"]');
		var $breakingCb = $breaking.parentNode.querySelector('input[type="checkbox"]');
		if ($unbreaking.value == 1) {
			$breakingCb.disabled = true;
			if ($breaking.value == 1) {
				$breakingCb.indeterminate = true;
				$breakingCb.checked = false;
				$breaking.value = "";
			}
		}
		else
			$breakingCb.disabled = false;
		if ($breaking.value == 1) {
			$unbreakingCb.disabled = true;
			if ($unbreaking.value == 1) {
				$unbreakingCb.indeterminate = true;
				$unbreakingCb.checked = false;
				$unbreaking.value = "";
			}
		}
		else
			$unbreakingCb.disabled = false;
		handleItemsChange();
	}
	function handleItemsChange() {		
		var $form = document.forms['decor-options-form'];
		var $items = $form.querySelector('input[name="items"]');
		var $itemsCb = document.querySelector('#items-cb');
		var $itemTypes = document.querySelector(".option-form-items-types");
		if ($itemsCb.checked) {
			$itemTypes.classList.add("shown");
			var $btnItems = $itemTypes.querySelectorAll('input[type="button"][data-selected="1"]');
			var selectedItems = [];
			for (var i=0;i<$btnItems.length;i++)
				selectedItems.push($btnItems[i].dataset.key);
			$items.value = JSON.stringify(selectedItems);
		}
		else {
			$itemTypes.classList.remove("shown");
			$items.value = "";
		}

		var $resetAll = document.querySelector('.option-form-reset-all');
		var $checked = document.querySelectorAll('.option-form-group input[type="checkbox"]:not([data-indeterminate])');
		var $itemsCb = document.querySelector('#items-cb');
		$resetAll.style.display = $checked.length || $itemsCb.checked ? 'inline-block' : 'none';
	}
	function selectItem($elt) {
		if ($elt.dataset.selected)
			delete $elt.dataset.selected;
		else
			$elt.dataset.selected = "1";
		handleItemsChange();
	}
	function resetCheck(e) {
		e.preventDefault();
		var $div = e.currentTarget.parentNode;
		var $input = $div.querySelector('input[type="hidden"]');
		var $checkbox = $div.querySelector('input[type="checkbox"]');
		$checkbox.indeterminate = true;
		$checkbox.dataset.indeterminate = "1";
		$input.value = "";
		handleFormChange();
	}
	function resetOptions() {
		for (var i=0;i<$checkboxInd.length;i++) {
			var $div = $checkboxInd[i].parentNode;
			var $input = $div.querySelector('input[type="hidden"]');
			$checkboxInd[i].indeterminate = true;
			$checkboxInd[i].checked = false;
			$checkboxInd[i].dataset.indeterminate = "1";
			$input.value = "";
		}
		var $itemsCb = document.querySelector('#items-cb');
		$itemsCb.checked = false;
		handleFormChange();
	}

	var $checkboxInd = document.querySelectorAll('.option-form-group input[type="checkbox"]');
	for (var i=0;i<$checkboxInd.length;i++) {
		var $checkbox = $checkboxInd[i];
		if ($checkbox.dataset.indeterminate)
			$checkbox.indeterminate = true;
		$checkbox.onclick = function() {
			var $div = this.parentNode;
			var $input = $div.querySelector('input[type="hidden"]');
			$input.value = this.checked ? 1 : 0;
			delete this.dataset.indeterminate;
			handleFormChange();
		}
	}
	handleFormChange();
	</script>
</body>
</html>
		<?php
		}
	}
	mysql_close();
}
?>