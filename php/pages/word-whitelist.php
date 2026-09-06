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
if (!hasRight('moderator')) {
	echo "Vous n'&ecirc;tes pas mod&eacute;rateur";
	mysql_close();
	exit;
}
if (!empty($_POST['word'])) {
    $goodWord = strtolower($_POST['word']);
    if (!mysql_fetch_array(mysql_query('SELECT id FROM mkgoodwords WHERE word="'. $goodWord .'"'))) {
        mysql_query('INSERT INTO mkgoodwords SET word="'. $goodWord .'"');
        $wordId = mysql_insert_id();
        if ($wordId)
            insertLog($id, 'Whitelist '. $wordId, array_merge(
                array('type' => 'good_word', 'id' => intval($wordId)),
                snapshotWord('mkgoodwords', $wordId, 'word')
            ));
    }
}
elseif (isset($_GET['del'])) {
    $wordSnapshot = snapshotWord('mkgoodwords', $_GET['del'], 'word');
    mysql_query('DELETE FROM mkgoodwords WHERE id="'. $_GET['del'] .'"');
    insertLog($id, 'Unwhitelist '. $_GET['del'], array_merge(
        array('type' => 'good_word', 'id' => intval($_GET['del'])),
        $wordSnapshot
    ));
}
?>
<!DOCTYPE html>
<html lang="<?php echo $language ? 'en':'fr'; ?>">
<head>
<title><?php echo $language ? 'Allowed words':'Mots autorisés'; ?> - Mario Kart PC</title>
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
form input[type="submit"] {
    margin-top: 5px;
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
?>
<main>
    <h1><?php echo $language ? 'Manage allowed words':'Gérer les mots autorisés'; ?></h1>
    <p><?php
    if ($language) {
        ?>
        A word listed here is never considered inappropriate, neither in usernames nor in the online chat.<br />
        It is useful when a forbidden word is hidden inside an innocent one: <em>cucumber</em> contains <em>cum</em>, and <em>baisse</em> is read as <em>baise</em> with a repeated letter.<br />
        The rest of the text is still checked: <em>cucumbercum</em> remains forbidden.<br />
        Manage the forbidden words themselves on the <a href="nick-blacklist.php"><strong>username blacklist</strong></a> and the <a href="chat-blacklist.php"><strong>online chat blacklist</strong></a>.
        <?php
    }
    else {
        ?>
        Un mot listé ici n'est jamais considéré comme inapproprié, ni dans les pseudos ni dans le chat en ligne.<br />
        C'est utile quand un mot interdit se cache dans un mot innocent : <em>cucumber</em> contient <em>cum</em>, et <em>baisse</em> est lu comme <em>baise</em> avec une lettre doublée.<br />
        Le reste du texte reste vérifié : <em>cucumbercum</em> est toujours interdit.<br />
        Gérez les mots interdits eux-mêmes sur la <a href="nick-blacklist.php"><strong>blacklist des pseudos</strong></a> et la <a href="chat-blacklist.php"><strong>blacklist du chat en ligne</strong></a>.
        <?php
    }
    ?>
    </p>
	<form method="post" action="word-whitelist.php">
        <label>
            <?php echo $language ? 'Add a word:' : 'Ajouter un mot :'; ?>
            <input type="text" name="word" placeholder="cucumber" required="required" />
        </label>
        <input type="submit" class="action_button" value="<?php echo $language ? 'Confirm' : 'Valider'; ?>" />
	</form>
    <h2><?php echo ($language ? 'Current allowed word list:' : 'Liste des mots autorisés :'); ?></h2>
    <table>
        <tr id="titres">
            <td style="min-width: 120px"><?php echo $language ? 'Word':'Mot'; ?></td>
            <td>Options</td>
        </tr>
        <?php
        $getWhitelist = mysql_query('SELECT id,word FROM mkgoodwords ORDER BY id DESC');
        $i = 0;
        while ($whitelist = mysql_fetch_array($getWhitelist)) {
            echo '<tr class="'. ($i%2 ? 'fonce':'clair') .'">
                <td>'.htmlspecialchars($whitelist['word']).'</td>
                <td><a class="action_button action_delete" href="?del='. $whitelist['id'] .'" onclick="return confirmDelete(&quot;'.htmlspecialchars(addslashes($whitelist['word'])).'&quot;)">'. ($language ? 'Delete':'Supprimer') .'</a></td>
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
include('../includes/footer.php');
mysql_close();
?>
</body>
</html>
