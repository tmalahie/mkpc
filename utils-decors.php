<?php
define('DECORS_DIR', 'images/sprites/uploads/');
require_once('imageutils.php');
function decor_sprite_paths($hash) {
    return array (
		'hd' => DECORS_DIR.$hash.'.png',
		'ld' => DECORS_DIR.$hash.'-ld.png',
		'map' => DECORS_DIR.$hash.'-map.png'
	);
}
function decor_sprite_srcs($hash) {
	$res = decor_sprite_paths($hash);
	if (!file_exists($res['ld']))
		$res['ld'] = $res['hd'];
	if (!file_exists($res['map']))
		$res['map'] = $res['ld'];
	return $res;
}
function default_decor_sprite_src($type) {
    return "images/sprites/sprite_$type.png";
}
function resize_decor_sprites($originalSrc,$thumbSrc, $cropWidth,$cropHeight) {
	$source = image_create_from($originalSrc);
	$sprite = imagecropreal($source, array('x'=>0,'y'=>0,'width'=>$cropWidth,'height'=>$cropHeight));
    imagedestroy($source);
    $thumbSize = max($cropWidth,$cropHeight);
    $maxSize = 32;
    if ($thumbSize > $maxSize) {
        $thumbWidth = round($cropWidth * $maxSize/$thumbSize);
        $thumbHeight = round($cropHeight * $maxSize/$thumbSize);
    }
    else {
        $thumbWidth = $cropWidth;
        $thumbHeight = $cropHeight;
    }
	$thumb = imagecreatetruecolor($thumbWidth,$thumbHeight);
	imagealphablending($thumb, false);
	imagesavealpha($thumb,true);
	imagefill($thumb, 0,0, imagecolorallocatealpha($thumb, 0,0,0, 127));
	imagecopyresampled($thumb, $sprite, 0,0, 0,0, $thumbWidth,$thumbHeight,$cropWidth,$cropHeight);
	imagedestroy($sprite);

	imagepng($thumb, $thumbSrc);
	imagedestroy($thumb);
}
function create_decor_sprite_thumbs($spriteSrcs,$spriteSizes) {
	resize_decor_sprites($spriteSrcs['hd'],$spriteSrcs['ld'], $spriteSizes['ld']['w'],$spriteSizes['ld']['h']);
}
function delete_decor_sprite_imgs($spriteSrcs) {
	foreach ($spriteSrcs as $key => $oldSrc)
		@unlink($oldSrc);
}
function move_decor_sprite_imgs($oldSrcs, $filehash) {
	foreach ($oldSrcs as $key => $oldSrc)
		@rename($oldSrc, preg_replace('#dc-\w+-\d+(-\w+)?\.png$#', $filehash.'$1.png', $oldSrc));
}
$CUSTOM_DECOR_TYPES = array(
    'tuyau' => null,
    'taupe' => null,
    'poisson' => null,
    'plante' => array('nbsprites' => 2),
    'boo' => null,
    'thwomp' => null,
    'spectre' => null,
    'crabe' => array('nbsprites' => 3),
    'cheepcheep' => array('nbsprites' => 2),
    'movingtree' => null,
    'pokey' => array('nbsprites' => 5),
    'firesnake' => array('nbsprites' => 3),
    'box' => null,
    'snowball' => array('nbsprites' => 3),
    'cannonball' => null,
    'truck' => array('nbsprites' => 22),
    'pendulum' => null,
    'snowman' => null,
    'goomba' => array('nbsprites' => 2),
    'fireplant' => array('nbsprites' => 4, 'extra_sprites' => array('fireball')),
    'fireball' => array('nbsprites' => 4, 'is_extra' => true),
    'piranhaplant' => array('nbsprites' => 2),
    'tortitaupe' => null,
    'billball' => array('nbsprites' => 24),
    'topitaupe' => null,
    'chomp' => array('nbsprites' => 8),
    'movingthwomp' => array('nbsprites' => 3),
    'firebar' => null,
    'tree' => null,
    'palm' => null,
    'coconut' => null,
    'sinistertree' => null,
    'falltree' => null,
    'mountaintree' => null,
    'fir' => null,
    'mariotree' => null,
    'peachtree' => null
);
function decor_sprite_sizes($type,$src) {
    global $CUSTOM_DECOR_TYPES;
    list($w,$h) = getimagesize($src);
    $res = array(
        'ld' => array(
            'w' => $w,
            'h' => $h
        ),
        'hd' => array(
            'w' => $w,
            'h' => $h
        ),
        'nb_sprites' => 1
    );
    if (isset($CUSTOM_DECOR_TYPES[$type]) && isset($CUSTOM_DECOR_TYPES[$type]['nbsprites'])) {
        $res['nb_sprites'] = $CUSTOM_DECOR_TYPES[$type]['nbsprites'];
        $res['ld']['w'] = round($w/$res['nb_sprites']);
    }
    return $res;
}
function generate_decor_sprite_src($id) {
	return 'dc-'.uniqid().'-'.$id;
}
function get_extra_sprites_payload($prefix) {
    $res = array();
    foreach ($_FILES as $key => $file) {
        if ($file['error'] !== 4) {
            $keys = explode(':', $key);
            if ($keys[0] === $prefix && isset($keys[1]))
                $res[$keys[1]] = $file;
        }
    }
    return $res;
}
function handle_decor_upload($type,$file,$extra,$decor=null) {
	global $language, $identifiants, $CUSTOM_DECOR_TYPES;
    $files = array();
    $isParentFile = !empty($file);
    if ($isParentFile) {
        $files[] = array(
            'payload' => $file,
            'type' => $type,
            'extraType' => false,
            'id' => $decor ? $decor['id'] : null
        );
    }
    if (!empty($extra)) {
        if (isset($CUSTOM_DECOR_TYPES[$type]['extra_sprites'])) {
            /** @var $extraSprites array */
            $extraSprites = $CUSTOM_DECOR_TYPES[$type]['extra_sprites'];
            foreach ($extraSprites as $extraType) {
                if (isset($extra[$extraType])) {
                    $files[] = array(
                        'payload' => $extra[$extraType],
                        'type' => $extraType,
                        'extraType' => true,
                        'id' => null
                    );
                }
            }
        }
    }
    $poids = file_total_size(($decor&&$isParentFile) ? array('decor'=>$decor['id']):array());
    foreach ($files as &$fileData) {
        $file = $fileData['payload'];
        if (!$file['error']) {
            $filesize = $file['size'];
            if ($filesize < 1000000) {
                $poids += $filesize;
                if ($poids < MAX_FILE_SIZE) {
                    $ext = get_img_ext($file['tmp_name']);
                    $extensions = Array('png', 'gif', 'jpg', 'jpeg');
                    if (in_array($ext, $extensions)) {
                        $spriteSizes = decor_sprite_sizes($type,$file['tmp_name']);
                        $originalW = $spriteSizes['hd']['w'];
                        $nbSprites = $spriteSizes['nb_sprites'];
                        if (!($originalW%$nbSprites)) {
                            $fileData['sprite_sizes'] = $spriteSizes;
                            continue;
                        }
                        else $error = $language ? 'Your image width must be a multiple of '.$nbSprites:'La largeur de votre image doit être un multiple de '. $nbSprites;
                    }
                    else $error = $language ? 'Your image must have a png, gif, jpg or jpeg extension.':'Votre image doit &ecirc;tre au format png, gif, jpg ou jpeg.';
                }
                else $error = $language ? 'You have exceeded your quota of '.filesize_str(MAX_FILE_SIZE).'. Delete decors or circuits to free space.':'Vous avez d&eacute;pass&eacute; votre quota de '.filesize_str(MAX_FILE_SIZE).'. Supprimez des décors ou des circuits pour lib&eacute;rer de l\'espace disque.';
            }
            else $error = $language ? 'Your image mustn\'t exceed 1 Mo. Compress or reduce it if necessary.':'Votre image ne doit pas d&eacute;passer 1 Mo. Compressez-la ou r&eacute;duisez la taille si n&eacute;cessaire.';
        }
        else $error = $language ? 'An error occured during the image transfer. Please try again later.':'Une erreur est survenue lors de l\'envoi de l\'image. R&eacute;essayez ult&egrave;rieurement.';
        return array('error' => $error);
    }
    unset($fileData);
    $parentFileId = $decor ? $decor['id'] : null;
    foreach ($files as &$fileData) {
        $file = $fileData['payload'];
        $spriteSizes = $fileData['sprite_sizes'];
        if (!$fileData['id']) {
            mysql_query('INSERT INTO `mkdecors` SET
                type="'. $fileData['type'] .'",identifiant="'. $identifiants[0] .'"'.
                ($fileData['extraType'] ? ',extra_parent_id="'. $parentFileId .'"':'')
            );
            $fileData['id'] = mysql_insert_id();
            if (!$parentFileId)
                $parentFileId = $fileData['id'];
        }
        $filehash = generate_decor_sprite_src($fileData['id']);
        $spriteSrcs = decor_sprite_paths($filehash);
        if ($decor && ($decor['id'] === $fileData['id'])) {
            $oldSrcs = decor_sprite_srcs($decor['sprites']);
            move_decor_sprite_imgs($oldSrcs,$filehash);
        }
        $spriteSrcs['tmp'] = DECORS_DIR.$filehash.'-tmp.png';
        move_uploaded_file($file['tmp_name'], $spriteSrcs['tmp']);
        clone_img_resource($spriteSrcs['tmp'],$spriteSrcs['hd']);
        @unlink($spriteSrcs['tmp']);
        create_decor_sprite_thumbs($spriteSrcs,$spriteSizes);
        mysql_query('UPDATE `mkdecors` SET sprites="'. $filehash .'" WHERE id="'. $fileData['id'] .'"');
    }
    unset($fileData);
    return array('id' => $parentFileId);
}
function handle_decor_advanced($file,$decor,$type) {
	global $language, $identifiants;
	if (!$file['error']) {
		$poids = $file['size'];
		if ($poids < 1000000) {
            $ext = get_img_ext($file['tmp_name']);
			$extensions = Array('png', 'gif', 'jpg', 'jpeg');
			if (in_array($ext, $extensions)) {
				$id = $decor['id'];
				$filehash = generate_decor_sprite_src($id);
				$spriteSrcs = decor_sprite_srcs($filehash);
				if ($decor) {
					$oldSrcs = decor_sprite_srcs($decor['sprites']);
					move_decor_sprite_imgs($oldSrcs,$filehash);
				}
				$spriteSrcs['tmp'] = DECORS_DIR.$filehash.'-tmp.png';
				$spriteSrcs[$type] = DECORS_DIR.$filehash.'-'.$type.'.png';
				move_uploaded_file($file['tmp_name'], $spriteSrcs['tmp']);
				switch ($type) {
				case 'map':
					$spriteW = 32;
					$spriteH = 32;
					break;
				}
				resize_img_resource($spriteSrcs['tmp'],$spriteSrcs[$type], $spriteW,$spriteH);
                @unlink($spriteSrcs['tmp']);
                mysql_query('UPDATE `mkdecors` SET sprites="'. $filehash .'" WHERE id="'. $id .'"');
				return array('id' => $id);
			}
			else $error = $language ? 'Your image must have a png, gif, jpg or jpeg extension.':'Votre image doit &ecirc;tre au format png, gif, jpg ou jpeg.';
		}
		else $error = $language ? 'Your image mustn\'t exceed 1 Mo. Compress or reduce it if necessary.':'Votre image ne doit pas d&eacute;passer 1 Mo. Compressez-la ou r&eacute;duisez la taille si n&eacute;cessaire.';
	}
	else $error = $language ? 'An error occured during the image transfer. Please try again later.':'Une erreur est survenue lors de l\'envoi de l\'image. R&eacute;essayez ult&egrave;rieurement.';
	return array('error' => $error);
}
?>