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
if (!empty($_POST['word'])) {
    mysql_query('INSERT IGNORE INTO mkbadwords SET word="'. strtolower($_POST['word']) .'"');
    $wordId = mysql_insert_id();
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
<title><?php echo $language ? 'Double accounts':'Double comptes'; ?> - Mario Kart PC</title>
<?php
include('heads.php');
?>
<link rel="stylesheet" type="text/css" href="styles/classement.css" />
<style type="text/css">
main tr.clair a.action_button, main tr.fonce a.action_button {
    color: white;
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
	<h1><?php echo $language ? 'Manage forbidden words in online chat':'Gérer les mots interdits du chat en ligne'; ?></h1>
	<form method="post" action="chat-blacklist.php">
	<blockquote>
        <?php echo $language ? 'Add banned word:' : 'Ajouter un mot :'; ?>
        <input type="text" name="word" />
        <input type="submit" class="action_button" value="Ok" />
	</blockquote>
	</form>
    <h2><?php echo ($language ? 'Banned words:' : 'Mots bannis :'); ?></h2>
    <table>
        <tr id="titres">
            <td style="min-width: 120px"><?php echo $language ? 'Word':'Mot'; ?></td>
            <td>Action</td>
        </tr>
        <?php
        $getBlacklist = mysql_query('SELECT id,word FROM mkbadwords ORDER BY id DESC');
        $i = 0;
        while ($blacklist = mysql_fetch_array($getBlacklist)) {
            echo '<tr class="'. ($i%2 ? 'fonce':'clair') .'">
                <td>'.$blacklist['word'].'</td>
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
</body>
</html>