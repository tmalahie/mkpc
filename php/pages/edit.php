<?php
include('../includes/language.php');
include('../includes/session.php');
include('../includes/initdb.php');
?>
<!DOCTYPE html>
<html lang="<?php echo $language ? 'en':'fr'; ?>">
<head>
<title><?= _('Mario Kart PC Forum') ?></title>
<?php
include('../includes/heads.php');
?>
<link rel="stylesheet" type="text/css" href="styles/forum.css" />
<link rel="stylesheet" type="text/css" href="styles/profil.css" />

<?php
include('../includes/o_online.php');
?>
</head>
<body>
<?php
include('../includes/header.php');
?>
<?php
$page = 'forum';
include('../includes/menu.php');
?>
<main>
	<?php
	if ($id) {
		include('../includes/smileys.php');
		?>
<h1><?php echo $language ? 'Edit':'Modifier'; ?></h1>
<?php
require_once('../includes/utils-ads.php');
showRegularAdSection();
?>
		<?php
		include('../includes/category_fields.php');
		$category = mysql_fetch_array(mysql_query('SELECT mkcategories.id,'. $categoryFields .' FROM `mkcategories` INNER JOIN `mktopics` ON category=mkcategories.id WHERE mktopics.id="'. $_GET['topic'] .'"'));
		$getBanned = mysql_query('SELECT banned FROM `mkjoueurs` WHERE id="'. $id .'"');
		$showForm = true;
		if (($banned=mysql_fetch_array($getBanned)) && $banned['banned'])
			include('../includes/ban_msg.php');
		elseif (isset($_POST['message']) && (trim($_POST['message'])!=='')) {
			require_once('../includes/getRights.php');
			require_once('../includes/forum-checks.php');
			$lastMessage = mysql_fetch_array(mysql_query('SELECT auteur,message FROM `mkmessages` WHERE id="'. $_GET['id'] .'" AND topic="'. $_GET['topic'] .'"'));
			if (($checks=checkMessageContent($_POST['message'])) && !$checks['success'])
				printCheckFailDetails($checks);
			elseif (($lastMessage['auteur'] == $id) || hasRight('moderator')) {
				$showForm = false;
				mysql_query('UPDATE `mkmessages` SET message="'. $_POST['message'] .'" WHERE id="'. $_GET['id'] .'" AND topic="'. $_GET['topic'].'"');
				if ($lastMessage['auteur'] != $id)
					mysql_query('INSERT INTO `mklogs` VALUES(NULL,NULL, '. $id .', "Edit '. $_GET['topic'] .' '. $_GET['id'] .'")');
				preg_match_all('#\B@([a-zA-Z0-9\-_]+?)#isU', stripcslashes($_POST['message']), $mentions);
				preg_match_all('#\B@([a-zA-Z0-9\-_]+?)#isU', $lastMessage['message'], $mentions0);
				function array_rmvalue(&$arr,&$val) {
					if (($key = array_search($val, $arr)) !== false)
					    unset($arr[$key]);
				}
				$oldMentions = array_unique($mentions0[1]);
				foreach ($mentions[1] as $pseudo)
					array_rmvalue($oldMentions,$pseudo);
				$newMentions = array_unique($mentions[1]);
				foreach ($mentions0[1] as $pseudo)
					array_rmvalue($newMentions,$pseudo);
				foreach ($oldMentions as $pseudo) {
					$getMids = mysql_query('SELECT id FROM `mkjoueurs` WHERE id!='. $id .' AND nom="'. $pseudo .'"');
					if ($getMid=mysql_fetch_array($getMids))
						mysql_query('DELETE FROM `mknotifs`  WHERE type="forum_mention" AND user="'. $getMid['id'] .'" AND link="'.$_GET['topic'].','. ($_GET['id']) .'"');
				}
				foreach ($newMentions as $pseudo) {
					$getMids = mysql_query('SELECT id FROM `mkjoueurs` WHERE id!='. $id .' AND nom="'. $pseudo .'"');
					if ($getMid=mysql_fetch_array($getMids))
						mysql_query('INSERT INTO `mknotifs`  SET type="forum_mention", user="'. $getMid['id'] .'", link="'.$_GET['topic'].','. ($_GET['id']) .'"');
				}
				preg_match_all('#\[quote=(.+)\].*\[\/quote\]#isU', stripcslashes($_POST['message']), $quotes);
				preg_match_all('#\[quote=(.+)\].*\[\/quote\]#isU', $lastMessage['message'], $quotes0);
				$oldQuotes = array_unique($quotes0[1]);
				foreach ($quotes[1] as $pseudo)
					array_rmvalue($oldQuotes,$pseudo);
				$newQuotes = array_unique($quotes[1]);
				foreach ($quotes0[1] as $pseudo)
					array_rmvalue($newQuotes,$pseudo);
				foreach ($oldQuotes as $pseudo) {
					$getMids = mysql_query('SELECT id FROM `mkjoueurs` WHERE id!='. $id .' AND nom="'. $pseudo .'"');
					if ($getMid=mysql_fetch_array($getMids))
						mysql_query('DELETE FROM `mknotifs`  WHERE type="forum_quote" AND user="'. $getMid['id'] .'" AND link="'.$_GET['topic'].','. ($_GET['id']) .'"');
				}
				foreach ($newQuotes as $pseudo) {
					$getMids = mysql_query('SELECT id FROM `mkjoueurs` WHERE id!='. $id .' AND nom="'. $pseudo .'"');
					if ($getMid=mysql_fetch_array($getMids))
						mysql_query('INSERT INTO `mknotifs`  SET type="forum_quote", user="'. $getMid['id'] .'", link="'.$_GET['topic'].','. ($_GET['id']) .'"');
				}
				echo $language ? '<p id="successSent">Message edited successfully<br />
				<a href="topic.php?topic='. urlencode($_GET['topic']).'&amp;page='. ceil(mysql_numrows(mysql_query('SELECT * FROM `mkmessages` WHERE topic="'. $_GET['topic'] .'" AND id<="'. $_GET['id'] .'"'))/20) .'">Click here</a> to go to the topic.<br />
				<a href="category.php?category='. $category['id'] .'">Click here</a> to return to the category.<br />
				<a href="forum.php">Click here</a> to return to the forum.</p>' :
				'<p id="successSent">Message modifi&eacute; avec succ&egrave;s<br />
				<a href="topic.php?topic='. urlencode($_GET['topic']) .'&amp;page='. ceil(mysql_numrows(mysql_query('SELECT * FROM `mkmessages` WHERE topic="'. $_GET['topic'] .'" AND id<="'. $_GET['id'] .'"'))/20) .'">Cliquez ici</a> pour acc&eacute;der au topic.<br />
				<a href="category.php?category='. $category['id'] .'">Cliquez ici</a> pour retourner à la catégorie.<br />
				<a href="forum.php">Cliquez ici</a> pour retourner au forum.</p>';
			}
			else
				echo '<p style="text-align: center">'. ($language ? 'Error while editing message.':'Erreur lors de la modification du message.') .'</p>';
		}
		if ($showForm) {
			$getMessage = mysql_fetch_array(mysql_query('SELECT message FROM `mkmessages` WHERE id="'. $_GET['id'] .'" AND topic="'. $_GET['topic'].'"'));
		?>
<form method="post" action="edit.php?id=<?php echo urlencode($_GET['id']); ?>&amp;topic=<?php echo urlencode($_GET['topic']); ?>" onsubmit="this.querySelector('[type=submit]').disabled=true">
<table id="nMessage">
<tr><td class="mLabel"><?= _('BBcode:') ?><br /><a href="javascript:helpBbCode()"><?php echo $language ? 'Help':'Aide'; ?></a></td><td><?php include('../includes/bbButtons.php'); ?></td></tr>
<tr><td class="mLabel"><p><label for="message"><?= _('Message:'); ?></label></p>
<p><?php
for ($i=0;$i<$nbSmileys;$i++)
	echo ' <a href="javascript:ajouter(\''. $smileys[$i] .'\')"><img src="images/smileys/smiley'. $i .'.png" alt="'. $smileys[$i] .'" /></a> ';
?>
<a href="javascript:moresmileys()" id="more-smileys"><?php echo $language ? 'More smileys':'Plus de smileys'; ?></a></p>
</td><td class="mInput"><textarea name="message" id="message" rows="10" required><?php
	if (isset($_POST['message']))
		echo htmlspecialchars($_POST['message']);
	else
		echo htmlspecialchars($getMessage['message']);
?></textarea></td></tr>
<tr><td colspan="2" class="mLabel"><input type="button" value="<?php echo $language ? 'Preview':'Aper&ccedil;u'; ?>" onclick="apercu()" /> &nbsp; <input type="submit" value="<?php echo $language ? 'Send':'Envoyer'; ?>" /></td></tr>
</table>
</form>
<?php
include('../includes/preview-msg.php');
?>
<p class="forumButtons" style="margin: 10px 0 0 23%">
	<a href="topic.php?topic=<?php echo urlencode($_GET['topic']); ?>"><?php echo $language ? 'Back to the topic':'Retour au topic'; ?></a><br />
	<a href="category.php?category=<?php echo $category['id']; ?>"><?php echo $language ? 'Back to '. $category['nom']:'Retour à '. $category['nom']; ?></a><br />
	<a href="forum.php"><?php echo $language ? 'Back to the forum':'Retour au forum'; ?></a></p>
			<?php
		}
	}
	else
		include('../includes/needCo.php');
?>
</main>
<?php
include('../includes/footer.php');
mysql_close();
?>
<script type="text/javascript" src="scripts/msg.php"></script>
</body>
</html>