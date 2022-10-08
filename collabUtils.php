<?php
$COLLAB_TYPES = array('arenes','circuits','mkcircuits','mkcups','mkmcups','mkchars','mkdecors','mkbgs');
function isCollabTypeValid($type) {
    global $COLLAB_TYPES;
    return in_array($type, $COLLAB_TYPES);
}
function isCollabOwner($type, $creationId) {
    global $identifiants;
    if (!isCollabTypeValid($type)) return false;
    $getCreation = mysql_fetch_array(mysql_query('SELECT identifiant FROM `'.$type.'` WHERE id="'. $creationId .'"'));
    return ($getCreation && ($getCreation['identifiant'] == $identifiants[0]));
}


function collabPayload(&$collab) {
    $rightsList = explode(',', $collab['rights']);
    $rightsDict = array();
    foreach ($rightsList as $right)
        $rightsDict[$right] = 1;
    return array(
        'id' => $collab['id'],
        'type' => $collab['type'],
        'creation_id' => $collab['creation_id'],
        'key' => $collab['secret'],
        'url' => getCollabUrl($collab),
        'rights' => $rightsDict
    );
}

function getCollabUrlPrefix(&$collab) {
    $creationId = $collab['creation_id'];
    switch ($collab['type']) {
    case 'circuits':
        return 'map.php?i='. $creationId;
    case 'mkcircuits':
        $circuit = mysql_fetch_array(mysql_query('SELECT type FROM `mkcircuits` WHERE id="'. $creationId .'"'));
        return ($circuit&&$circuit['type'] ? 'arena':'circuit') .'.php?id='. $creationId;
    case 'arenes':
        return 'battle.php?i='. $creationId;
    case 'mkcups':
        $circuit = mysql_fetch_array(mysql_query('SELECT mode FROM `mkcups` WHERE id="'. $creationId .'"'));
        return (empty($circuit['mode']) ? 'circuit':'map') .'.php?cid='. $creationId;
    case 'mkmcups':
        $circuit = mysql_fetch_array(mysql_query('SELECT mode FROM `mkmcups` WHERE id="'. $creationId .'"'));
        return (empty($circuit['mode']) ? 'circuit':'map') .'.php?mid='. $creationId;
    case 'mkchars':
        return 'editPerso.php?id='. $creationId;
    case 'mkdecors':
        return 'editDecor.php?id='. $creationId;
    case 'mkbgs':
        return 'editBg.php?id='. $creationId;
    }
}
function getCollabUrl(&$collab) {
    $origin = (isset($_SERVER['HTTPS'])?'https':'http') . '://' . $_SERVER['HTTP_HOST'] . '/';
    return $origin . getCollabUrlPrefix($collab) . '&collab=' . $collab['secret'];
}

function getCollabLink($type, $creationId, $key) {
    $getCollab = mysql_fetch_array(mysql_query('SELECT id,type,creation_id,secret,rights FROM mkcollablinks WHERE secret="'. $key .'" AND type="'. $type .'" AND creation_id="'. $creationId .'"'));
    if (!$getCollab) return null;
    return collabPayload($getCollab);
}
function getCollabLinkById($id) {
    $getCollab = mysql_fetch_array(mysql_query('SELECT id,type,creation_id,secret,rights FROM mkcollablinks WHERE id="'. $id .'"'));
    if (!$getCollab) return null;
    return collabPayload($getCollab);
}
function getCollabLinkFromQuery($type, $creationId) {
    if (isset($_GET['collab']))
        return getCollabLink($type, $creationId, $_GET['collab']);
    return null;
}
function hasCollabGrants($type, $creationId, &$key, $rightKey) {
    if (!$key) return false;
    $collab = getCollabLink($type, $creationId, $key);
    return ($collab && isset($collab['rights'][$rightKey]));
}

function getCollabLinks($type, $creationId) {
    $getCollabs = mysql_query('SELECT id,type,creation_id,secret,rights FROM mkcollablinks WHERE type="'. $type .'" AND creation_id="'. $creationId .'"');
    $res = array();
    while ($getCollab = mysql_fetch_array($getCollabs))
        $res[] = collabPayload($getCollab);
    return $res;
}

function getCollabInputValues(&$post) {
    if (isset($post['rights']))
        $rights = array_keys($post['rights']);
    else
        $rights = array();
    $rightsStr = implode(',', $rights);
    return array(
        'type' => isset($_POST['type']) ? $_POST['type'] : null,
        'creation_id' => isset($_POST['id']) ? $_POST['id'] : null,
        'rights' => $rightsStr
    );
}

