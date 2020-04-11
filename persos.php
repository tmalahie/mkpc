<?php
define('PERSOS_DIR', 'images/sprites/uploads/');
require_once('imageutils.php');
function clone_img_resource($original_src,$thumb_src) {
	switch (exif_imagetype($original_src)) {
	case 1 :
		if (is_animated_gif($original_src)) {
			copy($original_src,$thumb_src);
			return;
		}
		$original = imagecreatefromgif($original_src);
		break;
	case 2 :
		$original = imagecreatefromjpeg($original_src);
		break;
	case 3 :
		if (in_array(ord(@file_get_contents($original_src, NULL, NULL, 25, 1)), array(3,6))) {
			copy($original_src,$thumb_src);
			return;
		}
		$original = imagecreatefrompng($original_src);
		break;
	default :
		return;
	}
	$w = imagesx($original);
	$h = imagesy($original);
	$thumb = imagecreatetruecolor($w,$h);
	imagesavealpha($thumb, true);
	$transparent = imagecolorallocatealpha($thumb, 0,0,0, 127);
	imagefill($thumb, 0,0, $transparent);
	imagecopymerge($thumb,$original, 0,0, 0,0, $w,$h, 100);
	imagedestroy($original);
	imagepng($thumb, $thumb_src);
	imagedestroy($thumb);
}
function is_animated_gif($filename) {
    if(!($fh = @fopen($filename, 'rb')))
        return false;
    $count = 0;

    while (!feof($fh) && $count < 2) {
        $chunk = fread($fh, 1024 * 100); //read 100kb at a time
        $count += preg_match_all('#\x00\x21\xF9\x04.{4}\x00[\x2C\x21]#s', $chunk, $matches);
    }

    fclose($fh);
    return $count > 1;
}
function image_create_from($src) {
	switch (exif_imagetype($src)) {
	case 1 :
		return imagecreatefromgif($src);
	case 2 :
		return imagecreatefromjpeg($src);
	case 3 :
		return imagecreatefrompng($src);
	}
}
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
function imagealphamask(&$img, $r,$g,$b,$a) {
	$w = imagesx($img);
	$h = imagesy($img);
	$res = imagecreatetruecolor($w,$h);
	imagesavealpha($res, true);
	$transparent = imagecolorallocatealpha($res, $r,$g,$b, 127);
	imagefill($res, 0,0, $transparent);
	imagecopy($res,$img, 0,0, 0,0, $w,$h);
	imagedestroy($img);
	$img = imagecreatetruecolor($w,$h);
	$c = imagecolorallocate($img, $r,$g,$b);
	imagefill($img, 0,0, $c);
	imagecopymerge($img,$res, 0,0, 0,0, $w,$h, $a);
	imagecolortransparent($img, $c);
	imagecopymerge($res,$img, 0,0, 0,0, $w,$h, 100);
	imagedestroy($img);
	return $res;
}
function filter_img($original_src,$thumb_src, $r,$g,$b,$a) {
	$thumb = image_create_from($original_src);
	$thumb = imagealphamask($thumb, $r,$g,$b,$a);
	imagepng($thumb, $thumb_src);
	imagedestroy($thumb);
}
function has_transparency($src) {
	$type = ord(@file_get_contents($src, NULL, NULL, 25, 1));
	switch ($type) {
	case 2 :
	case 3 :
	case 4 :
		$imgdata = imagecreatefrompng($src);
		return (imagecolortransparent($imgdata) != -1);
	case 6 :
		$imgdata = imagecreatefrompng($src);
		$w = imagesx($imgdata);
		$h = imagesy($imgdata);

		if ($w>50 || $h>50){ //resize the image to save processing if larger than 50px:
			$thumb = imagecreatetruecolor(10, 10);
			imagealphablending($thumb, FALSE);
			imagecopyresized($thumb, $imgdata, 0, 0, 0, 0, 10, 10, $w, $h );
			$imgdata = $thumb;
			$w = imagesx($imgdata);
			$h = imagesy($imgdata);
		}
		for ($i=0;$i<$w;$i++) {
			for($j=0;$j<$h;$j++) {
				$rgba = imagecolorat($imgdata, $i, $j);
				if(($rgba & 0x7F000000) >> 24)
					return true;
			}
		}
		return false;
	default :
		if ('image/gif' === mime_content_type($src))
			return true;
		return false;
	}
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
	if (!file_exists($res['map']))
		$res['map'] = $res['ld'];
	if (!file_exists($res['podium']))
		$res['podium'] = $res['ld'];
	return $res;
}
function get_perso_music($perso) {
	if ($perso['music'])
		return $perso['music'];
	return 'mario';
}
function add_transparency($original_src,$thumb_src, $r,$g,$b) {
	$original = imagecreatefrompng($original_src);
	$w = imagesx($original);
	$h = imagesy($original);
	$thumb = imagecreatetruecolor($w,$h);
	$c = imagecolorallocate($thumb, $r,$g,$b);
	imagefill($thumb, 0,0, $c);
	imagecopy($thumb,$original, 0,0, 0,0, $w,$h);
	imagedestroy($original);
	imagecolortransparent($thumb, $c);
	imagepng($thumb, $thumb_src);
	imagedestroy($thumb);
}
function create_sprite_thumbs($spriteSrcs) {
	list($w,$h) = getimagesize($spriteSrcs['hd']);
	$w = round($w/24);
	resize_sprites($spriteSrcs['hd'],$spriteSrcs['ld'], $w,$h);
	filter_img($spriteSrcs['hd'],$spriteSrcs['star'], 255,255,0,30);
}
function delete_sprite_imgs($spriteSrcs) {
	foreach ($spriteSrcs as $key => $oldSrc)
		@unlink($oldSrc);
}
function move_sprite_imgs($oldSrcs, $filehash) {
	foreach ($oldSrcs as $key => $oldSrc)
		@rename($oldSrc, preg_replace('#cp-\w+-\d+(-\w+)?\.png$#', $filehash.'$1.png', $oldSrc));
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
		$poids = $file['size'];
		if ($poids < 1000000) {
			$poids += file_total_size($perso ? array('perso'=>$perso):array());
			if ($poids < MAX_FILE_SIZE) {
				$infosfichier = pathinfo($file['name']);
				$ext = strtolower($infosfichier['extension']);
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
								move_uploaded_file($file['tmp_name'], $spriteSrcs['tmp']);
								clone_img_resource($spriteSrcs['tmp'],$spriteSrcs['hd']);
								unlink($spriteSrcs['tmp']);
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
				else $error = $language ? 'Your image must have a png, gif, jpg or jpeg extension.':'Votre image doit &ecirc;tre au format png, gif, jpg ou jpeg.';
			}
			else $error = $language ? 'You have exceeded your quota of '.filesize_str(MAX_FILE_SIZE).'. Delete characters or circuits to free space.':'Vous avez d&eacute;pass&eacute; votre quota de '.filesize_str(MAX_FILE_SIZE).'. Supprimez des persos ou des circuits pour lib&eacute;rer de l\'espace disque.';
		}
		else $error = $language ? 'Your image mustn\'t exceed 1 Mo. Compress or reduce it if necessary.':'Votre image ne doit pas d&eacute;passer 1 Mo. Compressez-la ou r&eacute;duisez la taille si n&eacute;cessaire.';
	}
	else $error = $language ? 'An error occured during the image transfer. Please try again later.':'Une erreur est survenue lors de l\'envoi de l\'image. R&eacute;essayez ult&egrave;rieurement.';
	return array('error' => $error);
}
function handle_advanced($file,$perso,$type) {
	global $language, $identifiants;
	if (!$file['error']) {
		$poids = $file['size'];
		if ($poids < 1000000) {
			$infosfichier = pathinfo($file['name']);
			$ext = strtolower($infosfichier['extension']);
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
				move_uploaded_file($file['tmp_name'], $spriteSrcs['tmp']);
				switch ($type) {
				case 'map':
					$spriteW = 18;
					$spriteH = 18;
					break;
				case 'podium':
					$spriteW = 30;
					$spriteH = 45;
				}
				resize_img_resource($spriteSrcs['tmp'],$spriteSrcs[$type], $spriteW,$spriteH);
				unlink($spriteSrcs['tmp']);
				update_sprite_src($perso['sprites'],$filehash);
				return array('id' => $id);
			}
			else $error = $language ? 'Your image must have a png, gif, jpg or jpeg extension.':'Votre image doit &ecirc;tre au format png, gif, jpg ou jpeg.';
		}
		else $error = $language ? 'Your image mustn\'t exceed 1 Mo. Compress or reduce it if necessary.':'Votre image ne doit pas d&eacute;passer 1 Mo. Compressez-la ou r&eacute;duisez la taille si n&eacute;cessaire.';
	}
	else $error = $language ? 'An error occured during the image transfer. Please try again later.':'Une erreur est survenue lors de l\'envoi de l\'image. R&eacute;essayez ult&egrave;rieurement.';
	return array('error' => $error);
}
function resize_img_resource($original_src,$thumb_src, $minw,$minh) {
	list($width, $height) = getimagesize($original_src);
	if ($width*$minh > $height*$minw) {
		$newHeight = $minh;
		$newWidth = round($minh*$width/$height);
	}
	else {
		$newWidth = $minw;
		$newHeight = round($minw*$height/$width);
	}
	if (($newWidth >= $width) && ($newHeight >= $height)) {
		$newWidth = $width;
		$newHeight = $height;
	}
	if (is_animated_gif($original_src)) {
		$temp_dir = tempnam(sys_get_temp_dir(),'mk');
		if (file_exists($temp_dir)) @unlink($temp_dir);
		@mkdir($temp_dir);
		$temp_file = $temp_dir.'/coalesce.gif';

		$wh = $width.'x'.$height;
		$newWH = $newWidth.'x'.$newHeight;
		$thumb_gif = preg_replace("#png$#", 'gif', $thumb_src);
		system("convert $original_src -coalesce $temp_file");
		system("convert -size $wh $temp_file -resize $newWH $thumb_gif");
		rename($thumb_gif, $thumb_src);
		@unlink($temp_file);
		@rmdir($temp_dir);
	}
	else {
		$thumb = imagecreatetruecolor($newWidth,$newHeight);
		imagesavealpha($thumb, true);
		$transparent = imagecolorallocatealpha($thumb, $r,$g,$b, 127);
		imagefill($thumb, 0,0, $transparent);

		$source = image_create_from($original_src);

		imagecopyresized($thumb, $source, 0,0, 0,0, $newWidth,$newHeight, $width, $height);

		imagepng($thumb, $thumb_src);
		imagedestroy($thumb);
		imagedestroy($source);
	}
}
?>