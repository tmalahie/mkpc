<?php
define('PERSOS_DIR', 'images/sprites/uploads/');
require_once('imageutils.php');
function resize_sprites($original_src,$thumb_src, $newWidth,$newHeight) {
	$source = image_create_from($original_src);
	$sprite = imagecropreal($source, array('x'=>0,'y'=>0,'width'=>$newWidth,'height'=>$newHeight));
	imagedestroy($source);
	$thumb = imagecreatetruecolor(32,32);
	imagealphablending($thumb, false);
	imagesavealpha($thumb,true);
	imagefill($thumb, 0,0, imagecolorallocatealpha($thumb, 0,0,0, 127));
	imagecopy($thumb, $sprite, round((32-$newWidth)/2),round((32-$newHeight)/2), 0,0,$newWidth,$newHeight);
	imagedestroy($sprite);

	imagepng($thumb, $thumb_src);
	imagedestroy($thumb);
}
function generate_sprite_src($id) {
	return 'cp-'.uniqid().'-'.$id;
}
function get_sprite_srcs($hash) {
	$res = array (
		'hd' => PERSOS_DIR.$hash.'.png',
		'ld' => PERSOS_DIR.$hash.'-ld.png',
		'star' => PERSOS_DIR.$hash.'-star.png',
		'map' => PERSOS_DIR.$hash.'-map.png',
		'podium' => PERSOS_DIR.$hash.'-podium.png'
	);
	if (!file_exists('../../'.$res['map']))
		$res['map'] = $res['ld'];
	if (!file_exists('../../'.$res['podium']))
		$res['podium'] = $res['ld'];
	return $res;
}
function get_perso_music($perso) {
	if (!empty($perso['music']))
		return $perso['music'];
	return 'mario';
}
function create_sprite_thumbs($spriteSrcs) {
	list($w,$h) = getimagesize('../../'.$spriteSrcs['hd']);
	$w = round($w/24);
	resize_sprites('../../'.$spriteSrcs['hd'],'../../'.$spriteSrcs['ld'], $w,$h);
	filter_img('../../'.$spriteSrcs['hd'],'../../'.$spriteSrcs['star'], 255,255,0,30);
}
function delete_sprite_imgs($spriteSrcs) {
	foreach ($spriteSrcs as $key => $oldSrc)
		@unlink('../../'.$oldSrc);
}
function move_sprite_imgs($oldSrcs, $filehash) {
	foreach ($oldSrcs as $key => $oldSrc)
		@rename('../../'.$oldSrc, '../../'.preg_replace('#cp-\w+-\d+(-\w+)?\.png$#', $filehash.'$1.png', $oldSrc));
}
function update_sprite_src($oldHash,$newHash) {
	$tables = array (
		'mkchars' => 'sprites',
		'mkjoueurs' => 'joueur',
		'mkghosts' => 'perso',
		'mkrecords' => 'perso'
	);
	foreach ($tables as $table => $column)
		mysql_query('UPDATE `'.$table.'` SET '.$column.'="'.$newHash.'" WHERE '.$column.'="'.$oldHash.'"');
}
function handle_upload($file,$perso=null) {
	global $language, $identifiants;
	if (!$file['error']) {
		$file_size = $file['size'];
		if ($file_size < 1000000) {
			$file_size += file_total_size($perso ? array('perso'=>$perso['id'],'identifiants'=>array($perso['identifiant'],$perso['identifiant2'],$perso['identifiant3'],$perso['identifiant4'])):null);
			if ($file_size < file_total_quota($perso)) {
				$ext = get_img_ext($file['tmp_name']);
				$extensions = Array('png', 'gif', 'jpg', 'jpeg');
				if (in_array($ext, $extensions)) {
					list($w,$h) = getimagesize($file['tmp_name']);
					if ($w >= 384 && $w <= 1536) {
						if ($h >= 16 && $h <= 64) {
							if ($w%24 == 0) {
								if (!$perso) {
									mysql_query('INSERT INTO `mkchars` SET
										identifiant="'. $identifiants[0] .'",identifiant2="'. $identifiants[1] .'",identifiant3="'. $identifiants[2] .'",identifiant4="'. $identifiants[3] .'"
									');
									$id = mysql_insert_id();
								}
								else
									$id = $perso['id'];
								$filehash = generate_sprite_src($id);
								$spriteSrcs = get_sprite_srcs($filehash);
								if ($perso) {
									$oldSrcs = get_sprite_srcs($perso['sprites']);
									move_sprite_imgs($oldSrcs,$filehash);
								}
								$spriteSrcs['tmp'] = PERSOS_DIR.$filehash.'-tmp.png';
								move_uploaded_file($file['tmp_name'], '../../'.$spriteSrcs['tmp']);
								clone_img_resource('../../'.$spriteSrcs['tmp'],'../../'.$spriteSrcs['hd']);
								@unlink('../../'.$spriteSrcs['tmp']);
								create_sprite_thumbs($spriteSrcs);
								if ($perso)
									update_sprite_src($perso['sprites'],$filehash);
								else
									mysql_query('UPDATE `mkchars` SET sprites="'. $filehash .'" WHERE id="'. $id .'"');
								return array('id' => $id);
							}
							else $error = $language ? 'Your image width must be a multiple of 24.':'La largeur de votre image doit être un multiple de 24.';
						}
						else $error = $language ? 'Your image height must be between 16px and 64px.':'La hauteur de votre image doit être comprise entre 16px et 64px.';
					}
					else $error = $language ? 'Your image width must be between 384px and 1536px.':'La largeur de votre image doit être comprise entre 384px et 1536px.';
				}
				else $error = $language ? 'Your image must have a png, gif, jpg or jpeg extension.':'Votre image doit être au format png, gif, jpg ou jpeg.';
			}
			else $error = $language ? 'You have exceeded your quota of '.filesize_str(MAX_FILE_SIZE).'. Delete characters or circuits to free space.':'Vous avez dépassé votre quota de '.filesize_str(MAX_FILE_SIZE).'. Supprimez des persos ou des circuits pour libérer de l\'espace disque.';
		}
		else $error = $language ? 'Your image mustn\'t exceed 1 MB. Compress or reduce it if necessary.':'Votre image ne doit pas dépasser 1 Mo. Compressez-la ou réduisez la taille si nécessaire.';
	}
	else $error = $language ? 'An error occured during the image transfer. Please try again later.':'Une erreur est survenue lors de l\'envoi de l\'image. Réessayez ultérieurement.';
	return array('error' => $error);
}
function handle_advanced($file,$perso,$type) {
	global $language, $identifiants;
	if (!$file['error']) {
		$file_size = $file['size'];
		if ($file_size < 1000000) {
			$ext = get_img_ext($file['tmp_name']);
			$extensions = Array('png', 'gif', 'jpg', 'jpeg');
			if (in_array($ext, $extensions)) {
				$id = $perso['id'];
				$filehash = generate_sprite_src($id);
				$spriteSrcs = get_sprite_srcs($filehash);
				if ($perso) {
					$oldSrcs = get_sprite_srcs($perso['sprites']);
					move_sprite_imgs($oldSrcs,$filehash);
				}
				$spriteSrcs['tmp'] = PERSOS_DIR.$filehash.'-tmp.png';
				$spriteSrcs[$type] = PERSOS_DIR.$filehash.'-'.$type.'.png';
				move_uploaded_file($file['tmp_name'], '../../'.$spriteSrcs['tmp']);
				switch ($type) {
				case 'map':
					$spriteW = 18;
					$spriteH = 18;
					break;
				case 'podium':
					$spriteW = 30;
					$spriteH = 45;
				}
				resize_img_resource('../../'.$spriteSrcs['tmp'],'../../'.$spriteSrcs[$type], $spriteW,$spriteH);
				@unlink('../../'.$spriteSrcs['tmp']);
				update_sprite_src($perso['sprites'],$filehash);
				return array('id' => $id);
			}
			else $error = $language ? 'Your image must have a png, gif, jpg or jpeg extension.':'Votre image doit être au format png, gif, jpg ou jpeg.';
		}
		else $error = $language ? 'Your image mustn\'t exceed 1 MB. Compress or reduce it if necessary.':'Votre image ne doit pas dépasser 1 Mo. Compressez-la ou réduisez la taille si nécessaire.';
	}
	else $error = $language ? 'An error occured during the image transfer. Please try again later.':'Une erreur est survenue lors de l\'envoi de l\'image. Réessayez ultérieurement.';
	return array('error' => $error);
}
function get_perso_payload($perso) {
	global $identifiants;
	$mine = (($perso['identifiant'] == $identifiants[0]) && ($perso['identifiant2'] == $identifiants[1]) && ($perso['identifiant3'] == $identifiants[2]) && ($perso['identifiant4'] == $identifiants[3]));
	$spriteSrcs = get_sprite_srcs($perso['sprites']);
	return array(
		$perso['id'],
		$perso['sprites'],
		$perso['name'],
		$perso['author'],
		$mine,
		(null===$perso['author']),
		array($perso['acceleration'],$perso['speed'],$perso['handling'],$perso['mass']),
		$spriteSrcs['map'],
		$spriteSrcs['podium'],
		$perso['avgrating'],
		$perso['nbratings'],
		$perso['playcount']
	);
}
?>