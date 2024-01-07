<?php
include('../includes/language.php');
include('../includes/getId.php');
session_start();
include('../includes/tokens.php');
assign_token();
include('../includes/initdb.php');
require_once('../includes/utils-bgs.php');
include('../includes/file-quotas.php');
if (isset($_FILES['layer'])) {
    $files = array();
    $layerFiles = $_FILES['layer'];
    foreach ($layerFiles['tmp_name'] as $i=>$filePath) {
        $url = isset($_POST['url'][$i]) ? $_POST['url'][$i] : '';
        if ($url === '') {
            $files[] = array(
                'tmp_name' => $filePath,
                'error' => $layerFiles['error'][$i],
                'size' => $layerFiles['size'][$i],
            );
        }
        else
            $files[] = url_to_file_payload($url);
    }
	$upload = handle_bg_upload($files);
	if (isset($upload['id']))
		header('location: editBg.php?id='. $upload['id'] .'&new');
	if (isset($upload['error']))
		$error = $upload['error'];
}
?>
<!DOCTYPE html>
<html lang="<?php echo $language ? 'en':'fr'; ?>">
<head>
<meta charset="utf-8" />
<meta name="viewport" content="width=device-width, initial-scale=1">
<link rel="shortcut icon" type="image/x-icon" href="images/favicon.ico" />
<link rel="stylesheet" href="styles/editor.css?reload=1" />
<link rel="stylesheet" href="styles/collabs.css" />
<link rel="stylesheet" href="styles/bg-editor.css" />
<script type="text/javascript" src="scripts/collabs.js"></script>
<script type="text/javascript" src="scripts/bg-editor.js"></script>
<title><?php echo $language ? 'Background editor':'Éditeur d\'arrière-plans'; ?></title>
<script type="text/javascript">
var bgId = -1;
var author = "<?php if (isset($_COOKIE['mkauteur'])) echo htmlspecialchars($_COOKIE['mkauteur']); ?>";
var language = <?php echo ($language ? 'true':'false'); ?>;
var MAX_LAYERS = <?php echo MAX_LAYERS; ?>;
function selectBg(id) {
	if (bgId != -1)
		document.getElementById("mybg-"+bgId).classList.remove("bg-selected");
	if (id == bgId)
        bgId = -1;
	else
        bgId = id;
	if (bgId != -1) {
		document.getElementById("mybg-"+bgId).classList.add("bg-selected");
		document.getElementById("bg-actions").style.display = "inline-block";
		var bgName = document.getElementById("mybg-"+bgId).dataset.name;
		document.getElementById("bg-actions-name").innerHTML = bgName;
        document.querySelector(".bgs-list-container").scrollIntoView();
	}
	else
		document.getElementById("bg-actions").style.display = "none";
}
function editBg() {
	document.location.href = "editBg.php?id="+bgId;
}
function delBg() {
	if (confirm(language ? "Delete background?":"Supprimer l'arrière-plan ?"))
		document.location.href = "delBg.php?id="+bgId +"&token=<?php echo $_SESSION['csrf']; ?>";
}
function collabBg() {
    showCollabPopup("mkbgs", bgId, "getBgCollabPopup.php");
}
function toggleHelp() {
	document.getElementById("bg-instructions").style.display = (document.getElementById("bg-instructions").style.display =="block") ? "none":"block";
}
function addLayer() {
    var $layerContainer = document.getElementById("bg-form-layers");
    var nbLayers = $layerContainer.querySelectorAll(".bg-form-layer").length;
    var $layerTemplate = document.getElementById("bg-form-layer");
    var $layer = $layerTemplate.content.cloneNode(true).children[0];
    var $lastSelector = $layerContainer.querySelector(".bg-form-layer:last-child .editor-upload-tab-selected");
    $layerContainer.appendChild($layer);

    setupUploadTabs($layer);
    if ($lastSelector) {
        var lastSelectorIndex = Array.from($lastSelector.parentNode.children).indexOf($lastSelector);
        if (lastSelectorIndex > 0)
            $layer.querySelector(".editor-upload-tabs").children[lastSelectorIndex].click();
    }
    updateLayer($layer, nbLayers);
    nbLayers++;
    document.querySelector(".bg-form-submit button").disabled = false;
    if (nbLayers >= MAX_LAYERS)
        document.querySelector(".bg-form-layer-new").style.display = "none";
}
function updateLayer($layer, id) {
    $layer.querySelector(".layer-id").innerText = id+1;
}
function deleteLayer($layerBtn) {
    var $layerContainer = document.getElementById("bg-form-layers");
    $layerContainer.removeChild($layerBtn.parentNode);
    $allLayers = $layerContainer.querySelectorAll(".bg-form-layer");
    if ($allLayers.length) {
        for (var i=0;i<$allLayers.length;i++)
            updateLayer($allLayers[i], i);
    }
    else
        document.querySelector(".bg-form-submit button").disabled = true;
    document.querySelector(".bg-form-layer-new").style.display = "";
}
document.addEventListener("DOMContentLoaded", function() {
    addLayer();
});
</script>
</head>
<body>
<?php
if (isset($error))
    echo '<p id="error">'. $error .'</p>';
