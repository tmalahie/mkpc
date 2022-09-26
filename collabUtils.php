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