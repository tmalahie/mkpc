<?php
if (isset($_GET['id'])) {
	$bgId = $_GET['id'];
	include('initdb.php');
	if ($bg = mysql_fetch_array(mysql_query('SELECT * FROM `mkbgs` WHERE id="'. $bgId .'"'))) {
		include('getId.php');
		if ($bg['identifiant'] == $identifiants[0]) {
			include('language.php');
			require_once('utils-bgs.php');
            session_start();
            include('tokens.php');
            assign_token();
			if (isset($_POST['name'])) {
                $bg['name'] = preg_replace('#<[^>]+>#', '', $_POST['name']);
                mysql_query('UPDATE `mkbgs` SET name="'. $bg['name'] .'" WHERE id="'. $_GET['id'] .'"');
            }
            $bgLayers = get_bg_layers($bg['id']);
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
<script type="text/javascript">
var language = <?php echo $language ? 1:0; ?>;
function editLayer(id) {
    document.location.href = "editLayer.php?id=" + id;
}
function delLayer(id) {
    if (confirm(language ? "Delete layer?" : "Supprimer le calque ?"))
        document.location.href = "delBgLayer.php?id=" + id +"&token=<?php echo $_SESSION['csrf']; ?>";
}
function toggleLayerAdd() {
    var $form = document.getElementById("bg-layers-add-form");
    if ($form.classList.contains("shown"))
        $form.classList.remove("shown");
    else
        $form.classList.add("shown");
}
</script>
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
            print_bg_div(array(
                'layers' => $bgLayers
            ));
            ?>
        </div>
        <style type="text/css">
        <?php
        $nbLayers = count($bgLayers);
        $from = array();
        $to = array();
        $bgPreviewHeight = 48;
        foreach ($bgLayers as $i=>$bgLayer) {
            list($w,$h) = getimagesize($bgLayer['path']);
            $imgW = round($w * ($bgPreviewHeight/$h));
            $offset = -$imgW*($i+1);
            $from[] = '0px 0px';
            $to[] = $offset . 'px 0px';
        }
        $to = array_reverse($to);
        ?>
        @keyframes movebg {
            from {background-position: <?php echo implode(',', $from); ?>}
            to   {background-position: <?php echo implode(',', $to); ?>}
        }
        @media screen and (min-width: 480px) {
            .bg-edit-preview .bg-preview {
                animation: movebg 10s infinite linear;
            }
        }
        </style>
        <form method="post" id="bg-edit-form" class="bg-editor-form" action="editBg.php?id=<?php echo $_GET['id']; ?>">
            <label for="name"><?php echo $language ? 'Name for your background (optional):':'Nom pour votre arrière-plan (facultatif) :'; ?></label><br />
            <input type="text" maxlength="30" name="name" id="name" placeholder="<?php echo $language ? 'Sunset':'Coucher de soleil'; ?>" value="<?php echo htmlspecialchars($bg['name']); ?>" />
            <button type="submit">Ok</button>
        </form>
    </div>
    <div class="bgs-list-container bg-layers-container">
        <h3><?php echo $language ? 'Layers' : 'Calques'; ?></h3>
        <div class="bg-layers">
            <?php
            foreach ($bgLayers as $i=>$bgLayer) {
                ?>
                <div class="bg-layer">
                    <div class="bg-layer-img">
                        <img src="<?php echo $bgLayer['path']; ?>" alt="Layer <?php echo $i; ?>" />
                    </div>
                    <div class="bg-layer-actions">
                        <button class="bg-edit" onclick="editLayer(<?php echo $bgLayer['id']; ?>)">✎</button>
                        <?php
                        if ($nbLayers > 1) {
                            ?>
                            <button class="bg-del" onclick="delLayer(<?php echo $bgLayer['id']; ?>)">&times;</button>
                            <?php
                        }
                        ?>
                    </div>
                </div>
                <?php
            }
            ?>
        </div>
        <div class="bg-layers-add" action="addBgLayer.php">
            <a href="javascript:toggleLayerAdd()">+ <?php echo $language ? 'Add layer':'Ajouter un calque'; ?>...</a>
            <form method="post" action="addBgLayer.php" enctype="multipart/form-data" id="bg-layers-add-form" class="bg-editor-form">
                <input type="hidden" name="id" value="<?php echo $bg['id']; ?>" />
                <input type="file" required="required" name="layer[]" />
                <button type="submit">Ok</button>
            </form>
        </div>
    </div>
    <div class="editor-navigation">
        <a href="bgEditor.php">&lt; <u><?php echo $language ? "Back to background editor":"Retour à l'éditeur d'arrière-plans"; ?></u></a>
    </div>
</body>
</html>
		<?php
		}
	}
	mysql_close();
}
?>