<?php
include('language.php');
include('uploadByUrl.php');
if (isset($_FILES['image'])) {
	if (!$_FILES['image']['error']) {
		$poids = $_FILES['image']['size'];
		if ($poids < 1000000) {
			include('file-quotas.php');
			include('getId.php');
			include('initdb.php');
			$poids += file_total_size();
			$id = 0;
			if ($poids < MAX_FILE_SIZE) {
				$fileType = mime_content_type($_FILES['image']['tmp_name']);
				$extensions = array(
					'image/png' => 'png',
					'image/gif' => 'gif',
					'image/jpeg' => 'jpg'
				);
				if (isset($extensions[$fileType])) {
					$ext = $extensions[$fileType];
					mysql_query('INSERT INTO `circuits` SET identifiant='.$identifiants[0].',identifiant2='.$identifiants[1].',identifiant3='.$identifiants[2].',identifiant4='.$identifiants[3]);
					$id = mysql_insert_id();
					move_given_file($_FILES['image']['tmp_name'], 'images/uploads/map'.$id.'.'.$ext);
					mysql_close();
					header('Location: draw.php?i='.$id.'&uploaded=1');
					exit;
				}
				else $error = $language ? 'Your image must have a png, gif, or jpg extension.':'Votre image doit être au format png, gif ou jpg.';
			}
			else $error = $language ? 'You have exceeded your quota of '.filesize_str(MAX_FILE_SIZE).'. Delete courses to free space.':'Vous avez dépassé votre quota de '.filesize_str(MAX_FILE_SIZE).'. Supprimez des arènes pour libérer de l\'espace disque.';
			mysql_close();
		}
		else $error = $language ? 'Your image mustn\'t exceed 1 Mo. Compress or reduce it if necessary.':'Votre image ne doit pas dépasser 1 Mo. Compressez-la ou réduisez la taille si nécessaire.';
	}
	else $error = $language ? 'An error occured during the image transfer. Please try again later.':'Une erreur est survenue lors de l\'envoi de l\'image. Réessayez ultèrieurement.';
}
if (isset($error))
	header('Location: draw.php?error='.urlencode($error));
?>