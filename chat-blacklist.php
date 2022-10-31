<?php
include('session.php');
if (!$id) {
	echo "Vous n'&ecirc;tes pas connect&eacute;";
	exit;
}
include('language.php');
include('initdb.php');
if (!$id) {
	echo "Vous n'&ecirc;tes pas connect&eacute;";
	mysql_close();
	exit;
}
require_once('getRights.php');
if (!hasRight('moderator')) {
	echo "Vous n'&ecirc;tes pas mod&eacute;rateur";
	mysql_close();
	exit;
}
if (!empty($_POST['word']) && isset($_POST['action'])) {
    mysql_query('INSERT INTO mkbadwords SET word="'. strtolower($_POST['word']) .'",action="'.$_POST['action'].'" ON DUPLICATE KEY UPDATE action=VALUES(action)');
    $wordId = mysql_insert_id();
    if ($wordId)
        mysql_query('INSERT INTO `mklogs` VALUES(NULL,NULL, '. $id .', "Blacklist '. $wordId .'")');
}
elseif (isset($_GET['del'])) {
    mysql_query('DELETE FROM mkbadwords WHERE id="'. $_GET['del'] .'"');
    mysql_query('INSERT INTO `mklogs` VALUES(NULL,NULL, '. $id .', "Unblacklist '. $_GET['del'] .'")');
}
?>
<!DOCTYPE html>
<html lang="<?php echo $language ? 'en':'fr'; ?>">
<head>
<title><?php echo $language ? 'Online chat blacklist':'Blacklist chat en ligne'; ?> - Mario Kart PC</title>
<?php
include('heads.php');
?>
<link rel="stylesheet" type="text/css" href="styles/classement.css" />
<style type="text/css">
main tr.clair a.action_button, main tr.fonce a.action_button {
    color: white;
}
form label {
    display: block;
}
form label select {
    width: 200px;
}
form #action-label {
    display: none;
}
form input[type="submit"] {
    margin-top: 5px;
}
</style>
<?php
include('o_online.php');
?>
</head>
<body>
<?php
include('header.php');
$page = 'forum';
include('menu.php');
?>
<main>
	<h1><?php echo $language ? 'Manage forbidden words in online chat':'Gérer les mots surveillés du chat en ligne'; ?></h1>
    <p><?php
    if ($language) {
        ?>
        This page allows you to manage a words blacklist in online mode chat.<br />
        If a user sends a text containing one of these words, you will see it in the <a href="blacklist-logs.php"><strong>message logs</strong></a>.<br />
        Depending on the word, you can decide to just log, block the message, or even mute the member.<br />
        Note that this blacklist does not apply to private games.
        <?php
    }
    else {
        ?>
        Cette page vous permet de gérer une blacklist de mots dans le chat du mode en ligne.<br />
        Si un utilisateur envoie un texte contenant un de ces mots, vous pourrez le voir dans les <a href="blacklist-logs.php"><strong>logs des messages</strong></a>.<br />
        En fonction du mot, vous pouvez décider de juste logguer, bloquer le message, ou carrément muter le membre.<br />
        Notez que cette blacklist ne s'applique pas aux parties privées.
        <?php
    }
    ?>
    </p>
	<form method="post" action="chat-blacklist.php">
        <label>
            <?php echo $language ? 'Add a word:' : 'Ajouter un mot :'; ?>
            <input type="text" name="word" placeholder="fumier" onfocus="showAction()" />
        </label>
        <label id="action-label">
            <?php echo $language ? 'Action:' : 'Action :'; ?>
            <select name="action">
                <option value="none"><?php echo $language ? "None (just log message)" : "Aucune (logguer le message uniquement)"; ?></option>
                <option value="block"><?php echo $language ? "Don't send message" : "Ne pas envoyer le message"; ?></option>
                <option value="mute"><?php echo $language ? "Don't send message + Mute member" : "Ne pas envoyer le message + Muter le membre"; ?></option>
            </select>
        </label>
        <input type="submit" class="action_button" value="<?php echo $language ? 'Validate' : 'Valider'; ?>" />
	</form>
    <h2><?php echo ($language ? 'Current watched word list:' : 'Liste des mots surveillés :'); ?></h2>
    <table>
        <tr id="titres">
            <td style="min-width: 120px"><?php echo $language ? 'Word':'Mot'; ?></td>
            <td>Action</td>
            <td>Options</td>
        </tr>
        <?php
        $getBlacklist = mysql_query('SELECT id,word,action FROM mkbadwords ORDER BY id DESC');
        $i = 0;
        while ($blacklist = mysql_fetch_array($getBlacklist)) {
            $action = null;
            switch ($blacklist['action']) {
            case 'none':
                $action = $language ? 'None' : 'Aucune';
                break;
            case 'block':
                $action = 'Block msg';
                break;
            case 'mute':
                $action = 'Block+Mute';
                break;
            }
            echo '<tr class="'. ($i%2 ? 'fonce':'clair') .'">
                <td>'.$blacklist['word'].'</td>
                <td>'.$action.'</td>
                <td><a class="action_button" href="?del='. $blacklist['id'] .'" onclick="return confirmDelete(&quot;'.htmlspecialchars(addslashes($blacklist['word'])).'&quot;)">'. ($language ? 'Delete':'Supprimer') .'</a></td>
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
        return confirm("<?php echo $language ? 'Remove \\""+ word +"\\" from the list?' : 'Supprimer \\""+ word +"\\" de la liste ?'; ?>");
    }
</script>
<?php
include('footer.php');
mysql_close();
?>
<script type="text/javascript">
function showAction() {
    document.getElementById("action-label").style.display = "block";
}
</script>
</body>
</html>