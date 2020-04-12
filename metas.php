<?php
if (!isset($hthumbnail))
	$hthumbnail = 'https://mkpc.malahieude.net/images/screenshots/ss1.png';
?>
<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1">
<meta name="author" content="Timothé Malahieude" />
<meta name="description" content="Jeu de Mario Kart gratuit en ligne" />
<meta name="keywords" content="<?php echo $language ?
	'Mario, Kart, PC, game, race, free game, multiplayer' :
	'Mario, Kart, PC, jeu, course, jeu gratuit, multijoueur';
	?>" />
<meta name="viewport" content="width=device-width, user-scalable=no" />
<?php
if ($hthumbnail) {
	?>
<meta name="thumbnail" content="<?php echo $hthumbnail; ?>" />
<meta property="og:image" content="<?php echo $hthumbnail; ?>" />
	<?php
}
?>
<link rel="shortcut icon" type="image/x-icon" href="images/favicon.ico" />