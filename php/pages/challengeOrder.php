<?php
include('../includes/getId.php');
include('../includes/language.php');
include('../includes/initdb.php');
require_once('../includes/utils-challenges.php');
if (isset($_GET['cl']))
    $clRace = getClRace($_GET['cl']);
include('../includes/challenge-cldata.php');
$clOptions = array('alltracks' => true, 'status' => array('pending_completion','pending_publication','pending_moderation','active'), 'circuit' => true);
$clMsg = null;
if (!empty($clRace)) {
    $challenges = listChallenges($clRace['id'], $clOptions);
    $challengesById = array();
    foreach ($challenges as $challenge)
        $challengesById[$challenge['id']] = $challenge;
    if (isset($_POST['order'])) {
        mysql_query('DELETE FROM mkclorder WHERE clist="'. $clRace['id'] .'"');
        $newOrder = explode(',', $_POST['order']);
        $newOrderList = array();
        foreach ($newOrder as $i=>$challengeId) {
            if (isset($challengesById[$challengeId]))
                $newOrderList[] = '('.$clRace['id'].','.$challengesById[$challengeId]['id'].','.$i.')';
        }
        $newOrderListString = implode(',', $newOrderList);
        if ($newOrderListString)
            mysql_query('INSERT INTO `mkclorder` (clist,challenge,position) VALUES '.$newOrderListString);
        $clMsg = $language ? "Challenge order was updated successfully":"L'ordre des défis a été modifié avec succès";
    }
    $getChallengeOrder = mysql_query('SELECT challenge FROM mkclorder WHERE clist="'. $clRace['id'] .'" ORDER BY position');
    $challengeOrder = array();
    for ($order=0;$challenge=mysql_fetch_array($getChallengeOrder);$order++)
        $challengeOrder[$challenge['challenge']] = $order;
    foreach ($challenges as $challenge) {
        if (!isset($challengeOrder[$challenge['id']])) {
            $challengeOrder[$challenge['id']] = $order;
            $order++;
        }
    }
    usort($challenges, function($a,$b) use ($challengeOrder) {
        return $challengeOrder[$a['id']] - $challengeOrder[$b['id']];
    });
}
else
    $challenges = array();
?>
<!DOCTYPE html>
<html lang="<?php echo $language ? 'en':'fr'; ?>">
<head>
<meta charset="utf-8" />
<meta name="viewport" content="width=device-width, initial-scale=1">
<link rel="shortcut icon" type="image/x-icon" href="images/favicon.ico" />
<?php
include('../includes/c_challenges.php');
include('../includes/o_online.php');
?>
<script type="text/javascript" src="scripts/challenges.js"></script>
<style type="text/css">
.explain {
    color: white;
}
.challenge-order-list {
    width: 500px;
    max-width: 100%;
    margin-left: auto;
    margin-right: auto;
}
.challenge-order-item-zone {
    padding-top: 5px;
    padding-bottom: 5px;
}
.challenge-order-item {
    background-color: rgb(192,192,204);
    background-color: rgba(230,230,240,0.8);
    border-radius: 5px;
    color: #003;
    padding: 0.5em 1em;
    cursor: move;
    user-select: none;
}
.challenge-order-item:hover {
    background-color: rgba(230,230,240,1);
}
.challenge-order-list.dragging .challenge-order-item:not(.dragged) {
    pointer-events: none;
}
.challenge-order-item-zone.drop-preview-before {
    border-top: solid 12px rgba(230,230,240,0.8);
}
.challenge-order-item-zone.drop-preview-after {
    border-bottom: solid 12px rgba(230,230,240,0.8);
}
.challenge-order-reset {
    margin: 0.5em;
}
.challenge-order-reset a {
	color: #F40;
}
.challenge-order-reset a:hover {
	color: #F60;
}
</style>
<title><?php echo $language ? 'Challenge order':'Ordre des défis'; ?> - Mario Kart PC</title>
</head>
<body>
<?php
if ($clMsg)
    echo '<div class="challenge-msg-success">'. $clMsg .'</div>';
?>
<h1 class="challenge-main-title"><?php
    echo $language ? 'Edit challenge order' : 'Modifier l\'ordre des défis';
?></h1>
<p class="explain"><?php echo $language ? "This page allows you to specify the order of the challenges as they appear in your track.<br />Just drag &amp; drop the items to reorder them." : "Cette page vous permet de spécifier l'ordre des défis tels qu'ils apparaissent dans votre circuit.<br />Glissez-déposez les éléments pour les réordonner."; ?></p>
<form method="post" name="challenge-order" action="" class="challenge-edit-form challenge-order">
    <div class="challenge-order-list">
        <?php
        $challengeIds = array();
        foreach ($challenges as $challenge) {
            $challengeIds[] = $challenge['id'];
            ?>
            <div class="challenge-order-item-zone" ondragenter="previewDrop(this,event)" ontouchstart="checkUnsupportedDrag()" ondragover="followDrop(event)" ondragleave="unpreviewDrop(this)" ondrop="handleDrop(this)" onclick="handleUnsupportedDrag(this)" data-challenge="<?php echo $challenge['id']; ?>">
                <div class="challenge-order-item" draggable="true" ondragstart="handleDrag(this)" ondragend="handleUnDrag(this)">
                    <?php
                    if (isset($challenge['circuit']))
                        echo '<strong>'. $challenge['circuit']['name'] .'</strong> - ';
                    if ($challenge['name'])
                        echo htmlspecialchars($challenge['name']);
                    else
                        echo $challenge['description']['main'];
                    ?>
                </div>
            </div>
            <?php
        }
        ?>
    </div>
    <input type="hidden" name="order" value="<?php echo implode(',', $challengeIds); ?>" />
    <button type="submit" class="challenge-edit-submit"><?php echo $language ? 'Validate order':'Valider l\'ordre'; ?></button>
    <div class="challenge-order-reset">
        <a href="javascript:resetOrder()"><?php echo $language ? "Reset order" : "Réintialiser l'ordre"; ?></a>
    </div>
