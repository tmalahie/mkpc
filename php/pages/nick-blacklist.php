<?php
include('../includes/session.php');
if (!$id) {
	echo "Vous n'&ecirc;tes pas connect&eacute;";
	exit;
}
include('../includes/language.php');
include('../includes/initdb.php');
require_once('../includes/getRights.php');
if (!hasRight('moderator')) {
	echo "Vous n'&ecirc;tes pas mod&eacute;rateur";
	mysql_close();
	exit;
}
$checkWord = null;
$justAdded = false;
if (!empty($_POST['word'])) {
    $checkWord = strtolower($_POST['word']);
    if (!mysql_fetch_array(mysql_query('SELECT id FROM mkbadnicks WHERE word="'. $checkWord .'"'))) {
        mysql_query('INSERT INTO mkbadnicks SET word="'. $checkWord .'"');
        $wordId = mysql_insert_id();
        if ($wordId) {
            mysql_query('INSERT INTO `mklogs` VALUES(NULL,NULL, '. $id .', "NBlacklist '. $wordId .'")');
            $justAdded = true;
        }
    }
}
elseif (!empty($_GET['word']))
    $checkWord = strtolower($_GET['word']);
elseif (isset($_GET['del'])) {
    mysql_query('DELETE FROM mkbadnicks WHERE id="'. $_GET['del'] .'"');
    mysql_query('INSERT INTO `mklogs` VALUES(NULL,NULL, '. $id .', "NUnblacklist '. $_GET['del'] .'")');
}
$isListed = ($checkWord !== null) && mysql_fetch_array(mysql_query('SELECT id FROM mkbadnicks WHERE word="'. $checkWord .'"'));
$maxMatches = 200;
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
form label {
    display: block;
}
form input[type="submit"], form button {
    margin-top: 5px;
}
td.options-cell {
    white-space: nowrap;
}
td.options-cell .action_button + .action_button {
    margin-left: 6px;
}
.word-pending {
    color: #F60;
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
.word-added {
    color: #0A0;
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
    $getMatches = mysql_query('SELECT j.id,j.nom,j.banned,j.deleted,b.end_date,NULLIF(DATE(p.last_connect),0) AS last_connect FROM `mkjoueurs` j LEFT JOIN `mkbans` b ON b.player=j.id LEFT JOIN `mkprofiles` p ON p.id=j.id WHERE LOCATE("'. $checkWord .'",j.nom)>0 ORDER BY j.deleted ASC,j.banned ASC,last_connect DESC,j.id DESC LIMIT '. ($maxMatches+1));
    $matches = array();
    while ($match = mysql_fetch_array($getMatches))
        $matches[] = $match;
    $truncated = (count($matches) > $maxMatches);
    if ($truncated)
        array_pop($matches);
    $quotedWord = '&laquo;&nbsp;<strong>'. htmlspecialchars($checkWord) .'</strong>&nbsp;&raquo;';
    ?>
<main>
    <?php
    if ($justAdded) {
        if ($language)
            echo '<p class="word-added">The word '. $quotedWord .' has been added to the username blacklist.</p>';
        else
            echo '<p class="word-added">Le mot '. $quotedWord .' a été ajouté à la blacklist des pseudos.</p>';
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
                <input type="submit" class="action_button action_main" value="<?php echo $language ? 'Add to blacklist' : 'Blacklister'; ?>" />
                <a href="nick-blacklist.php"><?php echo $language ? 'Back':'Retour'; ?></a>
            </form>
            <?php
        }
        ?>
    </div>
    <h1><?php
    echo ($language ? 'Members whose username contains ':'Membres dont le pseudo contient ') . $quotedWord;
    echo ' ('. ($truncated ? $maxMatches.'+':count($matches)) .')';
    ?></h1>
    <?php
    if (!$matches)
        echo '<p>'. ($language ? 'No member matches this word.':'Aucun membre ne correspond à ce mot.') .'</p>';
    else {
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
            foreach ($matches as $match) {
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
                <tr class="<?php echo $i%2 ? 'fonce':'clair'; ?>">
                    <td><a href="profil.php?id=<?php echo $match['id']; ?>"><?php echo htmlspecialchars($match['nom']); ?></a></td>
                    <td class="match-status<?php if ($inactive) echo ' match-inactive'; ?>"><?php
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
else {
    ?>
<main>
    <h1><?php echo $language ? 'Manage forbidden words in usernames':'Gérer les mots interdits dans les pseudos'; ?></h1>
    <p><?php
    if ($language) {
        ?>
        This page allows you to manage a words blacklist for usernames.<br />
        A member can no longer register, nor rename themselves, with a username containing one of these words: they are told their username is inappropriate.<br />
        Unlike the <a href="chat-blacklist.php"><strong>online chat blacklist</strong></a>, a word matches anywhere inside the username, so <em>hitler</em> also blocks <em>xXHitler42Xx</em>.<br />
        Accounts registered before a word was added keep their username: rename them from the <a href="edit-pseudo.php"><strong>username change page</strong></a>.
        <?php
    }
    else {
        ?>
        Cette page vous permet de gérer une blacklist de mots pour les pseudos.<br />
        Un membre ne peut plus s'inscrire, ni se renommer, avec un pseudo contenant un de ces mots : il lui est indiqué que son pseudo est inapproprié.<br />
        Contrairement à la <a href="chat-blacklist.php"><strong>blacklist du chat en ligne</strong></a>, un mot est détecté n'importe où dans le pseudo, donc <em>hitler</em> bloque aussi <em>xXHitler42Xx</em>.<br />
        Les comptes inscrits avant l'ajout d'un mot gardent leur pseudo : renommez-les depuis la <a href="edit-pseudo.php"><strong>page de modification de pseudo</strong></a>.
        <?php
    }
    ?>
    </p>
	<form method="post" action="nick-blacklist.php">
        <label>
            <?php echo $language ? 'Add a word:' : 'Ajouter un mot :'; ?>
            <input type="text" name="word" placeholder="hitler" required="required" />
        </label>
        <button type="submit" formmethod="get" class="action_button"><?php echo $language ? 'Preview matching members' : 'Voir les membres concernés'; ?></button>
        <input type="submit" class="action_button action_warning" value="<?php echo $language ? 'Add to blacklist' : 'Blacklister'; ?>" />
	</form>
    <h2><?php echo ($language ? 'Current forbidden word list:' : 'Liste des mots interdits :'); ?></h2>
    <table>
        <tr id="titres">
            <td style="min-width: 120px"><?php echo $language ? 'Word':'Mot'; ?></td>
            <td>Options</td>
        </tr>
        <?php
        $getBlacklist = mysql_query('SELECT id,word FROM mkbadnicks ORDER BY id DESC');
        $i = 0;
        while ($blacklist = mysql_fetch_array($getBlacklist)) {
            echo '<tr class="'. ($i%2 ? 'fonce':'clair') .'">
                <td>'.htmlspecialchars($blacklist['word']).'</td>
                <td class="options-cell"><a class="action_button" href="?word='. urlencode($blacklist['word']) .'">'. ($language ? 'See members':'Voir les membres') .'</a><a class="action_button action_delete" href="?del='. $blacklist['id'] .'" onclick="return confirmDelete(&quot;'.htmlspecialchars(addslashes($blacklist['word'])).'&quot;)">'. ($language ? 'Delete':'Supprimer') .'</a></td>
            </tr>';
            $i++;
        }
        ?>
    </table>
	<p><a href="forum.php"><?php echo $language ? 'Back to the forum':'Retour au forum'; ?></a><br />
	<a href="index.php"><?php echo $language ? 'Back to Mario Kart PC':'Retour &agrave; Mario Kart PC'; ?></a></p>
</main>
<script type="text/javascript">
    function confirmDelete(word) {
        return confirm("<?php echo $language ? 'Remove \""+ word +"\" from the list?' : 'Supprimer \""+ word +"\" de la liste ?'; ?>");
    }
</script>
    <?php
}
include('../includes/footer.php');
mysql_close();
?>
</body>
</html>
