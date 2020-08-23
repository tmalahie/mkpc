<?php
if (isset($_GET['id'])) {
	$decorId = $_GET['id'];
	include('initdb.php');
	if ($decor = mysql_fetch_array(mysql_query('SELECT * FROM `mkdecors` WHERE id="'. $decorId .'"'))) {
		include('getId.php');
		if ($decor['identifiant'] == $identifiants[0]) {
			include('language.php');
			include('utils-decors.php');
            $decorSrcs = decor_sprite_srcs($decor['sprites']);
			if (isset($_POST['name'])) {
                $decor['name'] = preg_replace('#<[^>]+>#', '', $_POST['name']);
                mysql_query('UPDATE `mkdecors` SET name="'. $decor['name'] .'" WHERE id="'. $_GET['id'] .'"');
            }
            $spriteSizes = decor_sprite_sizes($decor['type']);
			?>
<!DOCTYPE html>
<html lang="<?php echo $language ? 'en':'fr'; ?>">
<head>
<meta charset="utf-8" />
<meta name="viewport" content="width=device-width, initial-scale=1">
<link rel="shortcut icon" type="image/x-icon" href="images/favicon.ico" />
<link rel="stylesheet" href="styles/editor.css" />
<link rel="stylesheet" href="styles/decor-editor.css" />
<title><?php echo $language ? 'Decor editor':'Éditeur de décors'; ?></title>
</head>
<body>
	<?php
	if (isset($_GET['new'])) {
		?>
		<p id="success"><?php echo $language ? "Your decor has been created":"Votre décor a été créé"; ?></p>
		<?php
	}
	elseif (isset($_POST['name'])) {
		?>
		<p id="success"><?php echo $language ? 'Decor renamed to &quot;'. $decor['name'] .'&quot;':'Décor renommé en &quot;'. $decor['name'] .'&quot;'; ?></p>
		<?php
	}
	else {
		?>
		<h2><?php echo $language ? "Edit a decor":"Modifier un décor"; ?></h2>
		<?php
	}
	?>
    <div class="decors-list-container">
        <div class="decor-edit-preview">
            <div class="decor-preview" style="width:<?php echo $spriteSizes['ld']['w']; ?>px;height:<?php echo $spriteSizes['ld']['h']; ?>px">
                <img src="<?php echo $decorSrcs['hd'] ?>" alt="<?php echo htmlspecialchars($decor['name']); ?>" />
            </div>
            <a href="decorSprite.php?id=<?php echo $_GET['id']; ?>"><?php echo $language ? 'Edit image':'Modifier l\'image'; ?></a>
        </div>
        <form method="post" id="decor-edit-form" class="decor-editor-form" action="editDecor.php?id=<?php echo $_GET['id']; ?>">
            <label for="name"><?php echo $language ? 'Name for your decor (optional):':'Nom pour votre décor (facultatif) :'; ?></label>
            <input type="text" maxlength="30" name="name" id="name" placeholder="<?php echo $language ? 'Red pipe':'Tuyau rouge'; ?>" value="<?php echo htmlspecialchars($decor['name']); ?>" />
            <button type="submit">Ok</button>
            <div class="advances-options">
                +<a href="decorOptions.php?id=<?php echo $_GET['id']; ?>">
                    <?php echo $language ? 'Advanced options':'Options avancées'; ?>...
                </a>
            </div>
        </form>
    </div>
    <div class="editor-navigation">
        <a href="decorEditor.php">&lt; <u><?php echo $language ? "Back to decor editor":"Retour à l'éditeur de décors"; ?></u></a>
    </div>
    <script type="text/javascript">
    function showDecorPreview($div) {
        var $img = $div.querySelector("img");
        var left = 0, contentWidth, totalWidth;
        function rotateImg() {
            left += contentWidth;
            if (left >= totalWidth)
                left = 0;
            $img.style.left = -left +"px";
        }
        $img.onload = function() {
            $img.onload = undefined;
            contentWidth = $div.offsetWidth;
            totalWidth = $img.offsetWidth;
            if (contentWidth >= totalWidth)
                $img.style.left = Math.round((contentWidth-totalWidth)/2) +"px";
            else
                setInterval(rotateImg,500);
        }
        if ($img.naturalHeight && $img.complete)
            $img.onload();
    }
    showDecorPreview(document.querySelector(".decor-preview"));
    </script>
</body>
</html>
		<?php
		}
	}
	mysql_close();
}
?>