function printCollabPopup($params) {
    global $language;
    $itemType = $params['type'];
    $itemId = $params['id'];
    $itemLabel = $params['item_label'];
    $existingLinks = getCollabLinks($itemType, $itemId);
    $hasLinks = !empty($existingLinks);
    ?>
    <div class="collab-popup<?php
        if (!empty($params['class']))
            echo ' ' . $params['class'];
    ?>">
        <a href="#null" class="collab-popup-close" onclick="closeCollabPopup(event)">&times;</a>
        <h2><?php echo $language ? "Collaborate with other members" : "Collaborer avec d'autres membres"; ?></h2>
        <form class="collab-form collab-popup-section<?php if (!$hasLinks) echo ' add show'; ?>" data-type="<?php echo $itemType; ?>" data-id="<?php echo $itemId; ?>">
            <div class="collab-explain collab-form-on-add">
            <?php
            echo $language
                ? "This option allows you to authorize other members to view, use or modify your $itemLabel via a link that you will send to them. This can be used as part of a collab for example."
                : "Cette option vous permet d'autoriser d'autres membres à visualiser, utiliser ou modifier votre $itemLabel via un lien que vous leur enverrez. Cela peut servir dans le cadre d'une collab par exemple.";
            ?>
            </div>
            <div class="collab-current collab-form-on-edit">
                <?php echo $language ? 'Edit link' : 'Modifier le lien'; ?> <a href="#null"></a>
            </div>
            <div class="collab-options">
                <h3>Options</h3>
                <?php echo $language ? 'Users with the link will be able to:' : 'Les utilisateurs ayant le lien pourront :'; ?>
                <div class="collab-options-list">
                    <?php
                    foreach ($params['rights'] as &$right) {
                        ?>
                    <label class="collab-option">
                        <input type="checkbox" name="rights[<?php echo $right['key']; ?>]"<?php
                            if (!empty($right['default_val']))
                                echo ' checked="checked"';
                            if (!empty($right['depends_on']))
                                echo ' data-depends-on="'. $right['depends_on'] .'"';
                        ?> />
                        <span><?php
                        echo $right['label'];
                        ?></span>
                    </label>
                        <?php
                    }
                    unset($right);
                    ?>
                </div>
            </div>
            <div class="collab-submit collab-form-on-add">
            <a href="#null" class="collab-form-on-new" onclick="backToPopupCollabLinks(event)"><?php echo $language ? 'Back' : 'Retour'; ?></a>
            <input type="submit" class="collab-btn-primary" value="<?php echo $language ? 'Create share link' :'Créer le lien de partage'; ?>" />
            </div>
            <div class="collab-submit collab-form-on-edit">
            <a href="#null" onclick="backToPopupCollabLinks(event)"><?php echo $language ? 'Back' : 'Retour'; ?></a>
            <input type="submit" class="collab-btn-primary" value="<?php echo $language ? 'Edit share link' :'Modifier le lien de partage'; ?>" />
            </div>
        </form>
        <div class="collab-popup-success collab-popup-section">
        <?php
        echo $language ? "The following link has been generated:" : "Le lien suivant a été généré :";
        ?><br />
        <a href="#null"></a><br />
        <?php
        echo $language ? "Share it with all the members you want to give access to." : "Partagez-le à tous les membres à qui vous souhaitez y donner accès.";
        ?>
        </div>
        <?php
        if ($hasLinks) {
            ?>
        <div class="collab-popup-links collab-popup-section show">
            <h3><?php echo $language ? 'Your links' :'Vos liens'; ?></h3>
            <div class="collab-popup-links-list">
            <?php
            foreach ($existingLinks as $existingLink) {
                ?>
                <div class="collab-popup-link">
                    <a href="<?php echo $existingLink['url']; ?>"><?php echo $existingLink['url']; ?></a>
                    <div class="collab-popup-link-actions">
                        <input type="button" value="✎" class="collab-popup-link-action-edit" onclick="editPopupCollabLink(<?php echo htmlspecialchars(json_encode($existingLink)); ?>)" />
                        <input type="button" value="&times;" class="collab-popup-link-action-del" onclick="delPopupCollabLink(<?php echo $existingLink['id']; ?>)" />
                    </div>
                </div>
                <?php
            }
            ?>
            </div>
            <div class="collab-popup-links-new">
                <input type="button" value="<?php echo $language ? 'New share link...' :'Nouveau lien de partage...'; ?>" class="collab-btn-primary" onclick="addPopupCollabLink()" />
            </div>
        </div>
            <?php
        }
        ?>
    </div>
    <?php
}