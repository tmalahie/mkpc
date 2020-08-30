<?php
include('language.php');
include('getId.php');
include('initdb.php');
require_once('utils-decors.php');
$myDecors = mysql_query('SELECT * FROM mkdecors WHERE identifiant="'. $identifiants[0] .'" ORDER BY id DESC');
?>
<!DOCTYPE html>
<html lang="<?php echo $language ? 'en':'fr'; ?>">
<head>
<meta charset="utf-8" />
<meta name="viewport" content="width=device-width, initial-scale=1">
<link rel="shortcut icon" type="image/x-icon" href="images/favicon.ico" />
<link rel="stylesheet" href="styles/editor.css?reload=1" />
<link rel="stylesheet" href="styles/decor-editor.css" />
<script type="text/javascript">
function selectDecor(elt) {
    window.opener.selectCustomDecor(elt.dataset);
    window.close();
}
function goToEditor() {
	window.close();
}
</script>
<title><?php echo $language ? 'Decor editor':'Éditeur de décors'; ?></title>
</head>
<body>
    <h2><?php echo $language ? 'Choose a decor from editor':'Choix d\'un décor à partir de l\'éditeur'; ?></h2>
    <div class="decors-list-container">
    <h3><?php echo $language ? 'Your decors':'Vos décors'; ?></h3>
    <?php
    $areDecors = mysql_numrows($myDecors);
    if ($areDecors) {
        ?>
        <div class="decors-list"><?php
        while ($decor = mysql_fetch_array($myDecors)) {
            $decorSrcs = decor_sprite_srcs($decor['sprites']);
            ?><div data-id="<?php echo $decor['id'] ?>" data-name="<?php echo htmlspecialchars($decor['name']) ?>" data-ld="<?php echo $decorSrcs['ld'] ?>" data-type="<?php echo $decor['type']; ?>" onclick="selectDecor(this)"><img src="<?php echo $decorSrcs['ld']; ?>" alt="<?php echo htmlspecialchars($decor['name']) ?>" /></div><?php
        }
        ?></div>
        <?php
    }
    else {
        ?>
        <div class="decors-list-empty"><?php
        echo $language ? 'You haven\'t created decors yet':'Vous n\'avez créé aucun décor pour l\'instant';
        ?></div>
        <div class="decors-list-more">
            <strong style="color:#a8d4ff">+</strong> <a href="decorEditor.php" target="_blank" onclick="goToEditor()"><?php echo $language ? "Go to characters editor":"Accéder à l'éditeur de décors"; ?></a>
        </div>
        <?php
    }
    ?>
    </div>
    <?php
    if ($areDecors) {
        ?>
        <div class="decors-list-container">
        <h3><?php echo $language ? 'New decor':'Nouveau décor'; ?></h3>
        <div class="decors-list-more">
            <strong style="color:#a8d4ff">+</strong> <a href="decorEditor.php" target="_blank" onclick="goToEditor()"><?php echo $language ? "Go to characters editor":"Accéder à l'éditeur de décors"; ?></a>
        </div>
        </div>
        <?php
    }
    ?>
    <div class="decors-bottom">
        <script async src="//pagead2.googlesyndication.com/pagead/js/adsbygoogle.js"></script>
        <!-- Mario Kart PC -->
        <ins class="adsbygoogle"
            style="display:inline-block;width:468px;height:60px"
            data-ad-client="ca-pub-1340724283777764"
            data-ad-slot="6691323567"></ins>
        <script>
        (adsbygoogle = window.adsbygoogle || []).push({});
        </script>
    </div>
</body>
</html>
<?php
mysql_close();
?>
