<?php
include('../includes/session.php');
if (!$id) {
	echo "Vous n'&ecirc;tes pas connect&eacute;";
	exit;
}
include('../includes/language.php');
include('../includes/initdb.php');
require_once('../includes/getRights.php');
require_once('../includes/utils-logs.php');
require_once('../includes/utils-blacklist.php');
if (!hasRight('moderator')) {
	echo "Vous n'&ecirc;tes pas mod&eacute;rateur";
	mysql_close();
	exit;
}
$chatActions = array('none', 'block', 'mute');
$checkWord = null;
$checkSeparators = false;
$checkAction = 'none';
$justAdded = false;
$justEdited = false;
$wordError = null;
$editedValue = null;
$editedWord = null;
$wordIdParam = isset($_POST['word_id']) ? $_POST['word_id'] : (isset($_GET['word_id']) ? $_GET['word_id'] : (isset($_GET['edit']) ? $_GET['edit'] : null));
if ($wordIdParam)
    $editedWord = mysql_fetch_array(mysql_query('SELECT id,word,action,ignore_separators FROM mkbadwords WHERE id="'. $wordIdParam .'"'));
$editedSeparators = $editedWord ? (bool)$editedWord['ignore_separators'] : false;
$wordRequest = !empty($_POST['word']) ? $_POST : (!empty($_GET['word']) ? $_GET : null);
if ($wordRequest) {
    $checkWord = stripslashes(strtolower($wordRequest['word']));
    $checkSeparators = !empty($wordRequest['ignore_separators']);
    if (isset($wordRequest['action']) && in_array($wordRequest['action'], $chatActions))
        $checkAction = $wordRequest['action'];
    $listed = isValidChatExpression($checkWord) ? mysql_fetch_array(mysql_query('SELECT id,action,ignore_separators FROM mkbadwords WHERE word="'. mysql_real_escape_string($checkWord) .'"')) : null;
    if (!isValidChatExpression($checkWord))
        $wordError = 'invalid';
    elseif ($editedWord && $listed && $listed['id'] != $editedWord['id'])
        $wordError = 'duplicate';
    if ($wordError) {
        $editedValue = $checkWord;
        $checkWord = null;
    }
    elseif ($wordRequest === $_POST) {
        $changedWord = $editedWord ? $editedWord : $listed;
        if (!$changedWord) {
            mysql_query('INSERT INTO mkbadwords SET word="'. mysql_real_escape_string($checkWord) .'",action="'. $checkAction .'",ignore_separators='. ($checkSeparators ? 1:0));
            $wordId = mysql_insert_id();
            insertLog($id, 'Blacklist '. $wordId, array_merge(
                array('type' => 'chat_word', 'id' => intval($wordId)),
                snapshotWord('mkbadwords', $wordId, 'word,action,ignore_separators')
            ));
            $justAdded = true;
        }
        elseif (($changedWord['word'] !== $checkWord) || ($changedWord['action'] !== $checkAction) || ((bool)$changedWord['ignore_separators'] !== $checkSeparators)) {
            $wordBefore = snapshotWord('mkbadwords', $changedWord['id'], 'word,action,ignore_separators');
            mysql_query('UPDATE mkbadwords SET word="'. mysql_real_escape_string($checkWord) .'",action="'. $checkAction .'",ignore_separators='. ($checkSeparators ? 1:0) .' WHERE id='. $changedWord['id']);
            insertLog($id, 'BlacklistEdit '. $changedWord['id'], array(
                'type' => 'chat_word',
                'id' => intval($changedWord['id']),
                'before' => $wordBefore,
                'after' => snapshotWord('mkbadwords', $changedWord['id'], 'word,action,ignore_separators')
            ));
            $justAdded = true;
            $justEdited = true;
        }
        $editedWord = null;
    }
}
elseif (isset($_GET['del'])) {
    $wordSnapshot = snapshotWord('mkbadwords', $_GET['del'], 'word,action,ignore_separators');
    mysql_query('DELETE FROM mkbadwords WHERE id="'. $_GET['del'] .'"');
    insertLog($id, 'Unblacklist '. $_GET['del'], array_merge(
        array('type' => 'chat_word', 'id' => intval($_GET['del'])),
        $wordSnapshot
    ));
}
$testMsg = isset($_GET['test']) ? stripslashes($_GET['test']) : '';
$listedWord = ($checkWord !== null) ? mysql_fetch_array(mysql_query('SELECT id,action,ignore_separators FROM mkbadwords WHERE word="'. mysql_real_escape_string($checkWord) .'"')) : null;
if ($editedWord && $checkWord !== null)
    $isListed = ($editedWord['word'] === $checkWord) && ($editedWord['action'] === $checkAction) && ($editedSeparators === $checkSeparators);
