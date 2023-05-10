<?php
include('../includes/language.php');
include('../includes/getId.php');
include('../includes/initdb.php');
require_once('../includes/utils-decors.php');
$myDecors = mysql_query('SELECT * FROM mkdecors WHERE identifiant="'. $identifiants[0] .'" AND extra_parent_id IS NULL ORDER BY id DESC');
?>
<!DOCTYPE html>
<html lang="<?php echo $language ? 'en':'fr'; ?>">
<head>
<meta charset="utf-8" />
<meta name="viewport" content="width=device-width, initial-scale=1">
<link rel="shortcut icon" type="image/x-icon" href="images/favicon.ico" />
<link rel="stylesheet" href="styles/editor.css" />
<link rel="stylesheet" href="styles/decor-editor.css" />
<?php
include('../includes/o_xhr.php');
?>
<script type="text/javascript">
var language = <?php echo $language ? 1 : 0; ?>;
function selectDecor(elt) {
    window.opener.selectCustomDecor(elt.dataset);
    window.close();
}
function goToEditor() {
	window.close();
}
function showDecorCollab() {
    var $decorCollab = document.getElementById("decors-collab");
    $decorCollab.className = $decorCollab.className ? "" : "shown";
    if ($decorCollab.className)
        $decorCollab.elements["collab-link"].focus();
}
function showDecorCollabExplein() {
    alert(language ? "Enter the decor's collaboration link here.\nTo get this link, the decor owner will simply need to select the decor in the editor and click on \"Collaborate\"" : "Saisissez ici le lien de collaboration du décor.\nPour obtenir ce lien, le propriétaire du décor devra simplement sélectionner le décor dans l'éditeur et cliquer sur \"Collaborer\"");
}
function selectDecorSelectorCollab(e) {
    e.preventDefault();
	var $form = e.target;
	var url = $form.elements["collab-link"].value;
	var creationId, creationKey;
	try {
		var urlParams = new URLSearchParams(new URL(url).search);
		creationId = urlParams.get('id');
		creationKey = urlParams.get('collab');
	}
	catch (e) {
	}
	if (!creationKey) {
		alert(language ? "Invalid URL" : "URL invalide");
		return;
	}
	var $submitBtn = $form.querySelector('button[type="submit"]');
	$submitBtn.disabled = true;
	xhr("importCollabDecor.php", "id="+creationId+"&collab="+creationKey, function(res) {
		$submitBtn.disabled = false;
		if (!res) {
			alert(language ? "Invalid link" : "Lien invalide");
			return true;
		}
		res = JSON.parse(res);
        window.opener.selectCustomDecor(res);
        window.close();

		return true;
	});
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
            <strong style="color:#a8d4ff">+</strong> <a href="decorEditor.php" target="_blank" onclick="goToEditor()"><?php echo $language ? "Go to decors editor":"Accéder à l'éditeur de décors"; ?></a>
        </div>
        <hr />
        <?php
    }
    ?>
    <div class="decors-collab">
        <strong style="color:#a8d4ff">+</strong> <a href="javascript:showDecorCollab()"><?php echo $language ? "Select decor from another member...":"Sélectionner le décor d'un autre membre..."; ?></a>
        <form id="decors-collab" onsubmit="selectDecorSelectorCollab(event)">
            <label>
                <span><?php echo $language ? 'Collaboration link' : 'Lien de collaboration'; ?><a href="javascript:showDecorCollabExplein()">[?]</a>:</span>
                <input type="url" name="collab-link" required="required" placeholder="<?php
                    include('../includes/collabUtils.php');
                    $collab = array(
                        'type' => 'mkdecors',
                        'creation_id' => 42,
                        'secret' => 'y-vf-erny_2401_pbasvezrq'
                    );
                    echo getCollabUrl($collab);
                ?>" />
                <button type="submit">Ok</button>
            </label>
        </form>
    </div>
    </div>
    <?php
    if ($areDecors) {
        ?>
        <div class="decors-list-container">
        <h3><?php echo $language ? 'New decor':'Nouveau décor'; ?></h3>
        <div class="decors-list-more">
            <strong style="color:#a8d4ff">+</strong> <a href="decorEditor.php" target="_blank" onclick="goToEditor()"><?php echo $language ? "Go to decors editor":"Accéder à l'éditeur de décors"; ?></a>
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