</form>
<div class="challenge-navigation">
    <a href="<?php echo nextPageUrl('challenges.php', array('ch'=>null,'cl'=>empty($clRace)?null:$clRace['clid'])); ?>">&lt; <u><?php echo $language ? 'Back to challenges list':'Retour à la liste des défis'; ?></u></a>
</div>
<script type="text/javascript">
var language = <?php echo $language ? 1:0; ?>;
function handleDrag($elt) {
    $elt.classList.add("dragged");
    document.querySelector('.challenge-order-list').classList.add("dragging");
}
function handleUnDrag($elt) {
    $elt.classList.remove("dragged");
    document.querySelector('.challenge-order-list').classList.remove("dragging");
}
var isDnDUnsupported = undefined;
var dndCheckHandler;
function checkUnsupportedDrag() {
    if (isDnDUnsupported === undefined) {
        if (!dndCheckHandler) {
            dndCheckHandler = setTimeout(function() {
                isDnDUnsupported = true;
            }, 500);
        }
        return;
    }
}
function handleUnsupportedDrag($elt) {
    if (isDnDUnsupported) {
        const $list = document.querySelector(".challenge-order-list");
        const $children = [...$list.children];
        const $eltIndex = $children.indexOf($elt);
        var newIndex = prompt(language ? "Sorry, drag&drop is not supported on your device. Please select the order manually" : "Désolé, le glisser-déposer n'est pas supporté sur votre appareil. Veuillez sélectionner l'ordre manuellement", 1+$eltIndex);
        if (newIndex == null) return;
        newIndex = newIndex - 1;
        if (isNaN(newIndex)) return;
        if (newIndex < 0) return;
        if (newIndex >= $children.length) return;
        $list.removeChild($elt);
        $list.insertBefore($elt, $list.children[newIndex]);
        updateListValue($list);
    }
}
function previewDrop($elt,e) {
    isDnDUnsupported = false;
    e.preventDefault();
    unpreviewDrops();
    const $draggedElt = document.querySelector('.challenge-order-list .dragged');
    const $list = document.querySelector(".challenge-order-list");
    const $children = [...$list.children];
    const $draggedEltIndex = $children.indexOf($draggedElt.parentNode);
    const $eltIndex = $children.indexOf($elt);
    if ($draggedEltIndex < $eltIndex)
        $elt.classList.add("drop-preview-after");
    else if ($draggedEltIndex > $eltIndex)
        $elt.classList.add("drop-preview-before");
}
function followDrop(e) {
    e.preventDefault();
}
function unpreviewDrop($elt) {
    $elt.classList.remove("drop-preview-after");
    $elt.classList.remove("drop-preview-before");
}
function unpreviewDrops() {
    let $prevDrop = document.querySelector('.challenge-order-list .drop-preview-after');
    if ($prevDrop) $prevDrop.classList.remove("drop-preview-after");
    $prevDrop = document.querySelector('.challenge-order-list .drop-preview-before');
    if ($prevDrop) $prevDrop.classList.remove("drop-preview-before");
}
function handleDrop($elt) {
    unpreviewDrops();
    const $draggedElt = document.querySelector('.challenge-order-list .dragged');
    const $draggedZone = $draggedElt.parentNode;
    const $list = document.querySelector(".challenge-order-list");
    const $children = [...$list.children];
    const $draggedEltIndex = $children.indexOf($draggedZone);
    const $eltIndex = $children.indexOf($elt);
    if ($draggedEltIndex === $eltIndex) return;
    $list.removeChild($draggedZone);
    if ($draggedEltIndex < $eltIndex)
        $list.insertBefore($draggedZone, $elt.nextSibling);
    else
        $list.insertBefore($draggedZone, $elt);
    updateListValue($list);
}
function updateListValue($list) {
    const newList = [];
    for (const $child of $list.children)
        newList.push($child.getAttribute("data-challenge"));
    document.querySelector('input[name="order"]').value = newList.join(",");
}
function resetOrder() {
    if (confirm(o_language ? "Reset order to default?" : "Rétablir l'ordre par défaut ?")) {
        document.querySelector('input[name="order"]').value = '';
        document.forms["challenge-order"].submit();
    }
}
</script>
</body>
</html>
<?php
mysql_close();
?>