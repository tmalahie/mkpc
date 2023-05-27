<?php
define('BGS_DIR', 'images/sprites/uploads/');
define('MAX_LAYERS', 5);
require_once('imageutils.php');
require_once('fileutils.php');
function handle_bg_upload($files,$options=array()) {
	global $language, $identifiants;
	$totalSizeOptions = null;
	$totalQuotaOptions = null;
	if (isset($options['layer']))
		$totalSizeOptions['layer'] = $options['layer'];
	if (isset($options['identifiant'])) {
		$totalSizeOptions['identifiants'] = array($options['identifiant']);
		$totalQuotaOptions['identifiant'] = $options['identifiant'];
	}
	$totalSize = file_total_size($totalSizeOptions);
	$layerFiles = array();
	foreach ($files as &$file) {
		$error = null;
		$filePath = $file['tmp_name'];
		if (!$file['error']) {
			$poids = $file['size'];
			if ($poids < 1000000) {
				if (!isset($file['url']))
					$totalSize += $poids;
				if ($totalSize < file_total_quota($totalQuotaOptions)) {
					$ext = get_img_ext($filePath);
					$extensions = Array('png', 'gif', 'jpg', 'jpeg');
					if (in_array($ext, $extensions)) {
						$layerFiles[] = array(
							'url' => isset($file['url']) ? $file['url'] : null,
							'path' => $filePath,
							'ext' => $ext
						);
					}
					else $error = $language ? 'Your image must have a png, gif, jpg or jpeg extension.':'Votre image doit être au format png, gif, jpg ou jpeg.';
				}
				else $error = $language ? 'You have exceeded your quota of '.filesize_str(MAX_FILE_SIZE).'. Delete backgrounds or circuits to free space.':'Vous avez dépassé votre quota de '.filesize_str(MAX_FILE_SIZE).'. Supprimez des arrière-plans ou des circuits pour libérer de l\'espace disque.';
			}
			else $error = $language ? 'Your image mustn\'t exceed 1 Mo. Compress or reduce it if necessary.':'Votre image ne doit pas dépasser 1 Mo. Compressez-la ou réduisez la taille si nécessaire.';
		}
		else $error = $language ? 'An error occured during the image transfer. Please try again later.':'Une erreur est survenue lors de l\'envoi de l\'image. Réessayez ultérieurement.';
	}
	unset($file);
	if ($error)
		return array('error' => $error);

	$ordering = 0;
	$nbLayers = count($layerFiles);
	$baseLayers = 0;
	if (isset($options['layer'])) {
		$getBgLayer = mysql_fetch_array(mysql_query('SELECT id,bg,filename FROM `mkbglayers` WHERE id="'. $options['layer'] .'"'));
		if (!$getBgLayer)
			return array('error' => 'Unknown error');
		if ($getBgLayer['filename'] !== '') {
			$filePath = get_layer_path($getBgLayer['filename']);
			@unlink('../../'.$filePath);
		}
		$id = $getBgLayer['bg'];
	}
	elseif (isset($options['bg'])) {
		$id = $options['bg'];
		$getLayerStats = mysql_fetch_array(mysql_query('SELECT MAX(ordering) AS max, COUNT(*) AS nb FROM `mkbglayers` WHERE bg="'. $options['bg'] .'"'));
		$ordering = $getLayerStats['max']+1;
		$baseLayers = +$getLayerStats['nb'];
	}
	else {
		mysql_query('INSERT INTO `mkbgs` SET identifiant="'. $identifiants[0] .'"');
		$id = mysql_insert_id();
	}
	if (($baseLayers+$nbLayers) > MAX_LAYERS)
		$layerFiles = array_slice($layerFiles,0, MAX_LAYERS-$baseLayers);

	foreach ($layerFiles as $layerFile) {
		if (isset($options['layer']))
			$layerId = $options['layer'];
		else {
			mysql_query('INSERT INTO `mkbglayers` SET bg="'. $id .'",ordering="'. $ordering .'",filename="",url=""');
			$layerId = mysql_insert_id();
		}
		if ($layerFile['url'] === null) {
			$fileName = generate_layer_name($layerId, $layerFile['ext']);
			$filePath = get_layer_path($fileName);
			move_uploaded_file($layerFile['path'], '../../'.$filePath);
			mysql_query('UPDATE mkbglayers SET filename="'. $fileName .'", url="" WHERE id='. $layerId);
		}
		else {
			@unlink($layerFile['path']);
			mysql_query('UPDATE mkbglayers SET url="'. $layerFile['url'] .'", filename="" WHERE id='. $layerId);
		}
		$ordering++;
	}
	return array('id' => $id);
}
function generate_layer_name($id, $ext) {
	return 'bg-'.uniqid().'-'.$id.'.'.$ext;
}
function get_layer_path($fileName) {
	return BGS_DIR.$fileName;
}
function get_bg_layers($bgId) {
	$bgLayers = array();
	$getLayers = mysql_query('SELECT id,filename,url FROM mkbglayers WHERE bg="'. $bgId .'" ORDER BY ordering');
	while ($getLayer = mysql_fetch_array($getLayers)) {
		$local = ($getLayer['filename'] !== '');
		$bgLayers[] = array(
			'id' => $getLayer['id'],
			'src' => $local ? get_layer_path($getLayer['filename']) : $getLayer['url'],
			'path' => $local ? '../../'.get_layer_path($getLayer['filename']) : $getLayer['url'],
			'local' => $local
		);
	}
	return $bgLayers;
}
function get_bg_payload($bg) {
	$bgPayload = array(
		'id' => $bg['id']
	);

	if (isset($bg['name']) && ($bg['name'] !== ''))
		$bgPayload['name'] = $bg['name'];

	$layers = get_bg_layers($bg['id']);
	foreach ($layers as &$layer) {
		$layer['path'] = $layer['src'];
		unset($layer['src']);
	}
	unset($layer);
	$bgPayload['layers'] = $layers;
	
	return $bgPayload;
}
function print_bg_div($options) {
	if (isset($options['bg']))
		$bgLayers = get_bg_layers($options['bg']);
	elseif (isset($options['layers']))
		$bgLayers = $options['layers'];
	$customAttrs = isset($options['attrs']) ? ' '.$options['attrs'] : '';
	$layerStyles = array();
	foreach ($bgLayers as $bgLayer)
		$layerStyles[] = "url('".$bgLayer['src']."')";
		$layerStyles = array_reverse($layerStyles);
	echo '<div class="bg-preview" style="background-image: '. implode(', ', $layerStyles) .'"'. $customAttrs .'></div>';
}
?>