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
require_once('../includes/utils-nicks.php');
if (!hasRight('moderator')) {
	echo "Vous n'&ecirc;tes pas mod&eacute;rateur";
	mysql_close();
	exit;
}
$checkWord = null;
$checkSeparators = false;
$justAdded = false;
$justEdited = false;
$wordError = null;
$editedValue = null;
$editedWord = null;
$wordIdParam = isset($_POST['word_id']) ? $_POST['word_id'] : (isset($_GET['word_id']) ? $_GET['word_id'] : (isset($_GET['edit']) ? $_GET['edit'] : null));
if ($wordIdParam)
    $editedWord = mysql_fetch_array(mysql_query('SELECT id,word,ignore_separators FROM mkbadnicks WHERE id="'. $wordIdParam .'"'));
$editedSeparators = $editedWord ? (bool)$editedWord['ignore_separators'] : false;
if (!empty($_POST['word'])) {
    $checkWord = strtolower($_POST['word']);
    $checkSeparators = !empty($_POST['ignore_separators']);
    $listed = isValidNickExpression($checkWord) ? mysql_fetch_array(mysql_query('SELECT id,ignore_separators FROM mkbadnicks WHERE word="'. $checkWord .'"')) : null;
    if (!isValidNickExpression($checkWord))
        $wordError = 'invalid';
    elseif ($editedWord && $listed && $listed['id'] != $editedWord['id'])
        $wordError = 'duplicate';
    if ($wordError) {
        $editedValue = $checkWord;
        $checkWord = null;
    }
    elseif ($editedWord) {
        if ($editedWord['word'] !== $checkWord || $editedSeparators !== $checkSeparators) {
            mysql_query('UPDATE mkbadnicks SET word="'. $checkWord .'",ignore_separators='. ($checkSeparators ? 1:0) .' WHERE id='. $editedWord['id']);
            insertLog($id, 'NBlacklist '. $editedWord['id'], array_merge(
                array('type' => 'nick_word', 'id' => intval($editedWord['id'])),
                snapshotWord('mkbadnicks', $editedWord['id'], 'word,ignore_separators')
            ));
            $justAdded = true;
            $justEdited = true;
        }
        $editedWord = null;
    }
    else {
        if (!$listed) {
            mysql_query('INSERT INTO mkbadnicks SET word="'. $checkWord .'",ignore_separators='. ($checkSeparators ? 1:0));
            $wordId = mysql_insert_id();
        }
        elseif ((bool)$listed['ignore_separators'] !== $checkSeparators) {
            mysql_query('UPDATE mkbadnicks SET ignore_separators='. ($checkSeparators ? 1:0) .' WHERE id='. $listed['id']);
            $wordId = $listed['id'];
        }
        else
            $wordId = null;
        if ($wordId) {
            insertLog($id, 'NBlacklist '. $wordId, array_merge(
                array('type' => 'nick_word', 'id' => intval($wordId)),
                snapshotWord('mkbadnicks', $wordId, 'word,ignore_separators')
            ));
            $justAdded = true;
        }
    }
}
elseif (!empty($_POST['good_word'])) {
    $goodWord = strtolower($_POST['good_word']);
    if (!mysql_fetch_array(mysql_query('SELECT id FROM mkgoodwords WHERE word="'. $goodWord .'"'))) {
        mysql_query('INSERT INTO mkgoodwords SET word="'. $goodWord .'"');
        $goodWordId = mysql_insert_id();
        if ($goodWordId)
            insertLog($id, 'Whitelist '. $goodWordId, array_merge(
                array('type' => 'good_word', 'id' => intval($goodWordId)),
                snapshotWord('mkgoodwords', $goodWordId, 'word')
            ));
    }
}
elseif (!empty($_GET['word'])) {
    $checkWord = strtolower($_GET['word']);
    $checkSeparators = !empty($_GET['ignore_separators']);
    if (!isValidNickExpression($checkWord))
        $wordError = 'invalid';
    elseif ($editedWord && ($listed = mysql_fetch_array(mysql_query('SELECT id FROM mkbadnicks WHERE word="'. $checkWord .'"'))) && $listed['id'] != $editedWord['id'])
        $wordError = 'duplicate';
    if ($wordError) {
        $editedValue = $checkWord;
        $checkWord = null;
    }
}
elseif (isset($_GET['del'])) {
    $wordSnapshot = snapshotWord('mkbadnicks', $_GET['del'], 'word,ignore_separators');
    mysql_query('DELETE FROM mkbadnicks WHERE id="'. $_GET['del'] .'"');
    insertLog($id, 'NUnblacklist '. $_GET['del'], array_merge(
        array('type' => 'nick_word', 'id' => intval($_GET['del'])),
        $wordSnapshot
    ));
}
elseif (isset($_GET['del_good'])) {
    $wordSnapshot = snapshotWord('mkgoodwords', $_GET['del_good'], 'word');
    mysql_query('DELETE FROM mkgoodwords WHERE id="'. $_GET['del_good'] .'"');
    insertLog($id, 'Unwhitelist '. $_GET['del_good'], array_merge(
        array('type' => 'good_word', 'id' => intval($_GET['del_good'])),
        $wordSnapshot
    ));
}
$testNick = isset($_GET['test']) ? stripslashes($_GET['test']) : '';
$listedWord = ($checkWord !== null) ? mysql_fetch_array(mysql_query('SELECT id,ignore_separators FROM mkbadnicks WHERE word="'. $checkWord .'"')) : null;
if ($editedWord && $checkWord !== null)
    $isListed = ($editedWord['word'] === $checkWord) && ($editedSeparators === $checkSeparators);
