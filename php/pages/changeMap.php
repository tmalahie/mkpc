<?php
$id = isset($_GET['i']) ? intval($_GET['i']) : 0;
$imgData = isset($_GET['img_data']) ? $_GET['img_data'] : null;
include('../includes/getId.php');
include('../includes/initdb.php');
include('../includes/language.php');
$success = (isset($_GET['x'])&&isset($_GET['y']))+isset($_GET['pivot']);
$lap = isset($_GET['lap']) ? intval($_GET['lap']):0;
$src = isset($_GET['arenes']) ? 'course':'map';
$db = isset($_GET['arenes']) ? 'arenes':'circuits';
$newImg = false;
$isrc = isset($_GET['arenes']) ? 'coursepreview':'racepreview';
require_once('../includes/collabUtils.php');
$requireOwner = !hasCollabGrants($db, $id, $_GET['collab'], 'edit');
if ($circuit = mysql_fetch_array(mysql_query('SELECT id,img_data,identifiant,identifiant2,identifiant3,identifiant4 FROM `'.$db.'` WHERE id="'.$id.'"'. ($requireOwner ? (' AND identifiant='.$identifiants[0].' AND identifiant2='.$identifiants[1].' AND identifiant3='.$identifiants[2].' AND identifiant4='.$identifiants[3]) : '')))) {
	require_once('../includes/circuitImgUtils.php');
	$baseCircuitImg = json_decode($circuit['img_data']);
	$circuitImg = $baseCircuitImg;
	if ($lap && $imgData && isset($circuitImg->lapOverrides)) {
		$imgData = json_decode($imgData);
		$shouldOverrideLap = true;
		if (isset($imgData->url)) {
			if (isset($circuitImg->lapOverrides->$lap)) {
				$lapOverride = $circuitImg->lapOverrides->$lap;
				if (isset($lapOverride->url) && $lapOverride->url === $imgData->url && $lapOverride->local === $imgData->local)
					$shouldOverrideLap = false;
			}
		}
		elseif (isset($imgData->override)) {
			if (!isset($circuitImg->lapOverrides->$lap->url))
				$shouldOverrideLap = false;
		}
		else {
			if (!isset($circuitImg->lapOverrides->$lap))
				$shouldOverrideLap = false;
		}
		if ($shouldOverrideLap) {
			if (isset($imgData->url)) {
				foreach ($circuitImg->lapOverrides as $lapId => $lapOverride) {
					if (isset($lapOverride->url) && $lapOverride->url === $imgData->url && $lapOverride->local === $imgData->local) {
						$lap = $lapId;
						$_GET['lap'] = $lap;
						break;
					}
				}
			}
			else {
				if (isset($imgData->override)) {
					for ($lapId=1;isset($circuitImg->lapOverrides->$lapId->url);$lapId++);
				}
				else {
					for ($lapId=1;isset($circuitImg->lapOverrides->$lapId);$lapId++);
				}
				$lap = $lapId;
				$_GET['lap'] = $lap;
			}
		}
		unset($_GET['img_data']);
	}
	if ($lap) {
		if (isset($circuitImg->lapOverrides->$lap))
			$circuitImg = $circuitImg->lapOverrides->$lap;
		else
			$newImg = true;
	}
	include('../includes/uploadByUrl.php');
	if (isset($_FILES['image'])) {
		if (!$_FILES['image']['error']) {
			$poids = $_FILES['image']['size'];
			include('../includes/file-quotas.php');
			$maxUploadSize = upload_max_size(array('external' => !$isUploaded));
			if ($poids < $maxUploadSize) {
				if ($isUploaded) {
					$ownerIds = array($circuit['identifiant'],$circuit['identifiant2'],$circuit['identifiant3'],$circuit['identifiant4']);
					$poids += file_total_size(isset($_POST['arenes']) ? array('arena'=>$id,'identifiants'=>$ownerIds):array('circuit'=>$id,'lap'=>$lap,'identifiants'=>$ownerIds));
				}
				if ($poids < file_total_quota($circuit)) {
					$fileType = mime_content_type($_FILES['image']['tmp_name']);
					$extensions = array(
						'image/png' => 'png',
						'image/gif' => 'gif',
						'image/jpeg' => 'jpg'
					);
					if (isset($extensions[$fileType])) {
						$ext = $extensions[$fileType];
						if (!$newImg)
							deleteCircuitFile($circuitImg);
						if ($isUploaded) {
							$circuitUrl = $src.$id.'-'.time().'.'.$ext;
							$circuitPath = CIRCUIT_BASE_PATH.$circuitUrl;
							move_uploaded_file($_FILES['image']['tmp_name'], $circuitPath);
						}
						else {
							$circuitUrl = $_POST['url'];
							$circuitPath = $_FILES['image']['tmp_name'];
						}
						$circuitImg = getCircuitImgData($circuitPath,$circuitUrl,$isUploaded);
						$circuitImgRaw = getBaseCircuitImgDataRaw($baseCircuitImg,$circuitImg, $lap);
						mysql_query('UPDATE `'.$db.'` SET img_data="'. $circuitImgRaw .'" WHERE id="'.$id.'"');
						if (!$lap) {
							require_once('../includes/cache_creations.php');
							@unlink(cachePath($isrc.$id.'.png'));
						}
						$success = 2;
						$newImg = false;
					}
					else $error = $language ? 'Your image must have the png, gif, jpg or jpeg format':'Votre image doit être au format png, gif, jpg ou jpeg';
				}
				else $error = $language ? 'You have exceeded your quota of '.filesize_str(MAX_FILE_SIZE):'Vous avez dépassé votre quota de '.filesize_str(MAX_FILE_SIZE);
			}
			else $error = $language ? 'You image mustn\'t exceed '.filesize_str($maxUploadSize):'Votre image ne doit pas dépasser '.filesize_str($maxUploadSize);
		}
	else $error = $language ? 'An error occured. Please try again':'Une erreur est survenue, veuillez réessayez';
	}
	elseif (isset($_GET['delete']) && $lap) {
		if (!$newImg) {
			deleteCircuitFile($circuitImg);
			unset($baseCircuitImg->lapOverrides->$lap);
			$circuitImgRaw = mysql_real_escape_string(json_encode($baseCircuitImg));
			$circuitImg = $baseCircuitImg;
			mysql_query('UPDATE `'.$db.'` SET img_data="'. $circuitImgRaw .'" WHERE id="'.$id.'"');
			$newImg = true;
		}
		$success = 3;
		unset($_GET['delete']);
	}
	elseif ($lap && $newImg) {
		if (isset($imgData->override)) {
			$circuitImg = $imgData;
			$newImg = false;
		}
	}
}
mysql_close();
if (!isset($circuitImg))
	exit;
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="<?php echo $language ? 'en':'fr'; ?>" >
<head>
 <title><?php echo $language ? 'Change circuit image':'Modifier image du circuit'; ?></title>
 <meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1" />
 
