<?php
include('../includes/language.php');
include('../includes/getId.php');
session_start();
include('../includes/tokens.php');
assign_token();
include('../includes/initdb.php');
require_once('../includes/utils-decors.php');
include('../includes/file-quotas.php');
if (isset($_POST['type']) && isset($_FILES['sprites'])) {
	$upload = handle_decor_upload($_POST['type'],get_basic_sprites_payload('sprites'),get_extra_sprites_payload('extraSprites'));
	if (isset($upload['id']))
		header('location: editDecor.php?id='. $upload['id'] .'&new');
	if (isset($upload['error']))
		$error = $upload['error'];
    else
        exit;
}
?>
<!DOCTYPE html>
<html lang="<?php echo $language ? 'en':'fr'; ?>">
<head>
<meta charset="utf-8" />
<meta name="viewport" content="width=device-width, initial-scale=1">
<link rel="shortcut icon" type="image/x-icon" href="images/favicon.ico" />
<link rel="stylesheet" href="styles/editor.css" />
<link rel="stylesheet" href="styles/collabs.css" />
<link rel="stylesheet" href="styles/decor-editor.css" />
<script type="text/javascript" src="scripts/collabs.js"></script>
<script type="text/javascript" src="scripts/decor-editor.js?reload=1"></script>
<title><?php echo $language ? 'Decor editor':'Éditeur de décors'; ?></title>
<script type="text/javascript">
var author = "<?php if (isset($_COOKIE['mkauteur'])) echo htmlspecialchars($_COOKIE['mkauteur']); ?>";
var language = <?php echo ($language ? 'true':'false'); ?>;
var decorId = -1;
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
		document.getElementById("decor-actions-name").innerHTML = decorName;
		document.querySelector(".decors-list-container").scrollIntoView();
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
function collabDecor() {
    showCollabPopup("mkdecors", decorId, "getDecorCollabPopup.php");
}
function toggleHelp() {
	document.getElementById("decor-instructions").style.display = (document.getElementById("decor-instructions").style.display =="block") ? "none":"block";
}
function selectDecorType($btn) {
    var $lastSelected = document.getElementById("decor-type-selected");
    if ($lastSelected) $lastSelected.id = "";
    var $decorFormNext = document.getElementById("decor-form-next");
    var type = $btn.dataset.type;
    var $form = document.getElementById("decor-new-form");
    if ($lastSelected === $btn) {
        $decorFormNext.style.display = "";
        $form.elements["type"].value = "";
    }
    else {
        $btn.id = "decor-type-selected";
        $decorFormNext.style.display = "block";
        var isAsset = (type.substring(0,7) === "assets/");
        var linkedSprite = $btn.dataset.linkedSprite;
        if (isAsset)
            document.getElementById("decor-model-img").src = "images/map_icons/"+type+".png";
        else if (linkedSprite)
            document.getElementById("decor-model-img").src = "images/sprites/sprite_"+linkedSprite+".png";
        else
            document.getElementById("decor-model-img").src = "images/sprites/sprite_"+type+".png";
        $form.elements["type"].value = type;

        var extraSprites = $btn.dataset.extraSprites;
        if (extraSprites) {
            var extraSpritesList = extraSprites.split(",");
            for (var i=0;i<extraSpritesList.length;i++) {
                var extraSprite = extraSpritesList[i];
                document.getElementById("decor-extra-model-img").src = "images/sprites/sprite_"+extraSprite+".png";
                document.getElementById("extra-sprites").name = "extraSprites:" + extraSprite;
                document.getElementById("extra-sprites-url").name = "extraSprites-url:" + extraSprite;
            }
            document.getElementById("decor-extra-model").style.display = "block";
        }
        else {
            document.getElementById("extra-sprites").name = "";
            document.getElementById("extra-sprites-url").name = "";
            document.getElementById("decor-extra-model").style.display = "";
        }
    }
}
</script>
</head>
<body>
<?php
if (isset($error))
    echo '<p id="error">'. $error .'</p>';
