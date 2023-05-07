<?php
if (isset($_GET['id'])) {
	include('initdb.php');
	$persoId = intval($_GET['id']);
	if ($perso = mysql_fetch_array(mysql_query('SELECT * FROM `mkchars` WHERE id="'. $persoId .'"'))) {
		include('language.php');
		include('getId.php');
        require_once('collabUtils.php');
        $collabSuffix = '';
        if (($perso['identifiant'] == $identifiants[0]) && ($perso['identifiant2'] == $identifiants[1]) && ($perso['identifiant3'] == $identifiants[2]) && ($perso['identifiant4'] == $identifiants[3])) {
            $hasReadGrants = true;
            $hasWriteGrants = true;
        }
        else {
            $collab = getCollabLinkFromQuery('mkchars', $persoId);
            $hasReadGrants = isset($collab['rights']['view']);
            $hasWriteGrants = isset($collab['rights']['edit']);
            if ($collab) $collabSuffix = '&amp;collab='. $collab['key'];
        }
        if ($hasReadGrants) {
			require_once('persos.php');
			$spriteSrcs = get_sprite_srcs($perso['sprites']);
			?>
<!DOCTYPE html>
<html lang="<?php echo $language ? 'en':'fr'; ?>">
<head>
<meta charset="utf-8" />
<meta name="viewport" content="width=device-width, initial-scale=1">
<link rel="shortcut icon" type="image/x-icon" href="images/favicon.ico" />
<link rel="stylesheet" href="styles/perso-editor.css?reload=1" />
<?php
include('o_online.php');
?>
<title><?php echo $language ? 'Character editor':'Éditeur de persos'; ?></title>
</head>
<body<?php if (!$hasWriteGrants) echo ' class="readonly"'; ?>>
	<h1><?php echo $language ? 'Advanced options':'Options avancées'; ?></h1>
	<div>
		<a class="advanced-option option-map" href="<?php
		echo $hasWriteGrants ? 'editSprite.php?id='. urlencode($_GET['id']) . $collabSuffix .'&amp;map' : 'javascript:void(0)';
		?>">
			<div class="option-bg">
				<img src="images/maps/map1.png" alt="Map" />
				<img src="<?php echo $spriteSrcs['map']; ?>" alt="Perso" />
			</div>
			<div class="option-label"><?php
			if ($hasWriteGrants)
				echo $language ? "Edit minimap icon":"Modifier l'icone de la mini-map";
			else
				echo $language ? "Minimap icon":"Icone mini-map";
			?></div>
		</a>
		<a class="advanced-option option-podium" href="<?php
		echo $hasWriteGrants ? 'editSprite.php?id='. urlencode($_GET['id']) . $collabSuffix .'&amp;podium' : 'javascript:void(0)';
		?>">
			<div class="option-bg">
				<img src="images/podium.gif" alt="Podium" />
				<img src="<?php echo $spriteSrcs['podium']; ?>" alt="Perso" />
			</div>
			<div class="option-label"><?php
			if ($hasWriteGrants)
				echo $language ? "Edit Grand Prix image":"Modifier l'image de fin de grand prix";
			else
				echo $language ? "Grand Prix image":"Image de grand prix";
			?></div>
		</a>
		<a class="advanced-option option-ending" href="persoMusic.php?id=<?php echo urlencode($_GET['id']) . $collabSuffix; ?>">
			<div class="option-bg">
				<img src="images/end_music.png" alt="Ending" />
				<div><img src="<?php echo $spriteSrcs['hd']; ?>" alt="Perso" onload="this.parentNode.style.height=Math.round(576*this.naturalHeight/this.naturalWidth)+'px'" /></div>
				<img src="images/ic_endmusic.png" alt="Music" />
			</div>
			<div class="option-label"><?php
			if ($hasWriteGrants)
				echo $language ? "Edit end race music":"Modifier la musique de fin de course";
			else
				echo $language ? "End race music":"Musique de fin de course";
			?></div>
		</a>
	</div>
	<p><a href="editPerso.php?id=<?php echo urlencode($_GET['id']) . $collabSuffix; ?>"><?php echo $language ? "Back to character editor":"Retour à l'édition du perso"; ?></a></p>
</body>
</html>
		<?php
		}
	}
	mysql_close();
}
?>