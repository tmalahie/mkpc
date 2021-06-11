<?php
if (!isset($hthumbnail))
	$hthumbnail = 'https://mkpc.malahieude.net/images/screenshots/ss1.png';
if (isset($hdescription)) {
	function hControlLength($str,$maxLength) {
		$pts = '...';
		if (mb_strlen($str) > $maxLength)
			return mb_substr($str,0,$maxLength-mb_strlen($pts)).$pts;
		return $str;
	}
	if (is_string($hdescription))
		$hdescription = htmlspecialchars(hControlLength(str_replace("\n"," ",preg_replace("#[\r\t]#", '', $hdescription)), 200));
}
else
	$hdescription = $language ? 'Free online Mario Kart game':'Jeu de Mario Kart gratuit en ligne';
?>
<meta charset="utf-8" />
<meta name="viewport" content="width=device-width, initial-scale=1">
<meta name="author" content="Timothé Malahieude" />
<?php
if ($hdescription) {
	?>
<meta name="description" content="<?php echo htmlspecialchars($hdescription); ?>" />
	<?php
}
?>
<meta name="keywords" content="<?php echo $language ? 'Mario, Kart, PC, game, race, free game, multiplayers, circuits editor':'Mario, Kart, PC, jeu, course, jeu gratuit, multijoueurs, éditeur de circuits'; ?>" />
<?php
if ($hthumbnail) {
	?>
<meta name="thumbnail" content="<?php echo $hthumbnail; ?>" />
<meta property="og:image" content="<?php echo $hthumbnail; ?>" />
	<?php
}
?>
<link rel="stylesheet" href="styles/main.css?reload=4" />
<link rel="stylesheet" href="styles/main-mountains2.css" />
<link rel="stylesheet" media="only screen and (max-width: 800px)" href="styles/mobile.css" />
<link rel="stylesheet" media="only screen and (min-width: 800px)" href="styles/computer.css" />
<link rel="stylesheet" href="styles/creations.css" />
<link rel="shortcut icon" type="image/x-icon" href="images/favicon.ico" />
<script src="scripts/main.js"></script>