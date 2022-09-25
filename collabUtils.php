<?php
function printTrackCollabScreen($itemType, $itemId) {
    global $language;
    $existingLinks = getCollabLinks($itemType, $itemId);
    $hasLinks = !empty($existingLinks);
    ?>
    <div class="collab-backdrop open">
        <div class="collab-track">
            <a href="#null" class="collab-track-close" onclick="closeCollabPopup(event)">&times;</a>
            <h2><?php echo $language ? "Collaborate with other members" : "Collaborer avec d'autres membres"; ?></h2>
            <form class="collab-form collab-track-section<?php if (!$hasLinks) echo ' show'; ?>" data-type="<?php echo $itemType; ?>" data-id="<?php echo $itemId; ?>" data-onsave="onSaveTrackCollab">
                <div class="collab-explain">
                    Cette option vous permet d'autoriser d'autres membres à visualiser, utiliser ou modifier votre circuit, dans le cadre d'une collab par exemple.
                </div>
                <div class="collab-options">
                    <h3>Options</h3>
                    Les utilisateurs ayant ce lien pourront :
                    <div class="collab-options-list">
                        <label class="collab-option">
                            <input type="checkbox" name="rights[view]" checked="checked" />
                            <span>Voir le circuit dans l'éditeur</span>
                        </label>
                        <label class="collab-option">
                            <input type="checkbox" name="rights[edit]" data-depends-on="view" />
                            <span>Modifier le circuit</span>
                        </label>
                        <label class="collab-option">
                            <input type="checkbox" name="rights[use]" />
                            <span>Utiliser le circuit dans les coupes</span>
                        </label>
                    </div>
                </div>
                <div class="collab-submit">
                    <input type="submit" value="Créer le lien de partage" />
                </div>
            </form>
            <div class="collab-track-success collab-track-section">
                Le lien suivant a été généré :<br />
                <a href="#null"></a><br />
                Partagez le à tous les membres à qui vous souhaitez donner accès au circuit.
            </div>
            <?php
            if ($hasLinks) {
                ?>
            <div class="collab-track-links collab-track-section show">
                <h3>Vos liens</h3>
                <div class="collab-track-links-list">
                <?php
                foreach ($existingLinks as $existingLink) {
                    ?>
                    <div class="collab-track-link">
                        <a href="<?php echo $existingLink['url']; ?>"><?php echo $existingLink['url']; ?></a>
                        <div class="collab-track-link-actions">
                            <input type="button" value="✎" />
                            <input type="button" value="&times;" />
                        </div>
                    </div>
                    <?php
                }
                ?>
                </div>
                <div class="collab-track-links-new">
                    <input type="button" value="Nouveau lien de partage..." />
                </div>
            </div>
                <?php
            }
            ?>
        </div>
    </div>
    <?php
}
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
        return ($circuit['mode'] ? 'map':'circuit') .'.php?cid='. $circuit['id'];
    case 'mkmcups':
        $circuit = mysql_fetch_array(mysql_query('SELECT mode FROM `mkmcups` WHERE id="'. $creationId .'"'));
        return ($circuit['mode'] ? 'map':'circuit') .'.php?mid='. $circuit['id'];
    case 'mkchars':
        return 'editPerso.php?id='. $creationId;
    case 'mkdecors':
        return 'editDecor.php?id='. $creationId;
    case 'mkbgs':
        return 'editBgs.php?id='. $creationId;
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
function getCollabLinkFromQuery($type, $creationId) {
    if (isset($_GET['collab']))
        return getCollabLink($type, $creationId, $_GET['collab']);
    return null;
}

function getCollabLinks($type, $creationId) {
    $getCollabs = mysql_query('SELECT id,type,creation_id,secret,rights FROM mkcollablinks WHERE type="'. $type .'" AND creation_id="'. $creationId .'"');
    $res = array();
    while ($getCollab = mysql_fetch_array($getCollabs))
        $res[] = collabPayload($getCollab);
    return $res;
}