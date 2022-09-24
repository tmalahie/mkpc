<?php
function printTrackCollabScreen($itemType, $itemId) {
    global $language;
    ?>
    <div class="collab-backdrop">
        <div class="collab-track">
            <a href="#null" class="collab-track-close" onclick="closeCollabPopup(event)">&times;</a>
            <form class="collab-form collab-track-section show" data-type="<?php echo $itemType; ?>" data-id="<?php echo $itemId; ?>" data-onsave="onSaveTrackCollab">
                <h2><?php echo $language ? "Collaborate with other members" : "Collaborer avec d'autres membres"; ?></h2>
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
                <a class="collab-track-link" href="#null">aaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaa</a><br />
                Partagez le à tous les membres à qui vous souhaitez donner accès au circuit.
            </div>
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


function getCollabLink($type, $creationId, $key) {
    $getCollab = mysql_fetch_array(mysql_query('SELECT id,secret,rights FROM mkcollablinks WHERE secret="'. $key .'" AND type="'. $type .'" AND creation_id="'. $creationId .'"'));
    if (!$getCollab) return null;
    $rightsList = explode(',', $getCollab['rights']);
    $rightsDict = array();
    foreach ($rightsList as $right)
        $rightsDict[$right] = 1;
    return array(
        'id' => $getCollab['id'],
        'key' => $getCollab['secret'],
        'rights' => $rightsDict
    );
}
function getCollabLinkFromQuery($type, $creationId) {
    if (isset($_GET['collab']))
        return getCollabLink($type, $creationId, $_GET['collab']);
    return null;
}