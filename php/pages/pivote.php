<?php
if (isset($_POST['id'])) {
	include('../includes/escape_all.php');
	$id = $_POST['id'];
	$src = isset($_POST['arenes']) ? 'course':'map';
	$db = isset($_POST['arenes']) ? 'arenes':'circuits';
	$isrc = isset($_POST['arenes']) ? 'coursepreview':'racepreview';
	include('../includes/getId.php');
	include('../includes/initdb.php');
	require_once('../includes/collabUtils.php');
	$requireOwner = !hasCollabGrants($db, $id, $_POST['collab'], 'edit');
	if ($circuit = mysql_fetch_array(mysql_query('SELECT id,img_data,identifiant,identifiant2,identifiant3,identifiant4 FROM `'.$db.'` WHERE id="'.$id.'"'. ($requireOwner ? (' AND identifiant='.$identifiants[0].' AND identifiant2='.$identifiants[1].' AND identifiant3='.$identifiants[2].' AND identifiant4='.$identifiants[3]) : '')))) {
		require_once('../includes/circuitImgUtils.php');
		$circuitImg = json_decode($circuit['img_data']);
		if (!$circuitImg->local)
			exit;
		$path = CIRCUIT_BASE_PATH.$circuitImg->url;
		$ext = $circuitImg->ext;
		$ext2 = str_replace('jpg', 'jpeg', $ext);
		header('Content-type: image/'.$ext2);
		eval('$source = imagecreatefrom'.$ext2.'("$path");');
		$image = Array(imagesx($source), imagesy($source));
		if ($_POST['pivot'] < 3)
			$destination = imagerotate($source, (3-$_POST['pivot'])*90, 0);
		else {
			$destination = imagecreatetruecolor($image[0], $image[1]);
			imagealphablending($destination, false);
			imagesavealpha($destination, true);
			if ($_POST['pivot'] == 4) {
				for ($i=0;$i<$image[1];$i++)
					imagecopy($destination, $source, 0, ($image[1] - $i - 1), 0, $i, $image[0], 1);
			}
			else {
				for ($i=0;$i<$image[0];$i++)
					imagecopy($destination, $source, ($image[0] - $i - 1), 0, $i, 0, 1, $image[1]);
			}
		}

		$oldUrl = $circuitImg->url;
		$circuitImg->url = $src.$id.'-'.time().'.'.$ext;
		$newPath = CIRCUIT_BASE_PATH.$circuitImg->url;
		eval('image'.$ext2.'($destination, "$newPath");');

		include('../includes/file-quotas.php');
		$ownerIds = array($circuit['identifiant'],$circuit['identifiant2'],$circuit['identifiant3'],$circuit['identifiant4']);
		$poids = file_total_size(array('identifiants'=> $ownerIds));
		if ($poids > file_total_quota($circuit)) {
			@unlink($newPath);
			$circuitImg->url = $oldUrl;
		}
		else {
			@unlink($path);
			mysql_query('UPDATE `'.$db.'` SET img_data="'. getCircuitImgDataRaw($newPath,$circuitImg->url,1) .'" WHERE id="'.$id.'"');
			require_once('../includes/cache_creations.php');
			@unlink(cachePath($isrc.$id.'.png'));
		}

		$collabSuffix = isset($_POST['collab']) ? '&collab='.$_POST['collab'] : '';
		header('Location: changeMap.php?i='.$id.(isset($_POST['arenes']) ? '&arenes=1':'').'&pivot='.$_POST['pivot'].$collabSuffix);
	}
	mysql_close();
}
?>