else
    $isListed = $listedWord && ((bool)$listedWord['ignore_separators'] === $checkSeparators);
$isEditing = $editedWord && ($checkWord !== null) && !$isListed;
$maxMatches = 200;
$maxScanned = 5000;
$trimmedRows = 10;
function separatorsLabel($ignoreSeparators) {
    global $language;
    if ($ignoreSeparators)
        return $language ? 'ignoring - and _':'en ignorant - et _';
    return $language ? 'not ignoring - and _':'sans ignorer - et _';
}
function separatorsCheckboxLabel() {
    global $language;
    if ($language)
        return 'Also ignore <strong>-</strong> and <strong>_</strong> between letters (<em>hit-ler</em>, <em>h_i_t_l_e_r</em>)';
    return 'Ignorer aussi les <strong>-</strong> et <strong>_</strong> entre les lettres (<em>hit-ler</em>, <em>h_i_t_l_e_r</em>)';
}
function expressionHint() {
    global $language;
    if ($language)
        return 'Matches anywhere in the username.<br /><code>[il1]</code> means any of <em>i</em>, <em>l</em> or <em>1</em>, and <code>e+</code> means one or more <em>e</em>.<br />Letters, digits, <code>-</code> and <code>_</code> are allowed besides.';
    return 'Détecté n\'importe où dans le pseudo.<br /><code>[il1]</code> signifie <em>i</em>, <em>l</em> ou <em>1</em>, et <code>e+</code> signifie un ou plusieurs <em>e</em>.<br />Les lettres, chiffres, <code>-</code> et <code>_</code> sont autorisés en plus.';
}
function wordErrorMessage($wordError) {
    global $language;
    switch ($wordError) {
    case 'invalid':
        if ($language)
            return '<p class="word-error">This word is not valid: only letters, digits, <code>-</code>, <code>_</code>, <code>[...]</code> and <code>+</code> are allowed.</p>';
        return '<p class="word-error">Ce mot n\'est pas valide : seuls les lettres, chiffres, <code>-</code>, <code>_</code>, <code>[...]</code> et <code>+</code> sont autorisés.</p>';
    case 'duplicate':
        if ($language)
            return '<p class="word-error">This word is already in the list.</p>';
        return '<p class="word-error">Ce mot est déjà dans la liste.</p>';
    }
    return '';
}
?>
<!DOCTYPE html>
<html lang="<?php echo $language ? 'en':'fr'; ?>">
<head>
<title><?php echo $language ? 'Username blacklist':'Blacklist des pseudos'; ?> - Mario Kart PC</title>
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
.whitelist-form {
    margin-bottom: 5px;
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
td.word-cell {
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
#word-matches td.match-status {
    white-space: nowrap;
}
#word-matches td.match-inactive {
    color: #888;
    font-style: italic;
}
#word-matches .match-seen {
    display: block;
    font-size: 0.85em;
    font-weight: normal;
    font-style: normal;
    color: #888;
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
.word-added, .test-accepted {
    color: #0A0;
}
.test-refused {
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
    $wordPattern = nickExpressionPattern($checkWord, $checkSeparators);
    $wordGroups = blacklistPatternGroups(array($checkWord => $wordPattern));
    $getMatches = mysql_query('SELECT j.id,j.nom,j.banned,j.deleted,b.end_date,NULLIF(DATE(p.last_connect),0) AS last_connect FROM `mkjoueurs` j LEFT JOIN `mkbans` b ON b.player=j.id LEFT JOIN `mkprofiles` p ON p.id=j.id WHERE j.nom REGEXP "'. mysql_real_escape_string($wordPattern) .'" ORDER BY j.deleted ASC,j.banned ASC,last_connect DESC,j.id DESC LIMIT '. ($maxScanned+1));
    $newMatches = array();
    $knownMatches = array();
    while ($match = mysql_fetch_array($getMatches)) {
        if (!findBlacklistedWord(stripWhitelistedWords($match['nom']), $wordGroups, false))
            continue;
        if (!$isListed && !matchNickBlacklist($match['nom']))
            $newMatches[] = $match;
        else
            $knownMatches[] = $match;
    }
    $nbMatches = count($newMatches) + count($knownMatches);
    $scanTruncated = ($nbMatches > $maxScanned);
    $matches = array_merge($newMatches, $knownMatches);
    $truncated = (count($matches) > $maxMatches);
    $matches = array_slice($matches, 0, $maxMatches);
    $quotedWord = '&laquo;&nbsp;<strong>'. htmlspecialchars($checkWord) .'</strong>&nbsp;&raquo;';
    ?>
<main>
    <?php
    if ($justEdited) {
        if ($language)
            echo '<p class="word-added">Changes saved: '. $quotedWord .' is blacklisted, '. separatorsLabel($checkSeparators) .'.</p>';
        else
            echo '<p class="word-added">Modifications enregistrées : '. $quotedWord .' est blacklisté, '. separatorsLabel($checkSeparators) .'.</p>';
    }
    elseif ($justAdded) {
        if ($language)
            echo '<p class="word-added">'. $quotedWord .' is now blacklisted, '. separatorsLabel($checkSeparators) .'.</p>';
        else
            echo '<p class="word-added">'. $quotedWord .' est maintenant blacklisté, '. separatorsLabel($checkSeparators) .'.</p>';
    }
    elseif ($isEditing) {
        $quotedOld = '&laquo;&nbsp;<strong>'. htmlspecialchars($editedWord['word']) .'</strong>&nbsp;&raquo;';
        if ($language)
            echo '<p class="word-pending">You are changing <strong>'. $quotedOld .'</strong> ('. separatorsLabel($editedSeparators) .') into '. $quotedWord .'</strong> ('. separatorsLabel($checkSeparators) .'). Check the members below, then save.</p>';
        else
            echo '<p class="word-pending">Vous modifiez <strong>'. $quotedOld .'</strong> ('. separatorsLabel($editedSeparators) .') en <strong>'. $quotedWord .'</strong> ('. separatorsLabel($checkSeparators) .'). Vérifiez les membres ci-dessous, puis enregistrez.</p>';
    }
    elseif ($listedWord && !$isListed) {
        if ($language)
            echo '<p class="word-pending"><strong>'. $quotedWord .' is already blacklisted, '. separatorsLabel(!$checkSeparators) .'.</strong> Check the members below, then confirm to switch it to '. separatorsLabel($checkSeparators) .'.</p>';
        else
            echo '<p class="word-pending"><strong>'. $quotedWord .' est déjà blacklisté, '. separatorsLabel(!$checkSeparators) .'.</strong> Vérifiez les membres ci-dessous, puis confirmez pour le passer '. separatorsLabel($checkSeparators) .'.</p>';
    }
    elseif (!$isListed) {
        if ($language)
            echo '<p class="word-pending"><strong>The word is not blacklisted yet.</strong> Check the members below to take actions accordingly, and validate there are no false positives.<br />Click the button below to proceed.</p>';
        else
            echo '<p class="word-pending"><strong>Le mot n\'est pas encore blacklisté.</strong> Vérifiez la liste ci-dessous pour prendre les actions nécessaires et vérifier l\'absence de faux positifs.<br />Puis cliquez sur le bouton ci-dessous pour confirmer.</p>';
    }
    ?>
    <div class="word-actions">
        <?php
        if (!$isListed) {
            ?>
            <form method="post" action="nick-blacklist.php">
                <input type="hidden" name="word" value="<?php echo htmlspecialchars($checkWord); ?>" />
                <?php
                if ($checkSeparators)
                    echo '<input type="hidden" name="ignore_separators" value="1" />';
                if ($isEditing) {
                    echo '<input type="hidden" name="word_id" value="'. $editedWord['id'] .'" />';
                    echo '<input type="submit" class="action_button action_main" value="'. ($language ? 'Save changes' : 'Enregistrer') .'" />';
                    echo '<a href="?edit='. $editedWord['id'] .'" onclick="history.back();return false">'. ($language ? 'Back':'Retour') .'</a>';
                }
                else {
                    echo '<input type="submit" class="action_button action_main" value="'. ($language ? 'Add to blacklist' : 'Blacklister') .'" />';
                    echo '<a href="nick-blacklist.php">'. ($language ? 'Back':'Retour') .'</a>';
                }
                ?>
            </form>
            <?php
        }
        else {
            if ($listedWord)
                echo '<a class="action_button action_warning" href="?edit='. $listedWord['id'] .'">'. ($language ? 'Edit':'Modifier') .'</a>';
            echo '<a href="nick-blacklist.php">'. ($language ? 'Back':'Retour') .'</a>';
        }
        ?>
    </div>
    <h1><?php
    echo ($language ? 'Members matching ':'Membres correspondant à ') . $quotedWord;
    echo ' ('. ($scanTruncated ? $maxScanned.'+':$nbMatches) .')';
    ?></h1>
    <?php
    if (!$matches)
        echo '<p>'. ($language ? 'No member matches this word.':'Aucun membre ne correspond à ce mot.') .'</p>';
    else {
        if ($newMatches) {
            if ($language)
                echo '<p class="word-pending"><strong>'. count($newMatches) .' of these members are not blocked today.</strong> They are listed first: make sure they are not false positives.</p>';
            else
                echo '<p class="word-pending"><strong>'. count($newMatches) .' de ces membres ne sont pas bloqués aujourd\'hui.</strong> Ils sont listés en premier : vérifiez qu\'il ne s\'agit pas de faux positifs.</p>';
        }
        elseif (!$isListed) {
            if ($language)
                echo '<p>All these members are already blocked by another forbidden word.</p>';
            else
                echo '<p>Tous ces membres sont déjà bloqués par un autre mot interdit.</p>';
        }
        if ($language)
            echo '<p>These members keep their username: rename or ban them if needed.</p>';
        else
            echo '<p>Ces membres gardent leur pseudo : renommez-les ou bannissez-les si nécessaire.</p>';
        ?>
        <table id="word-matches">
            <tr id="titres">
                <td style="min-width: 120px"><?php echo $language ? 'Username':'Pseudo'; ?></td>
                <td><?php echo $language ? 'Status':'Statut'; ?></td>
                <td>Options</td>
            </tr>
            <?php
            $i = 0;
            $nbNew = count($newMatches);
            foreach ($matches as $match) {
                $isNew = ($i < $nbNew);
                if ($match['deleted']) {
                    $status = $language ? 'Deleted account':'Compte supprimé';
                    $inactive = true;
                }
                elseif ($match['banned']) {
                    if ($match['end_date']) {
                        $status = ($language ? 'Banned until ':'Banni jusqu\'au ') . $match['end_date'];
                        $inactive = false;
                    }
                    else {
                        $status = $language ? 'Banned':'Banni';
                        $inactive = true;
                    }
                }
                else {
                    $status = $language ? 'Active':'Actif';
                    $inactive = false;
                }
                ?>
                <tr class="<?php echo ($i%2 ? 'fonce':'clair') . ($isNew ? ' match-new':''); ?>">
                    <td><a href="profil.php?id=<?php echo $match['id']; ?>"><?php echo htmlspecialchars($match['nom']); ?></a></td>
                    <td class="match-status<?php if ($inactive) echo ' match-inactive'; ?>"><?php
                    if ($isNew)
                        echo '<span class="match-new-label">'. ($language ? 'Not blocked today':'Pas bloqué aujourd\'hui') .'</span>';
                    echo $status;
                    if ($match['last_connect'] && !$inactive)
                        echo '<span class="match-seen">'. ($language ? ('Last activity: '.$match['last_connect']) : ('Dernière activité : ' . preg_replace('#^(\d{4})-(\d{2})-(\d{2})$#', '$3/$2/$1', $match['last_connect']))) .'</span>';
                    ?></td>
                    <td class="options-cell"><?php
                    if (!$match['deleted']) {
                        echo '<a class="action_button" href="edit-pseudo.php?member='. $match['id'] .'" target="_blank">'. ($language ? 'Rename':'Renommer') .'</a>';
                        if (!$match['banned'])
                            echo '<a class="action_button action_delete" href="ban-player.php?member='. $match['id'] .'" target="_blank">'. ($language ? 'Ban':'Bannir') .'</a>';
                    }
                    ?></td>
                </tr>
                <?php
                $i++;
            }
            ?>
        </table>
        <?php
        if ($truncated)
            echo '<p>'. ($language ? 'Only the first '. $maxMatches .' members are shown.':'Seuls les '. $maxMatches .' premiers membres sont affichés.') .'</p>';
    }
    ?>
    <p><a href="nick-blacklist.php"><?php echo $language ? 'Back to the username blacklist':'Retour à la blacklist des pseudos'; ?></a><br />
    <a href="forum.php"><?php echo $language ? 'Back to the forum':'Retour au forum'; ?></a></p>
</main>
    <?php
}
elseif ($editedWord) {
    $formValue = ($editedValue !== null) ? $editedValue : $editedWord['word'];
    $formSeparators = ($editedValue !== null) ? $checkSeparators : $editedSeparators;
    ?>
<main>
    <h1><?php echo ($language ? 'Edit forbidden word ':'Modifier le mot interdit ') .'&laquo;&nbsp;'. htmlspecialchars($editedWord['word']) .'&nbsp;&raquo;'; ?></h1>
	<form class="blacklist-form" method="post" action="nick-blacklist.php">
        <input type="hidden" name="word_id" value="<?php echo $editedWord['id']; ?>" />
        <label>
            <?php echo $language ? 'Word:' : 'Mot :'; ?>
            <input type="text" name="word" required="required" value="<?php echo htmlspecialchars($formValue); ?>" />
        </label>
        <label class="inline-check">
            <input type="checkbox" name="ignore_separators" value="1"<?php if ($formSeparators) echo ' checked="checked"'; ?> />
            <?php echo separatorsCheckboxLabel(); ?>
        </label>
        <button type="submit" formmethod="get" class="action_button"><?php echo $language ? 'Preview matching members' : 'Voir les membres concernés'; ?></button>
        <input type="submit" class="action_button action_warning" value="<?php echo $language ? 'Save' : 'Enregistrer'; ?>" />
	</form>
    <p class="section-hint"><?php echo expressionHint(); ?></p>
    <?php
    echo wordErrorMessage($wordError);
    ?>
    <p><a href="nick-blacklist.php#forbidden-words"><?php echo $language ? 'Back to the username blacklist':'Retour à la blacklist des pseudos'; ?></a></p>
</main>
    <?php
}
else {
    $nbForbidden = mysql_fetch_array(mysql_query('SELECT COUNT(*) AS nb FROM mkbadnicks'));
    $nbAllowed = mysql_fetch_array(mysql_query('SELECT COUNT(*) AS nb FROM mkgoodwords'));
    ?>
<main>
    <h1><?php echo $language ? 'Username blacklist':'Blacklist des pseudos'; ?></h1>
    <p class="section-hint"><?php
    if ($language)
        echo 'Members cannot register or rename themselves with a username matching a forbidden word.<br />Existing accounts keep their username.';
    else
        echo 'Les membres ne peuvent pas s\'inscrire ou se renommer avec un pseudo correspondant à un mot interdit.<br />Les comptes existants gardent leur pseudo.';
    ?></p>

    <h2><?php echo $language ? 'Test a username' : 'Tester un pseudo'; ?></h2>
    <form method="get" action="nick-blacklist.php">
        <label>
            <?php echo $language ? 'Username:' : 'Pseudo :'; ?>
            <input type="text" name="test" placeholder="xXHitler42Xx" required="required" value="<?php echo htmlspecialchars($testNick); ?>" />
        </label>
        <input type="submit" class="action_button" value="<?php echo $language ? 'Test' : 'Tester'; ?>" />
    </form>
    <?php
    if ($testNick !== '') {
        $quotedNick = '&laquo;&nbsp;<strong>'. htmlspecialchars($testNick) .'</strong>&nbsp;&raquo;';
        $testMatch = matchNickBlacklist($testNick);
        if ($testMatch) {
            $quotedMatch = '&laquo;&nbsp;<strong>'. htmlspecialchars($testMatch['match']) .'</strong>&nbsp;&raquo;';
            $quotedCause = '&laquo;&nbsp;<strong>'. htmlspecialchars($testMatch['word']) .'</strong>&nbsp;&raquo;';
            if ($language)
                echo '<p class="test-refused">The username '. $quotedNick .' is <strong>refused</strong>: '. $quotedMatch .' matches the forbidden word '. $quotedCause .'.</p>';
            else
                echo '<p class="test-refused">Le pseudo '. $quotedNick .' est <strong>refusé</strong> : '. $quotedMatch .' correspond au mot interdit '. $quotedCause .'.</p>';
        }
        elseif ($language)
            echo '<p class="test-accepted">The username '. $quotedNick .' is <strong>accepted</strong>.</p>';
        else
            echo '<p class="test-accepted">Le pseudo '. $quotedNick .' est <strong>accepté</strong>.</p>';
    }
    ?>

    <h2 id="forbidden-words"><?php echo ($language ? 'Forbidden words' : 'Mots interdits') .' ('. $nbForbidden['nb'] .')'; ?></h2>
	<form class="blacklist-form" method="post" action="nick-blacklist.php">
        <label>
            <?php echo $language ? 'Add a word:' : 'Ajouter un mot :'; ?>
            <input type="text" name="word" placeholder="h+i+t+l+e+r+" required="required" />
        </label>
        <label class="inline-check">
            <input type="checkbox" name="ignore_separators" value="1" />
            <?php echo separatorsCheckboxLabel(); ?>
        </label>
        <button type="submit" formmethod="get" class="action_button"><?php echo $language ? 'Preview matching members' : 'Voir les membres concernés'; ?></button>
        <input type="submit" class="action_button action_warning" value="<?php echo $language ? 'Add to blacklist' : 'Blacklister'; ?>" />
	</form>
    <p class="section-hint"><?php echo expressionHint(); ?></p>
    <?php
    echo wordErrorMessage($wordError);
    ?>
    <table id="forbidden-list" class="trimmed">
        <tr id="titres">
            <td style="min-width: 120px"><?php echo $language ? 'Word':'Mot'; ?></td>
            <td>Options</td>
        </tr>
        <?php
        $getBlacklist = mysql_query('SELECT id,word,ignore_separators FROM mkbadnicks ORDER BY id DESC');
        $i = 0;
        while ($blacklist = mysql_fetch_array($getBlacklist)) {
            $previewUrl = '?word='. urlencode($blacklist['word']) . ($blacklist['ignore_separators'] ? '&amp;ignore_separators=1':'');
            echo '<tr class="'. ($i%2 ? 'fonce':'clair') . ($i >= $trimmedRows ? ' extra-row':'') .'">
                <td class="word-cell"><code>'.htmlspecialchars($blacklist['word']).'</code>'. ($blacklist['ignore_separators'] ? '<br /><span class="word-flag">'. separatorsLabel(true) .'</span>':'') .'</td>
                <td class="options-cell"><a class="action_button" href="'. $previewUrl .'">'. ($language ? 'See members':'Voir les membres') .'</a><a class="action_button action_warning" href="?edit='. $blacklist['id'] .'">'. ($language ? 'Edit':'Modifier') .'</a><a class="action_button action_delete" href="?del='. $blacklist['id'] .'" onclick="return confirmDelete(&quot;'.htmlspecialchars(addslashes($blacklist['word'])).'&quot;)">'. ($language ? 'Delete':'Supprimer') .'</a></td>
            </tr>';
            $i++;
        }
        ?>
    </table>
    <?php
    if ($i > $trimmedRows)
        echo '<button type="button" class="action_button show-all" onclick="showAll(this, \'forbidden-list\')">'. ($language ? 'Show all':'Tout afficher') .' ('. $i .')</button>';
    ?>

    <h2 id="allowed-words"><?php echo ($language ? 'Allowed words' : 'Mots autorisés') .' ('. $nbAllowed['nb'] .')'; ?></h2>
    <p class="section-hint"><?php
    if ($language)
        echo 'Innocent words that contain a forbidden one.<br />They are ignored before checking, in usernames and in the <a href="chat-blacklist.php">online chat</a>:<br />allowing <em>cucumber</em> stops <em>cum</em> from blocking it, while <em>cucumbercum</em> stays forbidden.';
    else
        echo 'Les mots innocents qui contiennent un mot interdit.<br />Ils sont ignorés avant la vérification, dans les pseudos et dans le <a href="chat-blacklist.php">chat en ligne</a> :<br />autoriser <em>cucumber</em> empêche <em>cum</em> de le bloquer, alors que <em>cucumbercum</em> reste interdit.';
    ?></p>
	<form class="whitelist-form" method="post" action="nick-blacklist.php#allowed-words">
        <label>
            <?php echo $language ? 'Add a word:' : 'Ajouter un mot :'; ?>
            <input type="text" name="good_word" placeholder="cucumber" required="required" />
        </label>
        <input type="submit" class="action_button" value="<?php echo $language ? 'Allow' : 'Autoriser'; ?>" />
	</form>
    <table id="allowed-list" class="trimmed">
        <tr id="titres">
            <td style="min-width: 120px"><?php echo $language ? 'Word':'Mot'; ?></td>
            <td>Options</td>
        </tr>
        <?php
        $getWhitelist = mysql_query('SELECT id,word FROM mkgoodwords ORDER BY id DESC');
        $i = 0;
        while ($whitelist = mysql_fetch_array($getWhitelist)) {
            echo '<tr class="'. ($i%2 ? 'fonce':'clair') . ($i >= $trimmedRows ? ' extra-row':'') .'">
                <td class="word-cell"><code>'.htmlspecialchars($whitelist['word']).'</code></td>
                <td class="options-cell"><a class="action_button action_delete" href="?del_good='. $whitelist['id'] .'#allowed-words" onclick="return confirmDelete(&quot;'.htmlspecialchars(addslashes($whitelist['word'])).'&quot;)">'. ($language ? 'Delete':'Supprimer') .'</a></td>
            </tr>';
            $i++;
        }
        ?>
    </table>
    <?php
    if ($i > $trimmedRows)
        echo '<button type="button" class="action_button show-all" onclick="showAll(this, \'allowed-list\')">'. ($language ? 'Show all':'Tout afficher') .' ('. $i .')</button>';
    ?>

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
