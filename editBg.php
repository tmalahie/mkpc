<?php
if (isset($_GET['id'])) {
	$bgId = $_GET['id'];
	include('initdb.php');
	if ($bg = mysql_fetch_array(mysql_query('SELECT * FROM `mkbgs` WHERE id="'. $bgId .'"'))) {
		include('getId.php');
		if ($bg['identifiant'] == $identifiants[0]) {
			include('language.php');
			include('utils-bgs.php');
            session_start();
            include('tokens.php');
			if (isset($_POST['name'])) {
                $bg['name'] = preg_replace('#<[^>]+>#', '', $_POST['name']);
                mysql_query('UPDATE `mkbgs` SET name="'. $bg['name'] .'" WHERE id="'. $_GET['id'] .'"');
            }
			?>
<!DOCTYPE html>
<html lang="<?php echo $language ? 'en':'fr'; ?>">
<head>
<meta charset="utf-8" />
<meta name="viewport" content="width=device-width, initial-scale=1">
<link rel="shortcut icon" type="image/x-icon" href="images/favicon.ico" />
<link rel="stylesheet" href="styles/editor.css" />
<link rel="stylesheet" href="styles/bg-editor.css" />
<title><?php echo $language ? 'Background editor':'Éditeur d\'arrière-plans'; ?></title>
</head>
<body>
	<?php
    if (isset($_GET['error'])) {
        echo '<div id="error">'. $_GET['error'] .'</div>';
    }
	if (isset($_GET['new'])) {
		?>
		<p id="success"><?php echo $language ? "Your background has been created":"Votre arrière-plan a été créé"; ?></p>
		<?php
	}
	elseif (isset($_POST['name'])) {
		?>
		<p id="success"><?php echo $language ? 'Background renamed to &quot;'. $bg['name'] .'&quot;':'Arrière-plan renommé en &quot;'. $bg['name'] .'&quot;'; ?></p>
		<?php
	}
	else {
		?>
		<h2><?php echo $language ? "Edit a background":"Modifier un arrière-plan"; ?></h2>
		<?php
	}
	?>
    <div class="bgs-list-container">
        <div class="bg-edit-preview">
            <?php
            print_bg_div($bg['id']);
            ?>
            <a href="bgSprite.php?id=<?php echo $_GET['id']; ?>"><?php echo $language ? 'Edit image':'Modifier l\'image'; ?></a>
        </div>
        <form method="post" id="bg-edit-form" class="bg-editor-form" action="editBg.php?id=<?php echo $_GET['id']; ?>">
            <label for="name"><?php echo $language ? 'Name for your background (optional):':'Nom pour votre arrière-plan (facultatif) :'; ?></label>
            <input type="text" maxlength="30" name="name" id="name" placeholder="<?php echo $language ? 'Sunset':'Coucher de soleil'; ?>" value="<?php echo htmlspecialchars($bg['name']); ?>" />
            <button type="submit">Ok</button>
        </form>
    </div>
    <div class="editor-navigation">
        <a href="bgEditor.php">&lt; <u><?php echo $language ? "Back to background editor":"Retour à l'éditeur d'arrière-plans"; ?></u></a>
    </div>
    <script type="text/javascript">
    function showBgPreview($div) {
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
            if (contentWidth < totalWidth)
                setInterval(rotateImg,500);
        }
        if ($img.naturalHeight && $img.complete)
            $img.onload();
    }
    showBgPreview(document.querySelector(".bg-preview"));
    </script>
</body>
</html>
		<?php
		}
	}
	mysql_close();
}
?>