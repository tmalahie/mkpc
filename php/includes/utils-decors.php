<?php
define('DECORS_DIR', 'images/sprites/uploads/');
require_once('imageutils.php');
require_once('fileutils.php');
function decor_sprite_paths($hash,&$url) {
    return array (
        'isurl' => boolval($url),
        'ldir' => '../../',
        'hdir' => $url ? '' : '../../',
		'hd' => $url ? $url : DECORS_DIR.$hash.'.png',
		'ld' => DECORS_DIR.$hash.'-ld.png',
		'map' => DECORS_DIR.$hash.'-map.png'
	);
}
function decor_sprite_srcs($hash,&$url=null) {
	$res = decor_sprite_paths($hash,$url);
	if (!file_exists($res['ldir'].$res['ld']))
		$res['ld'] = $res['hd'];
	if (!file_exists($res['ldir'].$res['map']))
		$res['map'] = $res['ld'];
	return $res;
}
function get_decor_srcs(&$decor) {
    parse_decor_img_data($decor);
    return decor_sprite_srcs($decor['sprites'],$decor['imgdata']['url']);
}
function parse_decor_img_data(&$decor) {
    if (!isset($decor['imgdata'])) {
        if ($decor['img_data'])
            $decor['imgdata'] = json_decode($decor['img_data'],true);
        else
            $decor['imgdata'] = array();
    }
}
function decor_is_asset($type) {
    return str_starts_with($type, 'assets/');
}
function default_decor_sprite_src($type) {
    if (decor_is_asset($type))
        return "../../images/map_icons/$type.png";
    else
        return "../../images/sprites/sprite_$type.png";
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
	resize_decor_sprites($spriteSrcs['ldir'].$spriteSrcs['hd'],$spriteSrcs['ldir'].$spriteSrcs['ld'], $spriteSizes['ld']['w'],$spriteSizes['ld']['h']);
}
function get_local_file_keys($spriteSrcs) {
    $res = array();
    if (!$spriteSrcs['isurl'])
        $res[] = 'hd';
    if ($spriteSrcs['ld'] !== $spriteSrcs['hd'])
        $res[] = 'ld';
    if ($spriteSrcs['map'] !== $spriteSrcs['ld'])
        $res[] = 'map';
    return $res;
}
function delete_decor_sprite_imgs($spriteSrcs) {
    $spriteKeys = get_local_file_keys($spriteSrcs);
    foreach ($spriteKeys as $key)
        @unlink($spriteSrcs['ldir'].$spriteSrcs[$key]);
}
function move_decor_sprite_imgs($oldSrcs, $filehash) {
    $spriteKeys = get_local_file_keys($oldSrcs);
    foreach ($spriteKeys as $key) {
        $oldSrc = $oldSrcs[$key];
		@rename($oldSrcs['ldir'].$oldSrc, $oldSrcs['ldir'].preg_replace('#dc-\w+-\d+(-\w+)?\.png$#', $filehash.'$1.png', $oldSrc));
    }
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
    'firering' => array('linked_sprite' => 'fireballs'),
    'fire3star' => array('linked_sprite' => 'fireballs'),
    'tree' => null,
    'palm' => null,
    'coconut' => null,
    'sinistertree' => null,
    'falltree' => null,
    'mountaintree' => null,
    'fir' => null,
    'mariotree' => null,
    'peachtree' => null,
    'assets/oil1' => null,
    'assets/bumper' => null,
    'assets/flipper' => null,
    'assets/pivothand' => null,
    'fullcustom' => array('nbsprites' => 22),
);
function get_decor_sizes(&$decor) {
    parse_decor_img_data($decor);
    $w = $decor['imgdata']['w'];
    $h = $decor['imgdata']['h'];
    return compute_decor_sizes($decor['type'], $w,$h);
}
function decor_sprite_sizes($type,$src) {
    list($w,$h) = getimagesize($src);
    return compute_decor_sizes($type, $w,$h);
}
function compute_decor_sizes($type, $w,$h) {
    global $CUSTOM_DECOR_TYPES;
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
function get_basic_sprites_payload($prefix) {
    if (!empty($_POST[$prefix.'-url']))
        return url_to_file_payload($_POST[$prefix.'-url']);
    return $_FILES[$prefix];
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
    $prefixUrl = $prefix.'-url';
    foreach ($_POST as $key => $url) {
        if ($url) {
            $keys = explode(':', $key);
            if ($keys[0] === $prefixUrl && isset($keys[1]))
                $res[$keys[1]] = url_to_file_payload($url);
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
            /** @var array $extraSprites */
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
    $fileSizeOptions = null;
    if ($decor) {
        $fileSizeOptions['identifiants'] = array($decor['identifiant']);
        if ($isParentFile)
            $fileSizeOptions['decor'] = $decor['id'];
    }
    $poids = file_total_size($fileSizeOptions);
    foreach ($files as &$fileData) {
        $file = $fileData['payload'];
        if (!$file['error']) {
            $filesize = $file['size'];
            if ($filesize < 1000000) {
                $poids += $filesize;
                if ($poids < file_total_quota($decor)) {
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
                    else $error = $language ? 'Your image must have a png, gif, jpg or jpeg extension.':'Votre image doit être au format png, gif, jpg ou jpeg.';
                }
                else $error = $language ? 'You have exceeded your quota of '.filesize_str(MAX_FILE_SIZE).'. Delete decors or circuits to free space.':'Vous avez dépassé votre quota de '.filesize_str(MAX_FILE_SIZE).'. Supprimez des décors ou des circuits pour libérer de l\'espace disque.';
            }
            else $error = $language ? 'Your image mustn\'t exceed 1 MB. Compress or reduce it if necessary.':'Votre image ne doit pas dépasser 1 Mo. Compressez-la ou réduisez la taille si nécessaire.';
        }
        else $error = $language ? 'An error occured during the image transfer. Please try again later.':'Une erreur est survenue lors de l\'envoi de l\'image. Réessayez ultérieurement.';
        return array('error' => $error);
    }
    unset($fileData);
    $parentFileId = $decor ? $decor['id'] : null;
    $ownerId = $decor ? $decor['identifiant'] : $identifiants[0];
    foreach ($files as &$fileData) {
        $file = $fileData['payload'];
        $spriteSizes = $fileData['sprite_sizes'];
        if (!$fileData['id']) {
            mysql_query('INSERT INTO `mkdecors` SET
                type="'. $fileData['type'] .'",identifiant="'. $ownerId .'"'.
                ($fileData['extraType'] ? ',extra_parent_id="'. $parentFileId .'"':'')
            );
            $fileData['id'] = mysql_insert_id();
            if (!$parentFileId)
                $parentFileId = $fileData['id'];
        }
        $filehash = generate_decor_sprite_src($fileData['id']);
        $spriteSrcs = decor_sprite_paths($filehash,$null);
        if ($decor && ($decor['id'] === $fileData['id'])) {
            $oldSrcs = get_decor_srcs($decor);
            move_decor_sprite_imgs($oldSrcs,$filehash);
        }
        $spriteSrcs['tmp'] = DECORS_DIR.$filehash.'-tmp.png';
        if (isset($file['url']))
            rename($file['tmp_name'], $spriteSrcs['ldir'].$spriteSrcs['tmp']);
        else
            move_uploaded_file($file['tmp_name'], $spriteSrcs['ldir'].$spriteSrcs['tmp']);
        clone_img_resource($spriteSrcs['ldir'].$spriteSrcs['tmp'],$spriteSrcs['ldir'].$spriteSrcs['hd']);
        @unlink($spriteSrcs['ldir'].$spriteSrcs['tmp']);
        create_decor_sprite_thumbs($spriteSrcs,$spriteSizes);
        $imgData = array(
            'w' => $spriteSizes['hd']['w'],
            'h' => $spriteSizes['hd']['h']
        );
		if (isset($file['url'])) {
            $imgData['url'] = $file['url'];
            @unlink($spriteSrcs['ldir'].$spriteSrcs['hd']);
		}
        mysql_query('UPDATE `mkdecors` SET sprites="'. $filehash .'",img_data="'. mysql_real_escape_string(json_encode($imgData)) .'" WHERE id="'. $fileData['id'] .'"');
    }
    unset($fileData);
    return array('id' => $parentFileId);
}
function handle_decor_advanced($file,$decor,$type) {
	global $language;
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
					$oldSrcs = get_decor_srcs($decor);
					move_decor_sprite_imgs($oldSrcs,$filehash);
				}
				$spriteSrcs['tmp'] = DECORS_DIR.$filehash.'-tmp.png';
				$spriteSrcs[$type] = DECORS_DIR.$filehash.'-'.$type.'.png';
				move_uploaded_file($file['tmp_name'], $spriteSrcs['ldir'].$spriteSrcs['tmp']);
				switch ($type) {
				case 'map':
					$spriteW = 32;
					$spriteH = 32;
					break;
				}
				resize_img_resource($spriteSrcs['ldir'].$spriteSrcs['tmp'],$spriteSrcs['ldir'].$spriteSrcs[$type], $spriteW,$spriteH);
                @unlink($spriteSrcs['ldir'].$spriteSrcs['tmp']);
                mysql_query('UPDATE `mkdecors` SET sprites="'. $filehash .'" WHERE id="'. $id .'"');
				return array('id' => $id);
			}
			else $error = $language ? 'Your image must have a png, gif, jpg or jpeg extension.':'Votre image doit être au format png, gif, jpg ou jpeg.';
		}
		else $error = $language ? 'Your image mustn\'t exceed 1 MB. Compress or reduce it if necessary.':'Votre image ne doit pas dépasser 1 Mo. Compressez-la ou réduisez la taille si nécessaire.';
	}
	else $error = $language ? 'An error occured during the image transfer. Please try again later.':'Une erreur est survenue lors de l\'envoi de l\'image. Réessayez ultérieurement.';
	return array('error' => $error);
}
?>