else
    $isListed = $listedWord && ($listedWord['action'] === $checkAction) && ((bool)$listedWord['ignore_separators'] === $checkSeparators);
$isEditing = $editedWord && ($checkWord !== null) && !$isListed;
$maxMatches = 200;
$maxScanned = 2000;
$trimmedRows = 10;
function separatorsLabel($ignoreSeparators) {
    global $language;
    if ($ignoreSeparators)
        return $language ? 'ignoring spaces and punctuation':'en ignorant espaces et ponctuation';
    return $language ? 'not ignoring spaces and punctuation':'sans ignorer espaces et ponctuation';
}
function separatorsCheckboxLabel() {
    global $language;
    if ($language)
        return 'Also ignore spaces and punctuation between letters (<em>n i g g e r</em>, <em>ni.gg-er</em>)';
    return 'Ignorer aussi les espaces et la ponctuation entre les lettres (<em>n i g g e r</em>, <em>ni.gg-er</em>)';
}
function actionLabel($action) {
    global $language;
    switch ($action) {
    case 'mute':
        return $language ? 'Block + mute':'Bloquer + muter';
    case 'block':
        return $language ? 'Block message':'Bloquer le message';
    }
    return $language ? 'Log only':'Logguer uniquement';
}
function actionSelect($selectedAction) {
    global $language, $chatActions;
    $options = array(
        'none' => $language ? 'None (just log message)' : 'Aucune (logguer le message uniquement)',
        'block' => $language ? "Don't send message" : 'Ne pas envoyer le message',
        'mute' => $language ? "Don't send message + Mute member" : 'Ne pas envoyer le message + Muter le membre'
    );
    $html = '<select name="action">';
    foreach ($chatActions as $action)
        $html .= '<option value="'. $action .'"'. ($action === $selectedAction ? ' selected="selected"':'') .'>'. $options[$action] .'</option>';
    return $html .'</select>';
}
function expressionHint() {
    global $language;
    if ($language)
        return 'Matches whole words only.<br /><code>[il1]</code> means any of <em>i</em>, <em>l</em> or <em>1</em>, and <code>e+</code> means one or more <em>e</em>.<br />Any other character is matched as is.';
    return 'Détecté uniquement en tant que mot entier.<br /><code>[il1]</code> signifie <em>i</em>, <em>l</em> ou <em>1</em>, et <code>e+</code> signifie un ou plusieurs <em>e</em>.<br />Les autres caractères sont détectés tels quels.';
}
function wordErrorMessage($wordError) {
    global $language;
    switch ($wordError) {
    case 'invalid':
        if ($language)
            return '<p class="word-error">This word is not valid: every <code>[</code> needs a closing <code>]</code> with at least one character inside, and <code>+</code> must follow a character.</p>';
        return '<p class="word-error">Ce mot n\'est pas valide : chaque <code>[</code> doit être fermé par un <code>]</code> avec au moins un caractère à l\'intérieur, et <code>+</code> doit suivre un caractère.</p>';
    case 'duplicate':
        if ($language)
            return '<p class="word-error">This word is already in the list.</p>';
        return '<p class="word-error">Ce mot est déjà dans la liste.</p>';
    }
    return '';
}
function wordSettings($word, $action, $ignoreSeparators) {
    return '&laquo;&nbsp;<strong>'. htmlspecialchars($word) .'</strong>&nbsp;&raquo; ('. actionLabel($action) .', '. separatorsLabel($ignoreSeparators) .')';
}
function chatMessageText($message) {
    return html_entity_decode(strip_tags($message), ENT_QUOTES);
}
?>
<!DOCTYPE html>
<html lang="<?php echo $language ? 'en':'fr'; ?>">
<head>
<title><?php echo $language ? 'Online chat blacklist':'Blacklist chat en ligne'; ?> - Mario Kart PC</title>
<?php
include('../includes/heads.php');
?>
<link rel="stylesheet" type="text/css" href="styles/classement.css?reload=2" />
<style type="text/css">
main tr.clair a.action_button, main tr.fonce a.action_button {
    color: white;
}
.blacklist-form label {
    display: block;
}
.blacklist-form label select {
    width: 200px;
}
form input[type="submit"], form button {
    margin-top: 5px;
}
form label.inline-check {
    font-size: 0.9em;
    margin-top: 4px;
}
td.options-cell {
    white-space: nowrap;
}
td.options-cell .action_button + .action_button {
    margin-left: 6px;
}
.section-hint {
    font-size: 0.9em;
    color: #666;
    margin: 4px 0 8px 0;
}
.section-hint code, .word-cell code {
    font-size: 1.05em;
}
td.word-cell, td.action-cell {
    white-space: nowrap;
}
.word-flag {
    font-size: 0.8em;
    color: #888;
    margin-left: 6px;
}
table.trimmed tr.extra-row {
    display: none;
}
button.show-all {
    margin: 6px 0 0 0;
}
.word-pending {
    color: #F60;
}
.word-error {
    color: #C00;
}
#word-matches td.match-date, #word-matches td.match-status {
    white-space: nowrap;
}
#word-matches td.match-message {
    max-width: 420px;
    word-wrap: break-word;
    text-align: left;
}
#word-matches tr.match-new td {
    background-color: #FFE7B3;
}
#word-matches .match-new-label {
    display: block;
    font-size: 0.85em;
    font-weight: bold;
    color: #C60;
}
.word-added, .test-clean {
    color: #0A0;
}
.test-caught {
    color: #C00;
}
.word-actions {
    margin: 10px 0;
}
.word-actions form {
    display: inline-block;
    margin: 0;
}
.word-actions form input[type="submit"] {
    margin-top: 0;
}
.word-actions a {
    margin-left: 12px;
}
.action_main {
    font-size: 16px;
    padding: 7px 14px;
}
</style>
<?php
include('../includes/o_online.php');
?>
</head>
<body>
<?php
include('../includes/header.php');
$page = 'forum';
include('../includes/menu.php');
if ($checkWord !== null) {
    $wordGroups = blacklistPatternGroups(array($checkWord => chatExpressionPattern($checkWord, $checkSeparators)));
    $sqlPattern = '\\b('. chatExpressionPattern($checkWord, $checkSeparators, true) .')\\b';
    $getMatches = mysql_query('SELECT c.id,c.auteur,c.message,c.date,j.nom FROM `mkchat` c LEFT JOIN `mkjoueurs` j ON j.id=c.auteur WHERE c.message REGEXP "'. mysql_real_escape_string($sqlPattern) .'" ORDER BY c.id DESC LIMIT '. ($maxScanned+1));
    $newMatches = array();
    $knownMatches = array();
    $nbScanned = 0;
    while ($match = mysql_fetch_array($getMatches)) {
        $nbScanned++;
        $text = chatMessageText($match['message']);
        if (!findBlacklistedWord(stripWhitelistedWords($text), $wordGroups, true))
            continue;
        $match['caught'] = matchChatBlacklist($text);
        if (!$isListed && !$match['caught'])
            $newMatches[] = $match;
        else
            $knownMatches[] = $match;
    }
    $nbMatches = count($newMatches) + count($knownMatches);
    $scanTruncated = ($nbScanned > $maxScanned);
    $matches = array_slice(array_merge($newMatches, $knownMatches), 0, $maxMatches);
    $truncated = ($nbMatches > $maxMatches);
    $quotedWord = '&laquo;&nbsp;<strong>'. htmlspecialchars($checkWord) .'</strong>&nbsp;&raquo;';
    ?>
<main>
    <?php
    if ($justEdited) {
        if ($language)
            echo '<p class="word-added">Changes saved: '. wordSettings($checkWord, $checkAction, $checkSeparators) .'.</p>';
        else
            echo '<p class="word-added">Modifications enregistrées : '. wordSettings($checkWord, $checkAction, $checkSeparators) .'.</p>';
    }
    elseif ($justAdded) {
        if ($language)
            echo '<p class="word-added">'. wordSettings($checkWord, $checkAction, $checkSeparators) .' is now watched.</p>';
        else
            echo '<p class="word-added">'. wordSettings($checkWord, $checkAction, $checkSeparators) .' est maintenant surveillé.</p>';
    }
    elseif ($isEditing || ($listedWord && !$isListed)) {
        $currentWord = $isEditing ? $editedWord : array_merge($listedWord, array('word' => $checkWord));
        if ($language)
            echo '<p class="word-pending">You are changing '. wordSettings($currentWord['word'], $currentWord['action'], $currentWord['ignore_separators']) .' into '. wordSettings($checkWord, $checkAction, $checkSeparators) .'. Check the messages below, then save.</p>';
        else
            echo '<p class="word-pending">Vous modifiez '. wordSettings($currentWord['word'], $currentWord['action'], $currentWord['ignore_separators']) .' en '. wordSettings($checkWord, $checkAction, $checkSeparators) .'. Vérifiez les messages ci-dessous, puis enregistrez.</p>';
    }
    elseif (!$isListed) {
        if ($language)
            echo '<p class="word-pending"><strong>The word is not watched yet.</strong> Check the messages below to validate there are no false positives, then click the button below to proceed.</p>';
        else
            echo '<p class="word-pending"><strong>Le mot n\'est pas encore surveillé.</strong> Vérifiez les messages ci-dessous pour vous assurer de l\'absence de faux positifs, puis cliquez sur le bouton ci-dessous pour confirmer.</p>';
    }
    ?>
    <div class="word-actions">
        <?php
        if (!$isListed) {
            ?>
            <form method="post" action="chat-blacklist.php">
                <input type="hidden" name="word" value="<?php echo htmlspecialchars($checkWord); ?>" />
                <input type="hidden" name="action" value="<?php echo $checkAction; ?>" />
                <?php
                if ($checkSeparators)
                    echo '<input type="hidden" name="ignore_separators" value="1" />';
                if ($isEditing) {
                    echo '<input type="hidden" name="word_id" value="'. $editedWord['id'] .'" />';
                    echo '<input type="submit" class="action_button action_main" value="'. ($language ? 'Save changes' : 'Enregistrer') .'" />';
                    echo '<a href="?edit='. $editedWord['id'] .'" onclick="history.back();return false">'. ($language ? 'Back':'Retour') .'</a>';
                }
                else {
                    echo '<input type="submit" class="action_button action_main" value="'. ($language ? ($listedWord ? 'Save changes':'Add to blacklist') : ($listedWord ? 'Enregistrer':'Blacklister')) .'" />';
                    echo '<a href="chat-blacklist.php">'. ($language ? 'Back':'Retour') .'</a>';
                }
                ?>
            </form>
            <?php
        }
        else {
            if ($listedWord)
                echo '<a class="action_button action_warning" href="?edit='. $listedWord['id'] .'">'. ($language ? 'Edit':'Modifier') .'</a>';
            echo '<a href="chat-blacklist.php">'. ($language ? 'Back':'Retour') .'</a>';
        }
        ?>
    </div>
    <h1><?php
    echo ($language ? 'Messages matching ':'Messages correspondant à ') . $quotedWord;
    echo ' ('. ($scanTruncated ? $maxScanned.'+':$nbMatches) .')';
    ?></h1>
    <?php
    if ($scanTruncated) {
        if ($language)
            echo '<p class="section-hint">Only the latest '. $maxScanned .' matching messages were checked.</p>';
        else
            echo '<p class="section-hint">Seuls les '. $maxScanned .' derniers messages correspondants ont été vérifiés.</p>';
    }
    if (!$matches)
        echo '<p>'. ($language ? 'No chat message matches this word.':'Aucun message du chat ne correspond à ce mot.') .'</p>';
    else {
        if ($newMatches) {
            if ($language)
                echo '<p class="word-pending"><strong>'. count($newMatches) .' of these messages are not caught today.</strong> They are listed first: make sure they are not false positives.</p>';
            else
                echo '<p class="word-pending"><strong>'. count($newMatches) .' de ces messages ne sont pas détectés aujourd\'hui.</strong> Ils sont listés en premier : vérifiez qu\'il ne s\'agit pas de faux positifs.</p>';
        }
        elseif (!$isListed) {
            if ($language)
                echo '<p>All these messages are already caught by another watched word.</p>';
            else
                echo '<p>Tous ces messages sont déjà détectés par un autre mot surveillé.</p>';
        }
        ?>
        <table id="word-matches">
            <tr id="titres">
                <td>Date</td>
                <td><?php echo $language ? 'Member':'Membre'; ?></td>
                <td>Message</td>
                <td><?php echo $language ? 'Today':'Aujourd\'hui'; ?></td>
            </tr>
            <?php
            $i = 0;
            $nbNew = count($newMatches);
            foreach ($matches as $match) {
                $isNew = ($i < $nbNew);
                ?>
                <tr class="<?php echo ($i%2 ? 'fonce':'clair') . ($isNew ? ' match-new':''); ?>">
                    <td class="match-date"><?php echo substr($match['date'], 0, 10); ?></td>
                    <td><?php
                    if ($match['nom'] !== null)
                        echo '<a href="chat-logs.php?pseudo='. urlencode($match['nom']) .'" target="_blank">'. htmlspecialchars($match['nom']) .'</a>';
                    else
                        echo '<em>'. ($language ? 'Deleted account':'Compte supprimé') .'</em>';
                    ?></td>
                    <td class="match-message"><?php echo htmlspecialchars(chatMessageText($match['message'])); ?></td>
                    <td class="match-status"><?php
                    if ($isNew)
                        echo '<span class="match-new-label">'. ($language ? 'Not caught today':'Pas détecté aujourd\'hui') .'</span>';
                    elseif ($match['caught'])
                        echo actionLabel($match['caught']['action']);
                    ?></td>
                </tr>
                <?php
                $i++;
            }
            ?>
        </table>
        <?php
        if ($truncated)
            echo '<p>'. ($language ? 'Only the first '. $maxMatches .' messages are shown.':'Seuls les '. $maxMatches .' premiers messages sont affichés.') .'</p>';
    }
    ?>
    <p><a href="chat-blacklist.php"><?php echo $language ? 'Back to the online chat blacklist':'Retour à la blacklist du chat en ligne'; ?></a><br />
    <a href="forum.php"><?php echo $language ? 'Back to the forum':'Retour au forum'; ?></a></p>
</main>
    <?php
}
elseif ($editedWord) {
    $formValue = ($editedValue !== null) ? $editedValue : $editedWord['word'];
    $formSeparators = ($editedValue !== null) ? $checkSeparators : $editedSeparators;
    $formAction = ($editedValue !== null) ? $checkAction : $editedWord['action'];
    ?>
<main>
    <h1><?php echo ($language ? 'Edit watched word ':'Modifier le mot surveillé ') .'&laquo;&nbsp;'. htmlspecialchars($editedWord['word']) .'&nbsp;&raquo;'; ?></h1>
	<form class="blacklist-form" method="post" action="chat-blacklist.php">
        <input type="hidden" name="word_id" value="<?php echo $editedWord['id']; ?>" />
        <label>
            <?php echo $language ? 'Word:' : 'Mot :'; ?>
            <input type="text" name="word" required="required" value="<?php echo htmlspecialchars($formValue); ?>" />
        </label>
        <label class="inline-check">
            <input type="checkbox" name="ignore_separators" value="1"<?php if ($formSeparators) echo ' checked="checked"'; ?> />
            <?php echo separatorsCheckboxLabel(); ?>
        </label>
        <label>
            <?php echo ($language ? 'Action:' : 'Action :') .' '. actionSelect($formAction); ?>
        </label>
        <button type="submit" formmethod="get" class="action_button"><?php echo $language ? 'Preview matching messages' : 'Voir les messages concernés'; ?></button>
        <input type="submit" class="action_button action_warning" value="<?php echo $language ? 'Save' : 'Enregistrer'; ?>" />
	</form>
    <p class="section-hint"><?php echo expressionHint(); ?></p>
    <?php
    echo wordErrorMessage($wordError);
    ?>
    <p><a href="chat-blacklist.php#watched-words"><?php echo $language ? 'Back to the online chat blacklist':'Retour à la blacklist du chat en ligne'; ?></a></p>
</main>
    <?php
}
else {
    $nbWatched = mysql_fetch_array(mysql_query('SELECT COUNT(*) AS nb FROM mkbadwords'));
    $nbAllowed = mysql_fetch_array(mysql_query('SELECT COUNT(*) AS nb FROM mkgoodwords'));
    ?>
<main>
    <h1><?php echo $language ? 'Online chat blacklist':'Blacklist du chat en ligne'; ?></h1>
    <p class="section-hint"><?php
    if ($language)
        echo 'Messages containing a watched word are logged in the <a href="blacklist-logs.php">message logs</a>, and can also be blocked or get their author muted.<br />Private games are not checked.';
    else
        echo 'Les messages contenant un mot surveillé sont enregistrés dans les <a href="blacklist-logs.php">logs des messages</a>, et peuvent aussi être bloqués ou faire muter leur auteur.<br />Les parties privées ne sont pas vérifiées.';
    ?></p>

    <h2><?php echo $language ? 'Test a message' : 'Tester un message'; ?></h2>
    <form method="get" action="chat-blacklist.php">
        <label>
            <?php echo $language ? 'Message:' : 'Message :'; ?>
            <input type="text" name="test" placeholder="t'es un fumier" required="required" value="<?php echo htmlspecialchars($testMsg); ?>" />
        </label>
        <input type="submit" class="action_button" value="<?php echo $language ? 'Test' : 'Tester'; ?>" />
    </form>
    <?php
    if ($testMsg !== '') {
        $testMatch = matchChatBlacklist($testMsg);
        if ($testMatch) {
            $quotedMatch = '&laquo;&nbsp;<strong>'. htmlspecialchars($testMatch['match']) .'</strong>&nbsp;&raquo;';
            $quotedCause = '&laquo;&nbsp;<strong>'. htmlspecialchars($testMatch['word']) .'</strong>&nbsp;&raquo;';
            switch ($testMatch['action']) {
            case 'mute':
                $consequence = $language ? 'the message is blocked and the member is muted':'le message est bloqué et le membre est muté';
                break;
            case 'block':
                $consequence = $language ? 'the message is blocked':'le message est bloqué';
                break;
            default:
                $consequence = $language ? 'the message is sent and logged':'le message est envoyé et loggué';
            }
            if ($language)
                echo '<p class="test-caught">'. $quotedMatch .' matches the watched word '. $quotedCause .': '. $consequence .'.</p>';
            else
                echo '<p class="test-caught">'. $quotedMatch .' correspond au mot surveillé '. $quotedCause .' : '. $consequence .'.</p>';
        }
        elseif ($language)
            echo '<p class="test-clean">No watched word: the message is sent normally.</p>';
        else
            echo '<p class="test-clean">Aucun mot surveillé : le message est envoyé normalement.</p>';
    }
    ?>

    <h2 id="watched-words"><?php echo ($language ? 'Watched words' : 'Mots surveillés') .' ('. $nbWatched['nb'] .')'; ?></h2>
	<form class="blacklist-form" method="post" action="chat-blacklist.php">
        <label>
            <?php echo $language ? 'Add a word:' : 'Ajouter un mot :'; ?>
            <input type="text" name="word" placeholder="f+u+m+i+e+r+" required="required" />
        </label>
        <label class="inline-check">
            <input type="checkbox" name="ignore_separators" value="1" />
            <?php echo separatorsCheckboxLabel(); ?>
        </label>
        <label>
            <?php echo ($language ? 'Action:' : 'Action :') .' '. actionSelect('none'); ?>
        </label>
        <button type="submit" formmethod="get" class="action_button"><?php echo $language ? 'Preview matching messages' : 'Voir les messages concernés'; ?></button>
        <input type="submit" class="action_button action_warning" value="<?php echo $language ? 'Add to blacklist' : 'Blacklister'; ?>" />
	</form>
    <p class="section-hint"><?php echo expressionHint(); ?></p>
    <?php
    echo wordErrorMessage($wordError);
    ?>
    <table id="watched-list" class="trimmed">
        <tr id="titres">
            <td style="min-width: 120px"><?php echo $language ? 'Word':'Mot'; ?></td>
            <td>Action</td>
            <td>Options</td>
        </tr>
        <?php
        $getBlacklist = mysql_query('SELECT id,word,action,ignore_separators FROM mkbadwords ORDER BY id DESC');
        $i = 0;
        while ($blacklist = mysql_fetch_array($getBlacklist)) {
            $previewUrl = '?word='. urlencode($blacklist['word']) .'&amp;action='. $blacklist['action'] . ($blacklist['ignore_separators'] ? '&amp;ignore_separators=1':'');
            echo '<tr class="'. ($i%2 ? 'fonce':'clair') . ($i >= $trimmedRows ? ' extra-row':'') .'">
                <td class="word-cell"><code>'.htmlspecialchars($blacklist['word']).'</code>'. ($blacklist['ignore_separators'] ? '<br /><span class="word-flag">'. separatorsLabel(true) .'</span>':'') .'</td>
                <td class="action-cell">'. actionLabel($blacklist['action']) .'</td>
                <td class="options-cell"><a class="action_button" href="'. $previewUrl .'">'. ($language ? 'See messages':'Voir les messages') .'</a><a class="action_button action_warning" href="?edit='. $blacklist['id'] .'">'. ($language ? 'Edit':'Modifier') .'</a><a class="action_button action_delete" href="?del='. $blacklist['id'] .'" onclick="return confirmDelete(&quot;'.htmlspecialchars(addslashes($blacklist['word'])).'&quot;)">'. ($language ? 'Delete':'Supprimer') .'</a></td>
            </tr>';
            $i++;
        }
        ?>
    </table>
    <?php
    if ($i > $trimmedRows)
        echo '<button type="button" class="action_button show-all" onclick="showAll(this, \'watched-list\')">'. ($language ? 'Show all':'Tout afficher') .' ('. $i .')</button>';
    ?>

    <h2><?php echo ($language ? 'Allowed words' : 'Mots autorisés') .' ('. $nbAllowed['nb'] .')'; ?></h2>
    <p class="section-hint"><?php
    if ($language)
        echo 'Innocent words that contain a watched one, ignored before checking.<br />They are shared with usernames: manage them on the <a href="nick-blacklist.php#allowed-words">username blacklist</a>.';
    else
        echo 'Les mots innocents qui contiennent un mot surveillé, ignorés avant la vérification.<br />Ils sont communs avec les pseudos : gérez-les sur la <a href="nick-blacklist.php#allowed-words">blacklist des pseudos</a>.';
    ?></p>

	<p><a href="forum.php"><?php echo $language ? 'Back to the forum':'Retour au forum'; ?></a><br />
	<a href="index.php"><?php echo $language ? 'Back to Mario Kart PC':'Retour &agrave; Mario Kart PC'; ?></a></p>
</main>
<script type="text/javascript">
    function confirmDelete(word) {
        return confirm("<?php echo $language ? 'Remove \""+ word +"\" from the list?' : 'Supprimer \""+ word +"\" de la liste ?'; ?>");
    }
    function showAll(button, tableId) {
        document.getElementById(tableId).className = "";
        button.parentNode.removeChild(button);
    }
</script>
    <?php
}
include('../includes/footer.php');
mysql_close();
?>
</body>
</html>
