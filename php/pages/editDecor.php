<?php
if (isset($_GET['id'])) {
    $decorId = intval($_GET['id']);
    include('../includes/initdb.php');
    if ($decor = mysql_fetch_array(mysql_query('SELECT * FROM `mkdecors` WHERE id="'. $decorId .'"'))) {
        include('../includes/getId.php');
        require_once('../includes/collabUtils.php');
        $collabSuffix = '';
        if ($decor['identifiant'] == $identifiants[0]) {
            $hasReadGrants = true;
            $hasWriteGrants = true;
        }
        else {
            $collab = getCollabLinkFromQuery('mkdecors', $decor['extra_parent_id'] ?? $decorId);
            $hasReadGrants = isset($collab['rights']['view']);
            $hasWriteGrants = isset($collab['rights']['edit']);
            if ($collab) $collabSuffix = '&amp;collab='. $collab['key'];
        }
        if ($hasReadGrants) {
            include('../includes/language.php');
            include('../includes/utils-decors.php');
            session_start();
            include('../includes/tokens.php');
            $decorSrcs = get_decor_srcs($decor);
            if (isset($_POST['name']) && $hasWriteGrants) {
                $decor['name'] = preg_replace('#<[^>]+>#', '', $_POST['name']);
                mysql_query('UPDATE `mkdecors` SET name="'. $decor['name'] .'" WHERE id="'. $_GET['id'] .'"');
            }
            $spriteSizes = get_decor_sizes($decor);
            $imgW = $spriteSizes['ld']['w'];
            $imgH = $spriteSizes['ld']['h'];
            $mW = 32;
            $imgL = $imgH;
            if ($imgL < $mW) {
                $rescale = $imgL ? round($mW/$imgL) : 1;
                $imgW *= $rescale;
                $imgH *= $rescale;
            }
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
    if (isset($_GET['error'])) {
        echo '<div id="error">'. htmlspecialchars($_GET['error']) .'</div>';
    }
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
            <div class="decor-preview" style="width:<?php echo $imgW; ?>px;height:<?php echo $imgH; ?>px">
                <img src="<?php echo $decorSrcs['hd'] ?>" alt="<?php echo htmlspecialchars($decor['name']); ?>" />
            </div>
            <?php
            if ($hasWriteGrants)
                echo '<a href="decorSprite.php?id='. urlencode($_GET['id']) . $collabSuffix .'">'. ($language ? 'Edit image':'Modifier l\'image') .'</a>';
            if (isset($CUSTOM_DECOR_TYPES[$decor['type']]['extra_sprites'])) {
                echo '<div class="decor-edit-extra">';
                /** @var array $extraSprites */
                $extraSprites = $CUSTOM_DECOR_TYPES[$decor['type']]['extra_sprites'];
                $i = 1;
                foreach ($extraSprites as $extraSprite) {
                    $i++;
                    echo '<div class="decor-extra">';
                    echo 'Décor #'. $i .'&nbsp;:';
                    if ($extraDecor = mysql_fetch_array(mysql_query('SELECT * FROM `mkdecors` WHERE extra_parent_id="'. $decorId .'"'))) {
                        $decorSrcs = get_decor_srcs($extraDecor);
                        $spriteSizes = get_decor_sizes($extraDecor);
                        $imgW = $spriteSizes['ld']['w'];
                        $imgH = $spriteSizes['ld']['h'];
                        ?>
                        <div class="decor-preview" style="width:<?php echo $imgW; ?>px;height:<?php echo $imgH; ?>px">
                            <img src="<?php echo $decorSrcs['hd'] ?>" alt="<?php echo htmlspecialchars($extraSprite); ?>" />
                        </div>
                        <a class="decor-edit" href="editDecor.php?id=<?php echo $extraDecor['id'] . $collabSuffix; ?>"><?php echo $language ? 'Edit':'Modifier'; ?></a>
                        <a class="decor-del" href="delDecor.php?id=<?php echo $extraDecor['id'] . $collabSuffix; ?>&amp;token=<?php echo $_SESSION['csrf']; ?>" onclick="return confirm('<?php echo $language ? 'Delete decor?':'Supprimer le décor ?' ?>')"><?php echo $language ? 'Delete':'Supprimer'; ?></a>
                        <?php
                    }
                    else {
                        $baseSrc = 'images/sprites/sprite_'. $extraSprite .'.png';
                        list($imgW, $imgH) = getimagesize('../../'.$baseSrc);
                        $extraData = $CUSTOM_DECOR_TYPES[$extraSprite];
                        if (isset($extraData['nbsprites']))
                            $imgW = round($imgW/$extraData['nbsprites']);
                        ?>
                        <div class="decor-preview" style="width:<?php echo $imgW; ?>px;height:<?php echo $imgH; ?>px">
                            <img src="<?php echo $baseSrc ?>" alt="<?php echo htmlspecialchars($extraSprite); ?>" />
                        </div>
                        <a class="decor-edit" href="#null" onclick="document.getElementById('decor-extra-new-<?php echo $extraSprite; ?>').style.display=document.getElementById('decor-extra-new-<?php echo $extraSprite; ?>').style.display?'':'block';return false"><?php echo $language ? 'Edit':'Modifier'; ?></a>
                        <?php
                    }
                    echo '</div>';
                    if (!$extraDecor) {
                        ?>
                        <form class="decor-extra-new decor-editor-form" id="decor-extra-new-<?php echo $extraSprite; ?>" method="post" enctype="multipart/form-data" action="editDecorExtra.php?parent=<?php echo $decor['id'] . $collabSuffix; ?>">
                            <input type="file" name="extraSprites:<?php echo $extraSprite; ?>" />
                            <button type="submit">Ok</button>
                        </form>
                        <?php
                    }
                }
                echo '</div>';
            }
            ?>
        </div>
        <form method="post" id="decor-edit-form" class="decor-editor-form" action="editDecor.php?id=<?php echo urlencode($_GET['id']) . $collabSuffix; ?>">
            <?php
            if (!$decor['extra_parent_id']) {
                ?>
            <label for="name"><?php echo $language ? 'Name for your decor (optional):':'Nom pour votre décor (facultatif) :'; ?></label>
            <input type="text" maxlength="30" name="name" id="name" placeholder="<?php echo $language ? 'Red pipe':'Tuyau rouge'; ?>" value="<?php echo htmlspecialchars($decor['name']); ?>"<?php if (!$hasWriteGrants) echo ' disabled="disabled"'; ?> />
            <button type="submit"<?php if (!$hasWriteGrants) echo ' disabled="disabled"'; ?>>Ok</button>
                <?php
            }
            ?>
            <div class="advances-options">
                +<a href="decorOptions.php?id=<?php echo urlencode($_GET['id']) . $collabSuffix; ?>">
                    <?php echo $language ? 'Advanced options':'Options avancées'; ?>...
                </a>
            </div>
        </form>
    </div>
    <?php
    if ($decor['extra_parent_id']) {
        if ($parentDecor = mysql_fetch_array(mysql_query('SELECT * FROM `mkdecors` WHERE id="'. $decor['extra_parent_id'] .'"'))) {
            $decorSrcs = get_decor_srcs($parentDecor);
            $spriteSizes = get_decor_sizes($parentDecor);
            $imgW = $spriteSizes['ld']['w'];
            $imgH = $spriteSizes['ld']['h'];
        ?>
        <div class="decors-list-container main-decor">
            <div class="decor-preview" style="width:<?php echo $imgW; ?>px;height:<?php echo $imgH; ?>px">
                <img src="<?php echo $decorSrcs['hd'] ?>" alt="<?php echo htmlspecialchars($decor['name']); ?>" />
            </div>
            <a href="editDecor.php?id=<?php echo $decor['extra_parent_id'] . $collabSuffix; ?>">&lt; &nbsp;<u><?php echo $language ? 'Back to main decor':'Retour au décor principal'; ?></u></a>
        </div><br />
        <?php
        }
    }
    ?>
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
            if (contentWidth < totalWidth)
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