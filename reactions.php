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
function populateReactionsData(&$messages) {
	$reactionLinks = array();
	foreach ($messages as $message)
		$reactionLinks[] = $message['topic'].','.$message['id'];
	$messageReactions = getReactionsByLink('topic', $reactionLinks);
	foreach ($messages as &$message)
		$message['reactions'] = $messageReactions[$message['topic'].','.$message['id']];
}
function printReactions($type, $link, $reactions, $mayReact=null) {
    global $id;
    if (null === $mayReact)
        $mayReact = !!$id;
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
                echo '<div data-name="'. $name .'" data-list="'. htmlspecialchars(implode(',',$reaction['list'])) .'"'. ($reaction['checked'] ? ' data-checked="1"':'') . ($mayReact ? ' onclick="sendReaction(\''.$link.'\',this)"':' data-disabled="1"') .' onmouseover="showReactionDetails(this)" onmouseout="hideReactionDetails(this)">';
                    echo '<img src="images/forum/reactions/'.$name .'.png" alt="'. $name .'" oncontextmenu="return false" />';
                    echo '<span>'.count($reaction['list']).'</span>';
                echo '</div>';
            }
            if ($mayReact) {
                echo '<div class="mReactionAdd" onclick="openReactions(\''.$link.'\',this)">';
                    echo '<img src="images/forum/react.png" alt="React" />';
                echo '</div>';
            }
        }
        break;
    }
}
function printReactionUI() {
    global $id, $language, $reactions;
    if ($id) {
        ?>
        <div id="message-reactions" onclick="closeReactions()">
			<div class="message-reactions-dialog" onclick="event.stopPropagation()">
			<?php
			echo '<div class="message-reactions-title">';
				echo '<h3>'. ($language ? 'Add reaction...':'Ajouter une réaction...') .'</h3>';
				echo '<a href="#null" onclick="closeReactions();return false" title="'. ($language ? 'Close':'Fermer') .'">&times;</a>';
			echo '</div>';
			echo '<div class="message-reactions-cat">';
			foreach ($reactions as $list) {
				echo '<div>';
				foreach ($list as $reaction) {
					echo '<a href="#null" onclick="addReaction(\''.$reaction.'\');return false">';
					echo '<img src="images/forum/reactions/'. $reaction .'.png" alt="'. $reaction .'" title=":'. $reaction .':" />';
					echo '</a>';
				}
				echo '</div>';
			}
			echo '</div>';
			?>
            </div>
		</div>
        <?php
    }
    ?>
    <div id="message-reactions-details">
        <img src="images/forum/reactions/smile.png" alt="smile" />
        <div></div>
    </div>
    <?php
}