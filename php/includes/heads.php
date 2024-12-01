<?php
if (!isset($hthumbnail))
	$hthumbnail = 'https://mkpc.malahieude.net/images/screenshots/ss1.png';
require_once('utils-description.php');
$hdescription = formatDescription($hdescription);
?>
<meta charset="utf-8" />
<meta name="viewport" content="width=device-width, initial-scale=1">
<meta name="author" content="Timothé Malahieude" />
<?php
if ($hdescription) {
	?>
<meta name="description" content="<?php echo $hdescription; ?>" />
	<?php
}
?>
<meta name="keywords" content="<?php echo $language ? 'Mario, Kart, PC, game, race, free game, multiplayer, circuits editor':'Mario, Kart, PC, jeu, course, jeu gratuit, multijoueur, éditeur de circuits'; ?>" />
<?php
if ($hthumbnail) {
	?>
<meta name="thumbnail" content="<?php echo $hthumbnail; ?>" />
<meta property="og:image" content="<?php echo $hthumbnail; ?>" />
	<?php
}
?>
<link rel="stylesheet" href="styles/main.css?reload=1" />
<?php
require_once('advent-topic.php');
if ($adventEnabled)
	echo '<link rel="stylesheet" href="styles/advent-calendar.css" />';
?>
<link rel="stylesheet" media="only screen and (max-width: 800px)" href="styles/mobile.css" />
<link rel="stylesheet" media="only screen and (min-width: 800px)" href="styles/computer.css" />
<link rel="stylesheet" href="styles/creations.css" />
<link rel="shortcut icon" type="image/x-icon" href="images/favicon.ico" />
<script async src="scripts/main.js"></script>