?>
<h2><?php echo $language ? 'Background editor':'Éditeur d\'arrière-plans'; ?></h2>
    <div class="bgs-list-container">
        <?php
        if ($language) {
            ?>
            Welcome to the background editor!<br />
            With this mode, you can create your own backgrounds and use them in the complete mode of the track builder.
            <?php
        }
        else {
            ?>
            Bienvenue dans l'éditeur d'arrière-plans !<br />
            Grâce à ce mode, vous pouvez créer vos propes arrière-plans et les utiliser dans l'éditeur complet du jeu.
            <?php
        }
        ?>
    </div>
    <?php
    $myBgs = mysql_query('SELECT * FROM `mkbgs` WHERE identifiant='.$identifiants[0].' ORDER BY id DESC');
    $areBgs = mysql_numrows($myBgs);
    if ($areBgs) {
        ?>
        <div class="bgs-list-container">
            <h3><?php echo $language ? 'Your backgrounds':'Vos arrière-plans'; ?></h3>
            <div id="bg-actions">
                <div id="bg-actions-name"></div>
                <button class="bg-edit" onclick="editBg()"><?php echo $language ? "Edit":"Modifier"; ?></button>
                <button class="bg-del" onclick="delBg()"><?php echo $language ? "Delete":"Supprimer"; ?></button>
                <button class="bg-collab" onclick="collabBg()"><?php echo $language ? "Collaborate":"Collaborer"; ?>&mldr;</button>
            </div>
            <div class="bgs-list"><?php
            while ($bg = mysql_fetch_array($myBgs)) {
                print_bg_div(array(
                    'bg' => $bg['id'],
                    'attrs' => 'id="mybg-'. $bg['id'] .'" data-name="'. htmlspecialchars($bg['name']) .'" onclick="selectBg('. $bg['id'] .')"'
                ));
            }
            ?></div>
        <?php
        $poids = file_total_size();
        echo '<div class="file-quotas">'. ($language ? 'You use '.filesize_str($poids).' out of '.filesize_str(MAX_FILE_SIZE).' ('. filesize_percent($poids) .')' : 'Vous utilisez '.filesize_str($poids).' sur '.filesize_str(MAX_FILE_SIZE).' ('.filesize_percent($poids).')') .'</div>';
        ?>
        </div>
        <?php
    }
    ?>
    <div class="bgs-list-container">
        <?php
        if ($areBgs) {
            ?>
            <h3 style="margin-bottom:0.5em">
                <?php echo $language ? 'New background':'Nouvel arrière-plan'; ?>
            <a class="sprite-help" href="javascript:toggleHelp()">[<?php echo $language ? "Show help":"Afficher l'aide"; ?>]</a>
            </h3>
            <?php
        }
        ?>
        <div id="bg-instructions"<?php if ($areBgs) echo ' class="instructions-unshown"'; ?>>
        <?php
        if ($language) {
            ?>
            <div class="description">
                To create your background, you can upload several images: one image per background layer.
                <br />
                The 1<sup>st</sup> image will be the most behind layer, the last will be the frontmost.
                Each layer will move independently in-game to give a <a href="https://en.wikipedia.org/wiki/Parallax" target="_blank">parallax</a> effect.
                <br /><br />
                For example, Mario Circuit 1 has 2 background layers:
                <br />
                <div class="description-layers description-layers-24">
                    <div><img src="images/map_bg/hills.png" alt="Layer 1" /></div>
                    <div><img src="images/map_bg/trees1.png" alt="Layer 2" /></div>
                </div>
                <br />
                And Figure 8 Circuit has 3:
                <div class="description-layers description-layers-48">
                    <div><img src="images/map_bg/mariosky.png" alt="Layer 1" /></div>
                    <div><img src="images/map_bg/hills8.png" alt="Layer 2" /></div>
                    <div><img src="images/map_bg/banners8.png" alt="Layer 3" /></div>
                </div>
                <br />
                To proceed, simply upload as many images as you want in the form below. 
                You can specify up to <?php echo MAX_LAYERS; ?> layers.
                <hr />
            </div>
            <?php
        }
        else {
            ?>
            <div class="description">
                Pour créer votre arrière-plan, vous pouvez uploader plusieurs images : une image par calque d'arrière-plan.
                <br />
                La 1<sup>ère</sup> image sera le calque le plus derrière, la dernière sera le plus devant.
                Chaque calque se déplacera indépendamment dans le jeu pour donner un effet de <a href="https://fr.wikipedia.org/wiki/Parallaxe" target="_blank">parallaxe</a>.
                <br /><br />
                Par exemple, le Circuit Mario 1 a 2 calques d'arrière-plan :
                <br />
                <div class="description-layers description-layers-24">
                    <div><img src="images/map_bg/hills.png" alt="Calque 1" /></div>
                    <div><img src="images/map_bg/trees1.png" alt="Calque 2" /></div>
                </div>
                <br />
                Et le Circuit en 8 en a 3 :
                <div class="description-layers description-layers-48">
                    <div><img src="images/map_bg/mariosky.png" alt="Calque 1" /></div>
                    <div><img src="images/map_bg/hills8.png" alt="Calque 2" /></div>
                    <div><img src="images/map_bg/banners8.png" alt="Calque 3" /></div>
                </div>
                <br />
                Pour continuer, envoyez simplement autant d'images que vous le souhaitez dans le formulaire ci-dessous.
                Vous pouvez spécifier jusqu'à <?php echo MAX_LAYERS; ?> calques.
                <hr />
            </div>
            <?php
        }
        ?>
        </div>
        <form method="post" id="bg-new-form" class="bg-editor-form" action="bgEditor.php" enctype="multipart/form-data">
            <?php
            if (!$areBgs) {
                ?>
                <h3><?php echo $language ? 'New background':'Nouvel arrière-plan'; ?></h3>
                <?php
            }
            ?>
            <div id="bg-form-layers">
            </div>
            <template id="bg-form-layer">
                <div class="bg-form-layer">
                    <?php echo $language ? 'Layer <span class="layer-id"></span>:':'Calque <span class="layer-id"></span> :'; ?>
                    <div class="editor-upload">
                        <div class="editor-upload-tabs">
                            <div class="editor-upload-tab editor-upload-tab-selected">
                                <?php echo $language ? 'Upload an image':'Uploader une image'; ?>
                            </div><div class="editor-upload-tab">
                                <?php echo $language ? 'Paste image URL':'Coller l\'URL de l\'image'; ?>
                            </div>
                        </div>
                        <div class="editor-upload-inputs">
                            <div class="editor-upload-input editor-upload-input-selected">
                                <input type="file" accept="image/png,image/gif,image/jpeg" required="required" name="layer[]" />
                            </div>
                            <div class="editor-upload-input">
                                <input type="url" name="url[]" placeholder="https://tcrf.net/images/c/c0/SMK_UnusedChocoBGPalette.png" />
                            </div>
                        </div>
                    </div>
                    <button type="button" class="btn-del" onclick="deleteLayer(this)">&times;</button>
                </div>
            </template>
            <div class="bg-form-layer-new">
                <button type="button" class="btn-edit" onclick="addLayer()"><strong>+</strong> <?php echo $language ? 'Add layer' : 'Ajouter un calque'; ?></button>
            </div>
            <div class="bg-form-submit">
                <button type="submit"><?php echo $language ? 'Send !':'Valider !'; ?></button>
            </div>
        </form>
    </div>
    <div class="editor-navigation">
        <a href="index.php">&lt; <u><?php echo $language ? "Back to Mario Kart PC":"Retour à Mario Kart PC"; ?></u></a>
    </div>
    <?php
    if (isset($_POST['type'])) {
        ?>
        <script type="text/javascript">
        document.querySelector(".bg-type-selector button[data-type='<?php echo htmlspecialchars($_POST['type']); ?>']").click();
        </script>
        <?php
    }
    ?>
    <div class="bgs-bottom">
        <?php
        require_once('../includes/utils-ads.php');
        showSmallAd();
        ?>
    </div>
</body>
</html>
<?php
mysql_close();
?>
