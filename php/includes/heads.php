<?php
// Set default thumbnail if not provided
if (!isset($hthumbnail)) {
    $hthumbnail = 'https://mkpc.malahieude.net/images/screenshots/ss1.png';
}

require_once('utils-description.php');
$hdescription = formatDescription($hdescription);

// Set language-based keywords
$keywords = $language ? 
    'Mario, Kart, PC, game, race, free game, multiplayer, circuits editor' : 
    'Mario, Kart, PC, jeu, course, jeu gratuit, multijoueur, éditeur de circuits';
?>

<meta charset="utf-8" />
<meta name="viewport" content="width=device-width, initial-scale=1">
<meta name="author" content="Timothé Malahieude" />

<?php if ($hdescription): ?>
    <meta name="description" content="<?= $hdescription ?>" />
<?php endif; ?>

<meta name="keywords" content="<?= $keywords ?>" />

<?php if ($hthumbnail): ?>
    <meta name="thumbnail" content="<?= $hthumbnail ?>" />
    <meta property="og:image" content="<?= $hthumbnail ?>" />
<?php endif; ?>

<link rel="stylesheet" href="styles/main.css?reload=0" />
<link rel="stylesheet" href="styles/creations.css" />
<link rel="shortcut icon" type="image/x-icon" href="images/favicon.ico" />

<script async src="scripts/main.js"></script>