?>
<h2><?php echo $language ? 'Decor editor':'Éditeur de décors'; ?></h2>
    <div class="decors-list-container">
        <?php
        if ($language) {
            ?>
            Welcome to the decors editor!<br />
            With this mode, you can create your own decors and use them in the complete mode of the track builder.
            <?php
        }
        else {
            ?>
            Bienvenue dans l'éditeur de décors !<br />
            Grâce à ce mode, vous pouvez créer vos propes décors et les utiliser dans l'éditeur complet du jeu.
            <?php
        }
        ?>
    </div>
    <?php
    $myDecors = mysql_query('SELECT * FROM `mkdecors` WHERE identifiant='.$identifiants[0].' AND extra_parent_id IS NULL ORDER BY id DESC');
    $areDecors = mysql_numrows($myDecors);
    if ($areDecors) {
        ?>
        <div class="decors-list-container">
            <h3><?php echo $language ? 'Your decors':'Vos décors'; ?></h3>
            <div id="decor-actions">
                <div id="decor-actions-name"></div>
                <button class="edit-decor" onclick="editDecor()"><?php echo $language ? "Edit":"Modifier"; ?></button>
                <button class="suppr-decor" onclick="delDecor()"><?php echo $language ? "Delete":"Supprimer"; ?></button>
                <button class="collab-decor" onclick="collabDecor()"><?php echo $language ? "Collaborate":"Collaborer"; ?>&mldr;</button>
            </div>
            <div class="decors-list"><?php
            while ($decor = mysql_fetch_array($myDecors)) {
                $decorSrcs = get_decor_srcs($decor);
                ?><div id="mydecor-<?php echo $decor['id'] ?>" data-id="<?php echo $decor['id'] ?>" data-name="<?php echo htmlspecialchars($decor['name']) ?>" data-ld="<?php echo $decorSrcs['ld'] ?>" data-type="<?php echo $decor['type']; ?>" onclick="selectDecor(<?php echo $decor['id'] ?>)"><img src="<?php echo $decorSrcs['ld']; ?>" alt="<?php echo htmlspecialchars($decor['name']) ?>" /></div><?php
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
    <div class="decors-list-container">
        <?php
        if ($areDecors) {
            ?>
            <h3 style="margin-bottom:0.5em">
                <?php echo $language ? 'New decor':'Nouveau décor'; ?>
            <a class="sprite-help" href="javascript:toggleHelp()">[<?php echo $language ? "Show help":"Afficher l'aide"; ?>]</a>
            </h3>
            <?php
        }
        ?>
        <div id="decor-instructions"<?php if ($areDecors) echo ' class="instructions-unshown"'; ?>>
        <?php
        if ($language) {
            ?>
            <div class="description">
                To create your decor, you need 2 things:
                <ul>
                    <li>Choose a decor type, which defines its behavior.
                    If you select the thwomp <img src="images/map_icons/thwomp.png" alt="Thwomp" /> for example, your decor will behave exactly like the thwomp of MKPC (goes up slowly and falls down quckly).</li>
                    <li>Create an image (via a drawing software) containing the decor sprite(s) in order to give it the appearance you want. You can also take decors shared by other members on this <a href="topic.php?topic=6457" target="_blank">forum topic</a>.</li>
                </ul>
            </div>
            <div class="description">
                For example, the Piranha plant is modelled by the following image:<br />
                <img src="images/sprites/sprite_fireplant.png" alt="Plant Sprite" /><br />
                Thus, if you select this type, you must provide an image with the 4 sprites aligned in the same way.<br />
                You don't have to keep the same dimensions as the original sprite, but all your sprites should be the same size (in particular, in the example above, your image width must be a multiple of 4).<br />
                For more information about how sprites work, go to the <a href="persoEditor.php">character editor</a> which functions on the same principle.
            </div>
            <?php
        }
        else {
            ?>
            <div class="description">
                Pour créer votre décor, vous avez besoin de 2 choses :
                <ul>
                    <li>Choisir un type de décor, ce qui définit son comportement.
                    Si vous sélectionnez le thwomp <img src="images/map_icons/thwomp.png" alt="Thwomp" /> par exemple, votre décor se comportera exactement comme le thwomp de MKPC (monte progressivement et tombe brusquement).</li>
                    <li>Créer une image (via un logiciel de dessin) contenant le ou les sprites du décor afin de lui donner l'apparence que vous souhaitez. Vous pouvez aussi récupérer des décors partagés par les autres membres sur <a href="topic.php?topic=6457" target="_blank">ce topic</a>.</li>
                </ul>
            </div>
            <div class="description">
                Par exemple, le plante Piranha est modélisée par l'image suivante :<br />
                <img src="images/sprites/sprite_fireplant.png" alt="Sprite Plante" /><br />
                Si vous sélectionnez ce type, vous devrez ainsi fournir une image avec les 4 sprites alignés de la même façon.<br />
                Vous n'êtes pas obligé de garder les mêmes dimensions que le sprite d'origine, mais tous vos sprites doivent avoir la même taille (en particulier, dans l'exemple ci-dessus, la largeur de votre image doit être un multiple de 4).<br />
                Pour plus d'informations sur le fonctionnement des sprites, rendez-vous dans l'<a href="persoEditor.php">éditeur de persos</a> qui utilise le même principe.
            </div>
            <hr />
            <?php
        }
        ?>
        </div>
        <form method="post" id="decor-new-form" class="decor-editor-form" action="decorEditor.php" enctype="multipart/form-data">
            <?php
            if (!$areDecors) {
                ?>
                <h3><?php echo $language ? 'New decor':'Nouveau décor'; ?></h3>
                <?php
            }
            ?>
            <div>
                <?php echo $language ? 'Decor type:':'Type de décor :'; ?>
                <input type="text" name="type" required="required" style="display:none" />
                <span class="decor-type-selector"><?php
                /** @var array $decorType */
                foreach ($CUSTOM_DECOR_TYPES as $type=>$decorType) {
                    if (empty($decorType['is_extra'])) {
                        ?>
                        <button type="button" data-type="<?php echo $type; ?>" style="background-image:url('images/map_icons/<?php echo $type; ?>.png')"<?php
                            if (isset($decorType['extra_sprites']))
                                echo ' data-extra-sprites="'.implode(",", $decorType['extra_sprites']).'"';
                            if (isset($decorType['linked_sprite']))
                                echo ' data-linked-sprite="'.$decorType['linked_sprite'].'"';
                        ?> onclick="selectDecorType(this)"></button>
                        <?php
                    }
                }
                ?></span>
            </div>
            <div id="decor-form-next">
                <div class="decor-model">
                    <div class="decor-model-label"><?php echo $language ? 'Model:':'Modèle&nbsp;:'; ?></div>
                    <div class="decor-model-value"><img id="decor-model-img" src="images/sprites/sprite_tuyau.png" alt="" /></div>
                </div>
                <div class="decor-form-image">
                    <?php echo $language ? 'Image:':'Image :'; ?>
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
                                <input type="file" accept="image/png,image/gif,image/jpeg" required="required" name="sprites" />
                            </div>
                            <div class="editor-upload-input">
                                <input type="url" name="sprites-url" placeholder="https://mario.wiki.gallery/images/b/be/Warp_Pipe_SMB.png" />
                            </div>
                        </div>
                    </div>
                </div>
                <div id="decor-extra-model">
                    <div class="decor-model">
                        <div class="decor-model-label"><?php echo $language ? 'Model 2:':'Modèle 2&nbsp;:'; ?></div>
                        <div class="decor-model-value"><img id="decor-extra-model-img" src="images/sprites/sprite_tuyau.png" alt="" /></div>
                    </div>
                    <div class="decor-form-image">
                        <?php echo $language ? '(Optional) Image 2:':'(Facultatif) Image 2 :'; ?>
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
                                    <input type="file" accept="image/png,image/gif,image/jpeg" id="extra-sprites" />
                                </div>
                                <div class="editor-upload-input">
                                    <input type="url" id="extra-sprites-url" placeholder="https://mario.wiki.gallery/images/b/be/Warp_Pipe_SMB.png" />
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div><button type="submit"><?php echo $language ? 'Send !':'Valider !'; ?></button></div>
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
        document.querySelector(".decor-type-selector button[data-type='<?php echo htmlspecialchars($_POST['type']); ?>']").click();
        </script>
        <?php
    }
    ?>
    <div class="decors-bottom">
		<?php
		require_once('../includes/utils-ads.php');
		showSmallAd();
		?>
    </div>
    <script type="text/javascript">
        var $decorUploads = document.querySelectorAll(".editor-upload");
        for (var i=0;i<$decorUploads.length;i++)
            setupUploadTabs($decorUploads[i]);
    </script>
</body>
</html>
<?php
mysql_close();
?>
