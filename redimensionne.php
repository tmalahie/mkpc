<?php
if (isset($_POST['id']) && isset($_POST['x']) && isset($_POST['y']) && ($_POST['x'] > 0) && ($_POST['y'] > 0) && ($_POST['x'] < 10000) && ($_POST['y'] < 10000)) {
	include('escape_all.php');
	$id = $_POST['id'];
	$src = isset($_POST['arenes']) ? 'course':'map';
	$db = isset($_POST['arenes']) ? 'arenes':'circuits';
	$isrc = isset($_POST['arenes']) ? 'coursepreview':'racepreview';
	include('getId.php');
	include('initdb.php');
	if (mysql_numrows(mysql_query('SELECT * FROM `'. $db .'` WHERE id="'.$id.'" AND identifiant='.$identifiants[0].' AND identifiant2='.$identifiants[1].' AND identifiant3='.$identifiants[2].' AND identifiant4='.$identifiants[3]))) {
		include('getExt.php');
		$ext2 = str_replace('jpg', 'jpeg', $ext);
		header('Content-type: image/'.$ext2);
		eval('$source = imagecreatefrom'.$ext2.'("images/uploads/$src$id.$ext");');
		$image = Array(imagesx($source), imagesy($source));
		$dimensions = Array($_POST['x'], $_POST['y']);
		$destination = imagecreatetruecolor($dimensions[0], $dimensions[1]);
		imagecopyresampled($destination, $source, 0, 0, 0, 0, $dimensions[0], $dimensions[1], $image[0], $image[1]);

		eval('image'.$ext2.'($destination, "images/uploads/$src$id.$ext");');

		$aExt = $ext;
		$poids = filesize("images/uploads/$src$id.$ext");
		include('file-quotas.php');
		$poids += file_total_size();
		if ($poids > MAX_FILE_SIZE)
			eval('image'.$ext2.'($source, "images/uploads/$src$id.$aExt");');

		include('cache_creations.php');
		@unlink(cachePath($isrc.$id.'.png'));

		header('Location: changeMap.php?i='.$id.(isset($_POST['arenes']) ? '&arenes=1':'').'&x='.($dimensions[0]/$image[0]).'&y='.($dimensions[1]/$image[1]));
	}
	mysql_close();
}
?>