<style type="text/css">
body {
	background-color: #bbbfd0;
	margin: 15px 25px;
}
p {
	margin: 0;
}
fieldset {
	border-color: #67A;
	background-color: #cecee1;
}
fieldset:hover {
	background-color: #d6d6ec;
}
legend {
	color: #568;
	font-weight: bold;
}
.import-type {
	font-size: 0.9em;
	margin-bottom: 5px;
}
.import-type label {
	display: inline-block;
}
.import-fields input {
	width: 98%;
}
.import-type-picker {
	display: none;
}
.del-override {
	color: #C24;
}
.del-override:hover {
	color: #D35;
}
#fenetre {
	position: fixed;
	left: 0;
	top: 0;
	width: 100%;
	background-color: #4c7eb0;
	text-align: right;
	border-top-right-radius: 5px;
}
#fenetre input {
	background-color: #69C;
	color: white;
	border: solid 1px white;
	border-top-right-radius: 5px;
	cursor: default;
	text-decoration: none;
}
#fenetre input:hover {
	background-color: #669FD9;
}
#success, #error {
	font-weight: bold;
	text-align: center;
	margin: 0;
	padding: 10px 0 5px 0;
}
#success {
	color: #195;
}
#error {
	color: #d23;
}
input[type="text"] {
	cursor: default;
}
input[type="text"]:focus {
	cursor: text;
}
input[type="submit"] {
	cursor: pointer;
	background-color: #69C;
	color: white;
	border-color: #48A;
	font-size: 15px;
	font-weight: bold;
	margin-top: 5px;
	border-radius: 5px;
}
input[type="submit"]:hover {
	background-color: #669FD9;
	border-color: #59B;
}
input[type="submit"]:disabled {
	cursor: default;
	opacity: 0.3;
}
</style>
<script type="text/javascript">
var image = window.parent.document.getElementById("editor-img");
function apercu() {
	var $apercuauto = document.getElementById("apercuauto");
	if ($apercuauto && $apercuauto.checked) {
		image.style.width = document.forms[1].x.value+"px";
		image.style.height = document.forms[1].y.value+"px";
		window.parent.document.body.id = "changing";
	}
}
document.onclick = apercu;
function updateImportFields(elt) {
	var $fields = document.querySelectorAll(".import-type-picker");
	for (var i=0;i<$fields.length;i++) {
		$fields[i].style.display = "";
		$fields[i].value = "";
	}
	var $field = document.getElementById("import-type-"+elt.value);
	$field.style.display = 'inline-block';
	document.getElementById('modifier').disabled = (elt.value !== 'override');
	$field.focus();
}
function confirmRemoveOverride() {
	return confirm("<?php echo $language ? "Remove image lap override?":"Supprimer la modification d'image pour ce tour ?"; ?>");
}
</script>
</head>
<body onkeydown="window.parent.handleKeySortcuts(event)">
<p id="fenetre"><input type="button" value="&times;" onclick="window.parent.document.getElementById('mask-image').close()" /></p>
<?php
if ($success) {
	?>
<p id="success"><?php echo $language ? "The image has been changed successfully":"L'image a &eacute;t&eacute; modifi&eacute;e avec succ&egrave;s !"; ?></p>
<script type="text/javascript">
<?php
echo 'window.parent.';
if ($success == 3) {
	echo "handleImageDelete(";
}
else {
	echo 'handleImageUpdate('. json_encode(getCircuitImgUrl($circuitImg)) .', '. json_encode(array(
		'url' => $circuitImg->url,
		'local' => $circuitImg->local,
	)).', ';
}
echo 'function() {';
	switch ($success) {
	case 2:
	case 3:
		break;
	default:
		echo 'window.parent.'.(isset($_GET['pivot']) ? 'rotateImg('.($_GET['pivot']+1).');':'resizeImg('.floatval($_GET['x']).','.floatval($_GET['y']).');');
	}
echo '});';
?>
</script>
	<?php
}
elseif (isset($_POST['override']) && isset($_POST['import-type']) && ($_POST['import-type'] === 'override')) {
	?>
<p id="success"><?php echo $language ? "The image has been changed successfully":"L'image a &eacute;t&eacute; modifi&eacute;e avec succ&egrave;s !"; ?></p>
<script type="text/javascript">
window.parent.handleImageFromOverride(<?php echo $_POST['override'] ?>);
</script>
	<?php
	$circuitImg = (object) array(
		'local' => 0,
		'override' => $_POST['override']
	);
}
elseif (isset($error))
	echo '<p id="error">'.$error.'</p>';
else
	echo '<p>&nbsp;</p>';
?>
<form method="post" enctype="multipart/form-data" action="changeMap.php?<?php echo http_build_query($_GET); ?>">
<fieldset>
<legend><?php echo $language ? 'Change circuit image':'Modifier l\'image du circuit'; ?></legend>
<p>
<div class="import-type">
	<label><input type="radio" name="import-type" value="image" checked="checked" onchange="updateImportFields(this)" /><?php echo $language ? 'Upload an image':'Uploader une image' ?></label>
	<?php if ($lap) echo '<br />'; ?>
	<label><input type="radio" name="import-type" value="url" onchange="updateImportFields(this)" /><?php echo $language ? 'Paste image URL':'Coller l\'URL de l\'image' ?></label>
	<?php
	if ($lap)
		echo '<br /><label><input type="radio" name="import-type" value="override" onchange="updateImportFields(this)" />'. ($language ? "Reuse override image":"Réutiliser l'image d'un modificateur") .'</label>';
	?>
</div>
<div class="import-fields">
	<input type="file" name="image" class="import-type-picker" id="import-type-image" style="display:inline-block" onchange="document.getElementById('modifier').disabled=!this.value" />
	<input type="url" name="url" class="import-type-picker" id="import-type-url" placeholder="https://www.mariouniverse.com/wp-content/img/maps/ds/mk/delfino-square.png" oninput="document.getElementById('modifier').disabled=!this.value" />
	<?php
	if ($lap) {
		?>
		<label id="import-type-override" class="import-type-picker">
			<?php echo $language ? 'Override:' : 'Modificateur&nbsp;:'; ?>
			<select name="override">
			</select>
		</label>
		<script type="text/javascript">
			window.parent.initOverrideSelector(document.querySelector('#import-type-override select[name="override"]'));
		</script>
		<?php
	}
	?>
</div>
<?php
if ($lap)
	echo '<input type="hidden" name="lap" value="'.$lap.'" />';
?>
<input type="submit" value="<?php echo $language ? 'Send':'Valider'; ?>" id="modifier" disabled="disabled" />
<?php
if ($lap && !$newImg)
	echo '&nbsp;<a class="del-override" href="changeMap.php?'. http_build_query($_GET) .'&amp;delete" onclick="return confirmRemoveOverride()">'. ($language ? "Remove image override" : "Supprimer l'image") .'</a>';
?></p>
</fieldset>
</form>
<?php
if (!$newImg && $circuitImg->local) {
	?>
<script type="text/javascript">
function reForbid() {
	document.getElementById("redimensionner").disabled = ((document.forms[1].x.value==<?php echo $circuitImg->w ?>)&&(document.forms[1].y.value==<?php echo $circuitImg->h ?>));
}
function able() {
	document.getElementById("pivoter").disabled = false;
	able = function(){};
}
</script>
<form method="post" action="redimensionne.php">
<fieldset>
<legend><?php echo $language ? 'Resize':'Redimensionner'; ?></legend>
<p><?php echo $language ? 'Width':'Longueur'; ?> : <input type="text" name="x" value="<?php echo $circuitImg->w ?>" maxlength="4" size="3" onfocus="this.select()" onchange="if(document.getElementById('proportionnel').checked)this.form.y.value=Math.round(this.value*<?php echo $circuitImg->h ?>/<?php echo $circuitImg->w ?>);reForbid()" onblur="apercu()" />
&nbsp; <?php echo $language ? 'Height':'Largeur'; ?> : <input type="text" name="y" value="<?php echo $circuitImg->h ?>" maxlength="4" size="3" onfocus="this.select()" onchange="if(document.getElementById('proportionnel').checked)this.form.x.value=Math.round(this.value*<?php echo $circuitImg->w ?>/<?php echo $circuitImg->h ?>);reForbid()" onblur="apercu()" /><br />
<label for="proportionnel"><input type="checkbox" id="proportionnel" checked="checked" /> <?php echo $language ? 'Keep proportions':'Conserver les proportions'; ?></label><br />
<label for="apercuauto"><input type="checkbox" id="apercuauto" checked="checked" onchange="if(!this.checked)window.parent.resetImageOptions()" /> <?php echo $language ? 'Auto preview':'Aper&ccedil;u automatique'; ?></label><br />
<input type="hidden" name="id" value="<?php echo $id ?>" />
<?php
if (isset($_GET['arenes'])) echo '<input type="hidden" name="arenes" value="1" />';
if (isset($_GET['collab'])) echo '<input type="hidden" name="collab" value="'. htmlspecialchars($_GET['collab']) .'" />';
if ($lap) echo '<input type="hidden" name="lap" value="'.$lap.'" />';
?>
<input type="submit" value="<?php echo $language ? 'Validate' : 'Valider'; ?>" id="redimensionner" disabled="disabled" /></p>
</fieldset>
</form>
<form method="post" action="pivote.php">
<fieldset>
<legend><?php echo $language ? 'Rotate':'Pivoter'; ?></legend>
<?php
$options = $language ?
	Array('Rotate 90 degrees CW', 'Rotate 180 degrees', 'Rotate 90 degrees CCW', 'Horizontal symmetry', 'Vertical symmetry') :
	Array('Rotation 90&deg; horaire', 'Rotation 180&deg;', 'Rotation 90&deg; antihoraire', 'Sym&eacute;trie horizontale', 'Sym&eacute;trie verticale');
for ($i=0;$i<5;$i++)
	echo '<label for="pivot'.$i.'"><input type="radio" name="pivot" id="pivot'.$i.'" value="'.$i.'" onchange="able()" /> '.$options[$i].'</label><br />';
?>
<input type="hidden" name="id" value="<?php echo $id ?>" />
<?php
if (isset($_GET['arenes'])) echo '<input type="hidden" name="arenes" value="1" />';
if (isset($_GET['collab'])) echo '<input type="hidden" name="collab" value="'. htmlspecialchars($_GET['collab']) .'" />';
if ($lap) echo '<input type="hidden" name="lap" value="'.$lap.'" />';
?>
<input type="submit" value="<?php echo $language ? 'Validate':'Valider'; ?>" id="pivoter" disabled="disabled" />
</fieldset>
</form>
	<?php
}
?>
<?php
$importType = null;
$importValue = null;
if (isset($circuitImg->url) && empty($circuitImg->local)) {
	$importType = 'url';
	$importValue = $circuitImg->url;
}
elseif (isset($circuitImg->override))
	$importType = 'override';
if ($importType) {
	?>
	<script type="text/javascript">
	var $importType = document.querySelector('input[name="import-type"][value="<?php echo $importType ?>"]');
	if ($importType) {
		$importType.checked = true;
		$importType.value = '<?php echo $importType ?>';
		updateImportFields($importType);
		<?php
		if ($importValue)
			echo 'document.getElementById("import-type-'.$importType.'").value = '. json_encode($importValue) .';';
		?>
	}
	</script>
	<?php
}
?>
</body>
</html>