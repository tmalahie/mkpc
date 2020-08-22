<?php
include('language.php');
include('getId.php');
session_start();
include('tokens.php');
assign_token();
include('initdb.php');
require_once('utils-decors.php');
include('file-quotas.php');
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
<script type="text/javascript">
var decorId = -1;
var author = "<?php echo htmlspecialchars($_COOKIE['mkauteur']); ?>";
var language = <?php echo ($language ? 'true':'false'); ?>;
function selectDecor(id) {
	if (decorId != -1)
		document.getElementById("mydecor-"+decorId).className = "";
	if (id == decorId)
        decorId = -1;
	else
        decorId = id;
	if (decorId != -1) {
		document.getElementById("mydecor-"+decorId).className = "decor-selected";
		document.getElementById("decor-actions").style.display = "inline-block";
		var decorName = document.getElementById("mydecor-"+decorId).dataset.name;
		if (decorName) {
			document.getElementById("decor-actions-name").innerHTML = decorName;
			document.querySelector(".share-decor").style.display = "none"; // TODO show when ready
		}
		else {
			document.getElementById("decor-actions-name").innerHTML = "<em>"+ (language ? "Being created":"En cours de création") +"</em>";
			document.querySelector(".share-decor").style.display = "none";
		}
	}
	else
		document.getElementById("decor-actions").style.display = "none";
}
function editDecor() {
	document.location.href = "editDecor.php?id="+decorId;
}
function delDecor() {
	if (confirm(language ? "Delete decor?":"Supprimer le décor ?"))
		document.location.href = "delDecor.php?id="+decorId +"&token=<?php echo $_SESSION['csrf']; ?>";
}
function toggleHelp() {
	document.getElementById("decor-instructions").style.display = (document.getElementById("decor-instructions").style.display =="block") ? "none":"block";
}
function selectDecorType($btn) {
    var $lastSelected = document.getElementById("decor-type-selected");
    if ($lastSelected) $lastSelected.id = "";
    var $decorModel = document.getElementById("decor-model");
    var type = $btn.dataset.type;
    var $form = document.getElementById("decor-new-form");
    if ($lastSelected === $btn) {
        $decorModel.style.display = "";
        $form.elements["type"].value = "";
    }
    else {
        $btn.id = "decor-type-selected";
        $decorModel.style.display = "inline-flex";
        document.getElementById("decor-model-img").src = "images/sprites/sprite_"+type+".png";
        $form.elements["type"].value = type;
    }
}
</script>
</head>
<body>
<h2><?php echo $language ? 'Decor editor':'Éditeur de décors'; ?></h2>
    <div class="decors-list-container">
        Bienvenue dans l'éditeur de décors !
        Grâce à ce mode, vous pouvez créer vos propes décors et les utiliser dans l'éditeur complet du jeu.
    </div>
    <?php
    $myDecors = mysql_query('SELECT * FROM `mkdecors` WHERE identifiant='.$identifiants[0].' ORDER BY id DESC');
    $areDecors = mysql_numrows($myDecors);
    if ($areDecors) {
        ?>
        <div class="decors-list-container">
            <h3><?php echo $language ? 'Your decors':'Vos décors'; ?></h3>
            <div class="decors-list"><?php
            while ($decor = mysql_fetch_array($myDecors)) {
                $decorSrcs = decor_sprite_srcs($decor['sprites']);
                ?><div id="mydecor-<?php echo $decor['id'] ?>" data-id="<?php echo $decor['id'] ?>" data-name="<?php echo htmlspecialchars($decor['name']) ?>" data-ld="<?php echo $decorSrcs['ld'] ?>" data-type="<?php echo $decor['type']; ?>" onclick="selectDecor(<?php echo $decor['id'] ?>)"><img src="<?php echo $decorSrcs['ld']; ?>" alt="<?php echo htmlspecialchars($decor['name']) ?>" /></div><?php
            }
            ?></div>
        <?php
        $poids = file_total_size();
        echo '<div class="mydecors-size">'. ($language ? 'You use '.filesize_str($poids).' out of '.filesize_str(MAX_FILE_SIZE).' ('. filesize_percent($poids) .')' : 'Vous utilisez '.filesize_str($poids).' sur '.filesize_str(MAX_FILE_SIZE).' ('.filesize_percent($poids).')') .'</div>';
        ?>
        </div>
        <div id="decor-actions">
            <div id="decor-actions-name"></div>
            <button class="edit-decor" onclick="javascript:editDecor()"><?php echo $language ? "Edit":"Modifier"; ?></button>
            <button class="suppr-decor" onclick="javascript:delDecor()"><?php echo $language ? "Delete":"Supprimer"; ?></button>
            <button class="share-decor" onclick="javascript:shareDecor()"><?php echo $language ? "Share":"Partage"; ?>...</button>
        </div>
        <?php
    }
    ?>
    <div class="decors-list-container">
        <h3><?php
        echo $language ? 'New decor':'Nouveau décor';
        if ($areDecors) {
            ?>
            <a class="sprite-help" href="javascript:toggleHelp()">[<?php echo $language ? "Show help":"Afficher l'aide"; ?>]</a>
            <?php
        }
        ?></h3>
        <div id="decor-instructions"<?php if ($areDecors) echo ' class="instructions-unshown"'; ?>>
            Bite bite cigarre dans cul bit cigare dans le cul cigare dans le cul bite cigare dans le cul cul cul cul
        </div>
        <form method="post" id="decor-new-form" action="decorEditor.php" enctype="multipart/form-data">
            <div>Type de décor :
                <input type="text" name="type" required="required" style="display:none" />
                <span class="decor-type-selector"><?php
                foreach ($CUSTOM_DECOR_TYPES as $type=>$decorType) {
                    ?>
                    <button type="button" data-type="<?php echo $type; ?>" style="background-image:url('images/map_icons/<?php echo $type; ?>.png')" onclick="selectDecorType(this)"></button>
                    <?php
                }
                ?></span>
            </div>
            <div id="decor-model">
                <div id="decor-model-label">Modèle&nbsp;:</div>
                <div id="decor-model-value"><img id="decor-model-img" src="images/sprites/sprite_tuyau.png" /></div>
            </div>
            <div>Image : <input type="file" required="required" name="sprites" /></div>
            <div>Nom pour votre décor : <input type="text" required="required" name="name" /></div>
            <div><button type="submit"><?php echo $language ? 'Send !':'Valider !'; ?></button></div>
        </form>
    </div>
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
