<?php
$reactions = array(
    'faces' => array(
        'laugh',
        'smile',
        'wink',
        'joy',
        'sweat',
        'heart',
        'heart_eyes',
        'party',
        'tongue',
        'sunglasses',
        'blush',
        'innocent',
        'see_no_evil',
        'dragon',
        'pensive',
        'thinking',
        'scream',
        'cry',
        'sad'
    ),
    'signs' => array(
        'thumbsup',
        'thumbsdown',
        'wave',
        'clap',
        'ok_hand',
        'muscle',
        'handshake',
        'pray',
        'raised_hands',
        'facepalm',
        'shrugging',
        'policeman',
        'detective',
        'up',
        'ok',
        'down',
        'check',
        'cross'
    ),
    'mkpc' => array(
        'holy_fuck',
        'gamba',
        'genius',
        'koopa',
        'mario_wink',
        'mario_dance',
        'mario_shrug',
        'mario_facepalm',
        'mario_dead',
        'mario_no',
        'luigi_fear',
        'cappy_eyes',
        'rob_dabbing',
        'super_hammer',
        'coin'
    )
);
function isReactionValid($reaction) {
    global $reactions;
    foreach ($reactions as $list) {
        foreach ($list as $r) {
            if ($r === $reaction)
                return true;
        }
    }
    return false;
}
function getReactionsByLink($type, $links) {
    if (empty($links)) $links = array('');
    $linksString = '"'. implode('","', $links) .'"';
    $getReactions = mysql_query('SELECT r.*,j.nom FROM mkreactions r INNER JOIN mkjoueurs j ON r.member=j.id WHERE r.type="'. $type .'" AND r.link IN ('. $linksString .') ORDER BY r.id');
    $res = array();
    foreach ($links as $link)
        $res[$link] = array();
    while ($reaction = mysql_fetch_array($getReactions))
        $res[$reaction['link']][] = $reaction;
    return $res;
}
function getReactions($type, $link) {
    $res = getReactionsByLink($type, array($link));
    return $res[$link];
}
function printReactions($type, $link, $reactions) {
    global $id;
    $reactionsGrouped = array();
    foreach ($reactions as $reaction) {
        $name = $reaction['reaction'];
        if (!isset($reactionsGrouped[$name])) {
            $reactionsGrouped[$name] = array(
                'checked' => false,
                'list' => array()
            );
        }
        if ($reaction['member'] == $id)
            $reactionsGrouped[$name]['checked'] = true;
        $reactionsGrouped[$name]['list'][] = $reaction['nom'];
    }
    switch ($type) {
    case 'topic':
        if (!empty($reactionsGrouped)) {
            foreach ($reactionsGrouped as $name => $reaction) {
                echo '<div data-name="'. $name .'" data-list="'. htmlspecialchars(implode(',',$reaction['list'])) .'"'. ($reaction['checked'] ? ' data-checked="1"':'') . ($id ? ' onclick="sendReaction(\''.$link.'\',this)"':' data-disabled="1"') .' onmouseover="showReactionDetails(this)" onmouseout="hideReactionDetails(this)">';
                    echo '<img src="images/forum/reactions/'.$name .'.png" alt="Add reaction" />';
                    echo '<span>'.count($reaction['list']).'</span>';
                echo '</div>';
            }
            if ($id) {
                echo '<div class="mReactionAdd" onclick="openReactions(\''.$link.'\',this)">';
                    echo '<img src="images/forum/react.png" alt="React" />';
                echo '</div>';
            }
        }
        break;
    }
}